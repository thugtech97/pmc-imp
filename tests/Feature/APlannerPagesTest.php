<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APlannerPagesTest extends TestCase
{
    private $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = 6;
    }

    public function test_imf_index()
    {
        $user = User::find($this->id);
        $this->assertTrue($user->role_name() == "MCD Planner" || $user->role_name() == "MCD Verifier", 'User is not an MCD planner/verifier.');
        $response = $this->actingAs($user)->get('/admin-panel/imf/requests');

        $response->assertStatus(200);
        $response->assertSeeText('Inventory Maintenance Form');
        $response->assertViewIs('admin.ecommerce.inventory.imf-index');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_imf_view_edit()
    {
        $user = User::find($this->id);
        $this->assertTrue($user->role_name() == "MCD Planner" || $user->role_name() == "MCD Verifier", 'User is not an MCD planner/verifier.');
        $response = $this->actingAs($user)->get('/admin-panel/imf/request/view/2');

        $response->assertStatus(200);
        $response->assertSeeText('IMF Summary');
        $response->assertViewIs('admin.ecommerce.inventory.imf-view');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_mrs_index()
    {
        $user = User::find($this->id);
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
        $user = User::find($this->id);
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

    public function test_products(){
        $user = User::find($this->id);
        $this->assertTrue(
            $user->role_name() == "MCD Planner", 
            'User is not an MCD planner.');
        $response = $this->actingAs($user)->get('/admin-panel/admin/products');

        $response->assertStatus(200);

    }
}
