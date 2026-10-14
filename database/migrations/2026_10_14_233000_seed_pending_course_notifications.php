<?php

use App\Models\Course;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            $pendingCourses = Course::with('teacher')->where('status', 'pending')->get();
            $adminIds = User::where('role', 'admin')->pluck('id');

            if ($adminIds->isNotEmpty() && $pendingCourses->isNotEmpty()) {
                foreach ($pendingCourses as $course) {
                    $teacherName = $course->teacher ? $course->teacher->name : 'Giảng viên';
                    Notification::send(
                        $adminIds,
                        'Khóa học mới chờ duyệt',
                        "Khóa học \"{$course->title}\" ({$teacherName}) đang chờ quản trị viên phê duyệt.",
                        route('admin.courses.index', ['status' => 'pending']),
                        'course_pending'
                    );
                }
            }
        } catch (Throwable $e) {
            // bỏ qua nếu bảng chưa tồn tại hoặc trong môi trường test
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
