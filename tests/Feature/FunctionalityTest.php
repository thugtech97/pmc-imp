<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ecommerce\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Models\Ecommerce\SalesDetail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FunctionalityTest extends TestCase
{
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

        $response->assertStatus(403);
        /*
        $this->assertDatabaseHas('inventory_requests', [
            'department' => 'IT',
            'type' => 'new',
            'status' => 'pending',
            'user_id' => 1,
        ]);
        
        $this->assertDatabaseCount('inventory_request_items', 2);
        */
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
        /*
        $this->assertDatabaseHas('inventory_requests', [
            'department' => 'HR',
            'type' => 'existing',
            'status' => 'approved',
            'user_id' => 1,
        ]);
        $this->assertDatabaseHas('inventory_request_items', [
            'stock_code' => '33249',
            'product_id' => $product->id,
        ]);
        */
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
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success', 'message' => 'Request has been updated!']);

        //$this->assertDatabaseCount('inventory_request_items', 2);
        //$this->assertDatabaseHas('inventory_request_items', ['stock_code' => 'SC001', 'item_description' => 'Item 1']);
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
