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
                    @if($courses->isNotEmpty())
                        <div class="relative w-full max-w-lg mx-auto lg:max-w-none"
                             x-data="courseHeroGallery({{ $courses->count() }})"
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
                                            <span x-text="current + 1">1</span>/<span>{{ $courses->count() }}</span>
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
                                        @foreach($courses as $idx => $course)
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
                                                                    Đang học
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Thông tin khóa học sạch sẽ, thoáng đãng -->
                                                    <div class="mt-3.5 space-y-1.5 px-0.5">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <h3 class="font-bold text-base sm:text-lg text-white group-hover/slide:text-pink-400 transition-colors line-clamp-1">
                                                                {{ $course->title }}
                                                            </h3>
                                                            <span class="text-xs text-neutral-400 shrink-0 font-medium">
                                                                {{ $course->lessons_count ?? $course->lessons->count() }} bài học
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center gap-2 text-xs text-neutral-400">
                                                            @if($course->teacher && $course->teacher->avatar_url)
                                                                <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-4 h-4 rounded-full object-cover">
                                                            @endif
                                                            <span class="truncate">GV: {{ $course->teacher->name ?? 'Ban Đào Tạo' }}</span>
                                                        </div>

                                                        <div class="pt-1 flex items-center justify-between gap-3 text-xs">
                                                            <p class="text-neutral-400 line-clamp-1 flex-1">
                                                                {{ $course->description ?: 'Bấm để xem chi tiết giáo trình bài giảng.' }}
                                                            </p>
                                                            <span class="text-pink-400 group-hover/slide:text-pink-300 font-semibold inline-flex items-center gap-1 shrink-0 whitespace-nowrap">
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

    <!-- ================= 3 KHÓA HỌC NỔI BẬT ================= -->
    <section id="featured-courses" class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                <div>
                    <span class="text-xs font-extrabold text-pink-600 uppercase tracking-[0.25em] block mb-2">Mới nhất</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-neutral-900 tracking-tight">Khóa học nổi bật</h2>
                </div>
                <a href="{{ route('courses.index') }}"
                   class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.16em] text-neutral-900 hover:text-pink-600 border-b-2 border-pink-600 pb-1 self-start sm:self-auto transition-colors">
                    Xem tất cả khóa học &rarr;
                </a>
            </div>

            @if($courses->isEmpty())
                <div class="bg-white border border-pink-100 p-14 text-center">
                    <h3 class="font-black text-xl text-neutral-900 mb-2">Hiện chưa có khóa học nào được duyệt</h3>
                    <p class="text-sm text-neutral-500">Các bài giảng và khóa học mới sẽ sớm được giảng viên tải lên và kiểm duyệt.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <x-course-card :course="$course" :index="$loop->iteration" />
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
    </script>
    @endpush
</x-app-layout>