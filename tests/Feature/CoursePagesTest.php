<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
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
}
