<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APurchasingPagesTest extends TestCase
{
    private $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = 14;
    }

    public function test_pa_index()
    {
        $user = User::find($this->id);
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
        $user = User::find($this->id);
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

    public function test_pa_for_printing()
    {
        $user = User::find($this->id);
        $this->assertTrue(
            $user->role_name() == "Purchasing Officer", 
            'User is not a Purchasing Officer.');
        $response = $this->actingAs($user)->get('/admin-panel/pa/manage_pa');

        $response->assertStatus(200);
        $response->assertSeeText('Purchase Advice List');
        $response->assertViewIs('admin.purchasing.manage');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
