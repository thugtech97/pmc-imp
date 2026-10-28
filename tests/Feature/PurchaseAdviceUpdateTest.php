<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\PurchaseAdviceDetail;

/**
 * Covers the two update_pa() fixes:
 *   1. A planner re-edit must NOT wipe the purchaser's current_po / qty_ordered /
 *      po_date_released (those inputs are not rendered on the planner form, so they
 *      arrive absent and must fall back to the stored value).
 *   2. Re-entry into the "FOR VERIFICATION" queue must clear any stale
 *      verified_at / approved_at stamps, otherwise the verifier is locked out.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB. Every test wraps its writes in a
 * transaction that is rolled back in tearDown(), so the DB is left untouched.
 */
class PurchaseAdviceUpdateTest extends TestCase
{
    private const PLANNER_ID   = 5;   // MCD Planner
    private const PURCHASER_ID = 15;  // Purchaser
    private const PRODUCT_ID   = 42611;

    protected function setUp(): void
    {
        parent::setUp();

        // The SecureHeaders middleware calls header_remove()/header() directly, which throws
        // "headers already sent" once PHPUnit has emitted output. It is irrelevant to these
        // tests, so skip it.
        $this->withoutMiddleware(SecureHeaders::class);

        // Point the default connection at the test database, then isolate the whole
        // test in a transaction we roll back afterwards.
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

    private function makeDetail(PurchaseAdvice $pa, array $attrs = []): PurchaseAdviceDetail
    {
        return PurchaseAdviceDetail::create(array_merge([
            'purchase_advice_id' => $pa->id,
            'product_id'         => self::PRODUCT_ID,
            'qty_to_order'       => 10,
        ], $attrs));
    }

    /** Fix #1: planner re-edit of a purchaser-returned PA keeps the canvasser's PO data. */
    public function test_planner_reedit_preserves_purchaser_fields_and_bypasses_to_purchaser()
    {
        // Verified + approved historically, then returned by the purchaser for re-edit
        // (received_at cleared, received_by kept -> the bypass precondition).
        $pa = PurchaseAdvice::create([
            'created_by'  => self::PLANNER_ID,
            'status'      => 'HOLD (For MCD Planner re-edit)',
            'received_by' => self::PURCHASER_ID,
            'received_at' => null,
            'verified_at' => '2026-06-01 09:00:00',
            'approved_at' => '2026-06-02 09:00:00',
            'is_hold'     => 1,
        ]);

        $detail = $this->makeDetail($pa, [
            'qty_to_order'     => 10,
            'qty_ordered'      => 5,
            'current_po'       => 'PO-TEST-123',
            'po_date_released' => '2026-07-01',
        ]);

        // Planner form: sends planner-editable fields only, NOT the purchaser columns.
        $payload = [
            'pa_id'                       => $pa->id,
            'planner_remarks'             => 'planner edited',
            'qty_to_order' . $detail->id  => 10,
        ];

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.update'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $freshDetail = PurchaseAdviceDetail::find($detail->id);
        $this->assertSame('PO-TEST-123', $freshDetail->current_po, 'current_po was wiped');
        $this->assertEquals(5, (int) $freshDetail->qty_ordered, 'qty_ordered was wiped');
        $this->assertNotNull($freshDetail->po_date_released, 'po_date_released was wiped');
        $this->assertStringStartsWith('2026-07-01', (string) $freshDetail->po_date_released);

        // Bypass: goes straight back to the purchaser, not to verification.
        $this->assertSame('(For Purchasing Receival)', PurchaseAdvice::find($pa->id)->status);
    }

    /** Fix #2: normal re-entry into FOR VERIFICATION clears stale verify/approve stamps. */
    public function test_reentry_for_verification_clears_stale_verification_stamps()
    {
        // Bad state: held to planner, no purchaser attached, but carrying stale stamps.
        $pa = PurchaseAdvice::create([
            'created_by'  => self::PLANNER_ID,
            'status'      => 'HOLD (For MCD Planner re-edit)',
            'received_by' => null,
            'received_at' => null,
            'verified_at' => '2026-06-01 09:00:00',
            'verified_by' => 7,
            'approved_at' => '2026-06-02 09:00:00',
            'approved_by' => 8,
            'is_hold'     => 1,
        ]);
        $detail = $this->makeDetail($pa, ['qty_to_order' => 10, 'qty_ordered' => 0]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.update'), [
                'pa_id'                      => $pa->id,
                'planner_remarks'            => 'planner edited',
                'qty_to_order' . $detail->id => 10,
            ]);

        $response->assertStatus(302);

        $fresh = PurchaseAdvice::find($pa->id);
        $this->assertSame('APPROVED (MCD PLANNER) - FOR VERIFICATION', $fresh->status);
        $this->assertNull($fresh->verified_at, 'stale verified_at should be cleared');
        $this->assertNull($fresh->verified_by, 'stale verified_by should be cleared');
        $this->assertNull($fresh->approved_at, 'stale approved_at should be cleared');
        $this->assertNull($fresh->approved_by, 'stale approved_by should be cleared');
    }

    /** Regression guard: qty_ordered may not exceed qty_to_order. */
    public function test_qty_ordered_cannot_exceed_qty_to_order()
    {
        $pa = PurchaseAdvice::create([
            'created_by'  => self::PLANNER_ID,
            'status'      => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
            'is_hold'     => 0,
        ]);
        $detail = $this->makeDetail($pa, ['qty_to_order' => 5, 'qty_ordered' => 0]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.update'), [
                'pa_id'                      => $pa->id,
                'qty_to_order' . $detail->id => 5,
                'qty_ordered' . $detail->id  => 10, // exceeds
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('qty_ordered');

        // Nothing should have been written.
        $this->assertEquals(0, (int) PurchaseAdviceDetail::find($detail->id)->qty_ordered);
    }
}
