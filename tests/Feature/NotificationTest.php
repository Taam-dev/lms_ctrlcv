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

        $createdQuiz = Quiz::where('title', 'Quiz Vue Router & Pinia')->first();

        // Cả 2 học viên đã đăng ký nhận được thông báo với link trang bài kiểm tra kèm tên và quiz_id
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student1->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'target_url' => route('student.quizzes.index', ['search' => 'Quiz Vue Router & Pinia', 'quiz_id' => $createdQuiz->id]),
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student2->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'target_url' => route('student.quizzes.index', ['search' => 'Quiz Vue Router & Pinia', 'quiz_id' => $createdQuiz->id]),
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

        // 2. Học viên của khóa học cũng nhận được thông báo có bài kiểm tra mới trỏ đến trang quiz với search và quiz_id
        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'target_url' => route('student.quizzes.index', ['search' => 'Quiz Golang Concurrency', 'quiz_id' => $quiz->id]),
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

    /**
     * Admin nhận được thông báo khi giảng viên tạo khóa học mới chờ duyệt
     */
    public function test_admins_receive_notification_when_teacher_creates_pending_course(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Thầy Giáo Ba']);

        $response = $this->actingAs($teacher)->post(route('instructor.courses.store'), [
            'title' => 'Khóa học Lập trình Flutter & Dart',
            'description' => 'Mô tả khóa học lập trình mobile',
        ]);
        $response->assertRedirect();

        // Cả 2 admin đều nhận được thông báo về khóa học chờ duyệt
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin1->id,
            'type' => 'course_pending',
            'title' => 'Khóa học mới chờ duyệt',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin2->id,
            'type' => 'course_pending',
            'title' => 'Khóa học mới chờ duyệt',
        ]);

        $notif = Notification::where('user_id', $admin1->id)->where('type', 'course_pending')->first();
        $this->assertStringContainsString('Thầy Giáo Ba', $notif->message);
        $this->assertStringContainsString('Flutter & Dart', $notif->message);
    }

    /**
     * Người dùng có thể xóa một thông báo bằng nút X
     */
    public function test_user_can_delete_notification_via_x_button(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'quiz_created',
            'title' => 'Thông báo cần xóa',
            'message' => 'Nội dung thông báo',
            'target_url' => '/target',
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->delete(route('notifications.destroy', $notification->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    /**
     * Người dùng không thể xóa thông báo của người khác
     */
    public function test_user_cannot_delete_other_user_notification(): void
    {
        $user1 = User::factory()->create(['role' => 'student']);
        $user2 = User::factory()->create(['role' => 'student']);

        $notification = Notification::create([
            'user_id' => $user1->id,
            'type' => 'quiz_created',
            'title' => 'Thông báo của user 1',
            'message' => 'Nội dung',
            'target_url' => '/target',
        ]);

        $response = $this->actingAs($user2)->delete(route('notifications.destroy', $notification->id));
        $response->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
    }

    /**
     * Trang xem tất cả thông báo hiển thị đầy đủ nút X xóa cho từng thông báo
     */
    public function test_notifications_page_renders_x_delete_button_for_notifications(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'quiz_created',
            'title' => 'Thông báo bài thi Toán',
            'message' => 'Nội dung thông báo bài thi',
            'target_url' => '/target-math',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('Thông báo bài thi Toán');
        $response->assertSee(route('notifications.destroy', $notification->id));
    }

    /**
     * Nhấp vào thông báo bài kiểm tra mới sẽ chuyển hướng đến trang bài kiểm tra và tìm kiếm tên bài kèm quiz_id
     */
    public function test_clicking_quiz_created_notification_redirects_to_quizzes_index_with_search_and_quiz_id(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học AI Specialist',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Bài thi cuối kỳ AI',
            'status' => 'approved',
            'duration_minutes' => 45,
            'passing_score' => 8.0,
        ]);

        $notification = Notification::create([
            'user_id' => $student->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'message' => 'Khóa học "Khóa học AI Specialist" vừa có bài kiểm tra mới: "Bài thi cuối kỳ AI". Hãy tham gia làm bài ngay!',
            'target_url' => route('student.quizzes.index', ['search' => $quiz->title, 'quiz_id' => $quiz->id]),
            'read_at' => null,
        ]);

        $response = $this->actingAs($student)->get(route('notifications.go', $notification->id));
        $response->assertRedirect(route('student.quizzes.index', ['search' => 'Bài thi cuối kỳ AI', 'quiz_id' => $quiz->id]));

        $notification->refresh();
        $this->assertTrue($notification->isRead());
    }

    /**
     * Nhấp vào thông báo bài kiểm tra cũ có target_url trực tiếp vào bài làm vẫn được chuyển hướng đến trang quiz với search và quiz_id
     */
    public function test_clicking_legacy_quiz_created_notification_redirects_to_quizzes_index_with_search_and_quiz_id(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Laravel Master',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Eloquent Advance',
            'status' => 'approved',
            'duration_minutes' => 30,
            'passing_score' => 7.0,
        ]);

        $notification = Notification::create([
            'user_id' => $student->id,
            'type' => 'quiz_created',
            'title' => 'Bài kiểm tra mới',
            'message' => 'Nội dung',
            'target_url' => route('student.quizzes.take', $quiz->id),
            'read_at' => null,
        ]);

        $response = $this->actingAs($student)->get(route('notifications.go', $notification->id));
        $response->assertRedirect(route('student.quizzes.index', ['search' => 'Quiz Eloquent Advance', 'quiz_id' => $quiz->id]));

        $notification->refresh();
        $this->assertTrue($notification->isRead());
    }

    /**
     * Khi có 2 bài kiểm tra trùng tên ở 2 khóa học khác nhau, trang quizzes với quiz_id chỉ hiển thị đúng bài đó
     */
    public function test_quizzes_index_with_search_and_quiz_id_isolates_quiz_when_titles_are_identical(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $courseA = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Node.js Cơ Bản',
            'status' => 'approved',
        ]);
        $courseB = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Python Cơ Bản',
            'status' => 'approved',
        ]);

        Enrollment::create(['student_id' => $student->id, 'course_id' => $courseA->id]);
        Enrollment::create(['student_id' => $student->id, 'course_id' => $courseB->id]);

        $quizA = Quiz::create([
            'course_id' => $courseA->id,
            'teacher_id' => $teacher->id,
            'title' => 'Kiểm tra 15 phút',
            'status' => 'approved',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
        ]);

        $quizB = Quiz::create([
            'course_id' => $courseB->id,
            'teacher_id' => $teacher->id,
            'title' => 'Kiểm tra 15 phút',
            'status' => 'approved',
            'duration_minutes' => 15,
            'passing_score' => 5.0,
        ]);

        // Gửi request kèm search đúng tên bài và quiz_id của quizA
        $response = $this->actingAs($student)->get(route('student.quizzes.index', [
            'search' => 'Kiểm tra 15 phút',
            'quiz_id' => $quizA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Khóa học Node.js Cơ Bản');
        $response->assertDontSee('Khóa học Python Cơ Bản');
    }
}
