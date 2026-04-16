<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0912345678',
            'gender' => 'Nam',
            'birthday' => '1990-01-01',
            'occupation' => 'Sinh viên',
            'role' => 'customer',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]);

        $response->assertRedirect(route('login', absolute: false));
    }
}
