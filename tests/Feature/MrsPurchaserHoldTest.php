<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\SalesHeader;
use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\PurchaseAdvice;

/**
 * PA-DP (MRS) hold flow: when the canvasser/purchaser holds an MRS he has received,
 * it must go back to the MCD Planner for re-edit — on BOTH the MRS and its PA — and
 * the planner's re-edit must send it straight back to that same canvasser.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class MrsPurchaserHoldTest extends TestCase
{
    private const PLANNER_ID   = 5;   // MCD Planner
    private const PURCHASER_ID = 15;  // Purchaser / canvasser
    private const PRODUCT_ID   = 42611;

    protected function setUp(): void
    {
        parent::setUp();

        // SecureHeaders calls header()/header_remove() directly, which throws once
        // PHPUnit has emitted output. Irrelevant here.
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

    /** An MRS received for canvass, plus its PA. */
    private function makeReceivedMrs()
    {
        $mrs = SalesHeader::create([
            'user_id'                  => self::PLANNER_ID,
            'order_number'             => 'TEST-HOLD-' . uniqid(),
            'customer_delivery_adress' => 'test',
            'delivery_type'            => 'pickup',
            'status'       => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'for_pa'       => 1,
            'is_pa'        => 1,
            'received_by'  => self::PURCHASER_ID,
            'received_at'  => '2026-07-10 09:00:00',
            'verified_at'  => '2026-07-01 09:00:00',
            'approved_at'  => '2026-07-02 09:00:00',
            'planner_by'   => self::PLANNER_ID,
            'planner_at'   => '2026-06-30 09:00:00',
        ]);

        $detail = SalesDetail::create([
            'sales_header_id'  => $mrs->id,
            'product_id'       => self::PRODUCT_ID,
            'product_name'     => 'Test item',
            'product_category' => 'Test',
            'price'            => 0,
            'tax_amount'       => 0,
            'gross_amount'     => 0,
            'net_amount'       => 0,
            'qty'              => 10,
            'uom'              => 'PC',
            'created_by'       => self::PLANNER_ID,
            'qty_to_order'     => 10,
        ]);

        $pa = PurchaseAdvice::create([
            'mrs_id'      => $mrs->id,
            'pa_number'   => 'TEST-PA-' . uniqid(),
            'created_by'  => self::PLANNER_ID,
            'status'      => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
            'is_hold'     => 0,
        ]);

        return [$mrs, $detail, $pa];
    }

    /** Holding a received PA-DP returns BOTH the MRS and its PA to the planner. */
    public function test_purchaser_hold_returns_mrs_and_pa_to_planner()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $mrs->id, 'action' => 'hold-purchaser', 'note' => 'wrong stock code']));

        $response->assertStatus(302);

        $freshMrs = SalesHeader::find($mrs->id);
        $this->assertSame('HOLD (For MCD Planner re-edit)', $freshMrs->status);
        $this->assertSame('wrong stock code', $freshMrs->purchaser_note);
        $this->assertNull($freshMrs->received_at, 'aging must stop while it sits with the planner');
        $this->assertEquals(self::PURCHASER_ID, (int) $freshMrs->received_by, 'canvasser must stay attached for the bypass');

        // The PA must follow, otherwise it stays live in the canvasser's PA queue.
        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertSame('HOLD (For MCD Planner re-edit)', $freshPa->status);
        $this->assertEquals(1, (int) $freshPa->is_hold);
        $this->assertNull($freshPa->received_at);
        $this->assertSame('wrong stock code', $freshPa->purchaser_remarks);
    }

    /** After the planner re-edits it, it goes straight back to the same canvasser. */
    public function test_planner_reedit_sends_it_straight_back_to_the_canvasser()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $mrs->id, 'action' => 'hold-purchaser', 'note' => 'wrong stock code']));

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('mrs.update'), [
                'sales_header_id'                => $mrs->id,
                'planner_remarks'                => 'stock code corrected',
                'quantityToOrder' . $detail->id  => 8,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $freshMrs = SalesHeader::find($mrs->id);
        $this->assertSame('(For Purchasing Receival)', $freshMrs->status, 'must bypass verify/approve/assign');
        $this->assertEquals(self::PURCHASER_ID, (int) $freshMrs->received_by);
        $this->assertNull($freshMrs->received_at, 'canvasser still has to click Receive');
        $this->assertEquals(1, (int) $freshMrs->revision, 'a re-edit after a hold is a revision');
        // Bypassed stages keep their stamps so the printed PA still shows its posted date.
        $this->assertNotNull($freshMrs->verified_at);
        $this->assertNotNull($freshMrs->approved_at);

        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertSame('(For Purchasing Receival)', $freshPa->status);
        $this->assertEquals(0, (int) $freshPa->is_hold, 'hold must be lifted so the PA is printable again');
        $this->assertEquals(self::PURCHASER_ID, (int) $freshPa->received_by);
        $this->assertEquals(1, (int) $freshPa->revision);
    }

    /**
     * The longer detour: canvasser holds -> planner bounces it to the department user ->
     * the user revises -> the planner saves. It must still land back on the canvasser, and
     * the department user's revision must not be counted twice.
     */
    public function test_bypass_survives_the_detour_through_the_department_user()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        // 1. Canvasser holds it back to the planner.
        $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $mrs->id, 'action' => 'hold-purchaser', 'note' => 'wrong stock code']));

        // 2. Planner bounces it to the department user.
        $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('mrs.action', ['id' => $mrs->id, 'action' => 'hold-planner', 'note' => 'confirm the stock code']));

        $held = SalesHeader::find($mrs->id);
        $this->assertSame('REQUEST ON HOLD (Hold by MCD Planner)', $held->status);
        $this->assertEquals(self::PURCHASER_ID, (int) $held->received_by, 'canvasser link must survive the bounce');

        // 3. Department user revises (mirrors MyAccountController's hold branch).
        $held->update([
            'status'     => 'REVISED MRS - ' . date('Y-m-d h:i:s A'),
            'revision'   => (int) $held->revision + 1,
            'revised_at' => now(),
        ]);
        $revisionAfterUserEdit = (int) SalesHeader::find($mrs->id)->revision;

        // 4. Planner saves.
        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('mrs.update'), [
                'sales_header_id'               => $mrs->id,
                'planner_remarks'               => 'checked',
                'quantityToOrder' . $detail->id => 8,
            ]);

        $response->assertStatus(302);

        $freshMrs = SalesHeader::find($mrs->id);
        $this->assertSame('(For Purchasing Receival)', $freshMrs->status, 'must land back on the canvasser');
        $this->assertEquals(self::PURCHASER_ID, (int) $freshMrs->received_by);
        $this->assertEquals($revisionAfterUserEdit, (int) $freshMrs->revision, 'revision must not be double-counted');

        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertSame('(For Purchasing Receival)', $freshPa->status);
        $this->assertEquals(0, (int) $freshPa->is_hold);
    }

    /** A planner hold before any assignment has no canvasser, so it takes the full route. */
    public function test_planner_hold_before_assignment_still_goes_for_verification()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        // Never assigned to anyone, then revised by the department user.
        $mrs->update([
            'status'      => 'REVISED MRS - ' . date('Y-m-d h:i:s A'),
            'received_by' => null,
            'received_at' => null,
        ]);
        $pa->update(['status' => 'APPROVED (MCD PLANNER) - FOR VERIFICATION', 'received_by' => null, 'is_hold' => 0]);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('mrs.update'), [
                'sales_header_id'               => $mrs->id,
                'planner_remarks'               => 'checked',
                'quantityToOrder' . $detail->id => 8,
            ]);

        $this->assertSame(
            'APPROVED (MCD Planner) - MRS For Verification',
            SalesHeader::find($mrs->id)->status
        );
    }

    /** A verifier/approver hold (no canvasser attached) still goes the long way round. */
    public function test_hold_without_a_canvasser_reenters_the_verification_queue()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        $mrs->update([
            'status'      => 'HOLD (For MCD Planner re-edit)',
            'received_by' => null,
            'received_at' => null,
        ]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('mrs.update'), [
                'sales_header_id'               => $mrs->id,
                'planner_remarks'               => 'revised',
                'quantityToOrder' . $detail->id => 8,
            ]);

        $response->assertStatus(302);

        $freshMrs = SalesHeader::find($mrs->id);
        $this->assertSame('APPROVED (MCD Planner) - MRS For Verification', $freshMrs->status);
        $this->assertNull($freshMrs->verified_at, 'stale verification stamp must be cleared');
        $this->assertNull($freshMrs->approved_at, 'stale approval stamp must be cleared');
    }
}
