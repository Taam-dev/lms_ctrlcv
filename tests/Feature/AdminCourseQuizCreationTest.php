<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCourseQuizCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Admin có thể truy cập trang tạo khóa học, người không phải admin bị chặn
     */
    public function test_admin_can_access_create_course_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('admin.courses.create'))->assertForbidden();

        $response = $this->actingAs($admin)->get(route('admin.courses.create'));
        $response->assertStatus(200);
        $response->assertSee('Tạo Khóa Học Mới (Admin)');
    }

    /**
     * Admin có thể tạo khóa học mới với trạng thái đã duyệt ngay lập tức
     */
    public function test_admin_can_create_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($admin)->post(route('admin.courses.store'), [
            'title' => 'Khóa học do Admin tạo',
            'description' => 'Mô tả chi tiết khóa học',
            'teacher_id' => $teacher->id,
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'title' => 'Khóa học do Admin tạo',
            'teacher_id' => $teacher->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Admin có thể chỉnh sửa và cập nhật khóa học
     */
    public function test_admin_can_edit_and_update_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Tên ban đầu',
            'status' => 'pending',
        ]);

        $editResponse = $this->actingAs($admin)->get(route('admin.courses.edit', $course->id));
        $editResponse->assertStatus(200);

        $updateResponse = $this->actingAs($admin)->put(route('admin.courses.update', $course->id), [
            'title' => 'Tên đã sửa đổi',
            'description' => 'Mô tả mới',
            'status' => 'approved',
        ]);

        $updateResponse->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Tên đã sửa đổi',
            'status' => 'approved',
        ]);
    }

    /**
     * Admin có thể xóa khóa học
     */
    public function test_admin_can_delete_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học cần xóa',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course->id));
        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    /**
     * Admin có thể thêm bài giảng vào khóa học
     */
    public function test_admin_can_add_lesson_to_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học mới',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.courses.lessons.store', $course->id), [
            'title' => 'Bài học số 1',
            'content_type' => 'text',
            'content' => 'Nội dung bài học của Admin',
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'title' => 'Bài học số 1',
        ]);
    }

    /**
     * Admin có thể truy cập trang chỉnh sửa bài học
     */
    public function test_admin_can_access_edit_lesson_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học Test',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài học ban đầu',
            'content_type' => 'text',
            'content' => 'Nội dung ban đầu',
            'order_number' => 1,
        ]);

        $this->actingAs($student)->get(route('admin.lessons.edit', $lesson->id))->assertForbidden();

        $response = $this->actingAs($admin)->get(route('admin.lessons.edit', $lesson->id));
        $response->assertStatus(200);
        $response->assertSee('Chỉnh Sửa Bài Giảng: '.$lesson->title);
        $response->assertSee($lesson->title);
    }

    /**
     * Admin có thể cập nhật thông tin bài học
     */
    public function test_admin_can_update_lesson(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học Test',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài học cần sửa',
            'content_type' => 'text',
            'content' => 'Nội dung cũ',
            'order_number' => 1,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.lessons.update', $lesson->id), [
            'title' => 'Bài học đã cập nhật bởi Admin',
            'content_type' => 'video',
            'content' => 'https://www.youtube.com/watch?v=updated',
            'order_number' => 2,
        ]);

        $response->assertRedirect(route('admin.dashboard', ['tab' => 'lessons']));
        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'title' => 'Bài học đã cập nhật bởi Admin',
            'content_type' => 'video',
            'order_number' => 2,
        ]);
    }

    /**
     * Tab bài giảng trong dashboard hiển thị cấu trúc nhóm theo khóa học
     */
    public function test_admin_dashboard_lessons_tab_displays_course_accordion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học Hiển Thị Accordion',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài giảng mẫu Accordion',
            'content_type' => 'text',
            'content' => 'Nội dung',
            'order_number' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'lessons']));
        $response->assertStatus(200);
        $response->assertSee('Khóa học Hiển Thị Accordion');
        $response->assertSee('Bài giảng mẫu Accordion');
        $response->assertSee(route('admin.lessons.edit', $lesson->id));
    }

    /**
     * Admin có thể truy cập trang tạo quiz
     */
    public function test_admin_can_access_create_quiz_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('admin.quizzes.create'))->assertForbidden();

        $response = $this->actingAs($admin)->get(route('admin.quizzes.create'));
        $response->assertStatus(200);
        $response->assertSee('Tạo Bài Kiểm Tra (Quiz) Mới');
    }

    /**
     * Admin có thể tạo bài kiểm tra cho bất kỳ khóa học nào và mặc định đã duyệt
     */
    public function test_admin_can_create_quiz_for_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Lập trình Laravel',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Đề thi trắc nghiệm do Admin tạo',
            'description' => 'Mô tả bài thi',
            'duration_minutes' => 30,
            'passing_score' => 6.0,
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => 'Đề thi trắc nghiệm do Admin tạo',
            'status' => 'approved',
        ]);
    }

    /**
     * Admin có thể tạo quiz nhanh chóng bằng chuỗi JSON
     */
    public function test_admin_can_create_quiz_using_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học Docker',
            'status' => 'approved',
        ]);

        $jsonPayload = [
            'title' => 'Đề thi trắc nghiệm Docker cơ bản',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'questions' => [
                [
                    'question_text' => 'Dockerfile là gì?',
                    'options' => [
                        'Tệp tin cấu hình để xây dựng container image',
                        'Một chương trình chỉnh sửa ảnh',
                        'Một hệ thống quản lý cơ sở dữ liệu',
                    ],
                    'correct_option' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'json_content' => json_encode($jsonPayload),
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => 'Đề thi trắc nghiệm Docker cơ bản',
            'status' => 'approved',
        ]);

        $quiz = Quiz::where('title', 'Đề thi trắc nghiệm Docker cơ bản')->first();
        $this->assertCount(1, $quiz->questions);
        $this->assertTrue((bool) $quiz->questions[0]->options[0]->is_correct);
    }

    /**
     * Admin có thể quản lý câu hỏi (thêm, import JSON, xóa)
     */
    public function test_admin_can_manage_quiz_questions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $admin->id,
            'title' => 'Quiz Admin',
            'status' => 'approved',
        ]);

        // 1. Thêm câu hỏi thủ công
        $addResponse = $this->actingAs($admin)->post(route('admin.quizzes.questions.store', $quiz->id), [
            'question_text' => 'Câu hỏi Admin 1?',
            'options' => ['Đáp án A', 'Đáp án B'],
            'correct_option' => 1,
        ]);
        $addResponse->assertRedirect();
        $this->assertEquals(1, $quiz->questions()->count());

        // 2. Nhập thêm câu hỏi từ JSON
        $importResponse = $this->actingAs($admin)->post(route('admin.quizzes.questions.import', $quiz->id), [
            'json_content' => json_encode([
                [
                    'question_text' => 'Câu hỏi từ JSON?',
                    'options' => ['Opt 1', 'Opt 2'],
                    'correct_option' => 0,
                ],
            ]),
        ]);
        $importResponse->assertRedirect();
        $this->assertEquals(2, $quiz->questions()->count());

        // 3. Xóa câu hỏi
        $question = $quiz->questions()->first();
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.quizzes.questions.destroy', [$quiz->id, $question->id]));
        $deleteResponse->assertRedirect();
        $this->assertEquals(1, $quiz->questions()->count());
    }

    /**
     * Admin có thể xóa vĩnh viễn khóa học/bài giảng đã bị loại bỏ khỏi database
     */
    public function test_admin_can_hard_delete_rejected_course_from_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học đã bị loại bỏ',
            'status' => 'rejected',
        ]);

        // Kiểm tra giao diện hiển thị cả nút Duyệt lại và nút xóa X
        $indexResponse = $this->actingAs($admin)->get(route('admin.courses.index', ['status' => 'rejected']));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Duyệt lại');
        $indexResponse->assertSee(route('admin.courses.destroy', $course->id));

        // Thực hiện xóa vĩnh viễn khỏi database
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.courses.destroy', $course->id));
        $deleteResponse->assertRedirect(route('admin.courses.index', ['status' => 'rejected']));

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }

    /**
     * Người dùng có thể tạo quiz và nhập câu hỏi thủ công trực tiếp ngay khi tạo
     */
    public function test_can_create_quiz_with_manual_questions_directly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::create([
            'teacher_id' => $admin->id,
            'title' => 'Khóa học Demo Quiz',
            'status' => 'approved',
        ]);

        $manualQuestions = [
            [
                'question_text' => 'Câu hỏi soạn thủ công khi tạo quiz?',
                'explanation' => 'Giải thích đáp án chuẩn',
                'options' => [
                    ['option_text' => 'Đáp án A đúng', 'is_correct' => true],
                    ['option_text' => 'Đáp án B sai', 'is_correct' => false],
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'course_id' => $course->id,
            'title' => 'Quiz có câu hỏi thủ công',
            'duration_minutes' => 20,
            'passing_score' => 6.0,
            'status' => 'approved',
            'json_content' => json_encode($manualQuestions),
        ]);

        $quiz = Quiz::where('title', 'Quiz có câu hỏi thủ công')->first();
        $this->assertNotNull($quiz);
        $response->assertRedirect(route('admin.quizzes.questions', $quiz->id));

        $this->assertEquals(1, $quiz->questions()->count());
        $this->assertEquals('Câu hỏi soạn thủ công khi tạo quiz?', $quiz->questions()->first()->question_text);
    }

    /**
     * Khóa học hiển thị avatar người dùng/giảng viên nếu người đó có avatar
     */
    public function test_course_displays_user_avatar_if_available(): void
    {
        $teacher = User::factory()->create([
            'name' => 'Thầy Giáo Ba',
            'role' => 'teacher',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học có Giảng viên Avatar',
            'status' => 'approved',
        ]);

        // Kiểm tra trang chủ
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee($teacher->avatar_url);

        // Kiểm tra trang chi tiết khóa học
        $showResponse = $this->actingAs($student)->get(route('student.courses.show', $course->id));
        $showResponse->assertOk();
        $showResponse->assertSee($teacher->avatar_url);
    }
}
