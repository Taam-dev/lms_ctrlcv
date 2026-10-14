<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Nhập sai email hoặc mật khẩu',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * login loi khi mat khau qua ngan
     */
    public function test_login_fails_when_password_is_too_short(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '12345',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'password' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
        ]);
    }

    /**
     * login loi khi de trong thong tin
     */
    public function test_login_fails_when_fields_are_missing(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Vui lòng nhập email.',
            'password' => 'Vui lòng nhập mật khẩu.',
        ]);
    }

    /**
     * login loi khi email co chu dau viet hoa
     */
    public function test_login_fails_when_email_has_uppercase_first_letter(): void
    {
        $response = $this->post('/login', [
            'email' => 'User@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Email không hợp lệ',
        ]);
    }

    /**
     * login loi khi email sai dinh dang
     */
    public function test_login_fails_when_email_format_is_invalid(): void
    {
        $response = $this->post('/login', [
            'email' => 'emailkhongdungdinhdang',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Địa chỉ email không đúng định dạng.',
        ]);
    }
}
