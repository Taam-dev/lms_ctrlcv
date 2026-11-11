<x-app-layout
    :title="$document->title . ' | Kho Tài Liệu Ctrl C+V'"
    :meta-description="Str::limit(strip_tags($document->description ?? 'Tải tài liệu ' . $document->title . ' miễn phí tại Ctrl C+V'), 160)"
    :meta-keywords="$document->title . ', ' . $document->category . ', tài liệu học tập, giáo trình, slide, ctrl c+v'"
    :og-image="$document->cover_image_url">

    @push('schema')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Trang chủ',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Kho tài liệu',
                'item' => route('documents.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $document->title,
                'item' => route('documents.show', $document->slug ?: $document->id),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <!-- Header Banner with Cover Background -->
    <section class="relative bg-neutral-950 text-white border-b border-white/10 overflow-hidden">
        <!-- Background Cover Image with Blur Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $document->cover_image_url }}"
                 alt="{{ $document->title }}"
                 class="w-full h-full object-cover blur-2xl opacity-20 scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/80 to-neutral-950/90"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 relative z-10 space-y-6">
            <!-- Breadcrumb Navigation -->
            <nav class="flex items-center gap-2 text-xs text-neutral-400">
                <a href="{{ route('home') }}" class="hover:text-white transition">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('documents.index') }}" class="hover:text-white transition">Kho tài liệu</a>
                <span>/</span>
                <a href="{{ route('documents.index', ['category' => $document->category]) }}" class="hover:text-pink-400 transition">{{ $document->category }}</a>
                <span>/</span>
                <span class="text-white truncate max-w-xs">{{ $document->title }}</span>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <!-- Cover Preview + Main Info -->
                <div class="flex flex-col sm:flex-row items-start gap-6 max-w-3xl">
                    <div class="w-full sm:w-48 aspect-video sm:aspect-[4/3] rounded-2xl overflow-hidden bg-neutral-900 border border-white/10 shrink-0 shadow-2xl relative">
                        <img src="{{ $document->cover_image_url }}" alt="{{ $document->title }}" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-black/80 text-pink-400 border border-pink-500/30">
                            {{ $document->category }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-500/10 text-pink-400 border border-pink-500/20">
                                {{ $document->category }}
                            </span>
                            @if($document->is_featured)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1">
                                    ⭐ Nổi bật
                                </span>
                            @endif
                            <span class="text-xs text-neutral-400">
                                Đăng ngày {{ $document->created_at->format('d/m/Y') }}
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
                            {{ $document->title }}
                        </h1>

                        <!-- Author Row -->
                        <div class="flex items-center gap-3 pt-1">
                            @if($document->user?->avatar_url)
                                <img src="{{ $document->user->avatar_url }}" alt="{{ $document->user->name }}" class="w-9 h-9 rounded-full object-cover border border-white/10">
                            @else
                                <div class="w-9 h-9 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                    {{ strtoupper(substr($document->user?->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="text-xs font-bold text-white">{{ $document->user?->name ?? 'Ban Quản Trị' }}</div>
                                <div class="text-[11px] font-semibold text-pink-400">
                                    {{ $document->user?->isAdmin() ? 'Quản trị viên hệ thống' : ($document->user?->isTeacher() ? 'Giảng viên chính thức' : 'Tác giả') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Controls (Edit/Delete for author/admin) -->
                @auth
                    @if($document->canEditBy(Auth::user()))
                        <div class="flex items-center gap-2 shrink-0 bg-white/5 border border-white/10 p-2 rounded-xl backdrop-blur-md">
                            <a href="{{ route('documents.edit', $document->slug ?: $document->id) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Chỉnh sửa</span>
                            </a>

                            <form action="{{ route('documents.destroy', $document->slug ?: $document->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này và toàn bộ các tệp đính kèm?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white border border-rose-500/20 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Xóa</span>
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-white/10">
                <div class="bg-neutral-900/60 border border-white/5 rounded-xl p-3 text-center">
                    <div class="text-xs text-neutral-400">Số tệp đính kèm</div>
                    <div class="text-lg font-black text-cyan-400 mt-0.5">{{ $document->files->count() }} tệp tin</div>
                </div>
                <div class="bg-neutral-900/60 border border-white/5 rounded-xl p-3 text-center">
                    <div class="text-xs text-neutral-400">Tổng dung lượng</div>
                    <div class="text-lg font-black text-white mt-0.5">{{ $document->total_size_formatted }}</div>
                </div>
                <div class="bg-neutral-900/60 border border-white/5 rounded-xl p-3 text-center">
                    <div class="text-xs text-neutral-400">Lượt tải về</div>
                    <div class="text-lg font-black text-amber-400 mt-0.5">{{ $document->formatted_download_count }}</div>
                </div>
                <div class="bg-neutral-900/60 border border-white/5 rounded-xl p-3 text-center">
                    <div class="text-xs text-neutral-400">Lượt xem</div>
                    <div class="text-lg font-black text-pink-400 mt-0.5">{{ $document->formatted_view_count }}</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="py-10 bg-[#09090b] text-neutral-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column (2 Cols): Files list & Description -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Danh sách tệp đính kèm -->
                    <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                        <div class="flex items-center justify-between border-b border-white/5 pb-4">
                            <div>
                                <h2 class="text-lg font-black text-white flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                                    Tệp Tin Đính Kèm Có Thể Tải Về
                                </h2>
                                <p class="text-xs text-neutral-400 mt-0.5">Bấm nút "Tải xuống" tương ứng để lưu file về thiết bị của bạn.</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                {{ $document->files->count() }} tệp
                            </span>
                        </div>

                        <!-- Files List -->
                        <div class="space-y-3">
                            @forelse($document->files as $file)
                                @php
                                    $style = $file->file_type_style;
                                @endphp
                                <div class="bg-neutral-900/90 border border-white/10 hover:border-pink-500/40 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition duration-150">
                                    <!-- File Info -->
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <!-- Extension Badge -->
                                        <div class="w-12 h-12 rounded-xl {{ $style['bg'] }} {{ $style['border'] }} {{ $style['color'] }} border flex flex-col items-center justify-center shrink-0 shadow-inner">
                                            <span class="text-[11px] font-black uppercase tracking-wider">{{ $style['label'] }}</span>
                                        </div>

                                        <div class="min-w-0 space-y-0.5">
                                            <div class="text-sm font-bold text-white truncate hover:text-pink-400 transition" title="{{ $file->file_name }}">
                                                {{ $file->file_name }}
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-neutral-400">
                                                <span>{{ $file->formatted_size }}</span>
                                                <span>•</span>
                                                <span class="flex items-center gap-1 text-amber-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    {{ number_format($file->download_count) }} lượt tải
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Download Button -->
                                    <div class="shrink-0 flex items-center justify-end">
                                        <a href="{{ route('documents.files.download', $file->id) }}"
                                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white shadow-md shadow-pink-600/30 hover:shadow-pink-600/50 transition duration-150 transform hover:-translate-y-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>Tải xuống</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-neutral-400 bg-neutral-900 rounded-xl">
                                    Chưa có tệp tin nào được tải lên cho tài liệu này.
                                </div>
                            @endforelse
                        </div>

                        <!-- Policy Tip -->
                        <div class="flex items-center gap-2 p-3.5 rounded-xl bg-pink-500/10 border border-pink-500/20 text-xs text-neutral-300">
                            <span class="text-pink-400 text-base">💡</span>
                            <span>Mọi tệp tin đều được kiểm duyệt an toàn, không chứa mã độc. Học viên & Giảng viên có thể tải về không giới hạn.</span>
                        </div>
                    </div>

                    <!-- Mô tả tài liệu -->
                    <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-4 shadow-xl">
                        <h2 class="text-lg font-black text-white flex items-center gap-2 border-b border-white/5 pb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                            Mô Tả & Hướng Dẫn Sử Dụng
                        </h2>

                        @if(!empty($document->description))
                            <div class="text-sm text-neutral-300 leading-relaxed space-y-3 whitespace-pre-line">
                                {{ $document->description }}
                            </div>
                        @else
                            <p class="text-xs text-neutral-400 italic">
                                Tài liệu này không có mô tả bổ sung. Bạn có thể tải trực tiếp các tệp tin đính kèm ở mục trên.
                            </p>
                        @endif
                    </div>

                </div>

                <!-- Right Column (1 Col): Author, Meta & Related -->
                <div class="space-y-6">

                    <!-- Author Card -->
                    <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 space-y-4 shadow-xl">
                        <h3 class="text-xs font-black uppercase tracking-wider text-neutral-400">Người đăng tải</h3>
                        <div class="flex items-center gap-3">
                            @if($document->user?->avatar_url)
                                <img src="{{ $document->user->avatar_url }}" alt="{{ $document->user->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-pink-500/30">
                            @else
                                <div class="w-12 h-12 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-sm shrink-0">
                                    {{ strtoupper(substr($document->user?->name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-white truncate">{{ $document->user?->name ?? 'Người dùng' }}</div>
                                <div class="text-xs font-semibold text-pink-400">
                                    {{ $document->user?->isAdmin() ? 'Quản trị viên' : ($document->user?->isTeacher() ? 'Giảng viên' : 'Thành viên') }}
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-neutral-400 leading-relaxed border-t border-white/5 pt-3">
                            Đã đóng góp tài liệu này cho cộng đồng học viên Ctrl C+V.
                        </p>
                    </div>

                    <!-- Meta Specs Card -->
                    <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 space-y-3 shadow-xl text-xs">
                        <h3 class="text-xs font-black uppercase tracking-wider text-neutral-400 border-b border-white/5 pb-2.5">
                            Thông số tài liệu
                        </h3>
                        <div class="space-y-2.5">
                            <div class="flex justify-between py-1 border-b border-white/5">
                                <span class="text-neutral-400">Chuyên mục:</span>
                                <span class="font-bold text-white">{{ $document->category }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-white/5">
                                <span class="text-neutral-400">Số lượng file:</span>
                                <span class="font-bold text-cyan-400">{{ $document->files->count() }} tệp</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-white/5">
                                <span class="text-neutral-400">Tổng dung lượng:</span>
                                <span class="font-bold text-white">{{ $document->total_size_formatted }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-white/5">
                                <span class="text-neutral-400">Ngày đăng:</span>
                                <span class="font-bold text-white">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-neutral-400">Cập nhật:</span>
                                <span class="font-bold text-white">{{ $document->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Related Documents -->
                    @if($relatedDocuments->count() > 0)
                        <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 space-y-4 shadow-xl">
                            <h3 class="text-xs font-black uppercase tracking-wider text-neutral-400 border-b border-white/5 pb-2.5">
                                Tài liệu cùng chuyên mục
                            </h3>
                            <div class="space-y-3">
                                @foreach($relatedDocuments as $rel)
                                    <a href="{{ route('documents.show', $rel->slug ?: $rel->id) }}"
                                       class="flex items-center gap-3 group p-2 rounded-xl hover:bg-white/5 transition">
                                        <img src="{{ $rel->cover_image_url }}" alt="{{ $rel->title }}" class="w-14 h-11 rounded-lg object-cover bg-neutral-900 shrink-0">
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-white group-hover:text-pink-400 transition truncate">{{ $rel->title }}</h4>
                                            <div class="text-[10px] text-neutral-400 flex items-center gap-2 mt-0.5">
                                                <span>{{ $rel->files_count ?? $rel->files->count() }} tệp</span>
                                                <span>•</span>
                                                <span>{{ $rel->formatted_download_count }} lượt tải</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </main>
</x-app-layout>

