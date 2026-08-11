<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Constants\Status;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\InventoryRequest;
use App\Models\Ecommerce\InventoryRequestItems;
use App\Models\Ecommerce\InventoryRequestsOldItem;
use App\Models\Ecommerce\Product;
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
        return $this->userWithRole('Planning Supervisor', 'planning.supervisor.test@example.com');
    }

    /** A throwaway MCD Verifier — the desk that fills the inventory code. */
    private function verifier()
    {
        return $this->userWithRole('MCD Verifier', 'mcd.verifier.test@example.com');
    }

    private function userWithRole($roleName, $email)
    {
        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role, $roleName . ' role missing — run the role migration.');

        return User::create([
            'name'      => 'Test ' . $roleName,
            'email'     => $email,
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
        $item = $this->makeItem($imf);

        $response = $this->openImf($imf);

        $response->assertStatus(200);
        $response->assertSee('pa-action-bar', false);
        $response->assertSee('imfActionForm', false);
        // First pass: line remarks, then endorse to the MCD Verifier.
        $response->assertSee('Endorse to MCD Verifier', false);
        $response->assertSee('lines[' . $item->id . '][planner_remarks]', false);
        $response->assertSee('Hold (return to requestor)', false);
        $response->assertSee('Reject', false);
        // The inventory code belongs to the Verifier, the stock code to the
        // Planner's second pass — neither is editable here.
        $response->assertDontSee('lines[' . $item->id . '][inventory_code]', false);
        $response->assertDontSee('lines[' . $item->id . '][stock_code]', false);
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

    /**
     * The registered product carries the Classic stock code, not a number this
     * app invented. It used to be created with MAX(code)+1, which left the item
     * master out of step with Classic.
     */
    public function test_approval_registers_the_product_under_the_stock_code()
    {
        $imf = $this->makeImf(['status' => Status::APPROVED_MCD, 'type' => 'new']);
        $this->makeItem($imf, ['stock_code' => 'SC-REG-' . uniqid(), 'item_description' => 'Coded item']);

        $item = $imf->items()->first();

        $this->actingAs($this->supervisor())
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new'])
            ->assertRedirect(route('imf.requests'));

        $product = Product::where('code', $item->stock_code)->first();
        $this->assertNotNull($product, 'No product was registered under the stock code.');
        $this->assertEquals('Coded item', $product->name);
        $this->assertEquals($product->id, $item->fresh()->product_id);
    }

    /**
     * A stock code that already belongs to another product stops the approval.
     * Registering over it would rewrite an unrelated item master row.
     */
    public function test_approval_is_blocked_when_the_stock_code_is_taken()
    {
        $code = 'SC-DUP-' . uniqid();
        $existing = Product::create([
            'category_id' => 29, 'code' => $code, 'name' => 'Existing product',
            'description' => 'Existing product', 'slug' => 'new-product',
            'status' => 'PUBLISHED', 'created_by' => 1,
        ]);

        $imf = $this->makeImf(['status' => Status::APPROVED_MCD, 'type' => 'new']);
        $this->makeItem($imf, ['stock_code' => $code, 'item_description' => 'Renamed item']);

        $this->actingAs($this->supervisor())
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new'])
            ->assertRedirect(route('imf.requests.view', $imf->id));

        $this->assertEquals(Status::APPROVED_MCD, $imf->fresh()->status);
        $this->assertEquals(1, Product::where('code', $code)->count());
        $this->assertEquals('Existing product', $existing->fresh()->name);
    }

    /** The Planner is stopped at their own desk, where the code can still be fixed. */
    public function test_planner_cannot_endorse_a_stock_code_already_in_use()
    {
        $code = 'SC-TAKEN-' . uniqid();
        Product::create([
            'category_id' => 29, 'code' => $code, 'name' => 'Existing product',
            'description' => 'Existing product', 'slug' => 'new-product',
            'status' => 'PUBLISHED', 'created_by' => 1,
        ]);

        $imf  = $this->makeImf(['status' => Status::VERIFIED_MCD]);
        $item = $this->makeItem($imf, ['stock_code' => null]);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => $code]],
            ])
            ->assertRedirect(route('imf.requests.view', $imf->id));

        // Held at the Planner's desk, but what they typed is kept for the fix.
        $this->assertEquals(Status::VERIFIED_MCD, $imf->fresh()->status);
        $this->assertEquals($code, $item->fresh()->stock_code);
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
        $response->assertDontSee('Endorse to MCD Verifier', false);
        $response->assertDontSee('Approve &amp; Endorse', false);
        $response->assertDontSee('Approve &amp; Register', false);
    }

    /* ------------------------------------------------------------------
     | MCD Verifier stage
     |
     | Planner (line remarks) -> MCD Verifier (inventory code, class, DLT)
     | -> Planner (stock code from Classic) -> Planning Supervisor.
     ------------------------------------------------------------------ */

    /** The planner's first pass saves the line remarks and hands over to the Verifier. */
    public function test_planner_endorses_to_the_mcd_verifier()
    {
        $imf  = $this->makeImf(['status' => Status::APPROVED_WFS]);
        $item = $this->makeItem($imf);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['planner_remarks' => 'Please code as consumable.']],
            ])
            ->assertRedirect(route('imf.requests'));

        $this->assertEquals(Status::FOR_VERIFICATION, $imf->fresh()->status);
        $this->assertEquals('Please code as consumable.', $item->fresh()->planner_remarks);
    }

    /** The Verifier's own fields are editable only while the IMF is with them. */
    public function test_verifier_sees_the_coding_fields_when_actionable()
    {
        $imf  = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $item = $this->makeItem($imf);

        $response = $this->openImf($imf, $this->verifier()->id);

        $response->assertStatus(200);
        $response->assertSee('MCD Verification &amp; Coding', false);
        $response->assertSee('lines[' . $item->id . '][inventory_code]', false);
        $response->assertSee('lines[' . $item->id . '][item_class]', false);
        $response->assertSee('lines[' . $item->id . '][dlt]', false);
        $response->assertSee('Verify &amp; Return to Planner', false);
        // The stock code is the Planner's to type in, not theirs.
        $response->assertDontSee('lines[' . $item->id . '][stock_code]', false);
    }

    /** A new-item IMF cannot leave the Verifier without an inventory code. */
    public function test_verifier_must_enter_the_inventory_code()
    {
        $imf  = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $item = $this->makeItem($imf);

        $this->actingAs($this->verifier())
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['item_class' => 'A']],
            ])
            ->assertRedirect(route('imf.requests.view', $imf->id));

        // Still with the Verifier, but the class they did type is kept.
        $this->assertEquals(Status::FOR_VERIFICATION, $imf->fresh()->status);
        $this->assertEquals('A', $item->fresh()->item_class);
    }

    /** With the codes filled in, the IMF goes back to the Planner for the stock code. */
    public function test_verifier_returns_the_imf_to_the_planner()
    {
        $imf  = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $item = $this->makeItem($imf);
        $verifier = $this->verifier();

        $this->actingAs($verifier)
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => [
                    'inventory_code'   => 'INV-0001',
                    'item_class'       => 'A',
                    'dlt'              => '30',
                    'verifier_remarks' => 'Coded per class A.',
                ]],
            ])
            ->assertRedirect(route('imf.requests'));

        $imf  = $imf->fresh();
        $item = $item->fresh();

        $this->assertEquals(Status::VERIFIED_MCD, $imf->status);
        $this->assertEquals($verifier->name, $imf->verifier_approved_by);
        $this->assertEquals('INV-0001', $item->inventory_code);
        $this->assertEquals('A', $item->item_class);
        $this->assertEquals('30', $item->dlt);
        $this->assertEquals('Coded per class A.', $item->verifier_remarks);
    }

    /** A Verifier hold lands back on the MCD Planner with their own remark column. */
    public function test_verifier_hold_returns_to_the_planner()
    {
        $imf = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $this->makeItem($imf);

        $this->actingAs($this->verifier())
            ->post(route('imf.action', $imf->id), [
                'action'  => 'hold',
                'type'    => 'new',
                'remarks' => 'Description is too generic to code.',
            ]);

        $imf = $imf->fresh();
        $this->assertEquals(Status::HOLD_MCD_VERIFIER, $imf->status);
        $this->assertEquals('Description is too generic to code.', $imf->note_mcd_verifier);

        // ...and the Planner picks it up again at their first pass.
        $response = $this->openImf($imf);
        $response->assertStatus(200);
        $response->assertSee('Endorse to MCD Verifier', false);
    }

    /** Second pass: the Planner types the Classic stock code and endorses upward. */
    public function test_planner_enters_the_stock_code_after_verification()
    {
        $imf  = $this->makeImf(['status' => Status::VERIFIED_MCD]);
        $item = $this->makeItem($imf, ['stock_code' => null]);

        // Nothing typed in — the endorsement is refused.
        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new'])
            ->assertRedirect(route('imf.requests.view', $imf->id));
        $this->assertEquals(Status::VERIFIED_MCD, $imf->fresh()->status);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action' => 'approve',
                'type'   => 'new',
                'lines'  => [$item->id => ['stock_code' => 'SC-9001']],
            ])
            ->assertRedirect(route('imf.requests'));

        $this->assertEquals(Status::APPROVED_MCD, $imf->fresh()->status);
        $this->assertEquals('SC-9001', $item->fresh()->stock_code);
    }

    /** On the second pass a hold goes one step back — to the MCD Verifier. */
    public function test_planner_can_return_the_imf_to_the_verifier()
    {
        $imf = $this->makeImf(['status' => Status::VERIFIED_MCD]);
        $this->makeItem($imf);

        $this->actingAs(User::find(self::PLANNER_ID))
            ->post(route('imf.action', $imf->id), [
                'action'  => 'hold',
                'type'    => 'new',
                'remarks' => 'Inventory code does not match the class.',
            ]);

        $this->assertEquals(Status::FOR_VERIFICATION, $imf->fresh()->status);
    }

    /** A desk can only write its own columns, even if the post says otherwise. */
    public function test_verifier_cannot_write_the_stock_code()
    {
        $imf  = $this->makeImf(['status' => Status::FOR_VERIFICATION]);
        $item = $this->makeItem($imf, ['stock_code' => 'ORIGINAL']);

        $this->actingAs($this->verifier())
            ->post(route('imf.action', $imf->id), [
                'action' => 'save',
                'type'   => 'new',
                'lines'  => [$item->id => [
                    'inventory_code' => 'INV-0002',
                    'stock_code'     => 'HACKED',
                ]],
            ]);

        $item = $item->fresh();
        $this->assertEquals('INV-0002', $item->inventory_code);
        $this->assertEquals('ORIGINAL', $item->stock_code);
        // "save" keeps the IMF on the same desk.
        $this->assertEquals(Status::FOR_VERIFICATION, $imf->fresh()->status);
    }

    /**
     * The Planning Supervisor lands on the admin panel, not the storefront.
     * With no branch of its own, LoginController::redirectTo() returned null and
     * dropped the role on the department-user side.
     */
    public function test_planning_supervisor_lands_on_the_imf_panel_after_login()
    {
        $supervisor = $this->supervisor();

        $this->post('/admin-panel/login', [
            'email'    => $supervisor->email,
            'password' => 'secret',
        ])->assertRedirect(route('imf.requests'));
    }

    /** The Verifier's queue starts at the Planner's endorsement. */
    public function test_mcd_verifier_queue_scoping()
    {
        $forVerification = $this->makeImf(['status' => Status::FOR_VERIFICATION, 'department' => 'VERIFY-DEPT']);
        $stillWithWfs    = $this->makeImf(['status' => Status::APPROVED_WFS, 'department' => 'WFS-DEPT']);

        $response = $this->actingAs($this->verifier())->get(route('imf.requests'));

        $response->assertStatus(200);
        $response->assertSee(route('imf.requests.view', $forVerification->id), false);
        $response->assertDontSee(route('imf.requests.view', $stillWithWfs->id), false);
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
