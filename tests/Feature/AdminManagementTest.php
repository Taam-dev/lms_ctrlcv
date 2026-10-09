<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Người dùng không phải admin không thể vào trang admin dashboard
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    /**
     * Admin có thể truy cập trang admin dashboard
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Khiển Quản Trị');
        $response->assertSee('Quản lý Khóa học');
    }

    /**
     * Admin có thể duyệt và từ chối khóa học khi mới tạo (pending)
     */
    public function test_admin_can_approve_and_reject_pending_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Test',
            'status' => 'pending',
        ]);

        // Duyệt
        $response = $this->actingAs($admin)->post(route('admin.courses.approve', $course->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'approved',
        ]);

        // Từ chối
        $response = $this->actingAs($admin)->post(route('admin.courses.reject', $course->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Admin có thể loại bỏ khóa học đã duyệt (chuyển sang rejected để gỡ khỏi trang chủ)
     */
    public function test_admin_can_remove_approved_course(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Đã Duyệt',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.courses.remove', $course->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Admin có thể loại bỏ quiz đã duyệt
     */
    public function test_admin_can_remove_approved_quiz(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học Lập trình',
            'status' => 'approved',
        ]);
        $quiz = Quiz::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'Quiz Đã Duyệt',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.quizzes.remove', $quiz->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Admin có thể xóa bài giảng
     */
    public function test_admin_can_delete_lesson(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa học',
            'status' => 'approved',
        ]);
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'title' => 'Bài giảng mẫu',
            'content_type' => 'text',
            'content' => 'Nội dung bài học',
            'order_number' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.lessons.destroy', $lesson->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('lessons', [
            'id' => $lesson->id,
        ]);
    }

    /**
     * Header chính không hiển thị option Bảng Quản Trị, chỉ hiển thị ADMIN trong profile dropdown
     */
    public function test_profile_dropdown_shows_admin_link_and_header_does_not_contain_panel_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('home'));
        $response->assertStatus(200);

        // Header không hiển thị nút Bảng Quản Trị (Admin)
        $response->assertDontSee('Bảng Quản Trị (Admin)');

        // Profile dropdown hiển thị option ADMIN
        $response->assertSee('ADMIN');
        $response->assertSee(route('admin.dashboard'));

        // Profile dropdown không chứa các nút tạo quiz và khóa học rườm rà (đã nằm trong admin panel)
        $response->assertDontSee('Tạo đề thi Quiz');
    }

    /**
     * Admin có thể xem tab người dùng kèm form phân quyền
     */
    public function test_admin_can_see_user_role_management_in_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['name' => 'Nguyen Van A', 'role' => 'student']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['tab' => 'users']));
        $response->assertStatus(200);
        $response->assertSee('Danh Sách Tài Khoản');
        $response->assertSee('Nguyen Van A');
        $response->assertSee(route('admin.users.update-role', $student));
    }

    /**
     * Admin có thể cập nhật vai trò người dùng (Ví dụ: student -> teacher, teacher -> admin)
     */
    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser), [
            'role' => 'teacher',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'teacher',
        ]);
        $this->assertTrue($targetUser->fresh()->isTeacher());

        // Tiếp tục nâng quyền thành admin
        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser), [
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'admin',
        ]);
        $this->assertTrue($targetUser->fresh()->isAdmin());
    }

    /**
     * Admin không thể tự hạ quyền quản trị viên của chính mình
     */
    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $admin), [
            'role' => 'student',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    /**
     * Người dùng không phải admin không thể gọi route cập nhật vai trò
     */
    public function test_non_admin_cannot_update_user_role(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherUser = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->patch(route('admin.users.update-role', $otherUser), [
            'role' => 'admin',
        ]);

        $response->assertForbidden();
        $this->assertEquals('student', $otherUser->fresh()->role);
    }

    /**
     * Cập nhật vai trò phải qua xác thực dữ liệu (role bắt buộc thuộc admin, teacher, student)
     */
    public function test_updating_user_role_requires_valid_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)->patch(route('admin.users.update-role', $targetUser), [
            'role' => 'super_vip',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertEquals('student', $targetUser->fresh()->role);
    }
}
