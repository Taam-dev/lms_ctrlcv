<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:'.User::class,
                // không cho viết hoa chữ cái đầu
                function ($attribute, $value, $fail) {
                    if (preg_match('/^[A-Z\p{Lu}]/u', ltrim((string) $value))) {
                        $fail('Email không hợp lệ');
                    }
                },
                'lowercase',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên không hợp lệ.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Email đã được sử dụng',
            'email.lowercase' => 'Email không hợp lệ',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Check if an email address is already in use.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $rawEmail = trim((string) $request->input('email'));

        // kiểm tra chữ cái đầu viết hoa
        if (preg_match('/^[A-Z\p{Lu}]/u', $rawEmail)) {
            return response()->json([
                'exists' => false,
                'invalid' => true,
                'message' => 'Email không hợp lệ',
            ]);
        }

        $email = strtolower($rawEmail);
        $exists = false;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $exists = User::where('email', $email)->exists();
        }

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Email đã được sử dụng' : null,
        ]);
    }
}
