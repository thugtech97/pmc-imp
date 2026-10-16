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
 * An Update IMF can carry several existing stock items. Each item keeps its own
 * "before" snapshot, re-editing never overwrites it, and the final approval
 * updates every item's product.
 *
 * Runs against the PMC-ECOM-TEST duplicate DB inside a rolled-back transaction.
 */
class ImfMultiItemUpdateTest extends TestCase
{
    private const REQUESTOR_ID = 5;

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

    private function code($prefix)
    {
        return $prefix . '-' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * A product registered by an approved New IMF, so it has usage/min/max.
     */
    private function registeredProduct($code, $name, array $line = [])
    {
        $product = Product::create([
            'category_id' => 29,
            'code'        => $code,
            'name'        => $name,
            'description' => $name,
            'brand'       => 'SKF',
            'oem'         => 'OEM-' . $code,
            'uom'         => 'PC',
            'slug'        => 'test-' . strtolower($code),
            'status'      => 'PUBLISHED',
            'created_by'  => 1,
        ]);

        $imf = InventoryRequest::create([
            'user_id' => self::REQUESTOR_ID, 'status' => Status::APPROVED_SUPERVISOR,
            'department' => 'MAINTENANCE', 'type' => 'new',
        ]);

        InventoryRequestItems::create(array_merge([
            'imf_no'           => $imf->id,
            'stock_code'       => $code,
            'item_description' => $name,
            'brand'            => 'SKF',
            'OEM_ID'           => 'OEM-' . $code,
            'UoM'              => 'PC',
            'usage_rate_qty'   => 4,
            'usage_frequency'  => 'Monthly',
            'min_qty'          => 1,
            'max_qty'          => 10,
            'purpose'          => 'Conveyor',
            'product_id'       => $product->id,
        ], $line));

        return $product;
    }

    /**
     * Form post for an Update IMF. Each row: [stock_code, overrides, item_id].
     */
    private function updatePayload(array $rows, array $extra = [])
    {
        $payload = array_merge([
            'department' => 'MAINTENANCE', 'section' => 'ELECTRICAL', 'division' => 'MILL',
            'priority' => '2', 'type' => 'update', 'action' => 'SAVED',
            'update_type' => ['Min-max update'],
        ], $extra);

        foreach ($rows as $row) {
            $current = InventoryRequestItems::currentValuesFor($row[0]);
            $values = array_merge($current ? $current['values'] : [], $row[1] ?? []);

            $payload['item_id'][] = $row[2] ?? '';
            $payload['stock_code'][] = $row[0];
            foreach (InventoryRequestsOldItem::FIELDS as $pair) {
                $payload[$pair[1]][] = $values[$pair[1]] ?? '';
            }
        }

        return $payload;
    }

    private function requestor()
    {
        return User::find(self::REQUESTOR_ID);
    }

    private function supervisor()
    {
        return $this->userWithRole('Planning Supervisor');
    }

    private function userWithRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        $this->assertNotNull($role, $roleName . ' role missing.');

        return User::create([
            'name' => 'Test ' . $roleName, 'email' => 'multi.update.' . str_replace(' ', '.', strtolower($roleName)) . '@example.com',
            'password' => bcrypt('secret'), 'role_id' => $role->id, 'is_active' => 1,
        ]);
    }

