<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstructorPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Học viên không có quyền truy cập vào panel giảng viên
     */
    public function test_student_cannot_access_instructor_panel(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('instructor.dashboard'));
        $response->assertForbidden();
    }

    /**
     * Giảng viên với role instructor có thể truy cập panel
     */
    public function test_instructor_role_can_access_instructor_panel(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->get(route('instructor.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Khiển Giảng Viên');
        $response->assertSee('Khóa học của tôi');
    }

    /**
     * Giảng viên với role teacher cũng có thể truy cập panel
     */
    public function test_teacher_role_can_access_instructor_panel(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('instructor.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Khiển Giảng Viên');
    }

    /**
     * /dashboard chuyển hướng Giảng viên về /instructor
     */
    public function test_dashboard_redirects_instructor_to_instructor_panel(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->get(route('dashboard'));
        $response->assertRedirect(route('instructor.dashboard'));
    }

    /**
     * Profile dropdown hiển thị option GV cho giảng viên
     */
    public function test_profile_dropdown_shows_instructor_panel_link_for_instructor(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('GV');
        $response->assertSee(route('instructor.dashboard'));
    }

    /**
     * Header chính không hiển thị option Panel Giảng viên mà chỉ hiển thị trong profile
     */
    public function test_homepage_header_navbar_does_not_contain_instructor_panel_link(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('Panel Giảng Viên (GV)');
    }

    /**
     * Giảng viên có thể tạo khóa học mới của mình
     */
    public function test_instructor_can_create_course(): void
    {
        $instructor = User::factory()->instructor()->create();

        $response = $this->actingAs($instructor)->post(route('instructor.courses.store'), [
            'title' => 'Khóa học Vue 3 Chuyên Sâu',
            'description' => 'Mô tả chi tiết về khóa học',
        ]);

        $response->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));
        $this->assertDatabaseHas('courses', [
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học Vue 3 Chuyên Sâu',
            'status' => 'pending',
        ]);
    }

    /**
     * Giảng viên có thể tạo khóa học với banner đã crop
     */
    public function test_instructor_can_create_course_with_cropped_banner(): void
    {
        Storage::fake('public');

        $instructor = User::factory()->instructor()->create();
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($instructor)->post(route('instructor.courses.store'), [
            'title' => 'Khóa học React và NextJS',
            'description' => 'Mô tả chi tiết',
            'thumbnail_cropped_data' => $fakeBase64,
        ]);

        $response->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));
        $course = Course::where('title', 'Khóa học React và NextJS')->first();
        $this->assertNotNull($course);
        $this->assertNotNull($course->thumbnail);
        $this->assertStringStartsWith('storage/courses/', $course->thumbnail);
    }

    /**
     * Giảng viên có thể cập nhật khóa học của chính mình
     */
    public function test_instructor_can_edit_own_course(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học ban đầu',
            'description' => 'Mô tả cũ',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($instructor)->get(route('instructor.courses.edit', $course->id));
        $response->assertStatus(200);
        $response->assertSee('Khóa học ban đầu');

        $updateResponse = $this->actingAs($instructor)->put(route('instructor.courses.update', $course->id), [
            'title' => 'Khóa học đã cập nhật',
            'description' => 'Mô tả mới',
        ]);

        $updateResponse->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Khóa học đã cập nhật',
            'description' => 'Mô tả mới',
        ]);
    }

    /**
     * Giảng viên không thể chỉnh sửa khóa học của giảng viên khác
     */
    public function test_instructor_cannot_edit_other_instructor_course(): void
    {
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();

        $course = Course::create([
            'teacher_id' => $instructor1->id,
            'title' => 'Khóa học của GV 1',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($instructor2)->get(route('instructor.courses.edit', $course->id));
        $response->assertForbidden();

        $updateResponse = $this->actingAs($instructor2)->put(route('instructor.courses.update', $course->id), [
            'title' => 'Cố tình hack sửa',
        ]);
        $updateResponse->assertForbidden();
    }

    /**
     * Giảng viên có thể xóa khóa học của mình
     */
    public function test_instructor_can_delete_own_course(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học cần xóa',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($instructor)->delete(route('instructor.courses.destroy', $course->id));
        $response->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    /**
     * Giảng viên không thể xóa khóa học của giảng viên khác
     */
    public function test_instructor_cannot_delete_other_instructor_course(): void
    {
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();

        $course = Course::create([
            'teacher_id' => $instructor1->id,
            'title' => 'Khóa học của GV 1',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($instructor2)->delete(route('instructor.courses.destroy', $course->id));
        $response->assertForbidden();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
        ]);
    }

    /**
     * Giảng viên có thể thêm bài giảng vào khóa học của mình
     */
    public function test_instructor_can_create_lesson_for_own_course(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học của tôi',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($instructor)->post(route('instructor.lessons.store', $course->id), [
            'title' => 'Bài 1: Giới thiệu',
            'content_type' => 'text',
            'content' => 'Nội dung bài viết',
            'order_number' => 1,
        ]);

        $response->assertRedirect(route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $course->id]));
        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Bài 1: Giới thiệu',
        ]);
    }

    /**
     * Nhấn lưu bài giảng nhiều lần không tạo ra nhiều bài giảng trùng lặp
     */
    public function test_multiple_rapid_clicks_does_not_create_duplicate_lessons(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học Đơn',
            'status' => 'approved',
        ]);

        $payload = [
            'title' => 'Bài giảng chống duplicate',
            'content_type' => 'text',
            'content' => 'Nội dung bài viết',
            'order_number' => 1,
        ];

        // Lần 1: Lưu thành công
        $res1 = $this->actingAs($instructor)->post(route('instructor.lessons.store', $course->id), $payload);
        $res1->assertRedirect();

        // Lần 2 (ngay lập tức cùng nội dung): Bị chặn trùng lặp
        $res2 = $this->actingAs($instructor)->post(route('instructor.lessons.store', $course->id), $payload);
        $res2->assertRedirect();

        // Lần 3: Tiếp tục gửi lại
        $res3 = $this->actingAs($instructor)->post(route('instructor.lessons.store', $course->id), $payload);
        $res3->assertRedirect();

        // Đảm bảo chỉ có DUY NHẤT 1 bài giảng được tạo trong database
        $this->assertEquals(1, $course->lessons()->where('title', 'Bài giảng chống duplicate')->count());
    }

    /**
     * Giảng viên không thể thêm bài giảng vào khóa học của người khác
     */
    public function test_instructor_cannot_create_lesson_for_other_instructor_course(): void
    {
        $instructor1 = User::factory()->instructor()->create();
        $instructor2 = User::factory()->instructor()->create();

        $course = Course::create([
            'teacher_id' => $instructor1->id,
            'title' => 'Khóa học GV 1',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($instructor2)->post(route('instructor.lessons.store', $course->id), [
            'title' => 'Bài giảng gian lận',
            'content_type' => 'text',
            'content' => 'Cheat content',
            'order_number' => 1,
        ]);

        $response->assertForbidden();
    }

    /**
     * Giảng viên có thể chỉnh sửa và xóa bài giảng của mình
     */
    public function test_instructor_can_edit_and_delete_own_lesson(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài giảng cũ',
            'content_type' => 'text',
            'content' => 'Nội dung',
            'order_number' => 1,
        ]);

        // Cập nhật
        $updateResponse = $this->actingAs($instructor)->put(route('instructor.lessons.update', $lesson->id), [
            'title' => 'Bài giảng đã sửa',
            'content_type' => 'text',
            'content' => 'Nội dung mới',
            'order_number' => 2,
        ]);
        $updateResponse->assertRedirect(route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $course->id]));
        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'title' => 'Bài giảng đã sửa',
            'order_number' => 2,
        ]);

        // Xóa
        $deleteResponse = $this->actingAs($instructor)->delete(route('instructor.lessons.destroy', $lesson->id));
        $deleteResponse->assertRedirect();
        $this->assertDatabaseMissing('lessons', [
            'id' => $lesson->id,
        ]);
    }

    /**
     * Tab bài giảng hiển thị giáo trình được phân chia theo từng khóa học
     */
    public function test_instructor_can_view_lessons_grouped_by_course(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khóa học Thiết kế Web',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài học số 1: HTML cơ bản',
            'content_type' => 'text',
            'content' => 'Nội dung',
            'order_number' => 1,
        ]);

        $response = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'lessons']));
        $response->assertStatus(200);
        $response->assertSee('Giáo trình Bài giảng theo Khóa học');
        $response->assertSee('Khóa học Thiết kế Web');
        $response->assertSee('Bài học số 1: HTML cơ bản');
    }

    /**
     * Không tạo khóa học trùng lặp khi gửi liên tiếp nhiều lần
     */
    public function test_instructor_cannot_create_duplicate_courses_on_rapid_submission(): void
    {
        $instructor = User::factory()->instructor()->create();

        // Lần 1: Tạo khóa học thành công
        $response1 = $this->actingAs($instructor)->post(route('instructor.courses.store'), [
            'title' => 'Khóa học VueJS Nâng Cao',
            'description' => 'Mô tả chi tiết',
        ]);
        $response1->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));

        // Lần 2: Gửi lại ngay lập tức (mô phỏng double click)
        $response2 = $this->actingAs($instructor)->post(route('instructor.courses.store'), [
            'title' => 'Khóa học VueJS Nâng Cao',
            'description' => 'Mô tả chi tiết',
        ]);
        $response2->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));

        // Kiểm tra database chỉ có đúng 1 bản ghi duy nhất
        $this->assertEquals(1, Course::where('teacher_id', $instructor->id)->where('title', 'Khóa học VueJS Nâng Cao')->count());
    }

    /**
     * Đồng bộ chính xác giữa tab trên bảng điều khiển và menu sidebar
     */
    public function test_instructor_panel_tabs_and_sidebar_are_synchronized(): void
    {
        $instructor = User::factory()->instructor()->create();

        // 1. Khi truy cập tab courses, menu Khóa học của tôi phải được đánh dấu active, Bảng điều khiển không active
        $responseCourses = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'courses']));
        $responseCourses->assertStatus(200);
        $responseCourses->assertSee('border-pink-500 bg-white/[0.07] text-white');

        // 2. Khi truy cập tab lessons, menu Danh sách bài giảng được active
        $responseLessons = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'lessons']));
        $responseLessons->assertStatus(200);

        // 3. Khi truy cập tab quizzes, menu Bài kiểm tra được active
        $responseQuizzes = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'quizzes']));
        $responseQuizzes->assertStatus(200);

        // 4. Khi truy cập tab attempts, menu Kết quả học viên được active
        $responseAttempts = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'attempts']));
        $responseAttempts->assertStatus(200);
    }

    /**
     * Các route danh sách cũ của giảng viên tự động chuyển hướng về tab tương ứng trên Dashboard
     */
    public function test_legacy_instructor_courses_and_quizzes_index_redirect_to_dashboard_tabs(): void
    {
        $instructor = User::factory()->instructor()->create();

        $responseCourses = $this->actingAs($instructor)->get(route('instructor.courses.index'));
        $responseCourses->assertRedirect(route('instructor.dashboard', ['tab' => 'courses']));

        $responseQuizzes = $this->actingAs($instructor)->get(route('instructor.quizzes.index'));
        $responseQuizzes->assertRedirect(route('instructor.dashboard', ['tab' => 'quizzes']));
    }

    /**
     * Giảng viên có thể tìm kiếm khóa học của chính mình trên Dashboard
     */
    public function test_instructor_can_search_own_courses(): void
    {
        $instructor = User::factory()->instructor()->create();
        $otherInstructor = User::factory()->instructor()->create();

        Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Khoa hoc React Native Mobile',
            'description' => 'Lap trinh ung dung da nen tang',
            'status' => 'approved',
        ]);

        Course::create([
            'teacher_id' => $instructor->id,
            'title' => 'Lap trinh Golang Backend',
            'description' => 'Xay dung microservices',
            'status' => 'pending',
        ]);

        Course::create([
            'teacher_id' => $otherInstructor->id,
            'title' => 'Khoa hoc ReactJS Web',
            'description' => 'Cua giang vien khac',
            'status' => 'approved',
        ]);

        // Tìm kiếm 'React' -> Chỉ thấy khóa học React Native của chính mình, không thấy của người khác
        $res = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'courses', 'search' => 'React']));
        $res->assertOk();
        $res->assertSee('Khoa hoc React Native Mobile');
        $res->assertDontSee('Lap trinh Golang Backend');
        $res->assertDontSee('Khoa hoc ReactJS Web');

        // Tìm kết hợp search và status
        $resStatus = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'courses', 'search' => 'Golang', 'status' => 'approved']));
        $resStatus->assertOk();
        $resStatus->assertDontSee('Lap trinh Golang Backend');
    }

    /**
     * Giảng viên có thể tìm kiếm bài kiểm tra của chính mình trên Dashboard
     */
    public function test_instructor_can_search_own_quizzes(): void
    {
        $instructor = User::factory()->instructor()->create();
        $otherInstructor = User::factory()->instructor()->create();

        Quiz::create([
            'teacher_id' => $instructor->id,
            'title' => 'Kiem tra Thuật toán Sorting',
            'status' => 'approved',
        ]);

        Quiz::create([
            'teacher_id' => $instructor->id,
            'title' => 'Đề thi OOP Java nâng cao',
            'status' => 'pending',
        ]);

        Quiz::create([
            'teacher_id' => $otherInstructor->id,
            'title' => 'Kiem tra Thuật toán Dijkstra',
            'status' => 'approved',
        ]);

        // Tìm kiếm 'Thuật toán' -> Chỉ thấy quiz của chính mình, không thấy của giảng viên khác
        $res = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'quizzes', 'search' => 'Thuật toán']));
        $res->assertOk();
        $res->assertSee('Kiem tra Thuật toán Sorting');
        $res->assertDontSee('Đề thi OOP Java nâng cao');
        $res->assertDontSee('Kiem tra Thuật toán Dijkstra');
    }

    /**
     * Giảng viên có thể tìm kiếm kết quả học viên (attempts) theo tên hoặc email
     */
    public function test_instructor_can_search_student_attempts_on_dashboard(): void
    {
        $instructor = User::factory()->instructor()->create();

        $quiz = Quiz::create([
            'teacher_id' => $instructor->id,
            'title' => 'KT elonmusk',
            'status' => 'approved',
        ]);

        $student1 = User::factory()->create([
            'name' => 'tam1',
            'email' => 'tam1@gmail.com',
            'role' => 'student',
        ]);

        $student2 = User::factory()->create([
            'name' => 'tam2',
            'email' => 'tam2@gmail.com',
            'role' => 'student',
        ]);

        $student3 = User::factory()->create([
            'name' => 'tam3',
            'email' => 'tam3@gmail.com',
            'role' => 'student',
        ]);

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student1->id,
            'total_questions' => 10,
            'correct_answers' => 5,
            'score' => 5.0,
            'is_passed' => true,
        ]);

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student2->id,
            'total_questions' => 10,
            'correct_answers' => 5,
            'score' => 5.0,
            'is_passed' => true,
        ]);

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student3->id,
            'total_questions' => 10,
            'correct_answers' => 5,
            'score' => 5.0,
            'is_passed' => true,
        ]);

        // Tìm kiếm 'tam1' -> Chỉ thấy tam1, không thấy tam2 và tam3
        $res = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'attempts', 'search' => 'tam1']));
        $res->assertOk();
        $res->assertSee('tam1@gmail.com');
        $res->assertDontSee('tam2@gmail.com');
        $res->assertDontSee('tam3@gmail.com');

        // Tìm kiếm từ khóa không khớp
        $resEmpty = $this->actingAs($instructor)->get(route('instructor.dashboard', ['tab' => 'attempts', 'search' => 'KhongTonTai']));
        $resEmpty->assertOk();
        $resEmpty->assertSee('Không tìm thấy kết quả làm bài nào khớp với');
    }
}
