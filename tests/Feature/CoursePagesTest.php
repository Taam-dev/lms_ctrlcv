<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursePagesTest extends TestCase
{
    use RefreshDatabase;

    private function createCourses(int $approved, int $pending = 0): void
    {
        $teacher = User::factory()->teacher()->create();

        for ($i = 1; $i <= $approved; $i++) {
            Course::create([
                'teacher_id' => $teacher->id,
                'title' => "Khoa hoc duyet {$i}",
                'description' => 'Mo ta',
                'status' => 'approved',
            ]);
        }

        for ($i = 1; $i <= $pending; $i++) {
            Course::create([
                'teacher_id' => $teacher->id,
                'title' => "Khoa hoc cho {$i}",
                'description' => 'Mo ta',
                'status' => 'pending',
            ]);
        }
    }

    public function test_home_page_shows_only_three_approved_courses(): void
    {
        $this->createCourses(approved: 5, pending: 1);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Khoa hoc duyet 5');
        $response->assertSee('Khoa hoc duyet 4');
        $response->assertSee('Khoa hoc duyet 3');
        $response->assertDontSee('Khoa hoc duyet 2');
        $response->assertDontSee('Khoa hoc duyet 1');
        $response->assertDontSee('Khoa hoc cho 1');
    }

    public function test_courses_page_lists_every_approved_course_only(): void
    {
        $this->createCourses(approved: 5, pending: 1);

        $response = $this->get(route('courses.index'));

        $response->assertOk();

        foreach (range(1, 5) as $i) {
            $response->assertSee("Khoa hoc duyet {$i}");
        }

        $response->assertDontSee('Khoa hoc cho 1');
    }

    public function test_home_page_displays_hero_course_gallery_with_draggable_carousel(): void
    {
        $this->createCourses(approved: 3, pending: 0);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('courseHeroGallery');
        $response->assertSee('Khám Phá Môn Học');
        $response->assertSee('7000');
        $response->assertSee('Vào học ngay');
    }

    public function test_courses_page_can_search_by_title_description_and_teacher(): void
    {
        $teacher1 = User::factory()->teacher()->create(['name' => 'Nguyen Van A']);
        $teacher2 = User::factory()->teacher()->create(['name' => 'Tran Thi B']);

        Course::create([
            'teacher_id' => $teacher1->id,
            'title' => 'Khoa hoc Lap trinh Laravel 11',
            'description' => 'Huong dan tu co ban den nang cao',
            'status' => 'approved',
        ]);

        Course::create([
            'teacher_id' => $teacher2->id,
            'title' => 'Lap trinh Vue JS Frontend',
            'description' => 'Xay dung giao dien Single Page Application',
            'status' => 'approved',
        ]);

        // Tìm theo tiêu đề Laravel
        $res1 = $this->get(route('courses.index', ['search' => 'Laravel']));
        $res1->assertOk();
        $res1->assertSee('Khoa hoc Lap trinh Laravel 11');
        $res1->assertDontSee('Lap trinh Vue JS Frontend');

        // Tìm theo mô tả Single Page
        $res2 = $this->get(route('courses.index', ['search' => 'Single Page']));
        $res2->assertOk();
        $res2->assertSee('Lap trinh Vue JS Frontend');
        $res2->assertDontSee('Khoa hoc Lap trinh Laravel 11');

        // Tìm theo tên giảng viên Tran Thi B
        $res3 = $this->get(route('courses.index', ['search' => 'Tran Thi B']));
        $res3->assertOk();
        $res3->assertSee('Lap trinh Vue JS Frontend');
        $res3->assertDontSee('Khoa hoc Lap trinh Laravel 11');

        // Tìm không thấy kết quả
        $res4 = $this->get(route('courses.index', ['search' => 'TuKhoaKhongTonTai123']));
        $res4->assertOk();
        $res4->assertSee('Không tìm thấy khóa học nào phù hợp');
        $res4->assertSee('TuKhoaKhongTonTai123');
    }

    public function test_enrolled_course_card_displays_teacher_avatar_and_name(): void
    {
        $teacher = User::factory()->teacher()->create([
            'name' => 'Thầy Giáo Ba',
            'avatar' => 'https://example.com/avatar-ba.jpg',
        ]);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Vue 3 Pro',
            'description' => 'Mô tả chi tiết',
            'status' => 'approved',
        ]);

        $student = User::factory()->create(['role' => 'student']);
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($student)->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee('Thầy Giáo Ba');
        $response->assertSee('https://example.com/avatar-ba.jpg');
        $response->assertSee('Giảng viên');
        $response->assertSee('Vào học ngay');
        $response->assertSee('Tiến độ');
    }

    public function test_home_page_featured_courses_are_ordered_by_most_reviews(): void
    {
        $teacher = User::factory()->teacher()->create();

        // Khóa học 1 có 0 đánh giá
        $course1 = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Ít Đánh Giá',
            'description' => 'Mô tả 1',
            'status' => 'approved',
        ]);

        // Khóa học 2 có 3 đánh giá
        $course2 = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Nhiều Đánh Giá Nhất',
            'description' => 'Mô tả 2',
            'status' => 'approved',
        ]);

        // Khóa học 3 có 1 đánh giá
        $course3 = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Đánh Giá Vừa',
            'description' => 'Mô tả 3',
            'status' => 'approved',
        ]);

        $students = User::factory()->count(3)->create(['role' => 'student']);

        // Tạo 3 reviews cho course2
        foreach ($students as $student) {
            CourseReview::create([
                'course_id' => $course2->id,
                'user_id' => $student->id,
                'rating' => 5,
                'comment' => 'Tuyệt vời',
            ]);
        }

        // Tạo 1 review cho course3
        CourseReview::create([
            'course_id' => $course3->id,
            'user_id' => $students[0]->id,
            'rating' => 4,
            'comment' => 'Khá tốt',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();

        // Kiểm tra thứ tự xuất hiện trên trang chủ: course2 -> course3 -> course1
        $coursesOnPage = $response->viewData('courses');
        $this->assertEquals($course2->id, $coursesOnPage[0]->id);
        $this->assertEquals($course3->id, $coursesOnPage[1]->id);
        $this->assertEquals($course1->id, $coursesOnPage[2]->id);
    }

    public function test_home_page_does_not_display_search_bar_in_courses_section(): void
    {
        $this->createCourses(approved: 2, pending: 0);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Khóa học nổi bật');
        $response->assertDontSee('placeholder="Tìm kiếm khóa học theo tên, mô tả, giảng viên..."', false);
        $response->assertDontSee('coursesHandler');
    }

    public function test_courses_page_displays_search_and_price_filter_bar_with_custom_price_option(): void
    {
        $this->createCourses(approved: 2, pending: 0);

        $response = $this->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee('coursesHandler');
        $response->assertSee('Tìm kiếm khóa học theo tên, mô tả, giảng viên...');
        $response->assertSee('Tất cả mức giá');
        $response->assertSee('Miễn phí');
        $response->assertSee('Có phí');
        $response->assertSee('Dưới 200.000 đ');
        $response->assertSee('200.000 - 500.000 đ');
        $response->assertSee('Trên 500.000 đ');
        $response->assertSee('Tự nhập mức giá mong muốn...');
        $response->assertSee('name="min_price"', false);
        $response->assertSee('name="max_price"', false);
    }

    public function test_courses_page_can_filter_by_price_including_custom_range(): void
    {
        $teacher = User::factory()->teacher()->create();

        Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khoa Free',
            'price' => 0,
            'status' => 'approved',
        ]);

        Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khoa Mid 250k',
            'price' => 250000,
            'status' => 'approved',
        ]);

        Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khoa High 600k',
            'price' => 600000,
            'status' => 'approved',
        ]);

        // Lọc miễn phí
        $resFree = $this->get(route('courses.index', ['price' => 'free']));
        $resFree->assertOk();
        $resFree->assertSee('Khoa Free');
        $resFree->assertDontSee('Khoa Mid 250k');
        $resFree->assertDontSee('Khoa High 600k');

        // Lọc có phí
        $resPaid = $this->get(route('courses.index', ['price' => 'paid']));
        $resPaid->assertOk();
        $resPaid->assertSee('Khoa Mid 250k');
        $resPaid->assertSee('Khoa High 600k');
        $resPaid->assertDontSee('Khoa Free');

        // Lọc tự nhập mức giá: tối đa 300.000 đ (price=custom, max_price=300000)
        $resCustomMax = $this->get(route('courses.index', ['price' => 'custom', 'max_price' => '300000']));
        $resCustomMax->assertOk();
        $resCustomMax->assertSee('Khoa Mid 250k');
        $resCustomMax->assertDontSee('Khoa High 600k');

        // Lọc tự nhập khoảng giá: từ 200.000 đ đến 500.000 đ
        $resCustomRange = $this->get(route('courses.index', ['min_price' => '200000', 'max_price' => '500000']));
        $resCustomRange->assertOk();
        $resCustomRange->assertSee('Khoa Mid 250k');
        $resCustomRange->assertDontSee('Khoa High 600k');
        $resCustomRange->assertDontSee('Khoa Free');
    }

    public function test_quizzes_are_blurred_and_locked_when_user_is_not_enrolled(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học bí mật',
            'status' => 'approved',
            'price' => 50000,
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Cơ Bản 1',
            'duration_minutes' => 15,
            'passing_score' => 7.0,
            'status' => 'approved',
        ]);

        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('courses.show', $course->id));

        $response->assertOk();
        $response->assertSee('Bài kiểm tra đang bị khóa');
        $response->assertSee('Chưa mở khóa');
        $response->assertSee('min-h-[460px]');
        $response->assertSee('items-center justify-center');
        $response->assertDontSee('Làm bài thi');
    }

    public function test_quizzes_are_unlocked_when_user_is_enrolled(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học đã đăng ký',
            'status' => 'approved',
            'price' => 0,
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Cơ Bản 2',
            'duration_minutes' => 15,
            'passing_score' => 7.0,
            'status' => 'approved',
        ]);

        $student = User::factory()->create(['role' => 'student']);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'price_paid' => 0,
        ]);

        $response = $this->actingAs($student)->get(route('courses.show', $course->id));

        $response->assertOk();
        $response->assertDontSee('Bài kiểm tra đang bị khóa');
        $response->assertSee('Làm bài thi');
    }
}
