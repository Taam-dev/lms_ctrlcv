<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Người dùng có thể báo cáo tài khoản người khác với lý do avatar không phù hợp
     */
    public function test_authenticated_user_can_report_another_user(): void
    {
        $reporter = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create(['role' => 'student', 'avatar' => 'avatars/bad_avatar.jpg']);

        $response = $this->actingAs($reporter)->post(route('users.report', $targetUser), [
            'reason' => 'avatar_inappropriate',
            'details' => 'Avatar phản cảm, đề nghị ban quản trị xem xét gỡ bỏ.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'avatar_inappropriate',
            'status' => 'pending',
        ]);
    }

    /**
     * Người dùng không thể tự báo cáo chính mình
     */
    public function test_user_cannot_report_themselves(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->post(route('users.report', $user), [
            'reason' => 'name_inappropriate',
            'details' => 'Tự báo cáo bản thân',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reports', [
            'reporter_id' => $user->id,
            'reportable_id' => $user->id,
        ]);
    }

    /**
     * Người dùng không thể gửi trùng lặp báo cáo đang chờ xử lý cho cùng đối tượng
     */
    public function test_user_cannot_submit_duplicate_pending_report(): void
    {
        $reporter = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create(['role' => 'student']);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'name_inappropriate',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($reporter)->post(route('users.report', $targetUser), [
            'reason' => 'avatar_inappropriate',
            'details' => 'Báo cáo lần 2',
        ]);

        $response->assertSessionHas('info');
        $this->assertEquals(1, Report::where('reporter_id', $reporter->id)->where('reportable_id', $targetUser->id)->count());
    }

    /**
     * Người dùng có thể báo cáo bình luận đánh giá vi phạm
     */
    public function test_user_can_report_a_course_review(): void
    {
        $reporter = User::factory()->create(['role' => 'student']);
        $author = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học React nâng cao',
            'status' => 'approved',
        ]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $author->id,
            'rating' => 1,
            'comment' => 'Bình luận dùng từ ngữ rất tiêu cực và thô tục...',
        ]);

        $response = $this->actingAs($reporter)->post(route('reviews.report', $review), [
            'reason' => 'negative_comment',
            'details' => 'Bình luận chứa ngôn từ đả kích cá nhân.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => CourseReview::class,
            'reportable_id' => $review->id,
            'reason' => 'negative_comment',
            'status' => 'pending',
        ]);
    }

    /**
     * Người dùng không thể tự báo cáo bình luận của chính mình
     */
    public function test_user_cannot_report_their_own_review(): void
    {
        $author = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học VueJS',
            'status' => 'approved',
        ]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $author->id,
            'rating' => 5,
            'comment' => 'Khóa học rất hay!',
        ]);

        $response = $this->actingAs($author)->post(route('reviews.report', $review), [
            'reason' => 'spam',
            'details' => 'Tự báo cáo bài của mình',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reports', [
            'reportable_id' => $review->id,
        ]);
    }

    /**
     * Admin có thể truy cập danh sách báo cáo vi phạm trong Admin Dashboard
     */
    public function test_admin_can_view_reports_tab_in_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create(['role' => 'student']);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'avatar_inappropriate',
            'details' => 'Avatar không đứng đắn',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'reports']));
        $response->assertStatus(200);
        $response->assertSee('Quản Lý Báo Cáo Vi Phạm');
        $response->assertSee('Avatar không phù hợp');
        $response->assertSee($targetUser->name);
    }

    /**
     * Admin có thể giải quyết báo cáo tài khoản và gỡ avatar vi phạm
     */
    public function test_admin_can_resolve_report_and_reset_avatar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create([
            'role' => 'student',
            'avatar' => 'avatars/offensive.png',
        ]);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'avatar_inappropriate',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.action', $report), [
            'action' => 'resolve',
            'reset_avatar' => 1,
            'admin_notes' => 'Đã gỡ avatar phản cảm của tài khoản.',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('resolved', $report->status);
        $this->assertEquals($admin->id, $report->resolved_by);
        $this->assertEquals('Đã gỡ avatar phản cảm của tài khoản.', $report->admin_notes);

        $targetUser->refresh();
        $this->assertNull($targetUser->avatar);
    }

    /**
     * Admin có thể giải quyết báo cáo bình luận và xóa bình luận vi phạm
     */
    public function test_admin_can_resolve_report_and_delete_violating_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['role' => 'student']);
        $author = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Node.js',
            'status' => 'approved',
        ]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $author->id,
            'rating' => 1,
            'comment' => 'Spam link quảng cáo cờ bạc: http://spam.xyz',
        ]);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => CourseReview::class,
            'reportable_id' => $review->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.action', $report), [
            'action' => 'resolve',
            'delete_content' => 1,
            'admin_notes' => 'Xác nhận spam quảng cáo, đã xóa bình luận.',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('resolved', $report->status);

        $this->assertDatabaseMissing('course_reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Admin có thể bác bỏ báo cáo nếu không vi phạm
     */
    public function test_admin_can_dismiss_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create(['role' => 'student']);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'name_inappropriate',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.action', $report), [
            'action' => 'dismiss',
            'admin_notes' => 'Tên người dùng hợp lệ, không có dấu hiệu vi phạm.',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('dismissed', $report->status);
        $this->assertEquals($admin->id, $report->resolved_by);
    }
}
