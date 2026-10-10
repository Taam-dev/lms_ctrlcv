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

    public function test_lesson_video_embed_info_parses_various_video_providers(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test Video Embed',
            'status' => 'approved',
        ]);

        // 1. YouTube
        $ytLesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài Youtube',
            'content_type' => 'video',
            'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'order_number' => 1,
        ]);
        $ytInfo = $ytLesson->video_embed_info;
        $this->assertNotNull($ytInfo);
        $this->assertEquals('youtube', $ytInfo['type']);
        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ', $ytInfo['url']);

        // 2. Facebook
        $fbLesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài Facebook',
            'content_type' => 'video',
            'content' => 'https://www.facebook.com/share/v/1Ex6VmRaLs/',
            'order_number' => 2,
        ]);
        $fbInfo = $fbLesson->video_embed_info;
        $this->assertNotNull($fbInfo);
        $this->assertEquals('facebook', $fbInfo['type']);
        $this->assertStringContainsString('https://www.facebook.com/plugins/video.php', $fbInfo['url']);
        $this->assertStringContainsString(urlencode('https://www.facebook.com/share/v/1Ex6VmRaLs/'), $fbInfo['url']);

        // 3. Google Drive
        $driveLesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài Drive',
            'content_type' => 'video',
            'content' => 'https://drive.google.com/file/d/1AbC2DeFgHiJkLmNoP/view?usp=sharing',
            'order_number' => 3,
        ]);
        $driveInfo = $driveLesson->video_embed_info;
        $this->assertNotNull($driveInfo);
        $this->assertEquals('drive', $driveInfo['type']);
        $this->assertEquals('https://drive.google.com/file/d/1AbC2DeFgHiJkLmNoP/preview', $driveInfo['url']);

        // 4. Direct video (.mp4)
        $mp4Lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài MP4',
            'content_type' => 'video',
            'content' => 'https://example.com/videos/sample.mp4',
            'order_number' => 4,
        ]);
        $mp4Info = $mp4Lesson->video_embed_info;
        $this->assertNotNull($mp4Info);
        $this->assertEquals('direct', $mp4Info['type']);
        $this->assertEquals('https://example.com/videos/sample.mp4', $mp4Info['url']);
    }

    public function test_lesson_show_page_renders_facebook_video_and_no_stacked_header(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'PUBG Loot Do Course',
            'status' => 'approved',
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài 1: Cẩm Nang Loot Đồ',
            'content_type' => 'video',
            'content' => 'https://www.facebook.com/share/v/1Ex6VmRaLs/',
            'order_number' => 1,
        ]);

        $student = User::factory()->create();
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($student)->get(route('student.lessons.show', [$course->id, $lesson->id]));

        $response->assertOk();
        // Kiểm tra iframe video facebook được render trực tiếp
        $response->assertSee('facebook.com/plugins/video.php', false);
        // Kiểm tra có nút Làm bài Quizzes
        $response->assertSee('Làm bài Quizzes');
        // Kiểm tra có tiến độ bên sidebar
        $response->assertSee('Tiến độ khóa học');
    }
}
