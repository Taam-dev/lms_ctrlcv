<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_with_duplicate_email_fails_with_specific_message(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Email đã được sử dụng',
        ]);
        $this->assertGuest();
    }

    public function test_check_email_endpoint_returns_correct_status(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $responseTaken = $this->postJson('/check-email', [
            'email' => 'taken@example.com',
        ]);

        $responseTaken->assertOk()
            ->assertJson([
                'exists' => true,
                'message' => 'Email đã được sử dụng',
            ]);

        $responseAvailable = $this->postJson('/check-email', [
            'email' => 'available@example.com',
        ]);

        $responseAvailable->assertOk()
            ->assertJson([
                'exists' => false,
                'message' => null,
            ]);
    }
}
