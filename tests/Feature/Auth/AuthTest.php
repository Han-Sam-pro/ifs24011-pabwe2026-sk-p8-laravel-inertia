<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard/posts')->assertRedirect(route('login'));
    }

    public function test_user_can_register_as_member(): void
    {
        $this->post('/register', [
            'name' => 'Budi',
            'username' => 'budi',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'budi', 'role' => User::ROLE_MEMBER]);
    }

    public function test_user_can_login_with_username_and_is_routed_by_role(): void
    {
        User::factory()->create(['username' => 'anggota', 'password' => 'secret123']);
        User::factory()->admin()->create(['username' => 'boss', 'password' => 'secret123']);

        $this->post('/login', ['username' => 'anggota', 'password' => 'secret123'])
            ->assertRedirect(route('dashboard.index'));

        auth()->logout();

        $this->post('/login', ['username' => 'boss', 'password' => 'secret123'])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_wrong_password_shows_error(): void
    {
        User::factory()->create(['username' => 'anggota', 'password' => 'secret123']);

        $this->post('/login', ['username' => 'anggota', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_logged_in_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
