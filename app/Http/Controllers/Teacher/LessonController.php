<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LessonController extends Controller
{
    // trả về form tạo bài giảng cho 1 khóa học cụ thể
    public function create(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền thêm bài giảng vào khóa học này.');

        return view('teacher.lessons.create', compact('course'));
    }

    // lưu bài giảng mới vào database
    public function store(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền thêm bài giảng vào khóa học này.');

        $request->validate([
            'title' => 'required|string|max:255',
            'content_type' => 'required|in:video,text',
            'content' => 'required|string',
            'order_number' => 'required|integer|min:1',
        ]);

        // Tránh tạo bài giảng trùng lặp khi bấm nút Lưu nhiều lần liên tục
        $lockKey = 'create_lesson_'.auth()->id().'_'.$course->id;
        $lock = Cache::lock($lockKey, 5);

        if (! $lock->get()) {
            return redirect()->route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $course->id])
                ->with('success', 'Đang xử lý tạo bài giảng, vui lòng không nhấn gửi nhiều lần!');
        }

        try {
            $recentlyCreated = Lesson::where('course_id', $course->id)
                ->where('title', $request->title)
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentlyCreated) {
                return redirect()->route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $course->id])
                    ->with('success', 'Đã thêm bài giảng mới thành công!');
            }

            Lesson::create([
                'course_id' => $course->id,
                'title' => $request->title,
                'content_type' => $request->content_type,
                'content' => $request->content,
                'order_number' => $request->order_number,
            ]);
        } finally {
            $lock->release();
        }

        return redirect()->route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $course->id])
            ->with('success', 'Đã thêm bài giảng mới thành công!');
    }

    // trả về form chỉnh sửa bài giảng
    public function edit(Lesson $lesson)
    {
        $lesson->load('course');
        abort_if($lesson->course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa bài giảng này.');

        return view('teacher.lessons.edit', compact('lesson'));
    }

    // cập nhật bài giảng
    public function update(Request $request, Lesson $lesson)
    {
        $lesson->load('course');
        abort_if($lesson->course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa bài giảng này.');

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

        return redirect()->route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $lesson->course_id])
            ->with('success', 'Đã cập nhật bài giảng thành công!');
    }

    // xóa bài giảng
    public function destroy(Lesson $lesson)
    {
        $lesson->load('course');
        abort_if($lesson->course->teacher_id !== auth()->id(), 403, 'Bạn không có quyền xóa bài giảng này.');

        $lesson->delete();

        return back()->with('success', 'Đã xóa bài giảng thành công.');
    }
}
