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

    <!-- Header banner -->
    <section class="bg-neutral-950 text-white border-b border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-pink-500 mb-2">Thư viện học tập</p>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight mb-2">Tất cả khóa học</h1>
            <p class="text-neutral-400 max-w-xl text-xs sm:text-sm leading-relaxed">
                Khám phá kho khóa học lập trình, kỹ năng trực tuyến chất lượng cao tại Ctrl C+V.
                Hiện có <strong class="text-white font-extrabold">{{ $courses->count() }}</strong> khóa học đã được phê duyệt.
            </p>
        </div>
    </section>

    <!-- Main Content & Filter Area -->
    <section class="py-10 lg:py-14 min-h-[500px]" x-data="coursesHandler()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Thanh tìm kiếm & lọc khóa học -->
            <div class="mb-8 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
                <form method="GET" action="{{ route('courses.index') }}" class="space-y-3">
                    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                        <!-- Ô tìm kiếm -->
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" x-model="search"
                                   placeholder="Tìm kiếm khóa học theo tên, mô tả, giảng viên..."
                                   class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm border-slate-200 focus:border-pink-600 focus:ring-pink-500 rounded-xl bg-slate-50/50 focus:bg-white transition">
                            <button type="button" x-show="search && search.length > 0" @click="search = ''" style="display: none;"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 cursor-pointer" title="Xóa tìm kiếm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Dropdowns lọc: Giá, Sắp xếp, Trạng thái -->
                        <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                            <!-- Dropdown Lọc Giá -->
                            <select name="price" x-model="price" @change="onPriceChange()"
                                    class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white cursor-pointer">
                                <option value="all">Tất cả mức giá</option>
                                <option value="free">Miễn phí</option>
                                <option value="paid">Có phí</option>
                                <option value="under_200k">Dưới 200.000 đ</option>
                                <option value="200k_500k">200.000 - 500.000 đ</option>
                                <option value="over_500k">Trên 500.000 đ</option>
                                <option value="custom">Tự nhập mức giá mong muốn...</option>
                            </select>

                            <!-- Dropdown Sắp xếp -->
                            <select name="sort" x-model="sort"
                                    class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white cursor-pointer">
                                <option value="latest">Mới nhất</option>
                                <option value="popular">Nổi bật nhất</option>
                                <option value="price_asc">Giá: Thấp đến cao</option>
                                <option value="price_desc">Giá: Cao đến thấp</option>
                                <option value="rating">Đánh giá cao nhất</option>
                            </select>

                            @auth
                                <!-- Dropdown Trạng thái đăng ký -->
                                <select name="enrollment" x-model="enrollment"
                                        class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white cursor-pointer">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="enrolled">Đã đăng ký / Mua</option>
                                    <option value="not_enrolled">Chưa đăng ký</option>
                                </select>
                            @endauth

                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition whitespace-nowrap shadow-xs cursor-pointer">
                                Tìm kiếm
                            </button>

                            <button type="button" x-show="hasActiveFilters()" @click="resetFilters()" style="display: none;"
                                    class="px-3 py-2.5 text-xs font-bold text-slate-500 hover:text-pink-600 transition underline whitespace-nowrap cursor-pointer">
                                Đặt lại
                            </button>
                        </div>
                    </div>

                    <!-- Hàng nhập mức giá mong muốn (Khi chọn 'custom') -->
                    <div x-show="price === 'custom'" x-cloak class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-3 text-xs bg-pink-50/40 p-3 rounded-xl border border-pink-100/70">
                        <span class="font-bold text-slate-800 flex items-center gap-1.5 shrink-0">
                            <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                            <span>Mức giá bạn muốn:</span>
                        </span>
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-500 font-medium">Từ:</span>
                                <input type="number"
                                       name="min_price"
                                       x-model="minPrice"
                                       min="0"
                                       step="10000"
                                       placeholder="0 đ"
                                       class="w-28 sm:w-32 py-1.5 px-3 text-xs border border-slate-200 rounded-xl bg-white focus:border-pink-600 focus:ring-1 focus:ring-pink-500 font-semibold text-slate-900 shadow-2xs">
                            </div>
                            <span class="text-slate-400 font-bold">-</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-500 font-medium">Đến:</span>
                                <input type="number"
                                       name="max_price"
                                       x-model="maxPrice"
                                       min="0"
                                       step="10000"
                                       placeholder="Tối đa (đ)"
                                       class="w-28 sm:w-32 py-1.5 px-3 text-xs border border-slate-200 rounded-xl bg-white focus:border-pink-600 focus:ring-1 focus:ring-pink-500 font-semibold text-slate-900 shadow-2xs">
                            </div>
                            <span class="text-[11px] text-slate-400 italic">
                                (Nhập khoảng giá hoặc chỉ nhập mức giá tối đa bạn muốn chi trả)
                            </span>
                        </div>
                    </div>
                </form>

                <!-- Kết quả hiển thị / đếm số lượng -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <span>Đang hiển thị <strong class="text-pink-600 font-extrabold" x-text="countVisible()">{{ $courses->count() }}</strong> khóa học</span>
                    <div class="flex items-center gap-3 text-[11px] text-slate-400 flex-wrap">
                        <span x-show="search && search.trim() !== ''" style="display: none;">
                            Lọc theo từ khóa: "<span class="text-slate-700 font-semibold" x-text="search"></span>"
                        </span>
                        <span x-show="price === 'custom' && (minPrice || maxPrice)" style="display: none;">
                            Khoảng giá: <span class="text-pink-600 font-semibold" x-text="formatCustomPriceLabel()"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty state khi không khớp kết quả tìm kiếm -->
            <div x-show="countVisible() === 0" style="display: none;"
                 class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center max-w-xl mx-auto shadow-xs my-6">
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 mx-auto flex items-center justify-center mb-3 text-lg font-bold">
                    🔍
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 mb-1">Không tìm thấy khóa học nào phù hợp</h3>
                <p class="text-slate-500 text-xs mb-4">
                    Không có khóa học nào khớp với từ khóa tìm kiếm hoặc mức giá đã chọn.
                </p>
                <button type="button" @click="resetFilters()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-xs cursor-pointer">
                    Xóa bộ lọc
                </button>
            </div>

            @if($courses->isEmpty())
                <div class="bg-white border border-pink-100 p-14 text-center rounded-3xl">
                    <h3 class="font-black text-xl text-neutral-900 mb-2">Hiện chưa có khóa học nào được duyệt</h3>
                    <p class="text-sm text-neutral-500">Các bài giảng và khóa học mới sẽ sớm được giảng viên tải lên và kiểm duyệt.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                    @foreach($courses as $course)
                        <div x-show="matches({{ $course->id }})" class="transition-all duration-200 h-full">
                            <x-course-card :course="$course" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
    <script>
        function coursesHandler() {
            return {
                search: @json($search ?? ''),
                price: @json($price ?? 'all'),
                sort: @json($sort ?? 'latest'),
                enrollment: @json($enrollment ?? 'all'),
                minPrice: @json($minPrice ?? ''),
                maxPrice: @json($maxPrice ?? ''),
                courses: [
                    @foreach($courses as $c)
                    @php
                        $cTeacher = $c->teacher?->name ?? 'Ban Đào Tạo';
                        $cTeacherLower = mb_strtolower($cTeacher, 'UTF-8');
                        $cTitleLower = mb_strtolower($c->title, 'UTF-8');
                        $cDescLower = mb_strtolower($c->description ?? '', 'UTF-8');
                        $cPrice = (int) ($c->price ?? 0);
                    @endphp
                    {
                        id: {{ $c->id }},
                        title: @json($cTitleLower),
                        desc: @json($cDescLower),
                        teacher: @json($cTeacherLower),
                        price: {{ $cPrice }},
                        isEnrolled: {{ ($c->is_enrolled ?? false) ? 'true' : 'false' }},
                        rating: {{ (float) ($c->reviews_avg_rating ?? 0) }},
                        reviewsCount: {{ (int) ($c->reviews_count ?? 0) }}
                    },
                    @endforeach
                ],
                onPriceChange() {
                    if (this.price !== 'custom') {
                        this.minPrice = '';
                        this.maxPrice = '';
                    }
                },
                formatCustomPriceLabel() {
                    const min = parseInt(this.minPrice) || 0;
                    const max = parseInt(this.maxPrice) || null;
                    if (min > 0 && max !== null) {
                        return `${min.toLocaleString('vi-VN')} đ - ${max.toLocaleString('vi-VN')} đ`;
                    } else if (min > 0) {
                        return `Từ ${min.toLocaleString('vi-VN')} đ`;
                    } else if (max !== null) {
                        return `Đến ${max.toLocaleString('vi-VN')} đ`;
                    }
                    return '';
                },
                matches(id) {
                    const item = this.courses.find(x => x.id === id);
                    if (!item) return true;

                    // Lọc theo giá
                    if (this.price === 'free' && item.price > 0) return false;
                    if (this.price === 'paid' && item.price <= 0) return false;
                    if (this.price === 'under_200k' && (item.price <= 0 || item.price >= 200000)) return false;
                    if (this.price === '200k_500k' && (item.price < 200000 || item.price > 500000)) return false;
                    if (this.price === 'over_500k' && item.price <= 500000) return false;
                    if (this.price === 'custom') {
                        const min = parseInt(this.minPrice) || 0;
                        const max = parseInt(this.maxPrice);
                        if (min > 0 && item.price < min) return false;
                        if (!isNaN(max) && max > 0 && item.price > max) return false;
                    }

                    // Lọc theo trạng thái đăng ký
                    if (this.enrollment === 'enrolled' && !item.isEnrolled) return false;
                    if (this.enrollment === 'not_enrolled' && item.isEnrolled) return false;

                    // Lọc theo từ khóa tìm kiếm
                    if (!this.search || !this.search.trim()) return true;
                    const q = this.search.toLowerCase().trim();
                    return item.title.includes(q) || item.desc.includes(q) || item.teacher.includes(q);
                },
                countVisible() {
                    return this.courses.filter(item => {
                        if (this.price === 'free' && item.price > 0) return false;
                        if (this.price === 'paid' && item.price <= 0) return false;
                        if (this.price === 'under_200k' && (item.price <= 0 || item.price >= 200000)) return false;
                        if (this.price === '200k_500k' && (item.price < 200000 || item.price > 500000)) return false;
                        if (this.price === 'over_500k' && item.price <= 500000) return false;
                        if (this.price === 'custom') {
                            const min = parseInt(this.minPrice) || 0;
                            const max = parseInt(this.maxPrice);
                            if (min > 0 && item.price < min) return false;
                            if (!isNaN(max) && max > 0 && item.price > max) return false;
                        }
                        if (this.enrollment === 'enrolled' && !item.isEnrolled) return false;
                        if (this.enrollment === 'not_enrolled' && item.isEnrolled) return false;
                        if (!this.search || !this.search.trim()) return true;
                        const q = this.search.toLowerCase().trim();
                        return item.title.includes(q) || item.desc.includes(q) || item.teacher.includes(q);
                    }).length;
                },
                hasActiveFilters() {
                    return (this.search && this.search.trim() !== '') || this.price !== 'all' || this.sort !== 'latest' || this.enrollment !== 'all' || this.minPrice !== '' || this.maxPrice !== '';
                },
                resetFilters() {
                    this.search = '';
                    this.price = 'all';
                    this.sort = 'latest';
                    this.enrollment = 'all';
                    this.minPrice = '';
                    this.maxPrice = '';
                    if (window.location.search) {
                        window.location.href = @json(route('courses.index'));
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
