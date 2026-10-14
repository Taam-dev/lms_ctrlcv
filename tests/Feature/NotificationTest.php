<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Học viên tham gia khóa học nhận thông báo khi có bài kiểm tra mới được tạo hoặc duyệt
     */
    public function test_enrolled_students_receive_notification_when_quiz_is_created_for_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $nonEnrolledStudent = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học VueJS Thực Chiến',
            'status' => 'approved',
        ]);

        Enrollment::create(['student_id' => $student1->id, 'course_id' => $course->id]);
        Enrollment::create(['student_id' => $student2->id, 'course_id' => $course->id]);

        // Admin tạo bài quiz cho khóa học (status = approved trực tiếp)
        $response = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Quiz Vue Router & Pinia',
            'type' => 'quiz',
            'status' => 'approved',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
        ]);
        $response->assertRedirect();

        // Cả 2 học viên đã đăng ký nhận được thông báo
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student1->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student2->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
        ]);

        // Học viên chưa đăng ký không nhận được thông báo
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $nonEnrolledStudent->id,
        ]);
    }

    /**
     * Giảng viên nhận thông báo khi khóa học được admin duyệt
     */
    public function test_teacher_receives_notification_when_course_is_approved_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Docker DevOps',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.courses.approve', $course->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->id,
            'type' => 'course_approved',
            'title' => 'Khóa học đã được phê duyệt',
        ]);

        $notification = Notification::where('user_id', $teacher->id)->first();
        $this->assertStringContainsString('Docker DevOps', $notification->message);
    }

    /**
     * Giảng viên nhận thông báo khi bài kiểm tra được admin duyệt
     */
    public function test_teacher_receives_notification_when_quiz_is_approved_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Golang',
            'status' => 'approved',
        ]);

        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Golang Concurrency',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.quizzes.approve', $quiz->id));
        $response->assertRedirect();

        // 1. Giảng viên nhận được thông báo quiz đã được duyệt
        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->id,
            'type' => 'quiz_approved',
            'title' => 'Bài kiểm tra đã được phê duyệt',
        ]);

        // 2. Học viên của khóa học cũng nhận được thông báo có bài kiểm tra mới
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
        ]);
    }

    /**
     * Giảng viên nhận thông báo khi có học viên đánh giá/bình luận khóa học
     */
    public function test_course_owner_receives_notification_when_student_reviews_course(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Nguyễn Văn A']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học React TypeScript',
            'status' => 'approved',
        ]);

        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id, 'status' => 'active']);

        $this->actingAs($student)->post(route('student.courses.reviews.store', $course->id), [
            'rating' => 5,
            'comment' => 'Khóa học rất hay và dễ hiểu!',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacher->id,
            'type' => 'course_reviewed',
            'title' => 'Đánh giá mới cho khóa học',
        ]);

        $notification = Notification::where('user_id', $teacher->id)->first();
        $this->assertStringContainsString('Nguyễn Văn A', $notification->message);
        $this->assertStringContainsString('5/5 sao', $notification->message);
    }

    /**
     * Admin nhận thông báo khi có người dùng gửi báo cáo vi phạm
     */
    public function test_admins_receive_notification_when_user_submits_report(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student', 'name' => 'Trần Báo Cáo']);
        $badUser = User::factory()->create(['role' => 'student', 'name' => 'Kẻ Vi Phạm']);

        // Gửi báo cáo người dùng
        $this->actingAs($student)->post(route('users.report', $badUser->id), [
            'reason' => 'harassment',
            'details' => 'Người này có hành vi công kích thô tục.',
        ]);

        // Cả 2 admin đều nhận được thông báo
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin1->id,
            'type' => 'user_reported',
            'title' => 'Báo cáo người dùng vi phạm',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin2->id,
            'type' => 'user_reported',
            'title' => 'Báo cáo người dùng vi phạm',
        ]);
    }

    /**
     * Khi ấn vào thông báo sẽ đánh dấu đã đọc và chuyển hướng đến trang tương ứng
     */
    public function test_clicking_notification_marks_as_read_and_redirects_to_target(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'message' => 'Nội dung thông báo',
            'target_url' => 'http://localhost/test-quiz-target',
            'read_at' => null,
        ]);

        $this->assertFalse($notification->isRead());

        $response = $this->actingAs($user)->get(route('notifications.go', $notification->id));
        $response->assertRedirect('http://localhost/test-quiz-target');

        $notification->refresh();
        $this->assertTrue($notification->isRead());
    }

    /**
     * Người dùng có thể đánh dấu đã đọc tất cả thông báo
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'quiz_created',
            'title' => 'Thông báo 1',
            'message' => 'Nội dung 1',
            'target_url' => '/target-1',
            'read_at' => null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'course_approved',
            'title' => 'Thông báo 2',
            'message' => 'Nội dung 2',
            'target_url' => '/target-2',
            'read_at' => null,
        ]);

        $this->assertEquals(2, $user->unreadNotificationsCount());

        $response = $this->actingAs($user)->post(route('notifications.markAllRead'));
        $response->assertRedirect();

        $this->assertEquals(0, $user->unreadNotificationsCount());
    }
}
