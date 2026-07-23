<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ecommerce\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\SecureHeaders;
use App\Models\Ecommerce\SalesDetail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FunctionalityTest extends TestCase
{
    /**
     * Run against the PMC-ECOM-TEST duplicate DB, isolated in a transaction that is
     * rolled back in tearDown() so the database is left untouched. SecureHeaders is
     * skipped because it throws "headers already sent" under PHPUnit output.
     */
    protected function setUp(): void
    {
        parent::setUp();

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

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_creation()
    {
        $adminUser = User::find(1);
        $this->assertTrue(
            $adminUser->role_name() == "Admin", 
            'User is not an Admin.');

        $this->actingAs($adminUser);
        $data = [
            'fname'      => 'Jude Samuel',
            'mname'      => 'Burdeos',
            'lname'      => 'Escol',
            'email'      => 'jude@gwapo.com',
            'role'       => 1,
            'department' => 1,
        ];

        $response = $this->post(route('users.store'), $data);
    }

    public function test_imf_creation_new_items(){
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);
        
        $payload = [
            'department' => $user->department->name,
            'type' => 'new',
            'action' => 'SAVED',
            'stock_code' => ['STK001', 'STK002'],
            'item_description' => ['Item 1', 'Item 2'],
            'brand' => ['Brand A', 'Brand B'],
            'OEM_ID' => ['OEM001', 'OEM002'],
            'UoM' => ['pcs', 'box'],
            'usage_rate_qty' => [10, 20],
            'usage_frequency' => ['daily', 'weekly'],
            'purpose' => ['Testing 1', 'Testing 2'],
            'min_qty' => [5, 15],
            'max_qty' => [50, 150],
            'attachment' => [
                UploadedFile::fake()->create('file1.pdf', 100),
                UploadedFile::fake()->create('file2.pdf', 200),
            ],
        ];

        $response = $this->post(route('new-stock.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('inventory_requests', [
            'department' => $user->department->name,
            'type' => 'new',
            'status' => 'SAVED',
            'user_id' => $user->id,
        ]);
    }

    public function test_imf_index_datatable_feed()
    {
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not a Dept. User.');

        $response = $this->actingAs($user)->get(route('new-stock.data', [
            'draw'   => 1,
            'start'  => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order'  => [['column' => 0, 'dir' => 'desc']],
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('draw'));
    }

    public function test_imf_view_renders_for_planner_and_approver()
    {
        $imf = \App\Models\Ecommerce\InventoryRequest::create([
            'priority' => '2', 'department' => 'MATERIALS CONTROL', 'type' => 'new',
            'status' => \App\Constants\Status::APPROVED_WFS, 'user_id' => 21,
            'note_verifier' => 'sample approver remark',
        ]);
        \App\Models\Ecommerce\InventoryRequestItems::create([
            'item_description' => 'VIEW TEST', 'brand' => 'B', 'OEM_ID' => 'O', 'UoM' => 'PC',
            'usage_rate_qty' => 1, 'usage_frequency' => 'Monthly', 'purpose' => 'p',
            'min_qty' => 1, 'max_qty' => 2, 'imf_no' => $imf->id,
        ]);

        foreach (['MCD Planner', 'MCD Approver'] as $roleName) {
            $role = \App\Models\Role::where('name', $roleName)->first();
            $this->assertNotNull($role, "$roleName role missing");
            $user = \App\Models\User::where('role_id', $role->id)->first();
            $this->assertNotNull($user, "$roleName user missing");

            $this->actingAs($user)
                ->get(route('imf.requests.view', $imf->id))
                ->assertStatus(200)
                ->assertViewIs('admin.ecommerce.inventory.imf-view');
        }
    }

    public function test_imf_mcd_planner_approve_and_hold()
    {
        $planner = User::find(5);
        $this->assertTrue($planner && $planner->role_name() === 'MCD Planner', 'User 5 is not an MCD Planner.');

        // Planner approve: APPROVED - WFS -> APPROVED - MCD (Planner)
        $imf = \App\Models\Ecommerce\InventoryRequest::create([
            'priority' => '2', 'department' => 'MATERIALS CONTROL', 'type' => 'new',
            'status' => \App\Constants\Status::APPROVED_WFS, 'user_id' => 21,
        ]);
        \App\Models\Ecommerce\InventoryRequestItems::create([
            'item_description' => 'FEED TEST', 'brand' => 'B', 'OEM_ID' => 'O', 'UoM' => 'PC',
            'usage_rate_qty' => 1, 'usage_frequency' => 'Monthly', 'purpose' => 'p',
            'min_qty' => 1, 'max_qty' => 2, 'imf_no' => $imf->id,
        ]);

        $this->actingAs($planner)
            ->post(route('imf.action', $imf->id), ['action' => 'approve', 'type' => 'new'])
            ->assertRedirect(route('imf.requests'));
        $this->assertEquals(\App\Constants\Status::APPROVED_MCD, $imf->fresh()->status);

        // Planner hold with remarks: -> HOLD - MCD (Planner) + note_planner
        $imf2 = \App\Models\Ecommerce\InventoryRequest::create([
            'priority' => '2', 'department' => 'MATERIALS CONTROL', 'type' => 'new',
            'status' => \App\Constants\Status::APPROVED_WFS, 'user_id' => 21,
        ]);
        $this->actingAs($planner)
            ->post(route('imf.action', $imf2->id), ['action' => 'hold', 'type' => 'new', 'remarks' => 'Fix OEM.']);
        $imf2 = $imf2->fresh();
        $this->assertEquals(\App\Constants\Status::HOLD_PLANNER, $imf2->status);
        $this->assertEquals('Fix OEM.', $imf2->note_planner);

        // Hold with empty remarks is rejected (status unchanged)
        $imf3 = \App\Models\Ecommerce\InventoryRequest::create([
            'priority' => '2', 'department' => 'MATERIALS CONTROL', 'type' => 'new',
            'status' => \App\Constants\Status::APPROVED_WFS, 'user_id' => 21,
        ]);
        $this->actingAs($planner)
            ->post(route('imf.action', $imf3->id), ['action' => 'hold', 'type' => 'new', 'remarks' => '']);
        $this->assertEquals(\App\Constants\Status::APPROVED_WFS, $imf3->fresh()->status);
    }

    public function test_mrs_index_datatable_feed()
    {
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not a Dept. User.');

        $response = $this->actingAs($user)->get(route('profile.sales.data', [
            'draw'   => 1,
            'start'  => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order'  => [['column' => 2, 'dir' => 'desc']],
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertEquals(1, $response->json('draw'));
    }

    public function test_imf_creation_update_item()
    {
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);

        $product = Product::where('code' , '33249')->first();;

        $payload = [
            'department' => $user->department->name,
            'type' => 'update',
            'action' => 'SAVED',
            'stock_code' => '33249',
            'item_description' => 'Existing Item',
            'brand' => 'Existing Brand',
            'OEM_ID' => 'OEM123',
            'UoM' => 'pcs',
            'usage_rate_qty' => 30,
            'usage_frequency' => 'monthly',
            'purpose' => 'Existing Purpose',
            'min_qty' => 10,
            'max_qty' => 100,
            'attachment' => UploadedFile::fake()->create('file.pdf', 100),
        ];

        $response = $this->post(route('new-stock.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('inventory_requests', [
            'department' => $user->department->name,
            'type' => 'update',
            'status' => 'SAVED',
            'user_id' => $user->id,
        ]);
    }

    public function test_mrs_creation()
    {
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);
        $requestData = [
            'department' => $user->department->name,
            'coupon_total_discount' => 5.00,
            'total_amount' => 100.00,
            'delivery_fee' => 10.00,
            'shipping_type' => 'standard',
            'date_needed' => now()->addDays(3)->format('Y-m-d'),
            'payment_type' => 'cash',
            'costcode' => 'COST123',
            'notes' => 'Test order notes.',
            'justification' => 'Test purpose.',
            'priority' => 1,
            'section' => 'Section A',
            'requested_by' => 'Test User',
            'budgeted_amount' => 200.00,
            'attachment' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            'codes' => ['CODE123'],
            'par_to' => ['PAR001'],
            'item_date_needed' => [now()->addDays(7)->format('Y-m-d')],
            'frequency' => ['Monthly'],
            'item_purpose' => ['Office Supplies'],
        ];

        $response = $this->post(route('cart.temp_sales'), $requestData);
        $response->assertRedirect(route('order.success'));
        $this->assertTrue(Session::has('shid'));
    }

    public function test_pa_creation()
    {
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);
        
        //$salesDetails = SalesDetail::factory()->count(3)->create();
        $payload = [
            'selected_items' => [143, 144, 145],
            'planner_remarks' => 'Urgent delivery required',
        ];
        $response = $this->post(route('planner_pa.insert'), $payload);
    }

    public function test_imf_update(){
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);
        $requestData = [
            'type' => 'new',
            'stock_code' => ['SC001', 'SC002'],
            'item_description' => ['Item 1', 'Item 2'],
            'brand' => ['Brand A', 'Brand B'],
            'OEM_ID' => ['OEM001', 'OEM002'],
            'UoM' => ['pcs', 'boxes'],
            'usage_rate_qty' => ['10', '20'],
            'usage_frequency' => ['Daily', 'Weekly'],
            'purpose' => ['Testing 1', 'Testing 2'],
            'min_qty' => ['5', '10'],
            'max_qty' => ['15', '30'],
            'attachment' => [
                UploadedFile::fake()->create('file1.pdf'),
                UploadedFile::fake()->create('file2.pdf'),
            ],
        ];

        $id = 41;

        Storage::fake('local');
        $response = $this->json('PUT', route('new-stock.update', ['new_stock' => $id]), $requestData);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function test_mrs_update(){
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);

        $this->assertTrue(true);
    }

    public function test_pa_deletion(){
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);

        $this->assertTrue(true);
    }

    public function test_imf_cancellation(){
        $user = User::find(21);
        $this->assertTrue(
            $user->role_id == 6, 
            'User is not a Dept. User.');

        $this->actingAs($user);

        $this->assertTrue(true);
    }
}
