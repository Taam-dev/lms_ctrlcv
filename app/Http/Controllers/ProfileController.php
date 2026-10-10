<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Xóa avatar cũ nếu người dùng yêu cầu gỡ bỏ ảnh
        if ($request->boolean('remove_avatar') && $user->avatar) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        }

        // Lưu avatar mới nếu có tải lên (tệp tin hoặc dữ liệu đã crop)
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        } elseif ($request->filled('avatar_cropped_data') && str_starts_with($request->avatar_cropped_data, 'data:image/')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            if (preg_match('/^data:image\/(\w+);base64,/', $request->avatar_cropped_data, $type)) {
                $data = substr($request->avatar_cropped_data, strpos($request->avatar_cropped_data, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $ext = strtolower($type[1] === 'jpeg' ? 'jpg' : $type[1]);
                    $fileName = 'avatars/'.Str::uuid().'.'.$ext;
                    Storage::disk('public')->put($fileName, $decoded);
                    $user->avatar = $fileName;
                }
            }
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's email address with current password confirmation.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validateWithBag('changeEmail', [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($request->user()->id),
            ],
            'password' => ['required', 'current_password'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email mới.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng',
            'password.required' => 'Vui lòng nhập mật khẩu hiện tại để xác nhận đổi email.',
            'password.current_password' => 'Mật khẩu hiện tại không chính xác.',
        ]);

        $user = $request->user();
        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'email-updated');
    }

    /**
     * Send password reset link to user's original email.
     */
    public function sendPasswordResetLink(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $status = Password::sendResetLink(['email' => $user->email]);

        $message = $status === Password::RESET_LINK_SENT
            ? 'Đã gửi liên kết đổi mật khẩu về email gốc ('.$user->email.'). Vui lòng kiểm tra hộp thư để đổi lại mật khẩu.'
            : 'Không thể gửi email đặt lại mật khẩu: '.($status ? __($status) : '');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $status === Password::RESET_LINK_SENT,
                'message' => $message,
            ]);
        }

        return Redirect::route('profile.edit')
            ->with('status', 'reset-link-sent')
            ->with('reset_message', $message)
            ->with('email_modal_open', true);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
