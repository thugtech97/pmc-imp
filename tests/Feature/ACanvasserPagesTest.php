<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ACanvasserPagesTest extends TestCase
{
    private $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = 18;
    }
    ///purchaser/mrs_for_receive
    public function test_purchaser_for_received_assigned_pa()
    {
        $user = User::find($this->id);
        $this->assertTrue(
            $user->role_name() == "Purchaser", 
            'User is not a Purchaser.');
        $response = $this->actingAs($user)->get('/admin-panel/purchaser/mrs_for_receive');

        $response->assertStatus(200);
    }

    public function test_purchaser_assigned_pa()
    {
        $user = User::find($this->id);
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
        $user = User::find($this->id);
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
}
