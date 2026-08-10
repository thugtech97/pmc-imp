<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Constants\Status;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\InventoryRequestItems;
use App\Models\Ecommerce\InventoryRequestsOldItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Smoke test for the admin IMF view after it was moved onto the shared PA/MRS
 * design system.
 *
 * Renders both IMF shapes ("new" item list and "update" old-vs-new comparison)
 * for the roles that use the screen, and asserts the shared design-system markup
 * plus the action buttons and JS hooks the page's own scripts bind to. Catches
 * template regressions the Blade compiler cannot.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class ImfViewRendersTest extends TestCase
{
    private const PLANNER_ID  = 5;   // MCD Planner
    private const APPROVER_ID = 8;   // MCD Approver — view only since the Planning
                                     // Supervisor took over the final approval

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
            'status'     => Status::APPROVED_WFS,
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
            'stock_code'       => 'TEST-CODE',
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

    private function openImf($imf, $userId = self::PLANNER_ID)
    {
        return $this->actingAs(User::find($userId))
            ->get(route('imf.requests.view', $imf->id));
    }

    /**
     * A throwaway Planning Supervisor. The role id differs per environment, so it
     * is resolved by name; the row is rolled back with the rest of the test.
     */
    private function supervisor()
    {
        $role = Role::where('name', 'Planning Supervisor')->first();
        $this->assertNotNull($role, 'Planning Supervisor role missing — run the role migration.');

        return User::create([
            'name'      => 'Test Planning Supervisor',
            'email'     => 'planning.supervisor.test@example.com',
            'password'  => bcrypt('secret'),
            'role_id'   => $role->id,
            'is_active' => 1,
        ]);
    }

    /** The "new items" shape renders on the shared design system. */
    public function test_new_imf_renders_on_the_shared_design_system()
    {
        $imf = $this->makeImf();
        $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        // Shared design-system chrome — the same classes the MRS/PA screens use.
        $response->assertSee('pa-page-header', false);
        $response->assertSee('pa-status-badge', false);
        $response->assertSee('pa-card', false);
        $response->assertSee('pa-meta-grid', false);
        $response->assertSee('pa-table', false);

        // Header and cards
        $response->assertSee('IMF# ' . $imf->id, false);
        $response->assertSee('IMF Reference', false);
        $response->assertSee('IMF Timeline', false);
        $response->assertSee('Request Details', false);

        // Item content still prints
        $response->assertSee('Test IMF item', false);
        $response->assertSee('MAINTENANCE', false);
    }

    /** The "update" shape renders the old-vs-new comparison table. */
    public function test_update_imf_renders_the_comparison_table()
    {
        $imf = $this->makeImf(['type' => 'update', 'update_type' => 'Min Qty, Max Qty']);
        $this->makeItem($imf, ['item_description' => 'New description', 'min_qty' => 5]);

        InventoryRequestsOldItem::create([
            'imf_no'           => $imf->id,
            'stock_code'       => 'TEST-CODE',
            'item_description' => 'Old description',
            'min_qty'          => 1,
        ]);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('imf-diff', false);
        $response->assertSee('Requested Changes', false);
        $response->assertSee('Old Value', false);
        $response->assertSee('New Value', false);
        $response->assertSee('Old description', false);
        $response->assertSee('New description', false);
        // update_type is rendered as badges
        $response->assertSee('Purpose of Update', false);
        $response->assertSee('Min Qty', false);
    }

    /**
     * An IMF with no item rows must still render. The old screen read $items[0]
     * unguarded for the print button and blew up with "Undefined offset: 0".
     */
    public function test_imf_without_items_still_renders()
    {
        $imf = $this->makeImf();

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('No items found for this request.', false);
    }

    /** The planner gets the action bar while the IMF is waiting on them. */
    public function test_planner_sees_the_action_bar_when_actionable()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_WFS]);
        $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('pa-action-bar', false);
        $response->assertSee('imfActionForm', false);
        $response->assertSee('Approve &amp; Endorse', false);
        $response->assertSee('Hold (return to requestor)', false);
        $response->assertSee('Reject', false);
    }

    /** The final approver's labels differ, and they only act after the planner endorses. */
    public function test_planning_supervisor_sees_their_own_action_labels()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $this->makeItem($imf);

        $response = $this->openImf($imf, $this->supervisor()->id);

        $response->assertStatus(200);
        $response->assertSee('Approve &amp; Register', false);
        $response->assertSee('Hold (return to Planner)', false);
    }

    /**
     * The MCD Approver kept the screen but lost the stage: it opens read-only at
     * the point the Planning Supervisor now acts on.
     */
    public function test_mcd_approver_can_view_but_not_act()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $this->makeItem($imf);

        $response = $this->openImf($imf, self::APPROVER_ID);

        $response->assertStatus(200);
        $response->assertSee('Test IMF item', false);
        $response->assertSee('id="printDetails"', false);
        $response->assertDontSee(route('imf.action', $imf->id), false);
        $response->assertDontSee('Approve &amp; Register', false);
        $response->assertDontSee('Approve &amp; Endorse', false);
    }

    /** A posted action from the MCD Approver is refused, not silently applied. */
    public function test_mcd_approver_cannot_post_an_action()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $this->makeItem($imf);

        $this->actingAs(User::find(self::APPROVER_ID))
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new'])
            ->assertRedirect(route('imf.requests.view', $imf->id));

        $this->assertEquals(Status::APPROVED_MCD, $imf->fresh()->status);
    }

    /** The Planning Supervisor's approval is the final one. */
    public function test_planning_supervisor_approval_is_final()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD, 'type' => 'update']);
        $this->makeItem($imf, ['stock_code' => 'NO-SUCH-PRODUCT-CODE']);

        $supervisor = $this->supervisor();

        $this->actingAs($supervisor)
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'update'])
            ->assertRedirect(route('imf.requests'));

        $imf = $imf->fresh();
        $this->assertEquals(Status::APPROVED_SUPERVISOR, $imf->status);
        $this->assertEquals($supervisor->name, $imf->approver_approved_by);
    }

    /** A supervisor hold sends the IMF back to the MCD Planner's queue. */
    public function test_planning_supervisor_hold_returns_to_the_planner()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD]);
        $this->makeItem($imf);

        $this->actingAs($this->supervisor())
            ->post(route('imf.action', $imf->id), [
                'action'  => 'hold',
                'type'    => 'new',
                'remarks' => 'Check the usage rate.',
            ]);

        $imf = $imf->fresh();
        $this->assertEquals(Status::HOLD_SUPERVISOR, $imf->status);
        $this->assertEquals('Check the usage rate.', $imf->note_verifier);

        // ...and the planner can act on it again from there.
        $response = $this->openImf($imf);
        $response->assertStatus(200);
        $response->assertSee('Approve &amp; Endorse', false);
    }

    /** No action bar once the IMF is past the stage this role acts on. */
    public function test_no_action_bar_when_not_actionable()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_APPROVER]);
        $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        // The action <form> and its buttons are gone. (The helper JS is always
        // shipped, so assert on the form's endpoint rather than the element id.)
        $response->assertDontSee(route('imf.action', $imf->id), false);
        $response->assertDontSee('Approve &amp; Endorse', false);
        $response->assertDontSee('Approve &amp; Register', false);
    }

    /** Hold/reject remarks surface as notice banners, not buried in the page. */
    public function test_remarks_render_as_notice_banners()
    {
        $imf = $this->makeImf([
            'status'        => Status::HOLD_APPROVER,
            'note_planner'  => 'Planner says fix the stock code',
            'note_verifier' => 'Approver says check the min qty',
        ]);
        $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('pa-notice', false);
        $response->assertSee('Planner says fix the stock code', false);
        $response->assertSee('Approver says check the min qty', false);
    }

    /** The print button and its JS hook survive the redesign. */
    public function test_print_button_is_present_for_mcd_roles()
    {
        $imf = $this->makeImf();
        $item = $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('id="printDetails"', false);
        $response->assertSee('data-order="' . $item->imf_no . '"', false);
    }

    /**
     * The supervisor's queue is what the Planner endorsed plus what is already
     * fully approved — an IMF still sitting in WFS must not appear.
     */
    public function test_planning_supervisor_queue_scoping()
    {
        $endorsed = $this->makeImf(['status' => Status::APPROVED_MCD, 'department' => 'ENDORSED-DEPT']);
        $waiting  = $this->makeImf(['status' => Status::APPROVED_WFS, 'department' => 'WFS-DEPT']);

        $response = $this->actingAs($this->supervisor())->get(route('imf.requests'));

        $response->assertStatus(200);
        $response->assertSee('IMF Requests', false);
        $response->assertSee(route('imf.requests.view', $endorsed->id), false);
        $response->assertDontSee(route('imf.requests.view', $waiting->id), false);
    }

    /** The history panel added earlier still renders on the redesigned screen. */
    public function test_history_panel_still_renders()
    {
        $imf = $this->makeImf();
        $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('IMF History Log', false);
        $response->assertSee('dh-panel', false);
    }
}
