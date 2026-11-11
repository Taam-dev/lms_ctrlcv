<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\User;
use App\Services\Ai\AiGuardrailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guest_and_students_can_view_documents_index(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Document::create([
            'user_id' => $teacher->id,
            'title' => 'Tài liệu Lập trình Laravel 11',
            'slug' => 'tai-lieu-lap-trinh-laravel-11',
            'category' => 'Lập trình Web',
            'description' => 'Mô tả tài liệu học tập Laravel',
            'cover_image' => 'https://example.com/cover.jpg',
            'view_count' => 10,
            'download_count' => 5,
        ]);

        $response = $this->get(route('documents.index'));

        $response->assertStatus(200);
        $response->assertSee('Kho Tài Liệu Học Tập');
        $response->assertSee('Tài liệu Lập trình Laravel 11');
        $response->assertSee('Lập trình Web');
    }

    public function test_students_cannot_access_create_document_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('documents.create'));

        $response->assertStatus(403);
    }

    public function test_teacher_can_access_create_document_page(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($teacher)->get(route('documents.create'));

        $response->assertStatus(200);
        $response->assertSee('Đăng Tải Tài Liệu Học Tập');
    }

    public function test_teacher_can_upload_document_with_multiple_files(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $file1 = UploadedFile::fake()->create('slide-bai-giang.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->create('source-code.zip', 1200, 'application/zip');

        $response = $this->actingAs($teacher)->post(route('documents.store'), [
            'title' => 'Giáo trình Clean Architecture',
            'category' => 'Khoa học máy tính',
            'description' => 'Hướng dẫn kiến trúc phần mềm chuẩn',
            'cover_image_url' => 'https://example.com/clean-arch.jpg',
            'files' => [$file1, $file2],
        ]);

        $this->assertDatabaseHas('documents', [
            'user_id' => $teacher->id,
            'title' => 'Giáo trình Clean Architecture',
            'category' => 'Khoa học máy tính',
        ]);

        $document = Document::where('title', 'Giáo trình Clean Architecture')->firstOrFail();

        $this->assertCount(2, $document->files);
        $this->assertDatabaseHas('document_files', [
            'document_id' => $document->id,
            'file_name' => 'slide-bai-giang.pdf',
            'file_type' => 'pdf',
        ]);
        $this->assertDatabaseHas('document_files', [
            'document_id' => $document->id,
            'file_name' => 'source-code.zip',
            'file_type' => 'zip',
        ]);

        $response->assertRedirect(route('documents.show', $document->slug ?: $document->id));
    }

    public function test_user_can_view_document_and_download_file(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $document = Document::create([
            'user_id' => $teacher->id,
            'title' => 'Đề thi trắc nghiệm CSDL',
            'slug' => 'de-thi-trac-nghiem-csdl',
            'category' => 'Đề thi & Đề cương ôn tập',
            'description' => 'Đề thi kèm lời giải',
            'view_count' => 0,
            'download_count' => 0,
        ]);

        $storedPath = 'documents/'.$document->id.'/sample_test.docx';
        Storage::disk('public')->put($storedPath, 'Sample test file content');

        $docFile = DocumentFile::create([
            'document_id' => $document->id,
            'file_name' => 'De-thi-CSDL-2026.docx',
            'file_path' => $storedPath,
            'file_size' => 1024,
            'file_type' => 'docx',
            'download_count' => 0,
        ]);

        // Học viên vào xem trang chi tiết
        $viewResponse = $this->actingAs($student)->get(route('documents.show', $document->slug));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Đề thi trắc nghiệm CSDL');
        $viewResponse->assertSee('De-thi-CSDL-2026.docx');

        $this->assertEquals(1, $document->fresh()->view_count);

        // Học viên tải file xuống
        $downloadResponse = $this->actingAs($student)->get(route('documents.files.download', $docFile->id));
        $downloadResponse->assertStatus(200);

        // Kiểm tra biến đếm lượt tải
        $this->assertEquals(1, $docFile->fresh()->download_count);
        $this->assertEquals(1, $document->fresh()->download_count);
    }

    public function test_author_can_update_document_and_remove_file(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $document = Document::create([
            'user_id' => $teacher->id,
            'title' => 'Tài liệu ban đầu',
            'slug' => 'tai-lieu-ban-dau',
            'category' => 'Lập trình Web',
            'description' => 'Mô tả cũ',
        ]);

        $storedPath = 'documents/'.$document->id.'/old_file.pdf';
        Storage::disk('public')->put($storedPath, 'Old content');

        $docFile = DocumentFile::create([
            'document_id' => $document->id,
            'file_name' => 'old_file.pdf',
            'file_path' => $storedPath,
            'file_size' => 500,
            'file_type' => 'pdf',
        ]);

        $newFile = UploadedFile::fake()->create('new_document.pdf', 800, 'application/pdf');

        $response = $this->actingAs($teacher)->put(route('documents.update', $document->id), [
            'title' => 'Tài liệu đã cập nhật tiêu đề',
            'category' => 'Lập trình di động',
            'description' => 'Mô tả mới',
            'delete_file_ids' => [$docFile->id],
            'new_files' => [$newFile],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Tài liệu đã cập nhật tiêu đề',
            'category' => 'Lập trình di động',
        ]);

        // File cũ đã bị xóa
        $this->assertDatabaseMissing('document_files', ['id' => $docFile->id]);
        $this->assertFalse(Storage::disk('public')->exists($storedPath));

        // File mới đã được lưu
        $this->assertDatabaseHas('document_files', [
            'document_id' => $document->id,
            'file_name' => 'new_document.pdf',
        ]);
    }

    public function test_non_author_cannot_delete_document(): void
    {
        $author = User::factory()->create(['role' => 'teacher']);
        $anotherTeacher = User::factory()->create(['role' => 'teacher']);

        $document = Document::create([
            'user_id' => $author->id,
            'title' => 'Tài liệu riêng của tác giả',
            'slug' => 'tai-lieu-rieng',
            'category' => 'Lập trình Web',
        ]);

        $response = $this->actingAs($anotherTeacher)->delete(route('documents.destroy', $document->id));
        $response->assertStatus(403);

        $this->assertDatabaseHas('documents', ['id' => $document->id]);
    }

    public function test_admin_can_delete_any_document(): void
    {
        $author = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        $document = Document::create([
            'user_id' => $author->id,
            'title' => 'Tài liệu tác giả',
            'slug' => 'tai-lieu-tac-gia',
            'category' => 'Lập trình Web',
        ]);

        $response = $this->actingAs($admin)->delete(route('documents.destroy', $document->id));
        $response->assertRedirect(route('documents.index'));

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_ai_guardrail_recognizes_document_library_queries(): void
    {
        $guardrail = new AiGuardrailService;

        $this->assertFalse($guardrail->isOffTopic('Tôi muốn tìm tài liệu học tập về Laravel'));
        $this->assertFalse($guardrail->isOffTopic('Cho tôi xin slide bài giảng cơ sở dữ liệu'));
        $this->assertFalse($guardrail->isOffTopic('Có giáo trình PDF nào về AI không?'));
        $this->assertFalse($guardrail->isOffTopic('Cách tải tài liệu trên hệ thống thế nào?'));
    }
}

