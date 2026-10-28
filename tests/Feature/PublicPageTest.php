<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_landing_page()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText('IMF-MRS-PA');
        $response->assertViewIs('theme.pages.home');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText('Log In to your Account');
        $response->assertSeeInOrder(['Email Address', 'Password', 'Login']);
        $response->assertCookieMissing('session_id');
        $response->assertSessionMissing('errors');
        $response->assertViewIs('theme.pages.login');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function test_catalogue_page()
    {
        $response = $this->get('/catalogue/home');

        $response->assertStatus(200);
        $response->assertSeeText('Product Catalogue');
        $response->assertViewIs('catalogue.home');
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
