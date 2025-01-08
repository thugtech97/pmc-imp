<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleBasedPageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_panel_auth()
    {
        $response = $this->get('/admin-panel');

        $response->assertStatus(200);
        $response->assertSeeText('Log In');
        $response->assertSeeText('Welcome to IMF-MRS-PA (IMP) Admin Portal. Please sign in to continue.');
        $response->assertSeeInOrder(['Email', 'Password', 'Log In', 'Forgot Password']);
        $response->assertCookieMissing('session_id');
        $response->assertSessionMissing('errors');
        $response->assertViewIs('auth.login');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_panel_account_management()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/admin-panel/account/edit?1');

        $response->assertStatus(200);
        $response->assertSeeText('Account Settings');
        $response->assertViewIs('admin.settings.account.edit');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_imf_index()
    {
        $user = User::find(6);
        $this->assertTrue($user->role_name() == "MCD Planner" || $user->role_name() == "MCD Verifier", 'User is not an MCD planner/verifier.');
        $response = $this->actingAs($user)->get('/admin-panel/imf/requests');

        $response->assertStatus(200);
        $response->assertSeeText('Inventory Maintenance Form');
        $response->assertViewIs('admin.ecommerce.inventory.imf-index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_imf_view_edit()
    {
        $user = User::find(6);
        $this->assertTrue($user->role_name() == "MCD Planner" || $user->role_name() == "MCD Verifier", 'User is not an MCD planner/verifier.');
        $response = $this->actingAs($user)->get('/admin-panel/imf/request/view/2');

        $response->assertStatus(200);
        $response->assertSeeText('IMF Summary');
        $response->assertViewIs('admin.ecommerce.inventory.imf-view');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_mrs_index()
    {
        $user = User::find(6);
        $this->assertTrue(
            $user->role_name() == "MCD Planner" || 
            $user->role_name() == "MCD Verifier" ||
            $user->role_name() == "MCD Approver", 
            'User is not an MCD planner/verifier/approver.');
        $response = $this->actingAs($user)->get('/admin-panel/admin/sales-transaction');

        $response->assertStatus(200);
        $response->assertSeeText('MRS Requests');
        $response->assertViewIs('admin.ecommerce.sales.index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_mrs_view_edit()
    {
        $user = User::find(6);
        $this->assertTrue(
            $user->role_name() == "MCD Planner" || 
            $user->role_name() == "MCD Verifier" ||
            $user->role_name() == "MCD Approver", 
            'User is not an MCD planner/verifier/approver.');
        $response = $this->actingAs($user)->get('/admin-panel/admin/sales-transaction/view/5');

        $response->assertStatus(200);
        $response->assertSeeText('MRS# 20240925-0003 Transaction Summary');
        $response->assertViewIs('admin.ecommerce.sales.view');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_pa_index()
    {
        $user = User::find(14);
        $this->assertTrue(
            $user->role_name() == "Purchasing Officer", 
            'User is not a Purchasing Officer.');
        $response = $this->actingAs($user)->get('/admin-panel/pa/mrs_for_pa');

        $response->assertStatus(200);
        $response->assertSeeText('For Purchase Advice');
        $response->assertViewIs('admin.purchasing.index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_pa_view_assign()
    {
        $user = User::find(14);
        $this->assertTrue(
            $user->role_name() == "Purchasing Officer", 
            'User is not a Purchasing Officer.');
        $response = $this->actingAs($user)->get('/admin-panel/admin/mrs/view/5');

        $response->assertStatus(200);
        $response->assertSeeText('MRS# 20240925-0003 Transaction Summary');
        $response->assertSeeText('Assign');
        $response->assertViewIs('admin.purchasing.view');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_purchaser_assigned_pa()
    {
        $user = User::find(18);
        $this->assertTrue(
            $user->role_name() == "Purchaser", 
            'User is not a Purchaser.');
        $response = $this->actingAs($user)->get('/admin-panel/purchaser/mrs_received');

        $response->assertStatus(200);
        $response->assertSeeText('PA List (Received)');
        $response->assertViewIs('admin.purchasing.purchaser_index_received');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_purchaer_assigned_pa_view_edit()
    {
        $user = User::find(18);
        $this->assertTrue(
            $user->role_name() == "Purchaser", 
            'User is not a Purchaser.');
        $response = $this->actingAs($user)->get('/admin-panel/purchaser/mrs/view/3');

        $response->assertStatus(200);
        $response->assertSeeText('MRS# 20240913-0003 Transaction Summary');
        $response->assertSeeText('Submit');
        $response->assertViewIs('admin.purchasing.purchaser_view');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_pa_for_printing()
    {
        $user = User::find(14);
        $this->assertTrue(
            $user->role_name() == "Purchasing Officer", 
            'User is not a Purchasing Officer.');
        $response = $this->actingAs($user)->get('/admin-panel/pa/manage_pa');

        $response->assertStatus(200);
        $response->assertSeeText('Purchase Advice List');
        $response->assertViewIs('admin.purchasing.manage');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_index()
    {
        $user = User::find(1);
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/users/users');

        $response->assertStatus(200);
        $response->assertSeeText('Manage Users');
        $response->assertViewIs('admin.users.index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_create()
    {
        $user = User::find(1);
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/users/users/create');

        $response->assertStatus(200);
        $response->assertSeeText('Create a User');
        $expectedLabels = [
            'First Name *',
            'Middle Name',
            'Last Name *',
            'Email *',
            'Department *',
            'Role *',
            'Create User',
            'Cancel'
        ];
        $response->assertSeeInOrder($expectedLabels);
        $response->assertViewIs('admin.users.create');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_edit()
    {
        $user = User::find(1);
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/users/users/23/edit');

        $response->assertStatus(200);
        $response->assertSeeText('Edit a User');
        $expectedLabels = [
            'First Name *',
            'Middle Name *',
            'Last Name *',
            'Email *',
            'Role *',
            'Update User',
            'Cancel'
        ];
        $response->assertSeeInOrder($expectedLabels);
        $response->assertViewIs('admin.users.edit');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_view()
    {
        $user = User::find(1);
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/users/users/23');

        $response->assertStatus(200);
        $response->assertSeeText('Go back to Dashboard');
        $response->assertSeeText('My Recent Activities');
        $response->assertViewIs('admin.users.profile');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
