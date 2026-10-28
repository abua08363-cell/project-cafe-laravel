<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_username_and_password_fields(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false);
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_log_in_with_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'albu',
            'password' => 'password',
            'role' => 'staff',
        ]);

        $this->post(route('login.attempt'), [
            'username' => 'albu',
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('role', 'staff')
            ->assertSessionHas('username', 'albu');

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'albu',
            'password' => 'password',
        ]);

        $this->post(route('login.attempt'), [
            'username' => 'albu',
            'password' => 'wrong-password',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Username atau password salah.');

        $this->assertGuest();
    }
}
