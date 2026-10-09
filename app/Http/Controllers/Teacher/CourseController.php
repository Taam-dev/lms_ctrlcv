<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    // trả về giao diện danh sách khóa học của giảng viên
    public function index(Request $request)
    {
        return redirect()->route('instructor.dashboard', array_merge(['tab' => 'courses'], $request->query()));
    }

    // trả về form tạo khóa học
    public function create()
    {
        return view('teacher.courses.create');
    }

    // lưu khóa học mới vào database
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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

        // Tránh tạo khóa học trùng lặp khi bấm nút Tạo nhiều lần liên tục
        $lockKey = 'create_course_'.auth()->id();
        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return redirect()->route('instructor.dashboard', ['tab' => 'courses'])
                ->with('success', 'Đang xử lý tạo khóa học, vui lòng không nhấn gửi nhiều lần!');
        }

        try {
            $recentlyCreated = Course::where('teacher_id', auth()->id())
                ->where('title', $request->title)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentlyCreated) {
                return redirect()->route('instructor.dashboard', ['tab' => 'courses'])
                    ->with('success', 'Đã tạo khóa học thành công! Vui lòng chờ quản trị viên phê duyệt.');
            }

            Course::create([
                'teacher_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'thumbnail' => $thumbnail,
                'status' => 'pending',
            ]);
        } finally {
            optional($lock)->release();
        }

        return redirect()->route('instructor.dashboard', ['tab' => 'courses'])
            ->with('success', 'Đã tạo khóa học thành công! Vui lòng chờ quản trị viên phê duyệt.');
    }

    // hiển thị form chỉnh sửa khóa học
    public function edit(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa khóa học này.');

        return view('teacher.courses.edit', compact('course'));
    }

    // cập nhật thông tin khóa học
    public function update(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa khóa học này.');

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
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
            'thumbnail' => $thumbnail,
        ]);

        return redirect()->route('instructor.dashboard', ['tab' => 'courses'])
            ->with('success', 'Đã cập nhật thông tin khóa học thành công!');
    }

    // xóa khóa học
    public function destroy(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền xóa khóa học này.');

        $course->delete();

        return redirect()->route('instructor.dashboard', ['tab' => 'courses'])
            ->with('success', 'Đã xóa khóa học thành công.');
    }
}
