<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseReview>
 */
class CourseReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->randomElement([5, 5, 5, 4, 4, 3]),
            'comment' => fake()->randomElement([
                'Khóa học rất hay và dễ hiểu, giảng viên giải thích chi tiết!',
                'Nội dung thực tế, bài tập đa dạng, rất đáng học.',
                'Kiến thức bổ ích, áp dụng được ngay vào công việc.',
                'Bài giảng trực quan, dễ tiếp thu cho người mới bắt đầu.',
                'Khóa học tuyệt vời! Mong có thêm nhiều khóa học bổ ích như thế này.',
                'Rất hài lòng với chất lượng bài giảng và quizzes ôn tập.',
            ]),
        ];
    }
}
