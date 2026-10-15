<x-app-layout
    title="Tất cả khóa học trực tuyến | Ctrl C+V"
    meta-description="Khám phá kho khóa học lập trình, kỹ năng trực tuyến chất lượng cao tại Ctrl C+V. Học bài giảng miễn phí, luyện quizzes tự động chấm điểm và cấp chứng chỉ tiến độ."
    meta-keywords="tất cả khóa học, học online, lập trình laravel, quizzes trực tuyến, ctrl c+v lms"
    :canonical="route('courses.index')"
    :og-image="asset('images/hero-bg.jpg')"
    og-type="website">

    @push('schema')
    <!-- Schema.org BreadcrumbList JSON-LD -->
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
                'name' => 'Tất cả khóa học',
                'item' => route('courses.index'),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <section class="bg-neutral-950 text-white border-b border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-16">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-pink-500 mb-4">Thư viện học tập</p>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight mb-3">Tất cả khóa học</h1>
            <p class="text-neutral-300 max-w-xl text-sm sm:text-base leading-relaxed">
                Chọn khóa học phù hợp, đăng ký để bắt đầu xem bài giảng và làm bài thi trắc nghiệm.
                Hiện có <strong class="text-white">{{ $courses->count() }}</strong> khóa học đã được phê duyệt.
            </p>

            <!-- Thanh tìm kiếm khóa học -->
            <form method="GET" action="{{ route('courses.index') }}" class="mt-8 flex flex-col md:flex-row gap-3 items-stretch md:items-center max-w-3xl">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-neutral-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Tìm kiếm theo tên khóa học, mô tả, giảng viên..."
                           class="w-full pl-12 pr-10 py-3.5 bg-neutral-900 border border-neutral-700 rounded-2xl text-sm text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-inner">
                    @if(!empty($search))
                        <a href="{{ route('courses.index', ['price' => $price ?? 'all', 'sort' => $sort ?? 'latest']) }}"
                           class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-neutral-400 hover:text-white"
                           title="Xóa từ khóa">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                    <select name="price"
                            class="text-xs font-semibold rounded-2xl border-neutral-700 focus:border-pink-500 focus:ring-pink-500 py-3.5 px-4 bg-neutral-900 text-white cursor-pointer">
                        <option value="all" {{ ($price ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả mức giá</option>
                        <option value="free" {{ ($price ?? '') === 'free' ? 'selected' : '' }}>Miễn phí</option>
                        <option value="paid" {{ ($price ?? '') === 'paid' ? 'selected' : '' }}>Có phí</option>
                        <option value="under_200k" {{ ($price ?? '') === 'under_200k' ? 'selected' : '' }}>Dưới 200.000 đ</option>
                        <option value="200k_500k" {{ ($price ?? '') === '200k_500k' ? 'selected' : '' }}>200.000 - 500.000 đ</option>
                        <option value="over_500k" {{ ($price ?? '') === 'over_500k' ? 'selected' : '' }}>Trên 500.000 đ</option>
                    </select>

                    <select name="sort"
                            class="text-xs font-semibold rounded-2xl border-neutral-700 focus:border-pink-500 focus:ring-pink-500 py-3.5 px-4 bg-neutral-900 text-white cursor-pointer">
                        <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="popular" {{ ($sort ?? '') === 'popular' ? 'selected' : '' }}>Nổi bật nhất</option>
                        <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến cao</option>
                        <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến thấp</option>
                        <option value="rating" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>Đánh giá cao nhất</option>
                    </select>

                    <button type="submit"
                            class="px-5 py-3.5 bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold rounded-2xl shadow-xs transition shrink-0 cursor-pointer">
                        Tìm kiếm
                    </button>

                    @if(!empty($search) || ($price ?? 'all') !== 'all' || ($sort ?? 'latest') !== 'latest')
                        <a href="{{ route('courses.index') }}"
                           class="px-3 py-3.5 text-xs text-neutral-400 hover:text-white transition font-semibold rounded-2xl hover:bg-neutral-800 shrink-0">
                            Đặt lại
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section class="py-14 lg:py-16 min-h-[420px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!empty($search))
                <div class="mb-8 p-4 rounded-2xl bg-white border border-pink-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-2.5 text-sm text-neutral-700">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-pink-50 text-pink-600 font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <span>
                            Kết quả tìm kiếm cho: <strong class="text-pink-600 font-bold">"{{ $search }}"</strong>
                            ({{ $courses->count() }} khóa học)
                        </span>
                    </div>
                    <a href="{{ route('courses.index') }}"
                       class="text-xs font-bold text-neutral-500 hover:text-pink-600 inline-flex items-center gap-1 transition">
                        <span>&times; Xóa bộ lọc tìm kiếm</span>
                    </a>
                </div>
            @endif

            @if($courses->isEmpty())
                <div class="bg-white border border-pink-100 p-14 text-center rounded-3xl">
                    @if(!empty($search))
                        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-black text-xl text-neutral-900 mb-2">Không tìm thấy khóa học nào phù hợp</h3>
                        <p class="text-sm text-neutral-500 mb-6 max-w-md mx-auto">Không có khóa học nào khớp với từ khóa "<strong>{{ $search }}</strong>". Vui lòng thử lại với từ khóa khác.</p>
                        <a href="{{ route('courses.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition">
                            Xem tất cả khóa học
                        </a>
                    @else
                        <h3 class="font-black text-xl text-neutral-900 mb-2">Hiện chưa có khóa học nào được duyệt</h3>
                        <p class="text-sm text-neutral-500">Các bài giảng và khóa học mới sẽ sớm được giảng viên tải lên và kiểm duyệt.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                    @foreach($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
