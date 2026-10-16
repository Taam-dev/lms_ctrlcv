<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::where('status', 'approved')->get();
        if ($courses->isEmpty()) {
            return;
        }

        $students = User::where('email', 'like', 'reviewer_%@example.com')->get();
        if ($students->isEmpty()) {
            $students = collect([
                User::firstOrCreate(
                    ['email' => 'reviewer_1@example.com'],
                    ['name' => 'Nguyễn Văn Review', 'password' => bcrypt('password'), 'role' => 'student']
                ),
                User::firstOrCreate(
                    ['email' => 'reviewer_2@example.com'],
                    ['name' => 'Trần Thị Review', 'password' => bcrypt('password'), 'role' => 'student']
                ),
            ]);
        }

        $sampleComments = [
            'Khóa học rất hay và dễ hiểu, giảng viên giải thích chi tiết!',
            'Nội dung thực tế, bài tập đa dạng, rất đáng học.',
            'Kiến thức bổ ích, áp dụng được ngay vào công việc.',
            'Bài giảng trực quan, dễ tiếp thu cho người mới bắt đầu.',
            'Khóa học tuyệt vời! Mong có thêm nhiều khóa học bổ ích như thế này.',
            'Rất hài lòng với chất lượng bài giảng và quizzes ôn tập.',
        ];

        foreach ($courses as $course) {
            foreach ($students->take(2) as $student) {
                // Đảm bảo học viên đã được enroll trước khi đánh giá
                Enrollment::firstOrCreate([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ]);

                CourseReview::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'rating' => fake()->randomElement([4, 5, 5]),
                        'comment' => fake()->randomElement($sampleComments),
                    ]
                );
            }
        }
    }
}
