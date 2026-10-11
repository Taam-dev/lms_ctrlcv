<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_contains_google_login_button(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee(route('auth.google'));
        $response->assertSee('Google');
    }

    public function test_register_screen_contains_google_login_button(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee(route('auth.google'));
        $response->assertSee('Google');
    }

    public function test_google_redirect_shows_error_when_keys_missing(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_google_redirect_redirects_to_oauth_provider_when_keys_present(): void
    {
        config([
            'services.google.client_id' => 'dummy-client-id',
            'services.google.client_secret' => 'dummy-client-secret',
        ]);

        $response = $this->get(route('auth.google'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', (string) $response->headers->get('Location'));
    }

    public function test_google_callback_handles_user_cancellation(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Bạn đã hủy yêu cầu đăng nhập bằng Google.');
    }

    public function test_google_callback_registers_and_logs_in_new_user(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUserContract::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('newstudent@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Google Student');
        $socialiteUser->shouldReceive('getNickname')->andReturn('gstudent');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.png');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@example.com',
            'google_id' => 'google-unique-id-12345',
            'name' => 'Google Student',
            'role' => 'student',
        ]);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Existing User',
            'google_id' => null,
            'role' => 'teacher',
        ]);

        $socialiteUser = Mockery::mock(SocialiteUserContract::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-link-id-999');
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getNickname')->andReturn('existing');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar2.png');

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existingUser);
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'existing@example.com',
            'google_id' => 'google-link-id-999',
            'role' => 'teacher', // preserves existing role
        ]);
    }
}