    /**
     * The whole route of a multi-item Update IMF: requestor -> (WFS) -> MCD
     * Planner, who holds it back -> requestor revises and adds an item ->
     * Planner -> MCD Verifier -> Planner (stock code pass) -> Planning
     * Supervisor. Every desk's screen must render and its per-line entries
     * must land on the right item.
     */
    public function test_full_route_from_requestor_to_planning_supervisor()
    {
        $a = $this->code('RA');
        $b = $this->code('RB');
        $c = $this->code('RC');
        $productA = $this->registeredProduct($a, 'BEARING 6205');
        $productB = $this->registeredProduct($b, 'V-BELT A42');
        $productC = $this->registeredProduct($c, 'GREASE EP2');

        $requestor = User::find(21);
        $planner   = User::find(self::REQUESTOR_ID);
        $this->assertSame('MCD Planner', Role::find($planner->role_id)->name);
        $verifier  = $this->userWithRole('MCD Verifier');
        $super     = $this->supervisor();

        $act = function ($user, $imf, array $data) {
            return $this->actingAs($user)->post(route('imf.action', $imf->id), array_merge(['type' => 'update'], $data));
        };
        $screen = function ($user, $imf) {
            $this->actingAs($user)->get(route('imf.requests.view', $imf->id))->assertOk();
        };

        // Requestor raises it with two items.
        $this->actingAs($requestor)->post(route('new-stock.store'), $this->updatePayload([
            [$a, ['max_qty' => 20]],
            [$b, ['brand' => 'GATES']],
        ]))->assertJson(['status' => 'success']);
        $imf = InventoryRequest::where('type', 'update')->where('user_id', 21)->orderByDesc('id')->first();
        [$itemA, $itemB] = $imf->items()->orderBy('id')->get()->all();

        // WFS submission and approval happen outside this app; the poll then
        // sets the status.
        $imf->update(['status' => Status::APPROVED_WFS]);

        // Planner, pass 1: remarks per line, then holds it back to the requestor.
        $screen($planner, $imf);
        $act($planner, $imf, ['action' => 'hold', 'remarks' => 'Add the grease too.', 'lines' => [
            $itemA->id => ['planner_remarks' => 'OK max'],
            $itemB->id => ['planner_remarks' => 'Check brand'],
        ]]);
        $this->assertSame(Status::HOLD_PLANNER, $imf->fresh()->status);

        // Requestor revises: keeps A and B, adds C. Straight back to the Planner.
        $this->actingAs($requestor)->get(route('new-stock.edit', $imf->id))->assertOk();
        $this->actingAs($requestor)->post(route('imf.update', $imf->id), $this->updatePayload([
            [$a, ['max_qty' => 20], $itemA->id],
            [$b, ['brand' => 'GATES'], $itemB->id],
            [$c, ['min_qty' => 3]],
        ]))->assertJson(['status' => 'success']);
        $imf->refresh();
        $this->assertSame(Status::APPROVED_WFS, $imf->status);
        $this->assertSame(1, (int) $imf->revision);
        $items = $imf->items()->orderBy('id')->get();
        $this->assertCount(3, $items);
        $itemC = $items[2];
        // Revising doesn't wipe the Planner's line remarks on the kept items.
        $this->assertSame('OK max', $itemA->fresh()->planner_remarks);

        // Planner, pass 1 again: endorse to the Verifier.
        $screen($planner, $imf);
        $act($planner, $imf, ['action' => 'approve', 'lines' => [$itemC->id => ['planner_remarks' => 'New line OK']]]);
        $this->assertSame(Status::FOR_VERIFICATION, $imf->fresh()->status);

        // Verifier: codes per line, then verify.
        $screen($verifier, $imf);
        $act($verifier, $imf, ['action' => 'approve', 'lines' => [
            $itemA->id => ['inventory_code' => 'INV-A', 'item_class' => 'A', 'dlt' => '30'],
            $itemB->id => ['inventory_code' => 'INV-B', 'item_class' => 'B', 'dlt' => '45'],
            $itemC->id => ['inventory_code' => 'INV-C', 'item_class' => 'C', 'dlt' => '60'],
        ]]);
        $this->assertSame(Status::VERIFIED_MCD, $imf->fresh()->status);
        $this->assertSame('INV-B', $itemB->fresh()->inventory_code);

        // Planner, pass 2: nothing to type for an update; a stale or crafted
        // stock code must not move a line onto another product.
        $screen($planner, $imf);
        $act($planner, $imf, ['action' => 'approve', 'lines' => [$itemA->id => ['stock_code' => $b]]]);
        $this->assertSame(Status::APPROVED_MCD, $imf->fresh()->status);
        $this->assertSame($a, $itemA->fresh()->stock_code);

        // Planning Supervisor: final approval updates all three products.
        $screen($super, $imf);
        $act($super, $imf, ['action' => 'approve']);
        $imf->refresh();
        $this->assertSame(Status::APPROVED_SUPERVISOR, $imf->status);
        $this->assertNotNull($imf->supervisor_approved_at);
        $this->assertSame('GATES', $productB->fresh()->brand);
        $this->assertSame('BEARING 6205', $productA->fresh()->name);
        foreach ([$itemA, $itemB, $itemC] as $item) {
            $this->assertNotNull($item->fresh()->product_id);
        }

        // Approved: all three codes are free for a new Update IMF again.
        foreach ([$a, $b, $c] as $code) {
            $this->assertNull(InventoryRequestItems::openUpdateImfFor($code));
        }

        // The requestor's view and the print still show every item.
        $this->actingAs($requestor)->get(route('new-stock.show', $imf->id))->assertOk()->assertSee($c);
        $this->actingAs($requestor)->get(route('imf-request.generate_report', ['id' => $imf->id]))->assertOk();
    }

