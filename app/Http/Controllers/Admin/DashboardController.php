<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Report;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Giao diện quản trị tổng quan toàn hệ thống cho Admin
     */
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'overview');
        if ($currentTab === 'lessons') {
            return redirect()->route('admin.courses.index');
        }
        $statusFilter = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        // thong ke so lieu he thong
        $adminQuizIds = Quiz::where('teacher_id', auth()->id())->pluck('id');

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
            'total_attempts' => QuizAttempt::whereIn('quiz_id', $adminQuizIds)->count(),
            'total_users' => User::count(),
            'admins_count' => User::where('role', 'admin')->count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'students_count' => User::where('role', 'student')->count(),

            'total_reports' => Report::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'resolved_reports' => Report::where('status', 'resolved')->count(),
            'dismissed_reports' => Report::where('status', 'dismissed')->count(),
        ];

        // Dữ liệu cho trang Tổng quan (Overview)
        $pendingCourses = Course::with(['teacher', 'lessons'])->where('status', 'pending')->latest()->take(6)->get();
        $pendingQuizzes = Quiz::with(['course', 'teacher'])->withCount('questions')->where('status', 'pending')->latest()->take(6)->get();
        $pendingReports = Report::with(['reporter', 'reportable'])->where('status', 'pending')->latest()->take(6)->get();
        $recentCourses = Course::with(['teacher', 'lessons'])->withCount('lessons')->latest()->take(6)->get();
        $recentQuizzes = Quiz::with(['course', 'teacher'])->withCount('questions')->latest()->take(6)->get();
        $recentUsers = User::latest()->take(6)->get();

        // Lấy danh sách Khóa học
        $coursesQuery = Course::with(['teacher', 'lessons' => function ($q) {
            $q->orderBy('order_number');
        }])->withCount('lessons')->latest();
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
        $coursesWithLessonsQuery = Course::with(['teacher', 'lessons' => function ($q) {
            $q->orderBy('order_number');
        }])->withCount('lessons')->latest();

        if ($search !== '') {
            $coursesWithLessonsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lessons', function ($l) use ($search) {
                        $l->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
            });
        }
        $coursesWithLessons = $coursesWithLessonsQuery->get();

        // Lấy danh sách Bài giảng (tương thích ngược nếu cần)
        $lessonsQuery = Lesson::with(['course.teacher'])->latest();
        if ($search !== '') {
            $lessonsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('title', 'like', "%{$search}%");
                    });
            });
        }
        $lessons = $lessonsQuery->paginate(15);

        // Lấy danh sách Người dùng
        $usersQuery = User::latest();
        if ($search !== '') {
            $lowerSearch = mb_strtolower($search);
            $roleSearch = null;
            if (str_contains($lowerSearch, 'học viên') || $lowerSearch === 'student') {
                $roleSearch = 'student';
            } elseif (str_contains($lowerSearch, 'giảng viên') || $lowerSearch === 'teacher') {
                $roleSearch = 'teacher';
            } elseif (str_contains($lowerSearch, 'quản trị') || str_contains($lowerSearch, 'admin')) {
                $roleSearch = 'admin';
            }

            $usersQuery->where(function ($q) use ($search, $roleSearch) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");

                if ($roleSearch) {
                    $q->orWhere('role', $roleSearch);
                }
            });
        }
        $users = $usersQuery->paginate(15);

        // Lấy danh sách Báo cáo vi phạm (Tab reports)
        $reportsQuery = Report::with(['reporter', 'resolvedBy', 'reportable'])->latest();
        if ($statusFilter && in_array($statusFilter, ['pending', 'resolved', 'dismissed'])) {
            $reportsQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $reportsQuery->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($r) use ($search) {
                        $r->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }
        $reports = $reportsQuery->paginate(15);

        // lay ket qua hoc vien lam quiz do admin tao
        $attemptsQuery = QuizAttempt::with(['student', 'quiz.course'])
            ->whereIn('quiz_id', $adminQuizIds)
            ->latest();

        if ($search !== '' && $currentTab === 'attempts') {
            $lowerSearch = mb_strtolower($search);

            // xu ly tim kiem theo diem so:
            $scoreOperator = null;
            $scoreValue = null;
            $cleanScoreSearch = trim(preg_replace('/^(?:điểm|diem)\s*:?\s*/iu', '', $search));

            if (preg_match('/^(>=|<=|>|<|=)\s*([0-9]+(?:\.[0-9]+)?)(?:\s*\/\s*10)?$/', $cleanScoreSearch, $matches)) {
                $scoreOperator = $matches[1];
                $scoreValue = (float) $matches[2];
            } elseif (preg_match('/^([0-9]+(?:\.[0-9]+)?)(?:\s*\/\s*10)?$/', $cleanScoreSearch, $matches)) {
                $scoreOperator = '=';
                $scoreValue = (float) $matches[1];
            }

            $correctAnswersCount = null;
            if (preg_match('/^([0-9]+)\s*(?:câu|cau)$/iu', $cleanScoreSearch, $matches)) {
                $correctAnswersCount = (int) $matches[1];
            }

            $statusPassed = null;
            if (str_contains($lowerSearch, 'không đạt') || str_contains($lowerSearch, 'chưa đạt') || $lowerSearch === 'fail' || $lowerSearch === 'truot') {
                $statusPassed = false;
            } elseif (str_contains($lowerSearch, 'đạt') || $lowerSearch === 'dat' || $lowerSearch === 'pass') {
                $statusPassed = true;
            }

            $attemptsQuery->where(function ($q) use ($search, $scoreOperator, $scoreValue, $correctAnswersCount, $statusPassed) {
                $q->whereHas('student', function ($s) use ($search) {
                    $s->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('quiz', function ($qz) use ($search) {
                    $qz->where('title', 'like', "%{$search}%")
                        ->orWhereHas('course', function ($c) use ($search) {
                            $c->where('title', 'like', "%{$search}%");
                        });
                });

                if ($scoreOperator !== null && $scoreValue !== null) {
                    $q->orWhere('score', $scoreOperator, $scoreValue);
                }

                if ($correctAnswersCount !== null) {
                    $q->orWhere('correct_answers', $correctAnswersCount);
                }

                if ($statusPassed !== null) {
                    $q->orWhere('is_passed', $statusPassed);
                }
            });
        }

        $attempts = $attemptsQuery->paginate(15);

        return view('admin.dashboard', compact(
            'stats',
            'pendingCourses',
            'pendingQuizzes',
            'pendingReports',
            'recentCourses',
            'recentQuizzes',
            'recentUsers',
            'courses',
            'quizzes',
            'lessons',
            'coursesWithLessons',
            'users',
            'reports',
            'attempts',
            'currentTab',
            'statusFilter',
            'search'
        ));
    }

    /**
     * xoa tai khoan nguoi dung khoi he thong
     */
    public function destroyUser(User $user): RedirectResponse
    {
        // chan tu xoa chinh minh
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        // chan xoa admin khac
        if ($user->role === 'admin') {
            return back()->with('error', 'Bạn không được phép xóa tài khoản của Quản trị viên khác!');
        }

        $userName = $user->name;

        DB::transaction(function () use ($user) {
            // xoa avatar neu co
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // xoa bao cao vi pham lien quan den user
            Report::where('reportable_type', User::class)
                ->where('reportable_id', $user->id)
                ->delete();

            $user->delete();
        });

        return back()->with('success', "Đã xóa vĩnh viễn tài khoản \"{$userName}\" khỏi hệ thống thành công!");
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

        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            return back()->with('error', 'Bạn không được quyền hạ phân quyền của Quản trị viên khác!');
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

    /**
     * Chỉnh sửa số dư ví của người dùng trong hệ thống
     */
    public function updateUserWallet(Request $request, User $user)
    {
        $validated = $request->validate([
            'balance' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'balance.required' => 'Vui lòng nhập số dư ví mới.',
            'balance.integer' => 'Số dư ví phải là số nguyên.',
            'balance.min' => 'Số dư ví không thể là số âm.',
            'balance.max' => 'Số dư ví tối đa là 1.000.000.000 đ.',
        ]);

        $oldBalance = (int) ($user->balance ?? 0);
        $newBalance = (int) $validated['balance'];
        $diff = $newBalance - $oldBalance;

        DB::transaction(function () use ($user, $oldBalance, $newBalance, $diff, $validated) {
            $user->update([
                'balance' => $newBalance,
            ]);

            if ($diff !== 0) {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'code' => 'ADJ_'.strtoupper(Str::random(8)),
                    'type' => $diff > 0 ? 'deposit' : 'refund',
                    'amount' => abs($diff),
                    'balance_before' => $oldBalance,
                    'balance_after' => $newBalance,
                    'status' => 'completed',
                    'description' => 'Admin điều chỉnh số dư ví'.(! empty($validated['note']) ? ': '.$validated['note'] : ''),
                    'payment_method' => 'admin',
                ]);
            }
        });

        return back()->with('success', "Đã cập nhật số dư ví của người dùng \"{$user->name}\" thành ".number_format($newBalance, 0, ',', '.').' đ thành công!');
    }

    /**
     * Xử lý báo cáo vi phạm (Đánh dấu đã giải quyết hoặc bác bỏ, tùy chọn xử lý đối tượng vi phạm)
     */
    public function updateReportStatus(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:resolve,dismiss'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'delete_content' => ['nullable', 'boolean'],
            'reset_avatar' => ['nullable', 'boolean'],
        ]);

        $action = $validated['action'];
        $notes = $validated['admin_notes'] ?? null;
        $deleteContent = $request->boolean('delete_content');
        $resetAvatar = $request->boolean('reset_avatar');

        if ($action === 'resolve') {
            // Nếu admin chọn gỡ avatar của tài khoản bị report
            if ($resetAvatar && $report->reportable instanceof User) {
                $reportedUser = $report->reportable;
                if ($reportedUser->avatar && Storage::disk('public')->exists($reportedUser->avatar)) {
                    Storage::disk('public')->delete($reportedUser->avatar);
                }
                $reportedUser->update(['avatar' => null]);
            }

            // Nếu admin chọn xóa bình luận vi phạm
            if ($deleteContent && $report->reportable instanceof CourseReview) {
                $report->reportable->delete();
            }

            $report->update([
                'status' => 'resolved',
                'admin_notes' => $notes,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            return back()->with('success', 'Đã xử lý và giải quyết báo cáo vi phạm thành công!');
        }

        // Bác bỏ báo cáo
        $report->update([
            'status' => 'dismissed',
            'admin_notes' => $notes,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Đã bác bỏ báo cáo vi phạm!');
    }

    /**
     * Xóa bản ghi báo cáo vi phạm
     */
    public function destroyReport(Report $report): RedirectResponse
    {
        $reportId = $report->id;
        $report->delete();

        return back()->with('success', "Đã xóa bản ghi báo cáo #{$reportId} thành công!");
    }
}
