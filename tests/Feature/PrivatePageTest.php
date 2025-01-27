<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrivatePageTest extends TestCase
{
    //use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_dept_user_imf_index()
    {
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/inventory/new-stock');
        $response->assertStatus(403);
        /*
        $response->assertSee('Inventory Maintenance Form');
        $response->assertSee('Add New Request');
        $response->assertSee(route('new-stock.create'));
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        */
    }

    public function test_dept_user_imf_create(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/inventory/new-stock/create');
        $response->assertStatus(403);
        /*
        $response->assertSee('Inventory Maintenance Form (IMF) - New Request');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        */
    }

    public function test_dept_user_imf_edit(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/inventory/new-stock/2/edit');
        $response->assertStatus(200);
        /*
        $response->assertSee('Inventory Maintenance Form (IMF) - Update Request');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        */
    }

    public function test_dept_user_imf_view(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/inventory/new-stock/2');
        $response->assertStatus(200);
        /*
        $response->assertSee('Inventory Maintenance Form (IMF) - View Request');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        */
    }

    public function test_dept_user_mrs_index(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/my-orders');
        $response->assertStatus(200);
        $response->assertSee('MRS - For Purchase (DP, Stock Item)');
        $response->assertSee('Note: The next person has three days to review or approve it before forwarding to the next level. Please be reminded to raise a request 1 to 2 months earlier before the Date Needed to avoid rush processing of your request. Thank you.');
        $response->assertSee('Add New MRS');
        $response->assertSee(route('cart.front.show'));
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_dept_user_cart(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee('Cart');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_dept_user_place_mrs_request(){
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Review and Place Request');
        $response->assertSee('Priority #');
        $response->assertSee('Date Needed');
        $response->assertSeeInOrder(['Cost Code', 'Purpose', 'Attach files']);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_dept_user_manage_account()
    {
        $user = User::find(21);
        $this->assertTrue($user->role_id == 6, 'User is not dept. user.');
        $response = $this->actingAs($user)->get('/manage-account');
        $response->assertStatus(200);
        $response->assertSee('Personal Information');
        $response->assertSee('Address Information');
        $response->assertSee('Contact Information');
        $response->assertSee('Update');
        $expectedLabels = [
            'First Name',
            'Last Name',
            'Street *',
            'Municipality *',
            'City *',
            'Zip *',
            'Telephone Number *',
            'Mobile Number *',
        ];
        $response->assertSeeInOrder($expectedLabels);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }


}
