<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseProgressTest extends TestCase
{
    use RefreshDatabase;

    private function createCourseWithLessons(int $lessonCount = 2): array
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khoa hoc Lap trinh Laravel',
            'description' => 'Mo ta khoa hoc',
            'status' => 'approved',
        ]);

        $lessons = [];
        for ($i = 1; $i <= $lessonCount; $i++) {
            $lessons[] = Lesson::create([
                'course_id' => $course->id,
                'title' => "Bai giang {$i}",
                'content_type' => 'text',
                'content' => "Noi dung bai giang {$i}",
                'order_number' => $i,
            ]);
        }

        return [$course, $lessons];
    }

    public function test_student_can_complete_lesson_and_cannot_uncomplete(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(2);
        $student = User::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Student marks lesson 1 as complete
        $response = $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[0]->id]), [
            'action' => 'complete',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('lesson_completions', [
            'student_id' => $student->id,
            'lesson_id' => $lessons[0]->id,
            'course_id' => $course->id,
        ]);

        // Student attempts to uncomplete lesson 1 -> It must NOT be deleted
        $response = $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[0]->id]), [
            'action' => 'uncomplete',
        ]);

        $this->assertDatabaseHas('lesson_completions', [
            'student_id' => $student->id,
            'lesson_id' => $lessons[0]->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_student_must_learn_lessons_sequentially(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(2);
        $student = User::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Try to access lesson 2 directly before completing lesson 1 -> Redirected to lesson 1
        $response = $this->actingAs($student)->get(route('student.lessons.show', [$course->id, $lessons[1]->id]));
        $response->assertRedirect(route('student.lessons.show', [$course->id, $lessons[0]->id]));
        $response->assertSessionHas('error');

        // Try to complete lesson 2 directly before completing lesson 1 -> Disallowed
        $completeResponse = $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[1]->id]));
        $completeResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('lesson_completions', [
            'student_id' => $student->id,
            'lesson_id' => $lessons[1]->id,
        ]);

        // Complete lesson 1 first
        $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[0]->id]));

        // Visiting completed lesson 1 should display exactly one next lesson button
        $accessLesson1AfterComplete = $this->actingAs($student)->get(route('student.lessons.show', [$course->id, $lessons[0]->id]));
        $accessLesson1AfterComplete->assertOk();
        $this->assertSame(1, substr_count($accessLesson1AfterComplete->getContent(), 'Bài tiếp theo: '.$lessons[1]->title));

        // Now lesson 2 is accessible and completable
        $accessLesson2 = $this->actingAs($student)->get(route('student.lessons.show', [$course->id, $lessons[1]->id]));
        $accessLesson2->assertOk();

        $completeLesson2 = $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[1]->id]));
        $completeLesson2->assertSessionHas('success');
        $this->assertDatabaseHas('lesson_completions', [
            'student_id' => $student->id,
            'lesson_id' => $lessons[1]->id,
        ]);
    }

    public function test_student_sees_enrolled_badge_and_progress_percentage_on_home_page(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(2);
        $student = User::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Complete 1 out of 2 lessons -> 50%
        LessonCompletion::create([
            'student_id' => $student->id,
            'lesson_id' => $lessons[0]->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Đã đăng ký');
        $response->assertSee('50%');
        $response->assertSee('1/2 bài');
    }

    public function test_student_sees_progress_percentage_on_courses_page(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(4);
        $student = User::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // Complete 3 out of 4 lessons -> 75%
        for ($i = 0; $i < 3; $i++) {
            LessonCompletion::create([
                'student_id' => $student->id,
                'lesson_id' => $lessons[$i]->id,
                'course_id' => $course->id,
                'completed_at' => now(),
            ]);
        }

        $response = $this->actingAs($student)->get(route('courses.index'));

        $response->assertOk();
        $response->assertSee('Đã đăng ký');
        $response->assertSee('75%');
        $response->assertSee('3/4 bài');
    }

    public function test_course_show_page_displays_progress_and_completed_status(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(2);
        $student = User::factory()->create();

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        LessonCompletion::create([
            'student_id' => $student->id,
            'lesson_id' => $lessons[0]->id,
            'course_id' => $course->id,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('student.courses.show', $course->id));

        $response->assertOk();
        $response->assertSee('Đã đăng ký');
        $response->assertSee('50%');
        $response->assertSee('Hoàn thành 1/2 bài');
        $response->assertSee('Đã hoàn thành');
    }

    public function test_non_enrolled_user_cannot_complete_lesson(): void
    {
        [$course, $lessons] = $this->createCourseWithLessons(2);
        $student = User::factory()->create();

        $response = $this->actingAs($student)->post(route('student.lessons.complete', [$course->id, $lessons[0]->id]));

        $response->assertRedirect(route('student.courses.show', $course->id));
        $this->assertDatabaseMissing('lesson_completions', [
            'student_id' => $student->id,
            'lesson_id' => $lessons[0]->id,
        ]);
    }
}
