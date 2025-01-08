<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        $response->assertRedirect(route('users.index'))
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'firstname'     => 'Jude Samuel',
            'middlename'    => 'Burdeos',
            'lastname'      => 'Escol',
            'email'         => 'jude@gwapo.com',
            'role_id'       => 1,
            'department_id' => 1,
            'is_active'     => 1,
        ]);

        $user = User::where('email', 'jude@gwapo.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jude Samuel B. Escol', $user->name);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_imf_creation(){
        
    }
}
