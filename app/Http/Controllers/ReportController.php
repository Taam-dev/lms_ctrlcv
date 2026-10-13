<?php

namespace App\Http\Controllers;

use App\Models\CourseReview;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Báo cáo hồ sơ người dùng
     */
    public function storeUserReport(Request $request, User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Bạn không thể tự báo cáo tài khoản của chính mình.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'in:avatar_inappropriate,name_inappropriate,harassment,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Vui lòng chọn lý do báo cáo.',
            'reason.in' => 'Lý do báo cáo không hợp lệ.',
            'details.max' => 'Lý do chi tiết không được vượt quá 1000 ký tự.',
        ]);

        $existing = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('info', 'Bạn đã gửi báo cáo cho tài khoản này trước đó và đang chờ ban quản trị xử lý.');
        }

        Report::create([
            'reporter_id' => Auth::id(),
            'reportable_type' => User::class,
            'reportable_id' => $user->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Cảm ơn bạn! Báo cáo vi phạm tài khoản đã được gửi đến ban quản trị để kiểm duyệt.');
    }

    /**
     * Báo cáo bình luận / nhận xét
     */
    public function storeReviewReport(Request $request, CourseReview $review): RedirectResponse
    {
        if (Auth::id() === $review->user_id) {
            return back()->with('error', 'Bạn không thể tự báo cáo bình luận của chính mình.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'in:negative_comment,spam,misinformation,other'],
            'details' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Vui lòng chọn lý do báo cáo.',
            'reason.in' => 'Lý do báo cáo không hợp lệ.',
            'details.max' => 'Lý do chi tiết không được vượt quá 1000 ký tự.',
        ]);

        $existing = Report::where('reporter_id', Auth::id())
            ->where('reportable_type', CourseReview::class)
            ->where('reportable_id', $review->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('info', 'Bạn đã gửi báo cáo cho bình luận này trước đó và đang chờ ban quản trị xử lý.');
        }

        Report::create([
            'reporter_id' => Auth::id(),
            'reportable_type' => CourseReview::class,
            'reportable_id' => $review->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Cảm ơn bạn! Báo cáo bình luận vi phạm đã được gửi đến ban quản trị để kiểm duyệt.');
    }
}
