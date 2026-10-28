<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ecommerce\SalesHeader;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Support\Facades\DB;

/**
 * An MRS raised today cannot also be needed today.
 *
 * The checkout page used to pre-fill the date needed with today's date and set
 * the input's min to today, so the quickest way through the form produced a
 * request that was already due the moment it was raised — before anyone had
 * even seen it, let alone approved it.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class MrsDateNeededTest extends TestCase
{
    private const DEPT_USER_ID = 21;   // Dept. User (role 6)

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(SecureHeaders::class);

        config(['database.connections.sqlsrv.database' => 'PMC-ECOM-TEST']);
        DB::purge('sqlsrv');
        DB::reconnect('sqlsrv');
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function checkoutPayload(array $overrides = [])
    {
        $user = User::find(self::DEPT_USER_ID);

        return array_merge([
            'department'            => $user->department->name,
            'coupon_total_discount' => 0.00,
            'total_amount'          => 100.00,
            'delivery_fee'          => 0.00,
            'shipping_type'         => 'standard',
            'date_needed'           => now()->addDays(3)->format('Y-m-d'),
            'payment_type'          => 'cash',
            'costcode'              => 'COST123',
            'notes'                 => 'Test order notes.',
            'justification'         => 'Test purpose.',
            'priority'              => 1,
            'section'               => 'Section A',
            'requested_by'          => 'Test User',
            'budgeted_amount'       => 0,
            'codes'                 => ['CODE123'],
            'par_to'                => ['PAR001'],
            'item_date_needed'      => [now()->addDays(7)->format('Y-m-d')],
            'frequency'             => ['Monthly'],
            'item_purpose'          => ['Office Supplies'],
        ], $overrides);
    }

    private function placeRequest(array $overrides = [])
    {
        return $this->actingAs(User::find(self::DEPT_USER_ID))
            ->post(route('cart.temp_sales'), $this->checkoutPayload($overrides));
    }

    /** The case that started this: date needed left at today. */
    public function test_a_request_needed_today_is_refused()
    {
        $before = SalesHeader::count();

        $response = $this->placeRequest(['date_needed' => now()->format('Y-m-d')]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('date needed must be', session('error'));
        $this->assertEquals($before, SalesHeader::count(), 'An MRS was saved despite the date needed being today.');
    }

    /** A date already gone is no better than today's. */
    public function test_a_request_needed_in_the_past_is_refused()
    {
        $before = SalesHeader::count();

        $response = $this->placeRequest(['date_needed' => now()->subDays(2)->format('Y-m-d')]);

        $response->assertSessionHas('error');
        $this->assertEquals($before, SalesHeader::count());
    }

    /** The header date is not the only one — every line carries its own. */
    public function test_a_line_needed_today_is_refused_even_when_the_header_is_fine()
    {
        $before = SalesHeader::count();

        $response = $this->placeRequest([
            'date_needed'      => now()->addDays(5)->format('Y-m-d'),
            'item_date_needed' => [
                now()->addDays(5)->format('Y-m-d'),
                now()->format('Y-m-d'),
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals($before, SalesHeader::count(), 'An MRS was saved despite a line being needed today.');
    }

    /** An empty date cannot be waved through as "no opinion". */
    public function test_a_missing_date_is_refused()
    {
        $before = SalesHeader::count();

        $response = $this->placeRequest(['date_needed' => '']);

        $response->assertSessionHas('error');
        $this->assertEquals($before, SalesHeader::count());
    }

    /** Tomorrow is the soonest a request may be needed, and it goes through. */
    public function test_tomorrow_is_accepted()
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->placeRequest([
            'date_needed'      => $tomorrow,
            'item_date_needed' => [$tomorrow],
        ]);

        $response->assertSessionMissing('error');
        $response->assertRedirect(route('order.success'));
    }

    /** The checkout screen offers the earliest allowed date, not today. */
    public function test_the_checkout_page_floors_the_date_inputs_at_tomorrow()
    {
        $response = $this->actingAs(User::find(self::DEPT_USER_ID))->get(route('cart.front.checkout'));

        $response->assertStatus(200);
        $response->assertSee('EARLIEST_DATE_NEEDED', false);
        // The old behaviour pre-filled and floored the inputs at today.
        $response->assertDontSee("var today = new Date().toISOString().split('T')[0];", false);
    }
}
