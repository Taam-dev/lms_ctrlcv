<x-app-layout
    title="Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1"
    meta-description="Nền tảng học tập tinh gọn giúp bạn tiếp cận bài giảng chất lượng, làm bài trắc nghiệm tự chấm điểm và nắm bắt tiến độ học tập minh bạch."
    meta-keywords="ctrl c+v, học trực tuyến, khóa học online, quizzes lập trình, lms mini, đào tạo trực tuyến"
    canonical="https://ctrlcv.io.vn"
    :og-image="asset('images/thumbnail.png')"
    og-type="website">
    <!-- ================= HERO BANNER ================= -->
    <section class="relative bg-neutral-950 text-white border-b border-neutral-900 overflow-hidden">
        <!-- Background Hero Image -->
        <div class="absolute inset-0 z-0 pointer-events-none select-none">
            <img src="{{ asset('images/hero-bg.jpg') }}" 
                 alt="Ctrl C+V Hero Background" 
                 class="w-full h-full object-cover object-right-top lg:object-center opacity-35 mix-blend-screen scale-105 transition-transform duration-1000">
            <!-- Subtle gradient overlays for high contrast and readability -->
            <div class="absolute inset-0 bg-gradient-to-r from-neutral-950 via-neutral-950/85 to-neutral-950/50"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-transparent to-neutral-950/60"></div>
            <div class="absolute inset-0 bg-neutral-950/15 backdrop-blur-[0.5px]"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <!-- Cột trái: Tiêu đề & Giới thiệu -->
                <div class="lg:col-span-6 xl:col-span-7">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-900 border border-neutral-800 text-neutral-400 text-xs font-medium mb-5">
                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                        <span>Nền tảng học trực tuyến &bull; Nhóm CTRL C+V</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight mb-4">
                        Học qua bài giảng, <br>
                        luyện tập qua <span class="text-pink-500">quizzes</span>
                    </h1>

                    <p class="text-sm sm:text-base text-neutral-400 leading-relaxed mb-8 max-w-xl">
                        Nền tảng học tập tinh gọn giúp bạn tiếp cận bài giảng chất lượng, làm bài trắc nghiệm tự chấm điểm và nắm bắt tiến độ học tập minh bạch.
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('courses.index') }}"
                           class="px-6 py-3 rounded-xl bg-pink-600 hover:bg-pink-500 text-white text-xs font-bold uppercase tracking-wider transition shadow-sm">
                            Khám phá khóa học
                        </a>
                        @auth
                            <a href="{{ route('student.quizzes.index') }}"
                               class="px-6 py-3 rounded-xl border border-neutral-800 hover:border-neutral-700 bg-neutral-900 text-neutral-300 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Vào thi quizzes
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                               class="px-6 py-3 rounded-xl border border-neutral-800 hover:border-neutral-700 bg-neutral-900 text-neutral-300 hover:text-white text-xs font-bold uppercase tracking-wider transition">
                                Đăng ký miễn phí
                            </a>
                        @endauth
                    </div>

                    <!-- Chỉ số tinh gọn (minimal metrics) -->
                    <div class="mt-10 pt-6 border-t border-neutral-900 flex flex-wrap items-center gap-6 sm:gap-8 text-xs text-neutral-400">
                        <div>
                            <span class="text-lg font-bold text-white mr-1.5">{{ $totalCourses }}</span>
                            <span>Khóa học đã duyệt</span>
                        </div>
                        <div class="w-1 h-1 rounded-full bg-neutral-800 hidden sm:block"></div>
                        <div>
                            <span class="text-lg font-bold text-white mr-1.5">Quizzes</span>
                            <span>Tự chấm điểm</span>
                        </div>
                        <div class="w-1 h-1 rounded-full bg-neutral-800 hidden sm:block"></div>
                        <div>
                            <span class="text-lg font-bold text-white mr-1.5">Tiến độ</span>
                            <span>Lưu tự động</span>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Showcase môn học tối giản (Minimal Gallery) -->
                <div class="lg:col-span-6 xl:col-span-5">
                    @php
                        $galleryCourses = $heroCourses ?? $courses;
                    @endphp
                    @if($galleryCourses->isNotEmpty())
                        <div class="relative w-full max-w-lg mx-auto lg:max-w-none"
                             x-data="courseHeroGallery({{ $galleryCourses->count() }})"
                             x-init="init()"
                             @mouseenter="isHovered = true"
                             @mouseleave="isHovered = false; if (!isDragging) startAutoPlay()"
                             role="region"
                             aria-label="Gallery các môn học nổi bật">

                            <!-- Khung Card tối giản, sắc sảo, không hào quang neon lòe loẹt -->
                            <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-4 sm:p-5 select-none">
                                
                                <!-- Thanh tiêu đề thẻ -->
                                <div class="flex items-center justify-between pb-3 mb-3 border-b border-neutral-800 text-xs">
                                    <span class="font-semibold text-neutral-300 uppercase tracking-wider text-[11px]">
                                        Khám Phá Môn Học
                                    </span>
                                    <div class="flex items-center text-neutral-400">
                                        <span class="font-mono text-[11px] bg-neutral-800 px-2 py-0.5 rounded text-neutral-300">
                                            <span x-text="current + 1">1</span>/<span>{{ $galleryCourses->count() }}</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Vùng vuốt/kéo slide -->
                                <div class="relative overflow-hidden cursor-grab active:cursor-grabbing touch-pan-y rounded-xl"
                                     @mousedown="startDrag($event)"
                                     @mousemove="onDrag($event)"
                                     @mouseup="endDrag($event)"
                                     @mouseleave="endDrag($event)"
                                     @touchstart.passive="startTouch($event)"
                                     @touchmove="onTouch($event)"
                                     @touchend="endTouch($event)">

                                    <div class="flex transition-transform duration-300 ease-out"
                                         :style="'transform: translateX(calc(-' + (current * 100) + '% + ' + dragOffset + 'px));' + (isDragging ? ' transition: none;' : '')">
                                        @foreach($galleryCourses as $idx => $course)
                                            <div class="w-full shrink-0">
                                                <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                                                   @click="handleClick($event)"
                                                   class="block group/slide text-left focus:outline-none">

                                                    <!-- Ảnh banner tinh gọn, không đè chữ rối mắt -->
                                                    <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-neutral-950 border border-neutral-800">
                                                        <img src="{{ $course->banner_url }}" 
                                                             alt="{{ $course->title }}"
                                                             draggable="false"
                                                             class="w-full h-full object-cover group-hover/slide:scale-102 transition duration-300 select-none pointer-events-none">

                                                        @if($course->is_enrolled)
                                                            <div class="absolute top-2.5 right-2.5">
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-600/90 text-white backdrop-blur-xs">
                                                                    {{ ($course->purchased_price ?? 0) > 0 ? 'Đã mua (' . number_format($course->purchased_price, 0, ',', '.') . ' đ)' : 'Đang học' }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Thông tin khóa học sạch sẽ, thoáng đãng -->
                                                    <div class="mt-3.5 space-y-2 px-0.5 pb-1">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <h3 class="font-bold text-base sm:text-lg text-white group-hover/slide:text-pink-400 transition-colors truncate min-w-0 leading-normal" title="{{ $course->title }}">
                                                                {{ $course->title }}
                                                            </h3>
                                                            <span class="text-xs text-neutral-400 shrink-0 font-medium">
                                                                {{ $course->lessons_count ?? $course->lessons->count() }} bài học
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center justify-between gap-2 text-xs text-neutral-400">
                                                            <div class="flex items-center gap-2 min-w-0">
                                                                @if($course->teacher && $course->teacher->avatar_url)
                                                                    <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-4 h-4 rounded-full object-cover shrink-0">
                                                                @else
                                                                    <div class="w-4 h-4 rounded-full bg-neutral-700 text-neutral-300 flex items-center justify-center text-[9px] font-bold shrink-0">
                                                                        {{ strtoupper(substr($course->teacher->name ?? 'B', 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <span class="truncate">GV: {{ $course->teacher->name ?? 'Ban Đào Tạo' }}</span>
                                                            </div>
                                                            @php
                                                                $heroRevCount = (int) ($course->reviews_count ?? $course->reviews()->count());
                                                                $heroAvgRating = $course->average_rating;
                                                            @endphp
                                                            @if($heroRevCount > 0)
                                                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-400 font-bold shrink-0">
                                                                    <svg class="w-3 h-3 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                                    <span>{{ number_format($heroAvgRating, 1) }}</span>
                                                                    <span class="text-neutral-500 font-normal">({{ $heroRevCount }})</span>
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="pt-1.5 pb-0.5 flex items-center justify-between gap-3 text-xs leading-normal">
                                                            <p class="text-neutral-400 truncate flex-1 min-w-0 leading-normal pb-0.5" title="{{ $course->description }}">
                                                                {{ $course->description ?: 'Bấm để xem chi tiết giáo trình bài giảng.' }}
                                                            </p>
                                                            <span class="text-pink-400 group-hover/slide:text-pink-300 font-semibold inline-flex items-center gap-1 shrink-0 whitespace-nowrap pb-0.5">
                                                                <span>Vào học ngay</span>
                                                                <span>&rarr;</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Bộ điều khiển tối giản ở chân thẻ -->
                                <div class="flex items-center justify-between pt-3 mt-3 border-t border-neutral-800">
                                    <!-- Chấm Dots -->
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="i in total" :key="i">
                                            <button type="button"
                                                    @click.stop="goTo(i - 1)"
                                                    :class="current === (i - 1) ? 'w-5 bg-pink-500' : 'w-1.5 bg-neutral-700 hover:bg-neutral-600'"
                                                    class="h-1.5 rounded-full transition-all duration-300"
                                                    :aria-label="'Chuyển đến môn số ' + i"></button>
                                        </template>
                                    </div>

                                    <!-- Nút Mũi tên prev/next nhỏ gọn -->
                                    <div class="flex items-center gap-1">
                                        <button type="button"
                                                @click.stop="prev()"
                                                class="w-7 h-7 rounded-lg border border-neutral-800 hover:border-neutral-700 bg-neutral-900 text-neutral-400 hover:text-white flex items-center justify-center transition"
                                                aria-label="Môn trước">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                        </button>
                                        <button type="button"
                                                @click.stop="next()"
                                                class="w-7 h-7 rounded-lg border border-neutral-800 hover:border-neutral-700 bg-neutral-900 text-neutral-400 hover:text-white flex items-center justify-center transition"
                                                aria-label="Môn kế tiếp">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @else
                        <!-- Trạng thái trống nếu chưa có khóa học -->
                        <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-8 text-center">
                            <h3 class="font-bold text-white text-sm mb-1">Khám phá kho khóa học</h3>
                            <p class="text-xs text-neutral-400">Các bài giảng chất lượng cao đang được chuẩn bị để ra mắt.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- ================= KHÓA HỌC ================= -->
    <section id="featured-courses" class="py-16 lg:py-20" x-data="homeCoursesHandler()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
                <div>
                    <span class="text-xs font-extrabold text-pink-600 uppercase tracking-[0.25em] block mb-2">
                        {{ (!empty($search) || ($price ?? 'all') !== 'all' || ($sort ?? 'popular') !== 'popular') ? 'Tìm kiếm khóa học' : 'Được đánh giá nhiều nhất' }}
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-neutral-900 tracking-tight">
                        {{ (!empty($search) || ($price ?? 'all') !== 'all' || ($sort ?? 'popular') !== 'popular') ? 'Kết quả tìm kiếm' : 'Khóa học nổi bật' }}
                    </h2>
                </div>
                <a href="{{ route('courses.index') }}"
                   class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-neutral-900 hover:text-pink-600 border-b-2 border-pink-600 pb-1 self-start sm:self-auto transition-colors">
                    Xem tất cả khóa học &rarr;
                </a>
            </div>

            <!-- Thanh tìm kiếm & lọc khóa học (giống trang bài kiểm tra) -->
            <div class="mb-8 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
                <form method="GET" action="{{ route('home') }}#featured-courses" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
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
                        <select name="price" x-model="price"
                                class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white cursor-pointer">
                            <option value="all">Tất cả mức giá</option>
                            <option value="free">Miễn phí</option>
                            <option value="paid">Có phí</option>
                            <option value="under_200k">Dưới 200.000 đ</option>
                            <option value="200k_500k">200.000 - 500.000 đ</option>
                            <option value="over_500k">Trên 500.000 đ</option>
                        </select>

                        <!-- Dropdown Sắp xếp -->
                        <select name="sort" x-model="sort"
                                class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white cursor-pointer">
                            <option value="popular">Nổi bật nhất</option>
                            <option value="latest">Mới nhất</option>
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
                </form>

                <!-- Kết quả hiển thị / đếm số lượng -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Đang hiển thị <strong class="text-pink-600 font-extrabold" x-text="countVisible()">{{ $courses->count() }}</strong> khóa học</span>
                    <span x-show="search && search.trim() !== ''" style="display: none;" class="text-[11px] text-slate-400">
                        Lọc theo từ khóa: "<span class="text-slate-700 font-semibold" x-text="search"></span>"
                    </span>
                </div>
            </div>

            <!-- Empty state khi không khớp kết quả tìm kiếm -->
            <div x-show="countVisible() === 0" style="display: none;"
                 class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center max-w-xl mx-auto shadow-xs my-6">
                <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 mx-auto flex items-center justify-center mb-3 text-lg font-bold">
                    🔍
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 mb-1">Không tìm thấy khóa học nào</h3>
                <p class="text-slate-500 text-xs mb-4">
                    Không có khóa học nào khớp với từ khóa tìm kiếm hoặc bộ lọc giá đã chọn.
                </p>
                <button type="button" @click="resetFilters()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-xs cursor-pointer">
                    Xóa bộ lọc
                </button>
            </div>

            @if($courses->isEmpty())
                <div class="bg-white border border-pink-100 p-14 text-center rounded-2xl">
                    <h3 class="font-black text-xl text-neutral-900 mb-2">Hiện chưa có khóa học nào được duyệt</h3>
                    <p class="text-sm text-neutral-500">Các bài giảng và khóa học mới sẽ sớm được giảng viên tải lên và kiểm duyệt.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <div x-show="matches({{ $course->id }})" class="transition-all duration-200">
                            <x-course-card :course="$course" :index="$loop->iteration" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
    <script>
        function courseHeroGallery(totalCount) {
            return {
                current: 0,
                total: totalCount || 1,
                autoPlayTimer: null,
                duration: 7000, // 7 giây tự động chuyển môn học
                isDragging: false,
                wasDragged: false,
                startX: 0,
                currentX: 0,
                dragOffset: 0,
                isHovered: false,

                init() {
                    if (this.total > 1) {
                        this.startAutoPlay();
                    }
                },

                startAutoPlay() {
                    this.stopAutoPlay();
                    this.autoPlayTimer = setInterval(() => {
                        if (this.isHovered || this.isDragging) {
                            return;
                        }
                        this.next();
                    }, this.duration);
                },

                stopAutoPlay() {
                    if (this.autoPlayTimer) {
                        clearInterval(this.autoPlayTimer);
                        this.autoPlayTimer = null;
                    }
                },

                resetTimer() {
                    if (this.total > 1) {
                        this.startAutoPlay();
                    }
                },

                next() {
                    if (this.total <= 1) return;
                    this.current = (this.current + 1) % this.total;
                    this.resetTimer();
                },

                prev() {
                    if (this.total <= 1) return;
                    this.current = (this.current - 1 + this.total) % this.total;
                    this.resetTimer();
                },

                goTo(index) {
                    if (index >= 0 && index < this.total) {
                        this.current = index;
                        this.resetTimer();
                    }
                },

                // Thao tác kéo chuột (Mouse Drag)
                startDrag(e) {
                    if (this.total <= 1) return;
                    this.isDragging = true;
                    this.wasDragged = false;
                    this.startX = e.clientX;
                    this.currentX = e.clientX;
                    this.dragOffset = 0;
                },

                onDrag(e) {
                    if (!this.isDragging) return;
                    this.currentX = e.clientX;
                    this.dragOffset = this.currentX - this.startX;
                    if (Math.abs(this.dragOffset) > 8) {
                        this.wasDragged = true;
                    }
                },

                endDrag() {
                    if (!this.isDragging) return;
                    this.isDragging = false;
                    const threshold = 40; // Ngưỡng dịch chuyển 40px để nhảy slide
                    if (this.dragOffset < -threshold) {
                        this.next();
                    } else if (this.dragOffset > threshold) {
                        this.prev();
                    }
                    this.dragOffset = 0;
                },

                // Thao tác vuốt ngón tay (Touch Swipe)
                startTouch(e) {
                    if (this.total <= 1 || !e.touches || !e.touches.length) return;
                    this.isDragging = true;
                    this.wasDragged = false;
                    this.startX = e.touches[0].clientX;
                    this.currentX = this.startX;
                    this.dragOffset = 0;
                },

                onTouch(e) {
                    if (!this.isDragging || !e.touches || !e.touches.length) return;
                    this.currentX = e.touches[0].clientX;
                    this.dragOffset = this.currentX - this.startX;
                    if (Math.abs(this.dragOffset) > 8) {
                        this.wasDragged = true;
                    }
                },

                endTouch() {
                    this.endDrag();
                },

                handleClick(e) {
                    // Nếu đang trong thao tác kéo chuột hoặc vuốt tay, ngăn chặn mở link nhầm
                    if (this.wasDragged) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.wasDragged = false;
                    }
                }
            };
        }

        function homeCoursesHandler() {
            return {
                search: @json($search ?? ''),
                price: @json($price ?? 'all'),
                sort: @json($sort ?? 'popular'),
                enrollment: @json($enrollment ?? 'all'),
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
                        rating: {{ (float) ($c->average_rating ?? 0) }},
                        reviewsCount: {{ (int) ($c->reviews_count ?? 0) }}
                    },
                    @endforeach
                ],
                matches(id) {
                    const item = this.courses.find(x => x.id === id);
                    if (!item) return true;

                    // Lọc theo giá
                    if (this.price === 'free' && item.price > 0) return false;
                    if (this.price === 'paid' && item.price <= 0) return false;
                    if (this.price === 'under_200k' && (item.price <= 0 || item.price >= 200000)) return false;
                    if (this.price === '200k_500k' && (item.price < 200000 || item.price > 500000)) return false;
                    if (this.price === 'over_500k' && item.price <= 500000) return false;

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
                        if (this.enrollment === 'enrolled' && !item.isEnrolled) return false;
                        if (this.enrollment === 'not_enrolled' && item.isEnrolled) return false;
                        if (!this.search || !this.search.trim()) return true;
                        const q = this.search.toLowerCase().trim();
                        return item.title.includes(q) || item.desc.includes(q) || item.teacher.includes(q);
                    }).length;
                },
                hasActiveFilters() {
                    return (this.search && this.search.trim() !== '') || this.price !== 'all' || this.sort !== 'popular' || this.enrollment !== 'all';
                },
                resetFilters() {
                    this.search = '';
                    this.price = 'all';
                    this.sort = 'popular';
                    this.enrollment = 'all';
                    if (window.location.search) {
                        window.location.href = @json(route('home')) + '#featured-courses';
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>