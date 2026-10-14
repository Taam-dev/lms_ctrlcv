<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAndCoursePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_wallet_option_with_balance_in_navigation(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 150000,
        ]);

        $response = $this->actingAs($student)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Ví tiền');
        $response->assertSee('150.000 đ');
        $response->assertSee(route('wallet.deposit'));
    }

    public function test_student_can_view_deposit_page_and_empty_qr_state(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 0,
        ]);

        $response = $this->actingAs($student)->get(route('wallet.deposit'));

        $response->assertOk();
        $response->assertSee('Ví Tiền');
        $response->assertSee('0 đ');
        $response->assertSee('Tạo mã QR nạp tiền');
    }

    public function test_student_can_create_deposit_request_and_generates_vietqr(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 50000,
        ]);

        $response = $this->actingAs($student)->post(route('wallet.deposit.store'), [
            'amount' => 200000,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $student->id,
            'type' => 'deposit',
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $tx = WalletTransaction::where('user_id', $student->id)->latest()->first();

        $response->assertRedirect(route('wallet.deposit', ['tx' => $tx->code]));

        $followResponse = $this->actingAs($student)->get(route('wallet.deposit', ['tx' => $tx->code]));
        $followResponse->assertOk();
        $followResponse->assertSee('0929089332');
        $followResponse->assertSee('LE CHI TAM');
        $followResponse->assertSee('200.000 đ');
        $followResponse->assertSee($tx->metadata['transfer_content']);
    }

    public function test_student_can_confirm_deposit_and_balance_is_credited(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 50000,
        ]);

        $tx = WalletTransaction::create([
            'user_id' => $student->id,
            'code' => 'NAPTEST123',
            'type' => 'deposit',
            'amount' => 100000,
            'balance_before' => 50000,
            'balance_after' => 50000,
            'status' => 'pending',
            'description' => 'Nạp tiền ví test',
            'payment_method' => 'vietqr',
            'metadata' => ['transfer_content' => 'LMS NAP 1 NAPTEST123'],
        ]);

        $response = $this->actingAs($student)->post(route('wallet.transactions.confirm', $tx));

        $response->assertRedirect(route('wallet.deposit'));

        $this->assertEquals(150000, $student->fresh()->balance);
        $this->assertEquals('completed', $tx->fresh()->status);
        $this->assertEquals(150000, $tx->fresh()->balance_after);
    }

    public function test_teacher_can_create_course_with_price(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->post(route('instructor.courses.store'), [
            'title' => 'Khóa Học Có Phí Cao Cấp',
            'description' => 'Mô tả khóa học trả phí',
            'price' => 199000,
        ]);

        $response->assertRedirect(route('instructor.dashboard', ['tab' => 'courses', 'status' => 'pending']));

        $this->assertDatabaseHas('courses', [
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Có Phí Cao Cấp',
            'price' => 199000,
        ]);
    }

    public function test_student_cannot_enroll_in_paid_course_without_sufficient_balance(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học VIP 200k',
            'description' => 'Mô tả',
            'price' => 200000,
            'status' => 'approved',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 50000, // Chỉ có 50k, thiếu 150k
        ]);

        $response = $this->actingAs($student)->post(route('student.courses.enroll', $course->id));

        $response->assertSessionHas('error');
        $response->assertSessionHas('need_deposit', true);

        // Đảm bảo không tạo enrollment
        $this->assertFalse(Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->exists());
        $this->assertEquals(50000, $student->fresh()->balance);
    }

    public function test_student_can_purchase_paid_course_with_sufficient_balance(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học VIP 200k',
            'description' => 'Mô tả',
            'price' => 200000,
            'status' => 'approved',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 300000, // Đủ tiền
        ]);

        $response = $this->actingAs($student)->post(route('student.courses.enroll', $course->id));

        $response->assertSessionHas('success');

        // Đã trừ đúng 200k, còn lại 100k
        $this->assertEquals(100000, $student->fresh()->balance);

        // Đã ghi nhận enrollment với đúng giá mua
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals(200000, $enrollment->price_paid);

        // Đã ghi nhận lịch sử giao dịch mua khóa học
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $student->id,
            'type' => 'course_purchase',
            'amount' => 200000,
            'status' => 'completed',
            'reference_id' => $course->id,
        ]);

        // Kiểm tra giao diện chi tiết khóa học và danh sách hiển thị giá đã mua
        $showResponse = $this->actingAs($student)->get(route('courses.show', $course->slug ?: $course->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Đã mua (200.000 đ)');
        $showResponse->assertSee('Đã mua với giá:');

        $listResponse = $this->actingAs($student)->get(route('courses.index'));
        $listResponse->assertOk();
        $listResponse->assertSee('Đã mua (200.000 đ)');
    }

    public function test_student_can_enroll_in_free_course_without_balance_deduction(): void
    {
        $teacher = User::factory()->teacher()->create();
        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'Khóa Học Miễn Phí',
            'description' => 'Mô tả',
            'price' => 0,
            'status' => 'approved',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 50000,
        ]);

        $response = $this->actingAs($student)->post(route('student.courses.enroll', $course->id));

        $response->assertSessionHas('success');
        $this->assertEquals(50000, $student->fresh()->balance);
        $enrollment = Enrollment::where('student_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertEquals(0, $enrollment->price_paid);
    }

    public function test_student_can_cancel_deposit_and_shows_cancelled_with_zero_amount(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'balance' => 50000,
        ]);

        $tx = WalletTransaction::create([
            'user_id' => $student->id,
            'code' => 'XYZ98765',
            'type' => 'deposit',
            'amount' => 1000000,
            'balance_before' => 50000,
            'balance_after' => 50000,
            'status' => 'pending',
            'description' => 'Nạp tiền ví test',
            'payment_method' => 'vietqr',
            'metadata' => ['transfer_content' => 'LMS XYZ98765'],
        ]);

        $response = $this->actingAs($student)->post(route('wallet.transactions.cancel', $tx));
        $response->assertRedirect(route('wallet.deposit'));

        $this->assertEquals('cancelled', $tx->fresh()->status);
        $this->assertEquals('0 đ', $tx->fresh()->formatted_amount);

        $viewResponse = $this->actingAs($student)->get(route('wallet.deposit'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Đã hủy');
        $viewResponse->assertSee('0 đ');
    }
}