    public function test_lookup_prefills_current_values_and_blocks_codes_on_an_open_update_imf()
    {
        $code = $this->code('LK');
        $this->registeredProduct($code, 'BEARING 6205');

        $found = $this->actingAs($this->requestor())->post(route('products.search'), ['code' => $code])->json();
        $this->assertSame(1, $found['valid']);
        $this->assertSame('BEARING 6205', $found['data']['item_description']);
        $this->assertEquals(10, $found['data']['max_qty']);
        $this->assertSame('Monthly', $found['data']['usage_frequency']);

        $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([[$code, ['max_qty' => 20]]]));
        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();

        $blocked = $this->actingAs($this->requestor())->post(route('products.search'), ['code' => $code])->json();
        $this->assertSame(0, $blocked['valid']);
        $this->assertSame($imf->id, $blocked['open_imf']);

        // The IMF being edited doesn't block its own items.
        $own = $this->actingAs($this->requestor())->post(route('products.search'), ['code' => $code, 'except_imf' => $imf->id])->json();
        $this->assertSame(1, $own['valid']);

        // Once it is fully approved the item can be updated again.
        $imf->update(['status' => Status::APPROVED_SUPERVISOR]);
        $again = $this->actingAs($this->requestor())->post(route('products.search'), ['code' => $code])->json();
        $this->assertSame(1, $again['valid']);

