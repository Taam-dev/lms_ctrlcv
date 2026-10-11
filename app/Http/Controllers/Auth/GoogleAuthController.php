<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Chuyển hướng người dùng sang trang xác thực Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->with(
                'error',
                'Hệ thống chưa được cấu hình GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET. Vui lòng cấu hình file .env để sử dụng đăng nhập Google.'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Nhận phản hồi callback từ Google sau khi người dùng đồng ý đăng nhập.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Kiểm tra nếu người dùng bấm Hủy (Access Denied) từ Google
        if ($request->has('error')) {
            return redirect()->route('login')->with('status', 'Bạn đã hủy yêu cầu đăng nhập bằng Google.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::error('Google OAuth callback error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return redirect()->route('login')->with('error', 'Không thể xác thực với Google hoặc phiên đăng nhập đã hết hạn. Vui lòng thử lại.');
        }

        $googleId = (string) $googleUser->getId();
        $email = (string) $googleUser->getEmail();
        $name = (string) ($googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User');
        $avatar = $googleUser->getAvatar();

        if (empty($email)) {
            return redirect()->route('login')->with('error', 'Tài khoản Google của bạn không cung cấp địa chỉ email.');
        }

        // 1. Tìm người dùng theo google_id trước
        $user = User::where('google_id', $googleId)->first();

        // 2. Nếu chưa liên kết google_id, tìm theo email đã đăng ký
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $user->avatar ?: $avatar,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            }
        }

        // 3. Nếu chưa có tài khoản, tự động tạo mới
        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'role' => 'student',
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
