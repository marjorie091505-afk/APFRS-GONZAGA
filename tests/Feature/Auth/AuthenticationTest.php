<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/login')
                ->where('canResetPassword', true)
            );
    }

    public function test_admin_accounts_are_labeled_in_the_navigation()
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get('/flights')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('flights/index')
                ->where('auth.user.is_admin', true)
            );
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('upcomingBookings')
                ->missing('stats')
            );
    }

    public function test_admins_can_authenticate_and_are_redirected_to_the_admin_dashboard()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->get('/flights/create')->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('stats')
                ->missing('upcomingBookings')
            );
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/flights')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('flights/index')
                ->where('auth.user.id', $user->id)
            );

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
