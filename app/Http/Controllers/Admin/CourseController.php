<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Xem tất cả khóa học của các giảng viên
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $query = Course::with(['teacher', 'lessons' => function ($q) {
            $q->orderBy('order_number');
        }])->withCount('lessons')->latest();

        $totalCount = Course::count();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('lessons', function ($l) use ($search) {
                        $l->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $courses = $query->get();

        return view('admin.courses.index', compact('courses', 'status', 'totalCount', 'search'));
    }

    /**
     * Form tạo khóa học mới cho Admin
     */
    public function create()
    {
        $teachers = User::whereIn('role', ['teacher', 'admin'])->orderBy('name')->get();

        return view('admin.courses.create', compact('teachers'));
    }

    /**
     * Lưu khóa học mới do Admin tạo
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|integer|min:0',
            'teacher_id' => 'nullable|exists:users,id',
            'status' => 'required|in:approved,pending,rejected',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'thumbnail_cropped_data' => 'nullable|string',
            'thumbnail_preset' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url|max:500',
        ]);

        $thumbnail = null;
        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('courses', 'public');
            $thumbnail = 'storage/'.$path;
        } elseif ($request->filled('thumbnail_cropped_data') && str_starts_with($request->thumbnail_cropped_data, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->thumbnail_cropped_data, $type)) {
                $data = substr($request->thumbnail_cropped_data, strpos($request->thumbnail_cropped_data, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $ext = strtolower($type[1] === 'jpeg' ? 'jpg' : $type[1]);
                    $fileName = 'courses/'.Str::uuid().'.'.$ext;
                    Storage::disk('public')->put($fileName, $decoded);
                    $thumbnail = 'storage/'.$fileName;
                }
            }
        } elseif (! empty($request->thumbnail_url)) {
            $thumbnail = $request->thumbnail_url;
        } elseif (! empty($request->thumbnail_preset)) {
            $thumbnail = $request->thumbnail_preset;
        }

        $teacherId = $request->teacher_id ?: auth()->id();

        $lockKey = 'admin_create_course_'.auth()->id();
        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return redirect()->route('admin.courses.index')
                ->with('success', 'Đang xử lý tạo khóa học, vui lòng không nhấn gửi nhiều lần!');
        }

        try {
            $recentlyCreated = Course::where('teacher_id', $teacherId)
                ->where('title', $request->title)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentlyCreated) {
                return redirect()->route('admin.courses.index')
                    ->with('success', 'Đã tạo khóa học thành công!');
            }

            $course = Course::create([
                'teacher_id' => $teacherId,
                'title' => $request->title,
                'description' => $request->description,
                'price' => (int) ($request->input('price', 0) ?? 0),
                'thumbnail' => $thumbnail,
                'status' => $request->status,
            ]);

            if ($request->status === 'pending') {
                $adminIds = User::where('role', 'admin')->pluck('id');
                $teacher = User::find($teacherId);
                $teacherName = $teacher ? $teacher->name : auth()->user()->name;
                Notification::send(
                    $adminIds,
                    'Khóa học mới chờ duyệt',
                    "Khóa học \"{$course->title}\" ({$teacherName}) đang ở trạng thái chờ phê duyệt.",
                    route('admin.courses.index', ['status' => 'pending']),
                    'course_pending'
                );
            }
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('admin.courses.index')
            ->with('success', 'Đã tạo khóa học thành công!');
    }

    /**
     * Form chỉnh sửa thông tin khóa học (Admin)
     */
    public function edit(Course $course)
    {
        $teachers = User::whereIn('role', ['teacher', 'admin'])->orderBy('name')->get();

        return view('admin.courses.edit', compact('course', 'teachers'));
    }

    /**
     * Cập nhật thông tin khóa học (Admin)
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|integer|min:0',
            'teacher_id' => 'nullable|exists:users,id',
            'status' => 'required|in:approved,pending,rejected',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'thumbnail_cropped_data' => 'nullable|string',
            'remove_thumbnail' => 'nullable|boolean',
            'thumbnail_preset' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url|max:500',
        ]);

        $thumbnail = $course->thumbnail;
        if ($request->boolean('remove_thumbnail')) {
            $thumbnail = null;
        }

        if ($request->hasFile('thumbnail_file')) {
            $path = $request->file('thumbnail_file')->store('courses', 'public');
            $thumbnail = 'storage/'.$path;
        } elseif ($request->filled('thumbnail_cropped_data') && str_starts_with($request->thumbnail_cropped_data, 'data:image/')) {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->thumbnail_cropped_data, $type)) {
                $data = substr($request->thumbnail_cropped_data, strpos($request->thumbnail_cropped_data, ',') + 1);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $ext = strtolower($type[1] === 'jpeg' ? 'jpg' : $type[1]);
                    $fileName = 'courses/'.Str::uuid().'.'.$ext;
                    Storage::disk('public')->put($fileName, $decoded);
                    $thumbnail = 'storage/'.$fileName;
                }
            }
        } elseif (! empty($request->thumbnail_url)) {
            $thumbnail = $request->thumbnail_url;
        } elseif ($request->filled('thumbnail_preset')) {
            $thumbnail = $request->thumbnail_preset;
        }

        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => (int) ($request->input('price', 0) ?? 0),
            'thumbnail' => $thumbnail,
            'teacher_id' => $request->teacher_id ?: $course->teacher_id,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Đã cập nhật thông tin khóa học thành công!');
    }

    /**
     * Xóa khóa học (Admin)
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->back(fallback: route('admin.courses.index'))
            ->with('success', 'Đã xóa bài giảng / khóa học vĩnh viễn khỏi cơ sở dữ liệu!');
    }

    /**
     * Form thêm bài giảng vào khóa học (Admin)
     */
    public function createLesson(Course $course)
    {
        return view('admin.courses.create_lesson', compact('course'));
    }

    /**
     * Lưu bài giảng mới vào khóa học (Admin)
     */
    public function storeLesson(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:text,video',
            'content' => 'required|string',
            'order_number' => 'nullable|integer|min:1',
        ]);

        // Tránh tạo bài giảng trùng lặp khi bấm nút Lưu nhiều lần liên tục
        $lockKey = 'create_admin_lesson_'.auth()->id().'_'.$course->id;
        $lock = Cache::lock($lockKey, 5);

        $redirectTo = $request->input('redirect_to', route('admin.courses.index', ['expand' => $course->id]));

        if (! $lock->get()) {
            return redirect($redirectTo)
                ->with('success', 'Đang xử lý thêm bài giảng, vui lòng không nhấn gửi nhiều lần!');
        }

        try {
            $recentlyCreated = Lesson::where('course_id', $course->id)
                ->where('title', $request->title)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentlyCreated) {
                return redirect($redirectTo)
                    ->with('success', 'Đã thêm bài giảng vào khóa học thành công!');
            }

            $orderNumber = $request->filled('order_number')
                ? (int) $request->order_number
                : ($course->lessons()->count() + 1);

            Lesson::create([
                'course_id' => $course->id,
                'title' => $request->title,
                'content_type' => $request->content_type,
                'content' => $request->content,
                'order_number' => $orderNumber,
            ]);
        } finally {
            $lock->release();
        }

        return redirect($redirectTo)
            ->with('success', 'Đã thêm bài giảng vào khóa học thành công!');
    }

    /**
     * Duyệt khóa học (khi mới tạo hoặc phê duyệt lại)
     */
    public function approve($id)
    {
        $course = Course::findOrFail($id);
        $course->update(['status' => 'approved']);

        if ($course->teacher_id && $course->teacher_id !== auth()->id()) {
            Notification::send(
                $course->teacher_id,
                'Khóa học đã được phê duyệt',
                "Khóa học \"{$course->title}\" của bạn đã được quản trị viên duyệt và sẵn sàng cho học viên tham gia.",
                route('instructor.courses.edit', $course->id),
                'course_approved'
            );
        }

        return back()->with('success', 'Đã duyệt khóa học thành công!');
    }

    /**
     * Từ chối khóa học (khi giảng viên tạo mới và chưa đạt yêu cầu)
     */
    public function reject($id)
    {
        $course = Course::findOrFail($id);
        $course->update(['status' => 'rejected']);

        return back()->with('success', 'Đã từ chối khóa học!');
    }

    /**
     * Loại bỏ khóa học (khi khóa học đã duyệt và admin muốn gỡ bỏ/thu hồi)
     */
    public function remove($id)
    {
        $course = Course::findOrFail($id);
        $course->update(['status' => 'rejected']);

        return back()->with('success', 'Đã loại bỏ khóa học khỏi danh sách hiển thị!');
    }

    /**
     * Form chỉnh sửa bài giảng cho Admin
     */
    public function editLesson($id)
    {
        $lesson = Lesson::with('course.teacher')->findOrFail($id);

        return view('admin.courses.edit_lesson', compact('lesson'));
    }

    /**
     * Cập nhật bài giảng cho Admin
     */
    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text',
            'content' => 'required|string',
            'order_number' => 'required|integer|min:1',
        ]);

        $lesson->update([
            'title' => $request->title,
            'content_type' => $request->content_type,
            'content' => $request->content,
            'order_number' => $request->order_number,
        ]);

        $redirectTo = $request->input('redirect_to', route('admin.courses.index', ['expand' => $lesson->course_id]));

        return redirect($redirectTo)
            ->with('success', 'Đã cập nhật bài giảng thành công!');
    }

    /**
     * Xóa bài giảng riêng lẻ nếu không phù hợp
     */
    public function destroyLesson($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->delete();

        return back()->with('success', 'Đã xóa bài giảng thành công!');
    }
}
