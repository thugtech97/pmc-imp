<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Constants\Status;
use App\Models\Ecommerce\InventoryRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * The IMF No. (IMP-IMF-YYYYMMDD-####) is the IMF's WFS transid, so no two IMFs
 * may ever share one. The next number is read from IMP; two saves at the same
 * moment can both be handed it, and the unique index plus the retry in
 * InventoryRequest::save() must turn that into the next number, not an error.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class ImfNumberTest extends TestCase
{
    private const REQUESTOR_ID = 5;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function makeImf(array $overrides = [])
    {
        return InventoryRequest::create(array_merge([
            'user_id'    => self::REQUESTOR_ID,
            'status'     => Status::SAVED,
            'department' => 'MAINTENANCE',
            'type'       => 'new',
        ], $overrides));
    }

    public function test_each_new_imf_gets_the_next_number_of_the_day()
    {
        $prefix = 'IMP-IMF-' . now()->format('Ymd') . '-';

        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $numbers[] = $this->makeImf()->imf_number;
        }

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^' . preg_quote($prefix, '/') . '\d{4}$/', $number);
        }
        $this->assertCount(3, array_unique($numbers));

        $series = array_map(function ($number) use ($prefix) {
            return (int) substr($number, strlen($prefix));
        }, $numbers);
        $this->assertSame([$series[0], $series[0] + 1, $series[0] + 2], $series);
    }

    public function test_the_series_restarts_each_day()
    {
        $this->assertSame('IMP-IMF-20991231-0001', InventoryRequest::nextImfNumber('2099-12-31'));
    }

    public function test_a_number_already_taken_is_replaced_by_the_next_free_one()
    {
        // What a same-moment save looks like: handed a number another IMF has.
        $first = $this->makeImf();
        $second = $this->makeImf(['imf_number' => $first->imf_number]);

        $this->assertTrue($second->exists);
        $this->assertNotSame($first->imf_number, $second->imf_number);
        $this->assertSame(1, InventoryRequest::where('imf_number', $first->imf_number)->count());
        $this->assertSame(1, InventoryRequest::where('imf_number', $second->imf_number)->count());
    }

    public function test_the_database_refuses_a_duplicate_number()
    {
        $first = $this->makeImf();
        $second = $this->makeImf();

        $this->expectException(QueryException::class);
        InventoryRequest::where('id', $second->id)->update(['imf_number' => $first->imf_number]);
    }

    public function test_an_old_unnumbered_imf_is_numbered_once()
    {
        $old = $this->makeImf();
        InventoryRequest::where('id', $old->id)->update(['imf_number' => null]);
        $old->refresh();
        $this->assertNull($old->imf_number);
        $this->assertSame('IMF-' . $old->id, $old->display_no);

        $number = $old->assignImfNumber();
        $this->assertStringStartsWith('IMP-IMF-', $number);
        $this->assertSame($number, $old->fresh()->imf_number);

        // A second call keeps the number it already has.
        $this->assertSame($number, $old->assignImfNumber());
    }
}