        $missing = $this->actingAs($this->requestor())->post(route('products.search'), ['code' => 'NOPE-' . uniqid()])->json();
        $this->assertSame('error', $missing['status']);
    }

    public function test_one_update_imf_holds_several_items_each_with_its_own_snapshot()
    {
        $a = $this->code('MA');
        $b = $this->code('MB');
        $this->registeredProduct($a, 'BEARING 6205');
        $this->registeredProduct($b, 'V-BELT A42');

        $response = $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([
            [$a, ['max_qty' => 20]],
            [$b, ['item_description' => 'V-BELT A42 GATES', 'UoM' => 'SET']],
        ]))->json();

        $this->assertSame('success', $response['status']);

        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();
        $items = $imf->items()->orderBy('id')->get();
        $this->assertCount(2, $items);
        $this->assertSame([$a, $b], $items->pluck('stock_code')->all());
        $this->assertNotNull($items[0]->product_id);
        $this->assertSame('Min-max update', $imf->update_type);

        $old = InventoryRequestsOldItem::forItems($imf->id, $items);
        $this->assertEquals(10, $old[$items[0]->id]->max_qty);
        $this->assertSame('V-BELT A42', $old[$items[1]->id]->item_description);

        $changedA = array_column(array_filter(InventoryRequestsOldItem::diff($old[$items[0]->id], $items[0]), function ($r) {
            return $r['changed'];
        }), 'field');
        $changedB = array_column(array_filter(InventoryRequestsOldItem::diff($old[$items[1]->id], $items[1]), function ($r) {
            return $r['changed'];
        }), 'field');
        $this->assertSame(['max_qty'], array_values($changedA));
        $this->assertSame(['item_description', 'UoM'], array_values($changedB));
    }

    public function test_bad_rows_are_refused_and_nothing_is_saved()
    {
        $a = $this->code('BA');
        $this->registeredProduct($a, 'BEARING 6205');
        $before = InventoryRequest::count();

        $dup = $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([[$a], [$a]]))->json();
        $this->assertSame('error', $dup['status']);
        $this->assertStringContainsString('more than once', $dup['message']);

        $unknown = $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([
            [$a], ['NOPE-' . uniqid(), ['item_description' => 'X', 'UoM' => 'PC', 'usage_rate_qty' => 1, 'usage_frequency' => 'Daily', 'min_qty' => 1, 'max_qty' => 2]],
        ]))->json();
        $this->assertSame('error', $unknown['status']);
        $this->assertStringContainsString('was not found', $unknown['message']);

        $incomplete = $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([[$a, ['max_qty' => '']]]))->json();
        $this->assertSame('error', $incomplete['status']);

        $this->assertSame($before, InventoryRequest::count());
    }

    public function test_re_editing_keeps_snapshots_and_adds_and_removes_items()
    {
        $a = $this->code('EA');
        $b = $this->code('EB');
        $c = $this->code('EC');
        $this->registeredProduct($a, 'BEARING 6205');
        $this->registeredProduct($b, 'V-BELT A42');
        $this->registeredProduct($c, 'GREASE EP2');

        $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([
            [$a, ['max_qty' => 20]],
            [$b, ['UoM' => 'SET']],
        ]));
        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();
        $items = $imf->items()->orderBy('id')->get();
        $itemA = $items[0];

        // Keep A (change it again), drop B, add C.
        $response = $this->actingAs($this->requestor())->post(route('imf.update', $imf->id), $this->updatePayload([
            [$a, ['max_qty' => 30], $itemA->id],
            [$c, ['min_qty' => 5]],
        ]))->json();
        $this->assertSame('success', $response['status']);

        $items = $imf->items()->orderBy('id')->get();
        $this->assertSame([$a, $c], $items->pluck('stock_code')->all());
        $this->assertSame($itemA->id, $items[0]->id);
        $this->assertEquals(30, $items[0]->max_qty);

        $old = InventoryRequestsOldItem::forItems($imf->id, $items);
        // Still the value before the IMF, not the requestor's first draft (20).
        $this->assertEquals(10, $old[$itemA->id]->max_qty);
        $this->assertEquals(1, $old[$items[1]->id]->min_qty);
        $this->assertSame(2, InventoryRequestsOldItem::where('imf_no', $imf->id)->count());
    }

    public function test_a_pre_change_snapshot_is_upgraded_when_the_imf_is_edited()
    {
        $a = $this->code('LG');
        $this->registeredProduct($a, 'BEARING 6205');

        // Shape of an Update IMF saved before this change: one item, and an
        // old-values row with only the changed field and no item_id.
        $imf = InventoryRequest::create([
            'user_id' => self::REQUESTOR_ID, 'status' => Status::SAVED,
            'department' => 'MAINTENANCE', 'type' => 'update',
        ]);
        $item = InventoryRequestItems::create([
            'imf_no' => $imf->id, 'stock_code' => $a, 'item_description' => 'BEARING 6205 2RS',
            'brand' => 'SKF', 'OEM_ID' => 'OEM-' . $a, 'UoM' => 'PC', 'usage_rate_qty' => 4,
            'usage_frequency' => 'Monthly', 'min_qty' => 1, 'max_qty' => 10, 'purpose' => 'Conveyor',
        ]);
        InventoryRequestsOldItem::create(['imf_no' => $imf->id, 'item_description' => 'BEARING 6205']);

        $legacyDiff = InventoryRequestsOldItem::diff(InventoryRequestsOldItem::forItems($imf->id, collect([$item]))[$item->id], $item);
        $this->assertTrue($legacyDiff[0]['changed']);
        $this->assertFalse($legacyDiff[7]['changed']);

        $this->actingAs($this->requestor())->post(route('imf.update', $imf->id), $this->updatePayload([
            [$a, ['item_description' => 'BEARING 6205 2RS', 'max_qty' => 25], $item->id],
        ]));

        $old = InventoryRequestsOldItem::where('imf_no', $imf->id)->get();
        $this->assertCount(1, $old);
        $this->assertSame($item->id, (int) $old[0]->item_id);
        $this->assertSame('BEARING 6205', $old[0]->item_description);
        $this->assertEquals(10, $old[0]->max_qty);

        $changed = array_column(array_filter(InventoryRequestsOldItem::diff($old[0], $item->fresh()), function ($r) {
            return $r['changed'];
        }), 'field');
        $this->assertSame(['item_description', 'max_qty'], array_values($changed));
    }

    public function test_a_form_loaded_before_the_change_still_saves_and_keeps_the_snapshot()
    {
        $a = $this->code('OF');
        $this->registeredProduct($a, 'BEARING 6205');

        // Old create form: one item, plain (non-array) values.
        $single = function ($maxQty) use ($a) {
            return [
                'department' => 'MAINTENANCE', 'type' => 'update', 'action' => 'SAVED',
                'stock_code' => $a, 'item_description' => 'BEARING 6205', 'brand' => 'SKF',
                'OEM_ID' => 'OEM-' . $a, 'UoM' => 'PC', 'usage_rate_qty' => 4,
                'usage_frequency' => 'Monthly', 'purpose' => 'Conveyor', 'min_qty' => 1, 'max_qty' => $maxQty,
            ];
        };

        $this->actingAs($this->requestor())->post(route('new-stock.store'), $single(20))->assertJson(['status' => 'success']);
        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();
        $item = $imf->items()->first();

        // Old edit form: same shape, no item id.
        $this->actingAs($this->requestor())->post(route('imf.update', $imf->id), $single(30))->assertJson(['status' => 'success']);

        $items = $imf->items()->get();
        $this->assertCount(1, $items);
        $this->assertSame($item->id, $items[0]->id);
        $this->assertEquals(30, $items[0]->max_qty);
        $this->assertEquals(10, InventoryRequestsOldItem::forItems($imf->id, $items)[$item->id]->max_qty);
    }

    public function test_the_printed_form_lists_every_item()
    {
        $a = $this->code('PA');
        $b = $this->code('PB');
        $this->registeredProduct($a, 'BEARING 6205');
        $this->registeredProduct($b, 'V-BELT A42');

        $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([
            [$a, ['max_qty' => 20]],
            [$b, ['brand' => 'GATES']],
        ]));
        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();
        $items = $imf->items;

        $html = view('admin.ecommerce.inventory.generate-report', [
            'InventoryRequestData' => $imf,
            'items'                => $items,
            'oldItems'             => InventoryRequestsOldItem::forItems($imf->id, $items),
            'histories'            => collect(),
        ])->render();

        $this->assertStringContainsString('Item 1 &mdash; Stock Code: <span class="col-code">' . $a, $html);
        $this->assertStringContainsString('Item 2 &mdash; Stock Code: <span class="col-code">' . $b, $html);
        $this->assertStringContainsString('GATES', $html);

        $pdf = $this->actingAs($this->requestor())->get(route('imf-request.generate_report', ['id' => $imf->id]));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
    }

    public function test_final_approval_updates_every_items_product_and_the_screens_list_them_all()
    {
        $a = $this->code('FA');
        $b = $this->code('FB');
        $productA = $this->registeredProduct($a, 'BEARING 6205');
        $productB = $this->registeredProduct($b, 'V-BELT A42');

        $this->actingAs($this->requestor())->post(route('new-stock.store'), $this->updatePayload([
            [$a, ['item_description' => 'BEARING 6205 ZZ']],
            [$b, ['brand' => 'GATES']],
        ]));
        $imf = InventoryRequest::where('type', 'update')->orderByDesc('id')->first();
        $imf->update(['status' => Status::APPROVED_MCD]);

        $supervisor = $this->supervisor();

        $view = $this->actingAs($supervisor)->get(route('imf.requests.view', $imf->id));
        $view->assertOk();
        $view->assertSee($a);
        $view->assertSee($b);
        $view->assertSee('BEARING 6205 ZZ');

        $show = $this->actingAs($this->requestor())->get(route('new-stock.show', $imf->id));
        $show->assertOk();
        $show->assertSee($a);
        $show->assertSee($b);

        $this->actingAs($supervisor)->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'update']);

        $this->assertSame(Status::APPROVED_SUPERVISOR, $imf->fresh()->status);
        $this->assertSame('BEARING 6205 ZZ', $productA->fresh()->name);
        $this->assertSame('GATES', $productB->fresh()->brand);
    }
}
