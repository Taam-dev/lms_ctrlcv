<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createCourse(): Course
    {
        $teacher = User::factory()->teacher()->create();

        return Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học lập trình Laravel nâng cao',
            'description' => 'Mô tả chi tiết khóa học Laravel',
            'status' => 'approved',
        ]);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $course = $this->createCourse();

        $response = $this->post(route('student.courses.reviews.store', $course->id), [
            'rating' => 5,
            'comment' => 'Khóa học tuyệt vời!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('course_reviews', 0);
    }

    public function test_unenrolled_student_cannot_submit_review(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)
            ->from(route('courses.show', $course->slug ?: $course->id))
            ->post(route('student.courses.reviews.store', $course->slug ?: $course->id), [
                'rating' => 5,
                'comment' => 'Khóa học rất hay!',
            ]);

        $response->assertRedirect(route('courses.show', $course->slug ?: $course->id));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('course_reviews', 0);
    }

    public function test_enrolled_student_can_submit_review_with_rating_and_comment(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($student)
            ->from(route('courses.show', $course->slug ?: $course->id))
            ->post(route('student.courses.reviews.store', $course->slug ?: $course->id), [
                'rating' => 5,
                'comment' => 'Bài giảng rất dễ hiểu và chi tiết!',
            ]);

        $response->assertRedirect(route('courses.show', $course->slug ?: $course->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'comment' => 'Bài giảng rất dễ hiểu và chi tiết!',
        ]);
    }

    public function test_enrolled_student_can_update_their_existing_review(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 4,
            'comment' => 'Khóa học tạm ổn.',
        ]);

        $response = $this->actingAs($student)
            ->post(route('student.courses.reviews.store', $course->id), [
                'rating' => 5,
                'comment' => 'Sau khi học hết thì thấy quá xuất sắc!',
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('course_reviews', 1);

        $this->assertDatabaseHas('course_reviews', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'comment' => 'Sau khi học hết thì thấy quá xuất sắc!',
        ]);
    }

    public function test_rating_validation_requires_between_1_and_5(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $responseInvalidLow = $this->actingAs($student)
            ->post(route('student.courses.reviews.store', $course->id), [
                'rating' => 0,
            ]);
        $responseInvalidLow->assertSessionHasErrors('rating');

        $responseInvalidHigh = $this->actingAs($student)
            ->post(route('student.courses.reviews.store', $course->id), [
                'rating' => 6,
            ]);
        $responseInvalidHigh->assertSessionHasErrors('rating');
    }

    public function test_course_show_page_displays_reviews_and_average_rating(): void
    {
        $course = $this->createCourse();
        $student1 = User::factory()->create(['name' => 'Nguyễn Văn A', 'role' => 'student']);
        $student2 = User::factory()->create(['name' => 'Trần Thị B', 'role' => 'student']);

        Enrollment::create(['student_id' => $student1->id, 'course_id' => $course->id]);
        Enrollment::create(['student_id' => $student2->id, 'course_id' => $course->id]);

        CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student1->id,
            'rating' => 5,
            'comment' => 'Khóa học trên cả tuyệt vời!',
        ]);

        CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student2->id,
            'rating' => 4,
            'comment' => 'Nội dung thực tế và chất lượng.',
        ]);

        $response = $this->get(route('courses.show', $course->slug ?: $course->id));

        $response->assertStatus(200);
        $response->assertSee('Nguyễn Văn A');
        $response->assertSee('Khóa học trên cả tuyệt vời!');
        $response->assertSee('Trần Thị B');
        $response->assertSee('Nội dung thực tế và chất lượng.');
        $response->assertSee('4.5'); // Average rating (5+4)/2
        $response->assertSee('2 đánh giá');
    }

    public function test_student_can_delete_their_own_review(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);

        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 5,
            'comment' => 'Bình luận sắp xóa',
        ]);

        $response = $this->actingAs($student)
            ->delete(route('student.courses.reviews.destroy', [$course->id, $review->id]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('course_reviews', ['id' => $review->id]);
    }

    public function test_student_cannot_delete_another_students_review(): void
    {
        $course = $this->createCourse();
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        Enrollment::create(['student_id' => $student1->id, 'course_id' => $course->id]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student1->id,
            'rating' => 5,
            'comment' => 'Bình luận của học viên 1',
        ]);

        $response = $this->actingAs($student2)
            ->delete(route('student.courses.reviews.destroy', [$course->id, $review->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('course_reviews', ['id' => $review->id]);
    }

    public function test_admin_can_delete_any_review(): void
    {
        $course = $this->createCourse();
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->admin()->create();

        Enrollment::create(['student_id' => $student->id, 'course_id' => $course->id]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'rating' => 1,
            'comment' => 'Spam hoặc không phù hợp',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('student.courses.reviews.destroy', [$course->id, $review->id]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('course_reviews', ['id' => $review->id]);
    }
}
