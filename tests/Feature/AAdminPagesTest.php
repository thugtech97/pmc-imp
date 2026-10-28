<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AAdminPagesTest extends TestCase
{
    private $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = 1;
    }

    public function test_admin_panel_account_management_test()
    {
        $user = User::find($this->id );
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/account/edit?1');

        $response->assertStatus(200);
        $response->assertSeeText('Account Settings');
        $response->assertViewIs('admin.settings.account.edit');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_index_test()
    {
        $user = User::find($this->id );
        $this->assertTrue(
            $user->role_name() == "Admin", 
            'User is not an Admin.');
        $response = $this->actingAs($user)->get('/admin-panel/users/users');

        $response->assertStatus(200);
        $response->assertSeeText('Manage Users');
        $response->assertViewIs('admin.users.index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_admin_user_management_create_test()
    {
        $user = User::find($this->id );
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

    public function test_admin_user_management_edit_test()
    {
        $user = User::find($this->id );
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

    public function test_admin_user_management_view_test()
    {
        $user = User::find($this->id );
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
