<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DocumentHistory;
use App\Models\User;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\PurchaseAdvice;
use App\Models\Ecommerce\SalesDetail;
use App\Models\Ecommerce\SalesHeader;
use App\Services\History;
use Illuminate\Support\Facades\DB;

/**
 * Audit trail for IMF / MRS / PA-DP / PA-SR.
 *
 * Covers the three ways an entry is produced — the automatic model-event diff,
 * a workflow action that supplies its own wording and remark, and grouped item
 * edits — plus the requestor/internal split and the fact that both the MCD and
 * the department screens actually render the panel.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class DocumentHistoryTest extends TestCase
{
    private const PLANNER_ID   = 5;   // MCD Planner
    private const VERIFIER_ID  = 7;   // MCD Verifier
    private const PURCHASER_ID = 15;  // Purchaser / canvasser
    private const PRODUCT_ID   = 42611;

    /** @var int */
    private $errorReporting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(SecureHeaders::class);

        // The MRS screens require api/wfs-approvers-api.php, which calls header()
        // directly; under PHPUnit that raises a warning Laravel promotes to a 500.
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
        History::discardItemChanges();
        error_reporting($this->errorReporting);
        parent::tearDown();
    }

    private function makeMrs(array $overrides = [])
    {
        return SalesHeader::create(array_merge([
            'user_id'                  => self::PLANNER_ID,
            'order_number'             => 'TEST-HIST-' . uniqid(),
            'customer_delivery_adress' => 'test',
            'delivery_type'            => 'pickup',
            'status'                   => 'FULLY APPROVED (Approved by tester) - WFS',
            'for_pa'                   => 1,
        ], $overrides));
    }

    private function makeItem($mrs)
    {
        return SalesDetail::create([
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
    }

    private function makeImfItem($imf)
    {
        return \App\Models\Ecommerce\InventoryRequestItems::create([
            'imf_no'           => $imf->id,
            'stock_code'       => 'TEST-CODE',
            'item_description' => 'Test IMF item',
            'UoM'              => 'PC',
            'min_qty'          => 1,
            'max_qty'          => 10,
            'purpose'          => 'Testing',
        ]);
    }

    private function historyFor($type, $id)
    {
        return DocumentHistory::where('document_type', $type)
            ->where('document_id', $id)
            ->orderBy('id', 'asc')
            ->get();
    }

    /** Creating a request opens its trail without anyone calling the logger. */
    public function test_creating_an_mrs_records_the_first_entry()
    {
        $mrs = $this->makeMrs();

        $entries = $this->historyFor('MRS', $mrs->id);

        $this->assertCount(1, $entries);
        $this->assertSame('created', $entries->first()->action);
        $this->assertSame($mrs->order_number, $entries->first()->document_number);
    }

    /** An ordinary field edit is logged as a field-level diff. */
    public function test_field_edits_are_logged_with_old_and_new_values()
    {
        $mrs = $this->makeMrs(['section' => 'Original section']);
        $mrs->update(['section' => 'New section']);

        $entry = $this->historyFor('MRS', $mrs->id)->last();

        $this->assertSame('updated', $entry->action);
        $this->assertNotEmpty($entry->changes);

        $change = collect($entry->changes)->firstWhere('field', 'section');
        $this->assertNotNull($change, 'the section change must be recorded');
        $this->assertSame('Original section', $change['old']);
        $this->assertSame('New section', $change['new']);
    }

    /** Untracked columns (bookkeeping, timestamps) must not create noise. */
    public function test_untracked_field_changes_are_ignored()
    {
        $mrs = $this->makeMrs();
        $before = $this->historyFor('MRS', $mrs->id)->count();

        $mrs->update(['delivery_status' => 'Processing Stock']);

        $this->assertCount($before, $this->historyFor('MRS', $mrs->id));
    }

    /** A status move is phrased as a stage, and records both sides of the move. */
    public function test_status_change_records_the_stage_and_the_transition()
    {
        $mrs = $this->makeMrs();
        $mrs->update(['status' => 'APPROVED (MCD Approver) - PA for Delegation']);

        $entry = $this->historyFor('MRS', $mrs->id)->last();

        $this->assertSame('approved', $entry->action);
        $this->assertSame('FULLY APPROVED (Approved by tester) - WFS', $entry->status_from);
        $this->assertSame('APPROVED (MCD Approver) - PA for Delegation', $entry->status_to);
        // The requestor gets the plain wording, never the stored jargon.
        $this->assertSame('APPROVED BY MCD MANAGER - FOR CANVASSER ASSIGNMENT', $entry->requestor_label);
    }

    /** A workflow action supplies its own wording, remark and visibility. */
    public function test_workflow_context_supplies_wording_and_remark()
    {
        $mrs = $this->makeMrs();

        History::context($mrs, [
            'action'               => 'held',
            'title'                => 'Held by the MCD Verifier for Planner re-edit',
            'requestor_title'      => 'ON HOLD - WITH MCD PLANNER FOR RE-EDIT',
            'remarks'              => 'wrong stock code',
            'visible_to_requestor' => false,
        ]);
        $mrs->update(['status' => 'HOLD (For MCD Planner re-edit)']);

        $entry = $this->historyFor('MRS', $mrs->id)->last();

        $this->assertSame('held', $entry->action);
        $this->assertSame('Held by the MCD Verifier for Planner re-edit', $entry->title);
        $this->assertSame('wrong stock code', $entry->remarks);
        $this->assertFalse((bool) $entry->visible_to_requestor);
        $this->assertTrue($this->historyFor('MRS', $mrs->id)->last()->tone === 'hold');
    }

    /** Context is consumed once, so it cannot leak onto the next save. */
    public function test_context_applies_only_to_the_next_save()
    {
        $mrs = $this->makeMrs();

        History::context($mrs, ['title' => 'One-shot title']);
        $mrs->update(['section' => 'first']);
        $mrs->update(['section' => 'second']);

        $entries = $this->historyFor('MRS', $mrs->id);

        $this->assertSame('One-shot title', $entries[1]->title);
        $this->assertNotSame('One-shot title', $entries[2]->title);
    }

    /** Item edits are grouped into one entry rather than one per line. */
    public function test_item_edits_are_grouped_into_a_single_entry()
    {
        $mrs   = $this->makeMrs();
        $itemA = $this->makeItem($mrs);
        $itemB = $this->makeItem($mrs);

        $before = $this->historyFor('MRS', $mrs->id)->count();

        $itemA->update(['qty_to_order' => 25]);
        $itemB->update(['qty_to_order' => 30]);
        History::flushItemChanges();

        $entries = $this->historyFor('MRS', $mrs->id);

        $this->assertCount($before + 1, $entries, 'two line edits must collapse into one entry');
        $this->assertSame('item_updated', $entries->last()->action);
        $this->assertCount(2, $entries->last()->changes);
    }

    /** Creating a request with items must not bury the "created" entry. */
    public function test_creating_items_does_not_log_one_entry_per_line()
    {
        $mrs = $this->makeMrs();
        $this->makeItem($mrs);
        $this->makeItem($mrs);
        History::flushItemChanges();

        $entries = $this->historyFor('MRS', $mrs->id);

        $this->assertCount(1, $entries);
        $this->assertSame('created', $entries->first()->action);
    }

    /** A PA raised from an MRS is tagged DP; a stand-alone one is tagged SR. */
    public function test_pa_entries_are_tagged_dp_or_sr()
    {
        $mrs = $this->makeMrs();

        $dp = PurchaseAdvice::create([
            'mrs_id'     => $mrs->id,
            'pa_number'  => 'TEST-DP-' . uniqid(),
            'created_by' => self::PLANNER_ID,
            'status'     => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
        ]);

        $sr = PurchaseAdvice::create([
            'pa_number'  => 'TEST-SR-' . uniqid(),
            'created_by' => self::PLANNER_ID,
            'status'     => 'APPROVED (MCD PLANNER) - FOR VERIFICATION',
        ]);

        $this->assertSame('DP', $this->historyFor('PA', $dp->id)->first()->pa_type);
        $this->assertSame('SR', $this->historyFor('PA', $sr->id)->first()->pa_type);
    }

    /** IMF changes land in the same trail under their own document type. */
    public function test_imf_changes_are_recorded()
    {
        $imf = InventoryRequest::create([
            'user_id'    => self::PLANNER_ID,
            'status'     => \App\Constants\Status::SAVED,
            'department' => 'TEST',
            'type'       => 'NEW',
        ]);

        $imf->update(['status' => \App\Constants\Status::SUBMITTED]);

        $entries = $this->historyFor('IMF', $imf->id);

        $this->assertCount(2, $entries);
        $this->assertSame('created', $entries->first()->action);
        $this->assertSame('submitted', $entries->last()->action);
        $this->assertSame('Submitted - for WFS approval', $entries->last()->requestor_label);
    }

    /** Internal entries are withheld from the requestor's view. */
    public function test_internal_entries_are_hidden_from_the_requestor()
    {
        $mrs = $this->makeMrs();

        History::mrs($mrs, [
            'action'               => 'held',
            'title'                => 'Internal note',
            'visible_to_requestor' => false,
        ]);
        History::mrs($mrs, ['action' => 'status', 'title' => 'Shared note']);

        $visible = DocumentHistory::where('document_type', 'MRS')
            ->where('document_id', $mrs->id)
            ->visibleToRequestor()
            ->pluck('title');

        $this->assertContains('Shared note', $visible->all());
        $this->assertNotContains('Internal note', $visible->all());
    }

    /** The MCD/Admin MRS screen renders the panel. */
    public function test_admin_mrs_screen_shows_the_history_panel()
    {
        $mrs = $this->makeMrs();
        $this->makeItem($mrs);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('sales-transaction.view', $mrs->id));

        $response->assertStatus(200);
        $response->assertSee('MRS History Log', false);
        $response->assertSee('dh-panel', false);
        $response->assertSee('Request created', false);
    }

    /** The MCD/Admin PA screen renders the panel, labelled by PA type. */
    public function test_admin_pa_screen_shows_the_history_panel()
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
        $response->assertSee('PA-SR History Log', false);
        $response->assertSee('Purchase advice created', false);
    }

    /** The MCD/Admin IMF screen renders the panel. */
    public function test_admin_imf_screen_shows_the_history_panel()
    {
        $imf = InventoryRequest::create([
            'user_id'    => self::PLANNER_ID,
            'status'     => \App\Constants\Status::APPROVED_WFS,
            'department' => 'TEST',
            'type'       => 'NEW',
        ]);
        // The screen reads $items[0] directly, so it needs at least one line.
        $this->makeImfItem($imf);

        $response = $this->actingAs(User::find(self::PLANNER_ID))
            ->get(route('imf.requests.view', $imf->id));

        $response->assertStatus(200);
        $response->assertSee('IMF History Log', false);
        $response->assertSee('IMF raised', false);
    }

    /** The department user's own IMF screen renders the requestor-facing panel. */
    public function test_department_imf_screen_shows_the_history_panel()
    {
        $imf = InventoryRequest::create([
            'user_id'    => self::PURCHASER_ID,
            'status'     => \App\Constants\Status::SAVED,
            'department' => 'TEST',
            'type'       => 'NEW',
        ]);

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('new-stock.show', $imf->id));

        $response->assertStatus(200);
        $response->assertSee('Request History', false);
        $response->assertSee('rh-list', false);
    }

    /** The department user's own MRS screen renders the requestor-facing panel. */
    public function test_department_mrs_screen_shows_the_history_panel()
    {
        $mrs = $this->makeMrs(['user_id' => self::PURCHASER_ID]);
        $this->makeItem($mrs);

        $response = $this->actingAs(User::find(self::PURCHASER_ID))
            ->get(route('profile.sales.view', $mrs->id));

        $response->assertStatus(200);
        $response->assertSee('Request History', false);
        $response->assertSee('rh-list', false);
    }
}
