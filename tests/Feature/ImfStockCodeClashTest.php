<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Constants\Status;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\InventoryRequestItems;
use App\Models\Ecommerce\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A stock code the MCD Planner types may already belong to an item in the
 * master file. The endorsement is refused, and the Planner is shown what the
 * code clashes with on the IMF screen itself - the toast that carried the
 * message before was gone before it could be read, let alone acted on.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class ImfStockCodeClashTest extends TestCase
{
    private const PLANNER_ID = 5;   // MCD Planner

    /** @var int */
    private $errorReporting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(SecureHeaders::class);

        $this->errorReporting = error_reporting();
        error_reporting($this->errorReporting & ~E_WARNING);

        config(['database.connections.sqlsrv.database' => 'PMC-ECOM-TEST']);
        DB::purge('sqlsrv');
        DB::reconnect('sqlsrv');
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        error_reporting($this->errorReporting);
        parent::tearDown();
    }

    private function makeImf(array $overrides = [])
    {
        return InventoryRequest::create(array_merge([
            'user_id'    => self::PLANNER_ID,
            'status'     => Status::VERIFIED_MCD,
            'department' => 'MAINTENANCE',
            'section'    => 'ELECTRICAL',
            'division'   => 'MILL',
            'priority'   => 'HIGH',
            'type'       => 'new',
        ], $overrides));
    }

    private function makeItem($imf, array $overrides = [])
    {
        return InventoryRequestItems::create(array_merge([
            'imf_no'           => $imf->id,
            'stock_code'       => null,
            'item_description' => 'Test IMF item',
            'brand'            => 'ACME',
            'OEM_ID'           => 'OEM-123',
            'UoM'              => 'PC',
            'usage_rate_qty'   => 4,
            'usage_frequency'  => 'MONTHLY',
            'min_qty'          => 1,
            'max_qty'          => 10,
            'purpose'          => 'Testing',
        ], $overrides));
    }

    private function makeProduct($code, array $overrides = [])
    {
        return Product::create(array_merge([
            'category_id' => 29,
            'code'        => $code,
            'name'        => 'Existing bearing 6205',
            'description' => 'Existing bearing 6205',
            'brand'       => 'SKF',
            'uom'         => 'PC',
            'slug'        => 'existing-' . $code,
            'status'      => 'PUBLISHED',
            'created_by'  => 1,
        ], $overrides));
    }

    private function userWithRole($roleName, $email)
    {
        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role, $roleName . ' role missing - run the role migration.');

        return User::create([
            'name'      => 'Test ' . $roleName,
            'email'     => $email,
            'password'  => bcrypt('secret'),
            'role_id'   => $role->id,
            'is_active' => 1,
        ]);
    }

    /**
     * The Planner's endorsement is refused and the clashing item is handed to
     * the screen, not just named in a one-line error.
     */
    public function test_endorsing_a_taken_stock_code_returns_the_clashing_item_details()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $product = $this->makeProduct($code);
        $imf     = $this->makeImf();
        $item    = $this->makeItem($imf);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => $code]],
            ]);

        $response->assertRedirect(route('imf.requests.view', $imf->id));
        $response->assertSessionHas('error');

        // The IMF stays where it was - nothing was endorsed.
        $this->assertEquals(Status::VERIFIED_MCD, $imf->fresh()->status);

        $conflicts = session('stock_code_conflicts');
        $this->assertIsArray($conflicts);
        $this->assertCount(1, $conflicts);

        $clash = $conflicts[0];
        $this->assertEquals(1, $clash['line']);
        $this->assertEquals($code, $clash['stock_code']);
        $this->assertEquals('Test IMF item', $clash['item_description']);
        $this->assertEquals($product->description, $clash['product_name']);
        $this->assertEquals('SKF', $clash['product_brand']);
        $this->assertEquals('PC', $clash['product_uom']);
    }

    /** A free code still goes through - the guard only stops real clashes. */
    public function test_a_free_stock_code_endorses_without_an_alert()
    {
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => 'FREE-' . strtoupper(substr(uniqid(), -8))]],
            ]);

        $response->assertSessionMissing('stock_code_conflicts');
        $this->assertEquals(Status::APPROVED_MCD, $imf->fresh()->status);
    }

    /**
     * The refused endorsement lands back on the IMF screen with a red alert
     * under the coding grid naming the item that already holds the code.
     */
    public function test_the_imf_screen_renders_the_red_alert_under_the_coding_grid()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code, ['description' => 'Existing V-belt B-52']);
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $planner = User::find(self::PLANNER_ID);

        $this->actingAs($planner)
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => $code]],
            ]);

        $view = $this->actingAs($planner)->get(route('imf.requests.view', $imf->id));

        $view->assertStatus(200);
        $view->assertSee('Stock code already existing', false);
        $view->assertSee('Existing V-belt B-52', false);
        $view->assertSee($code, false);
        // Dismissible, and the offending line is flagged in the grid itself.
        $view->assertSee('imf-clash-close', false);
        $view->assertSee('imf-clash-input', false);
        // The Planner is offered the way out, not left stuck on the code.
        $view->assertSee('imfOverrideStockCode()', false);
        $view->assertSee('The item already exists', false);
    }

    /**
     * "Proceed anyway" is refused without a reason - the Supervisor has to be
     * able to see why the Planner ruled the way they did.
     */
    public function test_proceeding_without_a_reason_is_refused()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code);
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action'              => 'approve',
                'type'                => 'new',
                'lines'               => [$item->id => ['stock_code' => $code]],
                'stock_code_override' => '1',
                'stock_code_override_note' => '   ',
            ]);

        $this->assertEquals(Status::VERIFIED_MCD, $imf->fresh()->status);
        $this->assertFalse((bool) $item->fresh()->stock_code_override);
    }

    /**
     * The Planner rules that the item is already in Classic: the line is
     * acknowledged, the IMF moves on, and the trail records who and why.
     */
    public function test_the_planner_can_proceed_and_the_decision_is_recorded()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code);
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf);

        $planner = User::find(self::PLANNER_ID);

        $this->actingAs($planner)
            ->post(route('imf.action', $imf->id), [
                'action'                   => 'approve',
                'type'                     => 'new',
                'lines'                    => [$item->id => ['stock_code' => $code]],
                'stock_code_override'      => '1',
                'stock_code_override_note' => 'Already registered in Classic - raised as new by mistake.',
            ]);

        $this->assertEquals(Status::APPROVED_MCD, $imf->fresh()->status);

        $item = $item->fresh();
        $this->assertTrue((bool) $item->stock_code_override);
        $this->assertEquals('Already registered in Classic - raised as new by mistake.', $item->stock_code_override_note);
        $this->assertEquals($planner->name, $item->stock_code_override_by);
        $this->assertNotNull($item->stock_code_override_at);

        // The Supervisor can read the decision off the trail, not just the line.
        $trail = $imf->fresh()->histories()->where('action', 'acknowledged')->get();
        $this->assertCount(1, $trail);
        $this->assertStringContainsString($code, $trail->first()->title);
        $this->assertStringContainsString('Already registered in Classic', $trail->first()->remarks);
    }

    /** The Supervisor sees the ruling on the line before signing. */
    public function test_the_supervisor_sees_the_acknowledgement_on_the_line()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code);
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $this->makeItem($imf, [
            'stock_code'               => $code,
            'stock_code_override'      => 1,
            'stock_code_override_note' => 'Item exists in Classic already.',
            'stock_code_override_by'   => 'J. Cruz',
            'stock_code_override_at'   => now(),
        ]);

        $supervisor = $this->userWithRole('Planning Supervisor', 'clash.supervisor.sees@example.com');

        $view = $this->actingAs($supervisor)->get(route('imf.requests.view', $imf->id));

        $view->assertStatus(200);
        $view->assertSee('Will update the existing item', false);
        $view->assertSee('J. Cruz', false);
        $view->assertSee('Item exists in Classic already.', false);
    }

    /**
     * On final approval an acknowledged line updates the item already on file.
     * products.code has no unique index, so inserting a second row would go in
     * silently and leave every lookup by code with two rows to choose from.
     */
    public function test_an_acknowledged_line_updates_the_existing_item_instead_of_duplicating_it()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $product = $this->makeProduct($code, ['description' => 'Stale description', 'brand' => 'OLD']);
        $imf     = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $item    = $this->makeItem($imf, [
            'stock_code'               => $code,
            'item_description'         => 'Bearing 6205 2RS',
            'brand'                    => 'SKF',
            'stock_code_override'      => 1,
            'stock_code_override_note' => 'Item exists in Classic already.',
            'stock_code_override_by'   => 'J. Cruz',
            'stock_code_override_at'   => now(),
        ]);

        $supervisor = $this->userWithRole('Planning Supervisor', 'clash.supervisor.applies@example.com');

        $this->actingAs($supervisor)
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new']);

        $this->assertEquals(Status::APPROVED_SUPERVISOR, $imf->fresh()->status);

        // Still exactly one product under that code, carrying the IMF's details.
        $this->assertEquals(1, Product::where('code', $code)->count(), 'A duplicate product was created.');

        $product = $product->fresh();
        $this->assertEquals('Bearing 6205 2RS', $product->description);
        $this->assertEquals('SKF', $product->brand);
        // And the line points at the item it updated.
        $this->assertEquals($product->id, $item->fresh()->product_id);
    }

    /** Retyping the stock code withdraws the acknowledgement given for the old one. */
    public function test_editing_the_stock_code_withdraws_the_acknowledgement()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code);
        $imf  = $this->makeImf();
        $item = $this->makeItem($imf, [
            'stock_code'               => $code,
            'stock_code_override'      => 1,
            'stock_code_override_note' => 'Item exists in Classic already.',
            'stock_code_override_by'   => 'J. Cruz',
            'stock_code_override_at'   => now(),
        ]);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'save',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => 'TYPO-FIXED-' . strtoupper(substr(uniqid(), -6))]],
            ]);

        $item = $item->fresh();
        $this->assertFalse((bool) $item->stock_code_override, 'The acknowledgement outlived the code it was given for.');
        $this->assertNull($item->stock_code_override_note);
        $this->assertNull($item->stock_code_override_by);
    }

    /**
     * An update IMF points at a product that is meant to exist, so its code is
     * never treated as a clash.
     */
    public function test_an_update_imf_is_not_flagged_for_using_an_existing_code()
    {
        $code = 'CLASH-' . strtoupper(substr(uniqid(), -8));

        $this->makeProduct($code);
        $imf = $this->makeImf(['type' => 'update', 'status' => Status::APPROVED_MCD]);
        $this->makeItem($imf, ['stock_code' => $code]);

        $supervisor = $this->userWithRole('Planning Supervisor', 'clash.supervisor.test@example.com');

        $response = $this->actingAs($supervisor)
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'update']);

        $response->assertSessionMissing('stock_code_conflicts');
        $this->assertEquals(Status::APPROVED_SUPERVISOR, $imf->fresh()->status);
    }
}
