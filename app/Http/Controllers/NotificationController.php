<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Danh sách tất cả thông báo của người dùng
     */
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Khi người dùng nhấp vào một thông báo:
     * Đánh dấu đã đọc và chuyển hướng đến trang đích tương ứng
     */
    public function go(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền truy cập thông báo này.');
        }

        $notification->markAsRead();

        // Nếu là thông báo tạo bài kiểm tra, chuyển hướng tới danh sách bài kiểm tra và tìm kiếm đúng tên bài kiểm tra đó
        if ($notification->type === 'quiz_created') {
            // Xử lý các thông báo cũ còn lưu target_url trỏ trực tiếp đến bài thi (/quizzes/{id}/take)
            if (preg_match('#/quizzes/(\d+)/take#', $notification->target_url, $matches)) {
                $quiz = Quiz::with('course')->find($matches[1]);
                if ($quiz) {
                    return redirect()->route('student.quizzes.index', [
                        'search' => $quiz->title,
                        'quiz_id' => $quiz->id,
                    ]);
                }
            }

            // Xử lý thông báo cũ lưu link trỏ đến courses.index (/courses?search=...)
            if (str_contains($notification->target_url, '/courses')) {
                if (preg_match('/vừa có bài kiểm tra mới:\s*"([^"]+)"/u', $notification->message, $m)) {
                    $quizTitle = $m[1];
                    $courseTitle = null;
                    if (preg_match('/Khóa học\s*"([^"]+)"/u', $notification->message, $cm)) {
                        $courseTitle = $cm[1];
                    }

                    $quiz = Quiz::where('title', $quizTitle)
                        ->when($courseTitle, function ($q) use ($courseTitle) {
                            $q->whereHas('course', function ($cq) use ($courseTitle) {
                                $cq->where('title', $courseTitle);
                            });
                        })
                        ->latest()
                        ->first();

                    if ($quiz) {
                        return redirect()->route('student.quizzes.index', [
                            'search' => $quiz->title,
                            'quiz_id' => $quiz->id,
                        ]);
                    }

                    return redirect()->route('student.quizzes.index', ['search' => $quizTitle]);
                }
            }

            // Xử lý nếu target_url đã trỏ đến quizzes nhưng chưa có quiz_id
            if (str_contains($notification->target_url, '/quizzes') && ! str_contains($notification->target_url, 'quiz_id=')) {
                if (preg_match('/vừa có bài kiểm tra mới:\s*"([^"]+)"/u', $notification->message, $m)) {
                    $quizTitle = $m[1];
                    $courseTitle = null;
                    if (preg_match('/Khóa học\s*"([^"]+)"/u', $notification->message, $cm)) {
                        $courseTitle = $cm[1];
                    }

                    $quiz = Quiz::where('title', $quizTitle)
                        ->when($courseTitle, function ($q) use ($courseTitle) {
                            $q->whereHas('course', function ($cq) use ($courseTitle) {
                                $cq->where('title', $courseTitle);
                            });
                        })
                        ->latest()
                        ->first();

                    if ($quiz) {
                        return redirect()->route('student.quizzes.index', [
                            'search' => $quiz->title,
                            'quiz_id' => $quiz->id,
                        ]);
                    }
                }
            }
        }

        return redirect()->to($notification->target_url);
    }

    /**
     * Đánh dấu tất cả thông báo của người dùng là đã đọc
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả thông báo là đã đọc.',
            ]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    /**
     * Xóa một thông báo của người dùng
     */
    public function destroy(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền xóa thông báo này.');
        }

        $notification->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thông báo thành công.',
            ]);
        }

        return back()->with('success', 'Đã xóa thông báo thành công.');
    }

    /**
     * Xóa tất cả thông báo của người dùng
     */
    public function clearAll(Request $request): JsonResponse|RedirectResponse
    {
        Notification::where('user_id', auth()->id())->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tất cả thông báo.',
            ]);
        }

        return back()->with('success', 'Đã xóa tất cả thông báo thành công.');
    }
}
