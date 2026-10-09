<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Giao diện quản trị tổng quan toàn hệ thống cho Admin
     */
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'overview');
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        // Thống kê số liệu hệ thống
        $stats = [
            'total_courses' => Course::count(),
            'pending_courses' => Course::where('status', 'pending')->count(),
            'approved_courses' => Course::where('status', 'approved')->count(),
            'rejected_courses' => Course::where('status', 'rejected')->count(),

            'total_quizzes' => Quiz::count(),
            'pending_quizzes' => Quiz::where('status', 'pending')->count(),
            'approved_quizzes' => Quiz::where('status', 'approved')->count(),
            'rejected_quizzes' => Quiz::where('status', 'rejected')->count(),

            'total_lessons' => Lesson::count(),
            'total_users' => User::count(),
            'admins_count' => User::where('role', 'admin')->count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'students_count' => User::where('role', 'student')->count(),
        ];

        // Dữ liệu cho trang Tổng quan (Overview)
        $pendingCourses = Course::with(['teacher', 'lessons'])->where('status', 'pending')->latest()->take(6)->get();
        $pendingQuizzes = Quiz::with(['course', 'teacher'])->withCount('questions')->where('status', 'pending')->latest()->take(6)->get();
        $recentCourses = Course::with(['teacher', 'lessons'])->withCount('lessons')->latest()->take(6)->get();
        $recentQuizzes = Quiz::with(['course', 'teacher'])->withCount('questions')->latest()->take(6)->get();
        $recentUsers = User::latest()->take(6)->get();

        // Lấy danh sách Khóa học
        $coursesQuery = Course::with(['teacher', 'lessons'])->withCount('lessons')->latest();
        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $coursesQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $coursesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        $courses = $coursesQuery->get();

        // Lấy danh sách Bài kiểm tra (Quizzes)
        $quizzesQuery = Quiz::with(['course', 'teacher'])->withCount('questions')->latest();
        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $quizzesQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $quizzesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('title', 'like', "%{$search}%");
                    });
            });
        }
        $quizzes = $quizzesQuery->get();

        // Lấy danh sách Khóa học cùng các bài giảng cho Tab Bài giảng (Accordion theo Khóa học)
        $coursesWithLessons = Course::with(['teacher', 'lessons' => function ($q) {
            $q->orderBy('order_number');
        }])->withCount('lessons')->latest()->get();

        // Lấy danh sách Bài giảng (tương thích ngược nếu cần)
        $lessons = Lesson::with(['course.teacher'])->latest()->paginate(15);

        // Lấy danh sách Người dùng
        $users = User::latest()->paginate(15);

        return view('admin.dashboard', compact(
            'stats',
            'pendingCourses',
            'pendingQuizzes',
            'recentCourses',
            'recentQuizzes',
            'recentUsers',
            'courses',
            'quizzes',
            'lessons',
            'coursesWithLessons',
            'users',
            'currentTab',
            'statusFilter',
            'search'
        ));
    }

    /**
     * Cập nhật vai trò người dùng trong hệ thống
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,teacher,student'],
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền Quản trị viên của chính tài khoản mình đang sử dụng!');
        }

        $rolesMap = [
            'admin' => 'Quản trị viên',
            'teacher' => 'Giảng viên',
            'student' => 'Học viên',
        ];

        $oldRole = $rolesMap[$user->role] ?? $user->role;
        $newRole = $rolesMap[$validated['role']] ?? $validated['role'];

        $user->update([
            'role' => $validated['role'],
        ]);

        return back()->with('success', "Đã thay đổi vai trò của người dùng \"{$user->name}\" từ {$oldRole} thành {$newRole} thành công!");
    }
}
