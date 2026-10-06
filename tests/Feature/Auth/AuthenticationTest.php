<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->usuario,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        
        // La aplicación redirige a admin/dashboard o usuario/dashboard según el rol
        $expectedRoute = $user->role === 'admin' ? route('dashboard.admin') : route('dashboard.user');
        $response->assertRedirect($expectedRoute);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->usuario,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
