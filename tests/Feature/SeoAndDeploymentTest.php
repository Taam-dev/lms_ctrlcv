<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndDeploymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tự động tạo slug chuẩn SEO không dấu khi tạo khóa học
     */
    public function test_course_auto_generates_slug_from_title(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Lập trình Laravel 12 và Vue.js từ cơ bản đến nâng cao',
            'description' => 'Khóa học lập trình Laravel toàn diện',
            'status' => 'approved',
        ]);

        $this->assertNotNull($course->slug);
        $this->assertEquals('lap-trinh-laravel-12-va-vuejs-tu-co-ban-den-nang-cao', $course->slug);
    }

    /**
     * Test slug được đảm bảo duy nhất nếu có 2 khóa học cùng tiêu đề
     */
    public function test_course_ensures_unique_slug(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $course1 = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học React Native',
            'status' => 'approved',
        ]);

        $course2 = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học React Native',
            'status' => 'approved',
        ]);

        $this->assertEquals('khoa-hoc-react-native', $course1->slug);
        $this->assertEquals('khoa-hoc-react-native-1', $course2->slug);
    }

    /**
     * Test tự động tạo slug chuẩn SEO không dấu cho bài giảng
     */
    public function test_lesson_auto_generates_slug_from_title(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học VueJS',
            'status' => 'approved',
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài 1: Giới thiệu cú pháp Reactive trong Vue 3',
            'content_type' => 'text',
            'content' => 'Nội dung bài học giới thiệu cú pháp Reactive.',
            'order_number' => 1,
        ]);

        $this->assertNotNull($lesson->slug);
        $this->assertEquals('bai-1-gioi-thieu-cu-phap-reactive-trong-vue-3', $lesson->slug);
    }

    /**
     * Test khách vãng lai và bot tìm kiếm có thể xem chi tiết khóa học qua URL /khoa-hoc/{slug}
     */
    public function test_public_user_can_view_course_by_slug(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Lập trình Python cho người mới',
            'description' => 'Khóa học hướng dẫn lập trình Python thực chiến',
            'status' => 'approved',
        ]);

        $response = $this->get(route('courses.show', $course->slug));

        $response->assertOk();
        $response->assertSee('Lập trình Python cho người mới');
        $response->assertSee('EducationalOrganization', false);
        $response->assertSee('Course', false);
        $response->assertSee('BreadcrumbList', false);
        $response->assertSee('https://ctrlcv.io.vn', false);
    }

    /**
     * Test học viên đã đăng ký có thể xem bài giảng qua route thân thiện chuẩn SEO
     */
    public function test_enrolled_student_can_view_lesson_via_friendly_route(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Laravel Master',
            'status' => 'approved',
        ]);

        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Cài đặt môi trường Laravel',
            'content_type' => 'text',
            'content' => 'Nội dung hướng dẫn cài đặt',
            'order_number' => 1,
        ]);

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($student)->get(route('student.lessons.friendly', [$course->slug, $lesson->slug]));

        $response->assertOk();
        $response->assertSee('Cài đặt môi trường Laravel');
        $response->assertSee('BreadcrumbList', false);
    }

    /**
     * Test sitemap.xml động hoạt động và trả về định dạng XML hợp lệ
     */
    public function test_sitemap_returns_valid_xml_with_approved_courses(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $approvedCourse = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học SEO Web nâng cao',
            'status' => 'approved',
        ]);

        $pendingCourse = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học chưa được duyệt',
            'status' => 'pending',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('urlset', false);
        $response->assertSee('khoa-hoc/'.$approvedCourse->slug, false);
        // Khóa học chưa duyệt không được xuất vào sitemap
        $response->assertDontSee('khoa-hoc/'.$pendingCourse->slug, false);
    }

    /**
     * Test file robots.txt tồn tại và chứa cấu hình chuẩn SEO
     */
    public function test_robots_txt_exists_and_declares_sitemap(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('Sitemap: https://ctrlcv.io.vn/sitemap.xml', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /payment/', $content);
        $this->assertStringContainsString('Disallow: /thanh-toan/', $content);
        $this->assertStringContainsString('Allow: /khoa-hoc/', $content);
    }

    /**
     * Test trang chủ chứa đầy đủ thẻ Meta Open Graph và Twitter Card chuẩn SEO
     */
    public function test_home_page_renders_open_graph_and_twitter_cards(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:url" content="https://ctrlcv.io.vn">', false);
        $response->assertSee('<meta property="og:title" content="Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1">', false);
        $response->assertSee('<meta property="og:description" content="Nền tảng học tập tinh gọn giúp bạn tiếp cận bài giảng chất lượng, làm bài trắc nghiệm tự chấm điểm và nắm bắt tiến độ học tập minh bạch.">', false);
        $response->assertSee('images/thumbnail.png', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="twitter:url" content="https://ctrlcv.io.vn">', false);
        $response->assertSee('<meta name="twitter:title" content="Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1">', false);
        $response->assertSee('<meta name="twitter:description" content="Nền tảng học tập trực tuyến được phát triển bởi nhóm CtrlC+V">', false);
    }
}
