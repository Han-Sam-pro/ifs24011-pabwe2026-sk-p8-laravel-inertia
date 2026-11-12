<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_requires_login(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create(['username' => 'lama']);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru',
            'username' => 'baru',
            'email' => 'baru@example.com',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('baru', $user->username);
        $this->assertTrue(Hash::check('passwordbaru', $user->password));
    }

    public function test_profile_page_shows_current_data(): void
    {
        $user = User::factory()->create(['username' => 'profilku']);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Profile/Edit')
                ->where('profile.username', 'profilku'));
    }

    public function test_profile_keeps_password_when_left_blank(): void
    {
        $user = User::factory()->create(['password' => 'tetap123']);

        $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ]);

        $this->assertTrue(Hash::check('tetap123', $user->refresh()->password));
    }

    public function test_username_must_be_unique(): void
    {
        $other = User::factory()->create(['username' => 'dipakai']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'name' => 'X',
            'username' => $other->username,
            'email' => $user->email,
        ])->assertSessionHasErrors('username');
    }
}
