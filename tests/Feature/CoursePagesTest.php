<?php

namespace Tests\Feature;

use App\Models\Course;
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
}
