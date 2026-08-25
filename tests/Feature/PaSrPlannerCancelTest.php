<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\SalesHeader;

/**
 * PA-SR cancellation from the planner listing: only the MCD Planner may pull a
 * stand-alone PA, the reason is mandatory, and the cancel must clear the
 * verification/approval/receiving progress so the PA leaves everyone's queue.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class PaSrPlannerCancelTest extends TestCase
{
    private const PLANNER_ID   = 5;   // MCD Planner
    private const PURCHASER_ID = 15;  // Purchaser / canvasser

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

    /** A stand-alone PA (no MRS behind it) sitting with the canvasser. */
    private function makeSrPa(array $attrs = []): PurchaseAdvice
    {
        return PurchaseAdvice::create(array_merge([
            'pa_number'   => 'TEST-CXL-' . substr(uniqid(), -6),
            'created_by'  => self::PLANNER_ID,
            'status'      => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
            'verified_at' => '2026-07-01 09:00:00',
            'verified_by' => 7,
            'approved_at' => '2026-07-02 09:00:00',
            'approved_by' => 8,
            'is_hold'     => 0,
        ], $attrs));
    }

    /** The planner cancels with a reason: status, remarks and the trail all follow. */
    public function test_planner_cancels_sr_pa_with_reason()
    {
        $pa = $this->makeSrPa();

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.cancel_pa'), [
                'pa_id'  => $pa->id,
                'reason' => 'Requestor withdrew the request.',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $fresh = PurchaseAdvice::find($pa->id);
        $this->assertSame('CANCELLED PURCHASED ADVICE', $fresh->status);
        $this->assertSame('Requestor withdrew the request.', $fresh->planner_remarks);
        $this->assertNull($fresh->verified_at, 'verification stamp should be cleared');
        $this->assertNull($fresh->verified_by);
        $this->assertNull($fresh->approved_at, 'approval stamp should be cleared');
        $this->assertNull($fresh->approved_by);
        $this->assertNull($fresh->received_at, 'the canvasser no longer holds it');
        $this->assertNull($fresh->received_by);

        $logged = DocumentHistory::where('document_type', 'PA')
            ->where('document_id', $pa->id)
            ->where('action', 'cancelled')
            ->first();
        $this->assertNotNull($logged, 'cancellation should be on the audit trail');
        $this->assertSame('SR', $logged->pa_type);
        $this->assertStringContainsString('withdrew', (string) $logged->remarks);
    }

    /** The listing gives the planner the control, wired to the reason modal. */
    public function test_listing_shows_cancel_control_for_planner()
    {
        // Held PAs sort to the top of the planner's page 1, so this row is on screen.
        $pa = $this->makeSrPa(['status' => 'HOLD (For MCD Planner re-edit)', 'is_hold' => 1]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('planner_pa.index', ['pa_type' => 'sr']));

        $response->assertStatus(200);
        $response->assertSee('cancel-pa-modal');
        $response->assertSee('Reason for cancellation');
        $response->assertSee('data-id="' . $pa->id . '" data-number="' . $pa->pa_number . '"', false);
    }

    /** A cancelled PA has nothing left to cancel — the control is gone. */
    public function test_listing_hides_cancel_control_once_cancelled()
    {
        $pa = $this->makeSrPa(['status' => 'CANCELLED PURCHASED ADVICE']);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('planner_pa.index', ['pa_type' => 'sr']));

        $response->assertStatus(200);
        $response->assertDontSee('data-id="' . $pa->id . '" data-number="' . $pa->pa_number . '"', false);
    }

    /** No reason, no cancellation. */
    public function test_reason_is_required()
    {
        $pa = $this->makeSrPa();

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.cancel_pa'), ['pa_id' => $pa->id, 'reason' => '']);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(
            'RECEIVED FOR CANVASS (Purchasing Officer)',
            PurchaseAdvice::find($pa->id)->status,
            'PA must be untouched when the reason is missing'
        );
    }

    /** Anyone other than the MCD Planner is refused. */
    public function test_non_planner_cannot_cancel()
    {
        $pa = $this->makeSrPa();

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->post(route('pa.cancel_pa'), [
                'pa_id'  => $pa->id,
                'reason' => 'not my call',
            ]);

        $response->assertSessionHas('error');
        $this->assertSame(
            'RECEIVED FOR CANVASS (Purchasing Officer)',
            PurchaseAdvice::find($pa->id)->status
        );
    }

    /** A PA raised from an MRS (PA-DP) is out of scope for this control. */
    public function test_pa_dp_is_refused()
    {
        $mrs = SalesHeader::create([
            'user_id'                  => self::PLANNER_ID,
            'order_number'             => 'TEST-CXL-MRS-' . substr(uniqid(), -6),
            'customer_delivery_adress' => 'test',
            'delivery_type'            => 'pickup',
            'status'                   => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'for_pa'                   => 1,
            'is_pa'                    => 1,
        ]);
        $pa = $this->makeSrPa(['mrs_id' => $mrs->id]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.cancel_pa'), [
                'pa_id'  => $pa->id,
                'reason' => 'wrong queue',
            ]);

        $response->assertSessionHas('error');
        $this->assertSame(
            'RECEIVED FOR CANVASS (Purchasing Officer)',
            PurchaseAdvice::find($pa->id)->status
        );
    }

    /** Once cancelled the PA is frozen: update_pa() refuses it. */
    public function test_cancelled_pa_cannot_be_updated()
    {
        $pa = $this->makeSrPa(['status' => 'CANCELLED PURCHASED ADVICE']);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.update'), [
                'pa_id'           => $pa->id,
                'planner_remarks' => 'sneaking an edit in',
            ]);

        $response->assertSessionHas('error');

        $fresh = PurchaseAdvice::find($pa->id);
        $this->assertSame('CANCELLED PURCHASED ADVICE', $fresh->status, 'a cancelled PA must not re-enter the workflow');
        $this->assertNotSame('sneaking an edit in', $fresh->planner_remarks);
    }

    /** ...and so do the workflow actions. */
    public function test_cancelled_pa_cannot_be_verified()
    {
        $pa = $this->makeSrPa(['status' => 'CANCELLED PURCHASED ADVICE']);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('pa.purchase_action', $pa->id) . '?action=verify');

        $response->assertSessionHas('error');

        $fresh = PurchaseAdvice::find($pa->id);
        $this->assertSame('CANCELLED PURCHASED ADVICE', $fresh->status);
        // The verify stamp is whatever it was; the action must not have re-stamped it.
        $this->assertStringStartsWith('2026-07-01', (string) $fresh->verified_at);
    }

    /** The PA-SR view states the cancellation and its reason, and drops Update. */
    public function test_view_shows_cancellation_reason_and_no_update_button()
    {
        $pa = $this->makeSrPa();

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.cancel_pa'), [
                'pa_id'  => $pa->id,
                'reason' => 'Budget pulled for the quarter.',
            ]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('pa.pa_view', $pa->id));

        $response->assertStatus(200);
        $response->assertSee('This Purchase Advice was cancelled');
        $response->assertSee('Budget pulled for the quarter.');
        $response->assertDontSee('fa fa-save', false);   // the Update button is gone
    }

    /** Cancelling twice is a no-op with an explanation. */
    public function test_already_cancelled_pa_is_refused()
    {
        $pa = $this->makeSrPa(['status' => 'CANCELLED PURCHASED ADVICE']);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('pa.cancel_pa'), [
                'pa_id'  => $pa->id,
                'reason' => 'again',
            ]);

        $response->assertSessionHas('error');
        $this->assertNull(PurchaseAdvice::find($pa->id)->planner_remarks);
    }
}
