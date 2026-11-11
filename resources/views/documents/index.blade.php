<x-app-layout
    title="Kho Tài Liệu Học Tập & Bài Giảng | Ctrl C+V"
    meta-description="Tải miễn phí giáo trình, slide bài giảng, đề ôn tập, mã nguồn mẫu và tài liệu chuyên ngành CNTT do giảng viên và chuyên gia chia sẻ."
    meta-keywords="kho tài liệu, slide bài giảng, giáo trình pdf, mã nguồn đồ án, đề thi ôn tập, ctrl c+v">

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
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <!-- Hero Header Banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10 relative overflow-hidden">
        <!-- Subtle Glow Background -->
        <div class="absolute inset-0 pointer-events-none opacity-30">
            <div class="absolute -top-24 left-1/4 w-96 h-96 bg-pink-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 right-1/4 w-96 h-96 bg-rose-500/15 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-[0.2em] bg-pink-500/10 text-pink-400 border border-pink-500/20">
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse"></span>
                        Thư Viện Tài Nguyên Tri Thức
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white">
                        Kho Tài Liệu Học Tập
                    </h1>
                    <p class="text-neutral-300 text-sm sm:text-base leading-relaxed">
                        Nơi lưu trữ và chia sẻ slide bài giảng, giáo trình PDF, đề thi ôn tập và mã nguồn đồ án mẫu.
                        Tất cả học viên và giảng viên đều có thể tìm kiếm và tải về học tập hoàn toàn miễn phí.
                    </p>
                </div>

                <!-- Thống kê nhanh + Nút Đăng tài liệu -->
                <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                    <div class="flex items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md">
                        <div class="text-center px-2">
                            <div class="text-xl sm:text-2xl font-black text-pink-400">{{ number_format($totalDocuments) }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Tài liệu</div>
                        </div>
                        <div class="w-px h-8 bg-white/10"></div>
                        <div class="text-center px-2">
                            <div class="text-xl sm:text-2xl font-black text-cyan-400">{{ number_format($totalFiles) }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Tệp tin</div>
                        </div>
                        <div class="w-px h-8 bg-white/10"></div>
                        <div class="text-center px-2">
                            <div class="text-xl sm:text-2xl font-black text-amber-400">{{ number_format($totalDownloads) }}</div>
                            <div class="text-[11px] font-bold uppercase tracking-wider text-neutral-400">Lượt tải</div>
                        </div>
                    </div>

                    @auth
                        @if(Auth::user()->canManageDocuments())
                            <a href="{{ route('documents.create') }}"
                               class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-black text-xs uppercase tracking-wider bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white shadow-lg shadow-pink-600/30 hover:shadow-pink-600/50 transition duration-150 transform hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Đăng tài liệu mới</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <main class="py-10 bg-[#09090b] text-neutral-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Search & Filters Bar -->
            <div class="bg-[#141417] border border-white/10 rounded-2xl p-4 sm:p-6 shadow-xl space-y-4">
                <form method="GET" action="{{ route('documents.index') }}" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-3">
                        <!-- Search Input -->
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Tìm kiếm tài liệu, tên file, giảng viên hoặc mô tả..."
                                   class="w-full pl-11 pr-10 py-3 bg-neutral-900/80 border border-white/10 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">
                            @if($search)
                                <a href="{{ route('documents.index', array_merge(request()->except('search'), ['category' => $selectedCategory])) }}"
                                   class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-neutral-400 hover:text-white"
                                   title="Xóa tìm kiếm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="flex items-center gap-2 shrink-0">
                            <select name="sort"
                                    onchange="this.form.submit()"
                                    class="w-full md:w-auto py-3 px-4 bg-neutral-900/80 border border-white/10 rounded-xl text-xs font-bold text-white focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 cursor-pointer">
                                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="downloads" {{ $sort === 'downloads' ? 'selected' : '' }}>Tải nhiều nhất</option>
                                <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Xem nhiều nhất</option>
                                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Theo tên (A-Z)</option>
                            </select>

                            <button type="submit"
                                    class="px-5 py-3 bg-pink-600 hover:bg-pink-500 text-white font-black text-xs uppercase tracking-wider rounded-xl transition duration-150 shrink-0">
                                Lọc
                            </button>
                        </div>
                    </div>

                    <!-- Category Pills Filter -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                        <a href="{{ route('documents.index', array_filter(['search' => $search, 'sort' => $sort])) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ empty($selectedCategory) ? 'bg-pink-600 text-white shadow-md shadow-pink-600/30' : 'bg-neutral-900 text-neutral-300 hover:bg-neutral-800 hover:text-white border border-white/5' }}">
                            <span>Tất cả</span>
                            <span class="text-[10px] opacity-75 font-black">({{ $totalDocuments }})</span>
                        </a>

                        @foreach($categories as $cat)
                            @php
                                $cnt = $categoryCounts[$cat] ?? 0;
                                $isSelected = $selectedCategory === $cat;
                            @endphp
                            <a href="{{ route('documents.index', array_filter(['category' => $cat, 'search' => $search, 'sort' => $sort])) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition {{ $isSelected ? 'bg-pink-600 text-white shadow-md shadow-pink-600/30' : 'bg-neutral-900 text-neutral-300 hover:bg-neutral-800 hover:text-white border border-white/5' }}">
                                <span>{{ $cat }}</span>
                                <span class="text-[10px] opacity-75 font-black">({{ $cnt }})</span>
                            </a>
                        @endforeach
                    </div>
                </form>
            </div>

            <!-- Active filter chips indicator -->
            @if($search || $selectedCategory)
                <div class="flex items-center justify-between text-xs text-neutral-400 bg-white/5 border border-white/10 px-4 py-2.5 rounded-xl">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span>Đang lọc theo:</span>
                        @if($search)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30 font-semibold">
                                Từ khóa: "{{ $search }}"
                            </span>
                        @endif
                        @if($selectedCategory)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30 font-semibold">
                                Chuyên mục: {{ $selectedCategory }}
                            </span>
                        @endif
                        <span>(Tìm thấy {{ $documents->total() }} kết quả)</span>
                    </div>
                    <a href="{{ route('documents.index') }}" class="text-pink-400 hover:text-pink-300 font-bold hover:underline">
                        Xóa tất cả bộ lọc
                    </a>
                </div>
            @endif

            <!-- Documents Grid -->
            @if($documents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($documents as $doc)
                        <article class="group bg-[#141417] border border-white/10 hover:border-pink-500/50 rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl hover:shadow-pink-500/10 transition-all duration-300 flex flex-col justify-between">
                            <div>
                                <!-- Thumbnail / Cover -->
                                <a href="{{ route('documents.show', $doc->slug ?: $doc->id) }}" class="block relative aspect-video overflow-hidden bg-neutral-900">
                                    <img src="{{ $doc->cover_image_url }}"
                                         alt="{{ $doc->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-t from-[#141417] via-transparent to-black/40"></div>

                                    <!-- Category Pill -->
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-black/70 backdrop-blur-md text-pink-400 border border-pink-500/30">
                                        {{ $doc->category }}
                                    </span>

                                    <!-- Files count badge -->
                                    <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-neutral-900/80 backdrop-blur-md text-white border border-white/15 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $doc->files_count ?? $doc->files->count() }} tệp
                                    </span>

                                    @if($doc->is_featured)
                                        <span class="absolute bottom-3 left-3 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-amber-500 text-neutral-950 flex items-center gap-1 shadow-md">
                                            ⭐ Nổi bật
                                        </span>
                                    @endif
                                </a>

                                <!-- Content -->
                                <div class="p-5 space-y-3">
                                    <h2 class="font-bold text-base text-white group-hover:text-pink-400 transition-colors line-clamp-2 leading-snug">
                                        <a href="{{ route('documents.show', $doc->slug ?: $doc->id) }}">
                                            {{ $doc->title }}
                                        </a>
                                    </h2>

                                    <p class="text-xs text-neutral-400 line-clamp-2 leading-relaxed">
                                        {{ $doc->description ?: 'Tài liệu học tập hỗ trợ nâng cao kiến thức và kỹ năng thực hành.' }}
                                    </p>

                                    <!-- Author Info -->
                                    <div class="flex items-center gap-2.5 pt-2 border-t border-white/5">
                                        @if($doc->user?->avatar_url)
                                            <img src="{{ $doc->user->avatar_url }}" alt="{{ $doc->user->name }}" class="w-7 h-7 rounded-full object-cover shrink-0">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-[10px] shrink-0">
                                                {{ strtoupper(substr($doc->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-grow">
                                            <div class="text-xs font-bold text-neutral-200 truncate">{{ $doc->user?->name ?? 'Giảng viên' }}</div>
                                            <div class="text-[10px] font-semibold text-pink-400">
                                                {{ $doc->user?->isAdmin() ? 'Quản trị viên' : ($doc->user?->isTeacher() ? 'Giảng viên' : 'Thành viên') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-5 pb-5 pt-3 border-t border-white/5 bg-black/20 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 text-neutral-400 text-[11px] font-medium">
                                    <span class="flex items-center gap-1" title="Lượt tải về">
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        {{ $doc->formatted_download_count }}
                                    </span>
                                    <span class="flex items-center gap-1" title="Lượt xem">
                                        <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ $doc->formatted_view_count }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    @auth
                                        @if($doc->canEditBy(Auth::user()))
                                            <a href="{{ route('documents.edit', $doc->slug ?: $doc->id) }}"
                                               class="p-1.5 rounded-lg text-neutral-400 hover:text-white hover:bg-white/10 transition"
                                               title="Chỉnh sửa tài liệu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endauth

                                    <a href="{{ route('documents.show', $doc->slug ?: $doc->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg font-bold text-[11px] bg-pink-600/20 hover:bg-pink-600 text-pink-300 hover:text-white border border-pink-500/30 hover:border-pink-600 transition duration-150">
                                        <span>Tải về</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pt-6">
                    {{ $documents->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-12 text-center max-w-xl mx-auto space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-pink-600/10 border border-pink-500/20 text-pink-500 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-black text-white">Chưa có tài liệu nào phù hợp</h3>
                    <p class="text-xs text-neutral-400 leading-relaxed">
                        Không tìm thấy tài liệu học tập theo tiêu chí tìm kiếm của bạn. Hãy thử thay đổi từ khóa hoặc chọn chuyên mục khác.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('documents.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/15 text-white transition">
                            Xem tất cả tài liệu
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </main>
</x-app-layout>

