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
 * Smoke test for the PA-DP (MRS) view/edit screens after the PA-SR design alignment.
 *
 * Renders each screen for the role that actually uses it, in both the not-yet-received
 * and received states, and asserts the shared design-system markup plus the form fields
 * and JS hooks the page's own scripts bind to. Catches template regressions the Blade
 * compiler cannot (undefined variables, dropped inputs, renamed ids).
 */
class PaDpViewRendersTest extends TestCase
{
    private const PLANNER_ID    = 5;   // MCD Planner
    private const VERIFIER_ID   = 7;   // MCD Verifier
    private const APPROVER_ID   = 8;   // MCD Approver
    private const OFFICER_ID    = 14;  // Purchasing Officer
    private const PURCHASER_ID  = 15;  // Purchaser / canvasser
    private const PRODUCT_ID    = 42611;

    /** @var int */
    private $errorReporting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(SecureHeaders::class);

        // SalesController::show() requires api/wfs-approvers-api.php, which calls header()
        // directly. Under PHPUnit the output stream is already open, so that raises
        // "Cannot modify header information" — which Laravel promotes to an ErrorException
        // and turns every render into a 500. Mask E_WARNING for the duration; notices
        // (undefined variables in a template) still surface, which is what this covers.
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

    private function makeMrs(array $overrides = [])
    {
        $mrs = SalesHeader::create(array_merge([
            'user_id'                  => self::PLANNER_ID,
            'order_number'             => 'TEST-UI-' . uniqid(),
            'customer_delivery_adress' => 'Warehouse A',
            'delivery_type'            => 'pickup',
            'status'                   => 'APPROVED (MCD Planner) - MRS For Verification',
            'section'                  => 'Mill',
            'requested_by'             => 'Juan Dela Cruz:Foreman',
            'for_pa'                   => 1,
            'is_pa'                    => 1,
            'planner_at'               => '2026-06-30 09:00:00',
        ], $overrides));

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
            'par_to'           => 'Mill Section',
            'frequency'        => 'Monthly',
            'purpose'          => 'Routine replacement',
            'promo_id'         => 0,
        ]);

        PurchaseAdvice::create([
            'mrs_id'     => $mrs->id,
            'pa_number'  => 'TEST-PA-' . uniqid(),
            'created_by' => self::PLANNER_ID,
            'status'     => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
            'is_hold'    => 0,
        ]);

        return [$mrs, $detail];
    }

    /** Markup every PA-DP screen must share with PA SR. */
    private function assertPaDesignSystem($response, $detailId)
    {
        $response->assertStatus(200);
        $response->assertSee('pa-page-header', false);
        $response->assertSee('pa-status-badge', false);
        $response->assertSee('pa-card-header', false);
        $response->assertSee('pa-meta-grid', false);
        $response->assertSee('class="pa-table"', false);
        $response->assertSee('pa-action-bar', false);
        $response->assertSee('btn-pa', false);
        // Shared header cards + per-item detail strip.
        $response->assertSee('MRS Reference', false);
        $response->assertSee('MRS Timeline', false);
        $response->assertSee('Request Details', false);
        $response->assertSee('pa-subgrid', false);
        $response->assertSee('PAR To', false);
        // Hold controls and their JS hooks.
        $response->assertSee('id="checkbox-' . $detailId . '"', false);
        $response->assertSee('id="textarea-' . $detailId . '"', false);
        $response->assertSee('name="hold_desc' . $detailId . '"', false);
        $response->assertSee('name="is_hold' . $detailId . '"', false);
    }

    public function test_planner_screen_renders()
    {
        list($mrs, $detail) = $this->makeMrs();

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('name="quantityToOrder' . $detail->id . '"', false);
        $response->assertSee('name="previous_no' . $detail->id . '"', false);
        $response->assertSee('name="planner_remarks"', false);
        $response->assertSee('id="holdPlannerBtn"', false);
        $response->assertSee('id="printDetails"', false);
        $response->assertSee('Proceed', false);
    }

    public function test_verifier_screen_renders_with_its_actions()
    {
        list($mrs, $detail) = $this->makeMrs();

        $response = $this->actingAs(User::find(self::VERIFIER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('id="verifyVerifierBtn"', false);
        $response->assertSee('id="holdVerifierBtn"', false);
        $response->assertSee('id="note_verifier"', false);
        // The planner's editable fields must not be exposed to the verifier.
        $response->assertDontSee('name="planner_remarks"', false);
    }

    public function test_approver_screen_renders_with_its_actions()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => 'Verified (MCD Verifier) - PA For MCD Manager Approval',
            'verified_at' => '2026-07-01 09:00:00',
        ]);

        $response = $this->actingAs(User::find(self::APPROVER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('id="approverApproverBtn"', false);
        $response->assertSee('id="holdApproverBtn"', false);
        $response->assertSee('id="note_approver"', false);
    }

    /** Received MRS exposes the purchasing columns to the planner. */
    public function test_planner_screen_shows_purchasing_columns_once_received()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
        ]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('purchaser-col', false);
        $response->assertSee('name="qty_delivered' . $detail->id . '"', false);
        $response->assertSee('Update', false);
    }

    public function test_purchasing_officer_screen_renders()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => 'APPROVED (MCD Approver) - PA for Delegation',
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
        ]);

        $response = $this->actingAs(User::find(self::OFFICER_ID))
            ->get(route('pa.view_mrs', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('id="purchasers"', false);
        $response->assertSee('id="receiveBtn"', false);
        $response->assertSee('id="holdPurchaserBtn"', false);
        $response->assertSee('Assign Canvasser', false);
    }

    /** Assigned but not yet received: Receive is offered, the PO fields stay locked. */
    public function test_canvasser_screen_before_receiving()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => '(For Purchasing Receival)',
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
            'received_by' => self::PURCHASER_ID,
        ]);

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('purchaser.view_mrs', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('id="receivePurchaser"', false);
        $response->assertSee('name="po_no' . $detail->id . '"', false);
        $response->assertSee('name="qty_ordered' . $detail->id . '"', false);
        // Hold is only offered after receiving.
        $response->assertDontSee('id="holdPurchaserBtn"', false);
    }

    public function test_canvasser_screen_after_receiving()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => 'RECEIVED FOR CANVASS (Purchasing Officer)',
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
        ]);

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('purchaser.view_mrs', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('id="holdPurchaserBtn"', false);
        $response->assertSee('id="note"', false);
        $response->assertSee('name="po_date_released' . $detail->id . '"', false);
        $response->assertSee('Received', false);
    }

    /** A held PA locks the canvasser out and says why. */
    public function test_canvasser_screen_when_pa_is_on_hold()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'      => 'HOLD (For MCD Planner re-edit)',
            'verified_at' => '2026-07-01 09:00:00',
            'approved_at' => '2026-07-02 09:00:00',
            'received_by' => self::PURCHASER_ID,
            'received_at' => '2026-07-10 09:00:00',
        ]);
        PurchaseAdvice::where('mrs_id', $mrs->id)->update(['is_hold' => 1]);

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('purchaser.view_mrs', $mrs->id));

        $this->assertPaDesignSystem($response, $detail->id);
        $response->assertSee('Purchase advice on-hold', false);
        $response->assertSee('pa-notice', false);
    }

    /** The banner the planner sees when a canvasser returned the request. */
    public function test_planner_sees_returned_by_canvasser_banner()
    {
        list($mrs, $detail) = $this->makeMrs([
            'status'         => 'HOLD (For MCD Planner re-edit)',
            'verified_at'    => '2026-07-01 09:00:00',
            'approved_at'    => '2026-07-02 09:00:00',
            'received_by'    => self::PURCHASER_ID,
            'purchaser_note' => 'wrong stock code',
        ]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $response->assertStatus(200);
        $response->assertSee('Returned by Canvasser/Purchaser for re-edit', false);
        $response->assertSee('wrong stock code', false);
    }

    /** The PA SR screen must still render off the now-shared stylesheet. */
    public function test_pa_sr_screen_still_renders()
    {
        $pa = PurchaseAdvice::create([
            'pa_number'  => 'TEST-SR-' . uniqid(),
            'created_by' => self::PLANNER_ID,
            'status'     => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
            'is_hold'    => 0,
        ]);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('pa.pa_view', $pa->id));

        $response->assertStatus(200);
        $response->assertSee('pa-page-header', false);
        $response->assertSee('pa-action-bar', false);
        $response->assertSee('PA Reference', false);
    }
}
