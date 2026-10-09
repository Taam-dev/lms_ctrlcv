<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bắt buộc HTTPS khi chạy production hoặc phía sau reverse proxy của Railway
        if (app()->environment('production') || env('FORCE_HTTPS', false) || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('[MiniLMS] Hướng dẫn đặt lại mật khẩu của bạn')
                ->greeting('Xin chào '.$notifiable->name.'!')
                ->line('Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản MiniLMS liên kết với địa chỉ email này.')
                ->action('Đặt lại mật khẩu ngay', $resetUrl)
                ->line("Liên kết đặt lại mật khẩu này sẽ hết hiệu lực sau {$expireMinutes} phút.")
                ->line('Nếu bạn không gửi yêu cầu này, bạn có thể hoàn toàn yên tâm bỏ qua email này hoặc liên hệ quản trị viên nếu có nghi ngờ.')
                ->salutation("Trân trọng,\nĐội ngũ phát triển MiniLMS");
        });
    }
}
