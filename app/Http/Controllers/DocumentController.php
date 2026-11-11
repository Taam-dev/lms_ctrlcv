<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Danh sách chuyên mục chuẩn cho Kho Tài Liệu
     */
    public const CATEGORIES = [
        'Lập trình Web',
        'Khoa học máy tính',
        'Cơ sở dữ liệu',
        'Trí tuệ nhân tạo (AI)',
        'DevOps & Điện toán đám mây',
        'An toàn thông tin & Bảo mật',
        'Lập trình di động',
        'Giáo trình & Sách Ebook',
        'Slide & Bài giảng tóm tắt',
        'Đề thi & Đề cương ôn tập',
        'Source Code & Đồ án mẫu',
        'Tài liệu khác',
    ];

    /**
     * Danh sách ảnh bìa mặc định theo chuyên mục
     */
    public const DEFAULT_COVERS = [
        'Lập trình Web' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1000&auto=format&fit=crop&q=80',
        'Khoa học máy tính' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1000&auto=format&fit=crop&q=80',
        'Cơ sở dữ liệu' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=1000&auto=format&fit=crop&q=80',
        'Trí tuệ nhân tạo (AI)' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1000&auto=format&fit=crop&q=80',
        'DevOps & Điện toán đám mây' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1000&auto=format&fit=crop&q=80',
        'An toàn thông tin & Bảo mật' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1000&auto=format&fit=crop&q=80',
        'Lập trình di động' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1000&auto=format&fit=crop&q=80',
        'Giáo trình & Sách Ebook' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=1000&auto=format&fit=crop&q=80',
        'Slide & Bài giảng tóm tắt' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=1000&auto=format&fit=crop&q=80',
        'Đề thi & Đề cương ôn tập' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=1000&auto=format&fit=crop&q=80',
        'Source Code & Đồ án mẫu' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1000&auto=format&fit=crop&q=80',
        'Tài liệu khác' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1000&auto=format&fit=crop&q=80',
    ];

    /**
     * Hiển thị danh sách tài liệu trong Kho Tài Liệu
     */
    public function index(Request $request): View
    {
        $query = Document::with(['user:id,name,avatar,role', 'files'])
            ->withCount('files');

        // Lọc từ khóa tìm kiếm
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('files', function ($fq) use ($search) {
                        $fq->where('file_name', 'like', "%{$search}%");
                    });
            });
        }

        // Lọc theo Chuyên mục
        $selectedCategory = $request->input('category');
        if ($selectedCategory && in_array($selectedCategory, self::CATEGORIES, true)) {
            $query->where('category', $selectedCategory);
        }

        // Sắp xếp
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'downloads' => $query->orderBy('download_count', 'desc')->orderBy('id', 'desc'),
            'views' => $query->orderBy('view_count', 'desc')->orderBy('id', 'desc'),
            'name' => $query->orderBy('title', 'asc'),
            default => $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc'),
        };

        $documents = $query->paginate(12)->withQueryString();

        // Thống kê nhanh
        $totalDocuments = Document::count();
        $totalFiles = DocumentFile::count();
        $totalDownloads = Document::sum('download_count');

        // Danh mục kèm số lượng tài liệu
        $categoryCounts = Document::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        return view('documents.index', [
            'documents' => $documents,
            'categories' => self::CATEGORIES,
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'sort' => $sort,
            'totalDocuments' => $totalDocuments,
            'totalFiles' => $totalFiles,
            'totalDownloads' => $totalDownloads,
        ]);
    }

    /**
     * Giao diện tải lên tài liệu mới (Dành cho Giảng viên & Admin)
     */
    public function create(Request $request): View
    {
        if (! $request->user()?->canManageDocuments()) {
            abort(403, 'Bạn không có quyền tải tài liệu lên hệ thống. Tính năng này dành riêng cho Giảng viên và Quản trị viên.');
        }

        return view('documents.create', [
            'categories' => self::CATEGORIES,
            'defaultCovers' => self::DEFAULT_COVERS,
        ]);
    }

    /**
     * Lưu trữ tài liệu mới kèm nhiều tệp tin
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()?->canManageDocuments()) {
            abort(403, 'Bạn không có quyền đăng tải tài liệu.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_image_url' => ['nullable', 'url', 'max:500'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:51200'], // tối đa 50MB mỗi file
        ], [
            'title.required' => 'Vui lòng nhập tên tài liệu.',
            'category.required' => 'Vui lòng chọn chuyên mục tài liệu.',
            'files.required' => 'Vui lòng tải lên ít nhất 1 tệp tin tài liệu.',
            'files.min' => 'Vui lòng tải lên ít nhất 1 tệp tin tài liệu.',
            'files.*.max' => 'Mỗi tệp tin tải lên tối đa 50MB.',
            'cover_image_file.max' => 'Ảnh bìa tải lên không vượt quá 5MB.',
        ]);

        // Xử lý ảnh bìa (Cover Image)
        $coverPath = null;
        if ($request->hasFile('cover_image_file')) {
            $coverPath = $request->file('cover_image_file')->store('documents/covers', 'public');
        } elseif (! empty($validated['cover_image_url'])) {
            $coverPath = $validated['cover_image_url'];
        } else {
            $cat = $validated['category'];
            $coverPath = self::DEFAULT_COVERS[$cat] ?? self::DEFAULT_COVERS['Tài liệu khác'];
        }

        // Tạo Document
        $document = Document::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'cover_image' => $coverPath,
            'view_count' => 0,
            'download_count' => 0,
            'is_featured' => (bool) $request->input('is_featured', false),
        ]);

        // Lưu danh sách các tệp tin đính kèm
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'file');
                    $fileSize = $file->getSize();

                    $storedPath = $file->store('documents/'.$document->id, 'public');

                    DocumentFile::create([
                        'document_id' => $document->id,
                        'file_name' => $originalName,
                        'file_path' => $storedPath,
                        'file_size' => $fileSize,
                        'file_type' => $ext,
                        'download_count' => 0,
                    ]);
                }
            }
        }

        return redirect()->route('documents.show', $document->slug ?: $document->id)
            ->with('success', 'Đã đăng tài liệu "'.$document->title.'" thành công với '.$document->files()->count().' tệp đính kèm!');
    }

    /**
     * Xem chi tiết tài liệu & danh sách file có thể tải về
     */
    public function show(string $slugOrId): View
    {
        $document = Document::where('slug', $slugOrId)
            ->orWhere('id', is_numeric($slugOrId) ? (int) $slugOrId : 0)
            ->with(['user:id,name,avatar,role', 'files'])
            ->firstOrFail();

        // Tăng lượt xem
        $document->increment('view_count');

        // Tài liệu liên quan cùng chuyên mục
        $relatedDocuments = Document::where('category', $document->category)
            ->where('id', '!=', $document->id)
            ->with(['user:id,name,avatar', 'files'])
            ->withCount('files')
            ->take(4)
            ->get();

        return view('documents.show', [
            'document' => $document,
            'relatedDocuments' => $relatedDocuments,
        ]);
    }

    /**
     * Giao diện chỉnh sửa tài liệu
     */
    public function edit(Request $request, string $slugOrId): View
    {
        $document = Document::where('slug', $slugOrId)
            ->orWhere('id', is_numeric($slugOrId) ? (int) $slugOrId : 0)
            ->with('files')
            ->firstOrFail();

        if (! $document->canEditBy($request->user())) {
            abort(403, 'Bạn không có quyền chỉnh sửa tài liệu này.');
        }

        return view('documents.edit', [
            'document' => $document,
            'categories' => self::CATEGORIES,
            'defaultCovers' => self::DEFAULT_COVERS,
        ]);
    }

    /**
     * Cập nhật thông tin tài liệu & quản lý file đính kèm
     */
    public function update(Request $request, string $slugOrId): RedirectResponse
    {
        $document = Document::where('slug', $slugOrId)
            ->orWhere('id', is_numeric($slugOrId) ? (int) $slugOrId : 0)
            ->with('files')
            ->firstOrFail();

        if (! $document->canEditBy($request->user())) {
            abort(403, 'Bạn không có quyền cập nhật tài liệu này.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_image_url' => ['nullable', 'url', 'max:500'],
            'new_files' => ['nullable', 'array'],
            'new_files.*' => ['file', 'max:51200'],
            'delete_file_ids' => ['nullable', 'array'],
            'delete_file_ids.*' => ['integer'],
        ]);

        // Cập nhật ảnh bìa nếu có upload mới
        if ($request->hasFile('cover_image_file')) {
            if ($document->cover_image && ! str_starts_with($document->cover_image, 'http') && Storage::disk('public')->exists($document->cover_image)) {
                Storage::disk('public')->delete($document->cover_image);
            }
            $document->cover_image = $request->file('cover_image_file')->store('documents/covers', 'public');
        } elseif (! empty($validated['cover_image_url'])) {
            $document->cover_image = $validated['cover_image_url'];
        }

        $document->title = $validated['title'];
        $document->category = $validated['category'];
        $document->description = $validated['description'] ?? null;
        if ($request->user()->isAdmin() && $request->has('is_featured')) {
            $document->is_featured = (bool) $request->input('is_featured');
        }
        $document->save();

        // Xóa các file được chọn xóa
        if (! empty($validated['delete_file_ids'])) {
            $filesToDelete = DocumentFile::where('document_id', $document->id)
                ->whereIn('id', $validated['delete_file_ids'])
                ->get();

            foreach ($filesToDelete as $df) {
                if (Storage::disk('public')->exists($df->file_path)) {
                    Storage::disk('public')->delete($df->file_path);
                }
                $df->delete();
            }
        }

        // Tải thêm file mới
        if ($request->hasFile('new_files')) {
            foreach ($request->file('new_files') as $file) {
                if ($file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'file');
                    $fileSize = $file->getSize();

                    $storedPath = $file->store('documents/'.$document->id, 'public');

                    DocumentFile::create([
                        'document_id' => $document->id,
                        'file_name' => $originalName,
                        'file_path' => $storedPath,
                        'file_size' => $fileSize,
                        'file_type' => $ext,
                        'download_count' => 0,
                    ]);
                }
            }
        }

        return redirect()->route('documents.show', $document->slug ?: $document->id)
            ->with('success', 'Đã cập nhật tài liệu "'.$document->title.'" thành công!');
    }

    /**
     * Xóa tài liệu và toàn bộ tệp tin vật lý đính kèm
     */
    public function destroy(Request $request, string $slugOrId): RedirectResponse
    {
        $document = Document::where('slug', $slugOrId)
            ->orWhere('id', is_numeric($slugOrId) ? (int) $slugOrId : 0)
            ->with('files')
            ->firstOrFail();

        if (! $document->canDeleteBy($request->user())) {
            abort(403, 'Bạn không có quyền xóa tài liệu này.');
        }

        // Xóa toàn bộ file đính kèm trên storage
        foreach ($document->files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        // Xóa ảnh bìa nếu là file local upload
        if ($document->cover_image && ! str_starts_with($document->cover_image, 'http') && Storage::disk('public')->exists($document->cover_image)) {
            Storage::disk('public')->delete($document->cover_image);
        }

        $title = $document->title;
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Đã xóa tài liệu "'.$title.'" thành công!');
    }

    /**
     * Tải xuống 1 tệp tin cụ thể
     */
    public function download(DocumentFile $file): StreamedResponse|BinaryFileResponse
    {
        if (! Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'Tệp tin không tồn tại trên máy chủ hoặc đã bị gỡ bỏ.');
        }

        // Tăng lượt tải cho file và cho bài đăng tài liệu
        $file->increment('download_count');
        $file->document()->increment('download_count');

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }
}
