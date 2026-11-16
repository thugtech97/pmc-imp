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
 * Purchasing Officer re-assigns an MRS (PA-DP) from one canvasser to another.
 *
 *  - received / waiting: goes to the new canvasser as "(For Purchasing Receival)".
 *  - held by the canvasser: only the canvasser changes; it stays with the MCD Planner,
 *    and the planner's Update sends it to the NEW canvasser.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class MrsCanvasserReassignTest extends TestCase
{
    private const PLANNER_ID   = 5;   // MCD Planner
    private const PURCHASER_ID = 15;  // Purchaser / canvasser (the original one)
    private const PRODUCT_ID   = 42611;

    /** @var \App\Models\User */
    private $officer;
    /** @var \App\Models\User */
    private $otherPurchaser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(SecureHeaders::class);

        config(['database.connections.sqlsrv.database' => 'PMC-ECOM-TEST']);
        DB::purge('sqlsrv');
        DB::reconnect('sqlsrv');
        DB::beginTransaction();

        $this->officer        = User::withRole('Purchasing Officer')->first();
        $this->otherPurchaser = User::withRole('Purchaser')->where('id', '!=', self::PURCHASER_ID)->first();
        $this->assertNotNull($this->officer, 'test DB needs a Purchasing Officer');
        $this->assertNotNull($this->otherPurchaser, 'test DB needs a second Purchaser');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function makeReceivedMrs($qtyOrdered = 0)
    {
        $mrs = SalesHeader::create([
            'user_id'                  => self::PLANNER_ID,
            'order_number'             => 'TEST-REASSIGN-' . uniqid(),
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
            'qty_ordered'      => $qtyOrdered,
            'po_no'            => $qtyOrdered ? 'PO-123' : null,
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

    private function reassign($mrs, $to, $as = null, $reason = 'not his line of items')
    {
        return $this->actingAs($as ?: $this->officer)
            ->post(route('pa.reassign_mrs', $mrs->id), ['canvasser_id' => $to, 'reason' => $reason]);
    }

    public function test_received_mrs_goes_to_new_canvasser_for_receival()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs(4);

        $this->reassign($mrs, $this->otherPurchaser->id)
            ->assertRedirect(route('pa.index', ['view' => 'assigned']))
            ->assertSessionHas('success');

        $fresh = SalesHeader::find($mrs->id);
        $this->assertSame('(For Purchasing Receival)', $fresh->status);
        $this->assertEquals($this->otherPurchaser->id, (int) $fresh->received_by);
        $this->assertNull($fresh->received_at, 'the new canvasser must receive it');

        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertSame('(For Purchasing Receival)', $freshPa->status);
        $this->assertEquals($this->otherPurchaser->id, (int) $freshPa->received_by);
        $this->assertNull($freshPa->received_at);

        // Canvass work already done is kept.
        $freshDetail = SalesDetail::find($detail->id);
        $this->assertSame('PO-123', $freshDetail->po_no);
        $this->assertEquals(4, (int) $freshDetail->qty_ordered);

        // Audit trail names both canvassers and the reason.
        $log = DB::table('document_histories')
            ->where('document_id', $mrs->id)->where('document_type', 'MRS')
            ->orderBy('id', 'desc')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('Re-assigned from', $log->title);
        $this->assertSame('not his line of items', $log->remarks);
    }

    public function test_held_mrs_keeps_hold_and_planner_update_routes_to_new_canvasser()
    {
        list($mrs, $detail, $pa) = $this->makeReceivedMrs();

        // Canvasser holds it ("not mine").
        $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $mrs->id, 'action' => 'hold-purchaser', 'note' => 'not mine']));
        $this->assertSame('HOLD (For MCD Planner re-edit)', SalesHeader::find($mrs->id)->status);

        $this->reassign($mrs, $this->otherPurchaser->id)->assertSessionHas('success');

        $fresh = SalesHeader::find($mrs->id);
        $this->assertSame('HOLD (For MCD Planner re-edit)', $fresh->status, 'stays with the planner');
        $this->assertEquals($this->otherPurchaser->id, (int) $fresh->received_by);
        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertSame('HOLD (For MCD Planner re-edit)', $freshPa->status);
        $this->assertEquals(1, (int) $freshPa->is_hold);
        $this->assertEquals($this->otherPurchaser->id, (int) $freshPa->received_by);

        // Planner updates -> straight to the NEW canvasser.
        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('mrs.update'), [
                'sales_header_id'               => $mrs->id,
                'planner_remarks'               => 'moved to the right canvasser',
                'quantityToOrder' . $detail->id => 10,
            ])->assertSessionHas('success');

        $fresh = SalesHeader::find($mrs->id);
        $this->assertSame('(For Purchasing Receival)', $fresh->status);
        $this->assertEquals($this->otherPurchaser->id, (int) $fresh->received_by);
        $freshPa = PurchaseAdvice::find($pa->id);
        $this->assertEquals($this->otherPurchaser->id, (int) $freshPa->received_by);
        $this->assertEquals(0, (int) $freshPa->is_hold);
    }

    public function test_reason_is_required()
    {
        list($mrs) = $this->makeReceivedMrs();

        $this->reassign($mrs, $this->otherPurchaser->id, null, '')->assertSessionHasErrors('reason');
        $this->assertEquals(self::PURCHASER_ID, (int) SalesHeader::find($mrs->id)->received_by);
    }

    public function test_only_the_purchasing_officer_can_reassign()
    {
        list($mrs) = $this->makeReceivedMrs();

        $this->reassign($mrs, $this->otherPurchaser->id, User::find(self::PURCHASER_ID))->assertStatus(403);
        $this->assertEquals(self::PURCHASER_ID, (int) SalesHeader::find($mrs->id)->received_by);
    }

    public function test_cannot_reassign_to_a_non_purchaser_or_the_same_canvasser()
    {
        list($mrs) = $this->makeReceivedMrs();

        $this->reassign($mrs, self::PLANNER_ID)->assertSessionHas('error');
        $this->reassign($mrs, self::PURCHASER_ID)->assertSessionHas('error');
        $this->assertEquals(self::PURCHASER_ID, (int) SalesHeader::find($mrs->id)->received_by);
    }

    public function test_fully_ordered_mrs_cannot_be_reassigned()
    {
        list($mrs) = $this->makeReceivedMrs(10);

        $this->reassign($mrs, $this->otherPurchaser->id)->assertSessionHas('error');
        $this->assertSame('RECEIVED FOR CANVASS (Purchasing Officer)', SalesHeader::find($mrs->id)->status);
    }

    public function test_old_assign_action_no_longer_overrides_a_hold()
    {
        list($mrs) = $this->makeReceivedMrs();
        $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $mrs->id, 'action' => 'hold-purchaser', 'note' => 'x']));

        $this->actingAs($this->officer)
            ->get(route('mrs.action', ['id' => $mrs->id, 'action' => 'mrs-assign', 'note' => $this->otherPurchaser->id]))
            ->assertSessionHas('error');

        $this->assertSame('HOLD (For MCD Planner re-edit)', SalesHeader::find($mrs->id)->status);
    }

    public function test_assigned_view_lists_held_and_received_mrs_and_page_shows_reassign()
    {
        list($received) = $this->makeReceivedMrs();
        list($held)     = $this->makeReceivedMrs();
        $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('pa.action', ['id' => $held->id, 'action' => 'hold-purchaser', 'note' => 'x']));

        $this->actingAs($this->officer)
            ->get(route('pa.index', ['view' => 'assigned', 'search' => 'TEST-REASSIGN-']))
            ->assertOk()
            ->assertSee($received->order_number)
            ->assertSee($held->order_number);

        $this->actingAs($this->officer)
            ->get(route('pa.view_mrs', $held->id))
            ->assertOk()
            ->assertSee('Re-assign Canvasser')
            ->assertSee('reassignForm')
            ->assertDontSee('id="holdPurchaserBtn"', false);
    }
}
