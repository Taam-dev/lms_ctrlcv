<x-app-layout 
    :title="$course->title . ' | Khóa học trực tuyến Ctrl C+V'"
    :meta-description="\Illuminate\Support\Str::limit(strip_tags($course->description ?? 'Khóa học ' . $course->title . ' tại nền tảng Ctrl C+V. Tham gia học qua bài giảng và luyện tập quizzes ngay.'), 160)"
    :meta-keywords="$course->title . ', học trực tuyến, ctrl c+v, bài giảng, quizzes, khóa học online'"
    :canonical="route('courses.show', $course->slug ?: $course->id)"
    :og-image="$course->banner_url"
    og-type="article">

    @push('schema')
    <!-- Schema.org Course JSON-LD -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->title,
        'description' => \Illuminate\Support\Str::limit(strip_tags($course->description ?? $course->title), 250),
        'image' => $course->banner_url,
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Ctrl C+V',
            'sameAs' => 'https://ctrlcv.io.vn',
        ],
        'instructor' => [
            '@type' => 'Person',
            'name' => $course->teacher->name ?? 'Ban Đào Tạo Ctrl C+V',
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'VND',
            'category' => 'Free',
        ],
        'hasCourseInstance' => [
            '@type' => 'CourseInstance',
            'courseMode' => 'Online',
            'inLanguage' => 'vi',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

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
                'name' => 'Khóa học',
                'item' => route('courses.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $course->title,
                'item' => route('courses.show', $course->slug ?: $course->id),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('courses.index') }}" class="text-xs font-semibold text-pink-600 hover:underline inline-flex items-center gap-1 mb-1">
                    &larr; Quay lại danh sách khóa học
                </a>
                <h2 class="font-black text-2xl md:text-3xl text-slate-900 tracking-tight">
                    {{ $course->title }}
                </h2>
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 mt-3 text-xs sm:text-sm text-slate-500">
                    <div class="flex items-center gap-2.5">
                        @if($course->teacher && $course->teacher->avatar_url)
                            <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-pink-200 shadow-xs shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-black text-sm border-2 border-pink-200 shadow-xs shrink-0">
                                {{ strtoupper(substr($course->teacher->name ?? 'G', 0, 1)) }}
                            </div>
                        @endif
                        <span>Giảng viên: <strong class="text-slate-800 font-bold">{{ $course->teacher->name ?? 'Ban Đào Tạo' }}</strong></span>
                    </div>
                    <span>&bull;</span>
                    <span>{{ $course->lessons->count() }} Bài giảng</span>
                    <span>&bull;</span>
                    <span>{{ $course->approvedQuizzes->count() }} Bài Quizzes</span>
                </div>
            </div>

            <div>
                @if(!$isEnrolled)
                    @auth
                        <form action="{{ route('student.courses.enroll', $course->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-2xl shadow-md shadow-pink-500/25 transition">
                                Đăng ký học ngay
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-2xl shadow-md shadow-pink-500/25 transition">
                            Đăng nhập để học ngay
                        </a>
                    @endauth
                @else
                    @php
                        $progress = max(0, min(100, (int) ($progressPercent ?? 0)));
                        $circumference = 2 * M_PI * 16;
                        $dashoffset = $circumference - ($progress / 100) * $circumference;
                        $completedLessonsCount = count($completedLessonIds ?? []);
                        $totalLessons = $course->lessons->count();
                    @endphp
                    <div class="flex items-center gap-3.5 bg-white border border-pink-100 p-2.5 px-4 rounded-2xl shadow-xs">
                        <!-- Hình tròn tiến độ với số % ở giữa -->
                        <div class="relative shrink-0" style="width: 44px; height: 44px; min-width: 44px;">
                            <svg class="-rotate-90 transform" style="width: 44px; height: 44px;" viewBox="0 0 38 38">
                                <circle cx="19" cy="19" r="16" stroke="#fce7f3" stroke-width="3.5" fill="transparent" />
                                <circle cx="19" cy="19" r="16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" fill="transparent"
                                        class="{{ $progress >= 100 ? 'text-emerald-500' : 'text-pink-600' }} transition-all duration-500"
                                        style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashoffset }};" />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center font-black text-xs {{ $progress >= 100 ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $progress }}%
                            </span>
                        </div>
                        <div class="text-left leading-tight whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 whitespace-nowrap shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <span>Đã đăng ký</span>
                            </span>
                            <span class="block text-xs font-bold text-slate-800 mt-1 whitespace-nowrap">
                                Hoàn thành {{ $completedLessonsCount }}/{{ $totalLessons }} bài
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Tổng quan khóa học & Banner chuẩn kích thước nguyên bản như bên ngoài -->
            <div class="bg-white border border-pink-100 p-6 md:p-8 shadow-sm">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6 lg:gap-8">
                    <!-- Khung ảnh banner: Giữ nguyên kích thước và thiết kế y chang như nhìn ở bên ngoài thẻ khóa học -->
                    <div class="w-full max-w-sm md:w-80 lg:w-96 shrink-0 mx-auto md:mx-0">
                        <div class="relative h-48 w-full overflow-hidden bg-neutral-950 border border-pink-100 shadow-md group">
                            <img src="{{ $course->banner_url }}" 
                                 alt="{{ $course->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950/80 via-transparent to-black/20"></div>

                            <!-- Badges trên banner y chang bên ngoài -->
                            <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none">
                                @if($isEnrolled)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-md shadow-emerald-950/40 whitespace-nowrap shrink-0">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Đã đăng ký</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider bg-neutral-950/90 text-pink-400 border border-pink-500/30 backdrop-blur-xs whitespace-nowrap shrink-0">
                                        Khóa học
                                    </span>
                                @endif
                                <span class="text-xs font-black px-2 py-0.5 bg-pink-600 text-white shrink-0">
                                    {{ $course->approvedQuizzes->count() }} Quizzes
                                </span>
                            </div>

                            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs font-bold pointer-events-none">
                                <span class="flex items-center gap-1.5 text-neutral-200">
                                    @if($isEnrolled)
                                        <span class="text-pink-300 font-black">{{ count($completedLessonIds ?? []) }}/{{ $course->lessons->count() }} bài đã học</span>
                                    @else
                                        <span>{{ $course->lessons->count() }} bài giảng</span>
                                    @endif
                                </span>
                                <span class="text-neutral-300 text-[11px]">
                                    {{ $course->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chi tiết khóa học & Mô tả -->
                    <div class="flex-1 min-w-0 space-y-4">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-500">
                                <span class="px-2.5 py-1 bg-pink-50 text-pink-700 border border-pink-200/80 font-bold uppercase text-[10px] tracking-wider">
                                    Khóa học trực tuyến
                                </span>
                                <span>&bull;</span>
                                <span>Giảng viên: <strong class="text-slate-800">{{ $course->teacher->name ?? 'Ban Đào Tạo' }}</strong></span>
                                <span>&bull;</span>
                                <span>Ngày tạo: {{ $course->created_at->format('d/m/Y') }}</span>
                            </div>

                            <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                                {{ $course->title }}
                            </h2>
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-neutral-900 mb-2 flex items-center gap-2">
                                <span class="w-1.5 h-4 bg-pink-600"></span>
                                Mô tả
                            </h3>
                            <p class="text-neutral-700 text-sm md:text-base leading-relaxed">
                                {{ $course->description ?? 'Khóa học này hiện chưa có mô tả chi tiết.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Cột trái (lg:col-span-7): Danh sách bài giảng -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            Danh sách bài giảng ({{ $course->lessons->count() }} bài)
                        </h3>
                    </div>

                    @if($course->lessons->isEmpty())
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                            Khóa học này chưa có bài giảng nào được đăng tải.
                        </div>
                    @else
                        <div class="bg-white rounded-3xl border border-pink-100 shadow-xs divide-y divide-slate-100 overflow-hidden">
                            @foreach($course->lessons as $lesson)
                                @php
                                    $isLessonDone = in_array($lesson->id, $completedLessonIds ?? []);
                                    $isLessonUnlocked = in_array($lesson->id, $unlockedLessonIds ?? []);
                                @endphp
                                <div class="p-5 flex items-center justify-between gap-4 hover:bg-pink-50/30 transition">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <span class="w-8 h-8 rounded-xl {{ $isLessonDone ? 'bg-emerald-600 text-white shadow-xs' : ($isLessonUnlocked ? 'bg-pink-50 text-pink-700 border border-pink-100' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center font-bold text-xs shrink-0 transition">
                                            @if($isLessonDone)
                                                &check;
                                            @elseif(! $isLessonUnlocked && $isEnrolled)
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            @else
                                                {{ $lesson->order_number }}
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 text-sm md:text-base {{ ! $isLessonUnlocked && $isEnrolled ? 'text-slate-500' : '' }}">
                                                {{ $lesson->title }}
                                            </h4>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 uppercase mt-0.5">
                                                @if($lesson->content_type === 'video')
                                                    Video bài giảng
                                                @else
                                                    Tài liệu bài đọc
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        @if($isEnrolled)
                                            @if($isLessonDone)
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] md:text-[11px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200/60 whitespace-nowrap">
                                                    Đã hoàn thành
                                                </span>
                                            @elseif(! $isLessonUnlocked)
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] md:text-[11px] font-extrabold uppercase bg-slate-100 text-slate-500 whitespace-nowrap">
                                                    Chưa mở khóa
                                                </span>
                                            @endif

                                            @if($isLessonUnlocked)
                                                <a href="{{ route('student.lessons.show', [$course->id, $lesson->id]) }}" 
                                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl {{ $isLessonDone ? 'bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200' : 'bg-pink-50 hover:bg-pink-600 text-pink-700 hover:text-white' }} font-bold text-xs transition duration-150 whitespace-nowrap">
                                                    <span>{{ $isLessonDone ? 'Xem lại bài' : 'Vào học' }}</span>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed whitespace-nowrap" title="Cần hoàn thành bài học trước đó theo thứ tự từ dưới lên">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                    Đang khóa
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 italic whitespace-nowrap">Đăng ký để học</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cột phải (lg:col-span-5): Danh sách Quizzes & Bài kiểm tra -->
                <div class="lg:col-span-5 space-y-4" x-data="{
                    confirmModalOpen: false,
                    pendingQuiz: { title: '', duration: 0, url: '' },
                    askStartQuiz(url, title, duration) {
                        this.pendingQuiz = { url, title, duration };
                        this.confirmModalOpen = true;
                    }
                }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            
                            Bài kiểm tra Quizzes ({{ $course->approvedQuizzes->count() }})
                        </h3>
                    </div>

                    @if($course->approvedQuizzes->isEmpty())
                        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500 text-sm">
                            Khóa học này chưa có bài kiểm tra trắc nghiệm nào được mở.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($course->approvedQuizzes as $quiz)
                                @php
                                    $attempts = $studentAttempts->get($quiz->id, collect());
                                    $lastAttempt = $attempts->first();
                                    $bestScore = $attempts->max('score');
                                    $hasPassed = $attempts->contains('is_passed', true);
                                @endphp
                                <div class="bg-white rounded-2xl border border-pink-100 shadow-xs p-5 hover:border-pink-300 transition">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <h4 class="font-bold text-slate-900 text-base">
                                            {{ $quiz->title }}
                                        </h4>
                                        @if($attempts->isNotEmpty())
                                            @if($hasPassed)
                                                <span class="shrink-0 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Đạt ({{ $bestScore }}/10)
                                                </span>
                                            @else
                                                <span class="shrink-0 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Chưa đạt ({{ $bestScore }}/10)
                                                </span>
                                            @endif
                                        @endif
                                    </div>

                                    <p class="text-xs text-slate-500 mb-3 line-clamp-2">
                                        {{ $quiz->description ?? 'Làm bài kiểm tra để đánh giá kiến thức đã học.' }}
                                    </p>

                                    <div class="flex items-center gap-3 text-xs text-slate-500 pb-3 mb-3 border-b border-slate-100">
                                        <span>
                                            @if($quiz->question_limit && $quiz->question_limit < $quiz->questions->count())
                                                {{ $quiz->question_limit }}/{{ $quiz->questions->count() }} câu hỏi
                                            @else
                                                {{ $quiz->questions->count() }} câu hỏi
                                            @endif
                                        </span>
                                        <span>&bull;</span>
                                        <span>{{ $quiz->duration_minutes }} phút</span>
                                        <span>&bull;</span>
                                        <span>Điểm đạt &ge; {{ $quiz->passing_score }}/10</span>
                                    </div>

                                    @if($quiz->starts_at || $quiz->ends_at)
                                        <div class="text-[11px] text-slate-500 mb-3 flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="truncate">
                                                @if($quiz->starts_at && $quiz->ends_at)
                                                    Thời gian mở: {{ $quiz->starts_at->format('d/m/Y H:i') }} &rarr; {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                                @elseif($quiz->starts_at)
                                                    Bắt đầu từ: {{ $quiz->starts_at->format('d/m/Y H:i') }}
                                                @else
                                                    Hạn chót: {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        @if($lastAttempt)
                                            <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" class="text-xs font-semibold text-pink-600 hover:underline">
                                                Xem kết quả gần nhất &rarr;
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">Chưa tham gia làm bài</span>
                                        @endif

                                        @if($isEnrolled)
                                            @if($quiz->isExpired())
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
                                                    Đã hết hạn
                                                </span>
                                            @elseif($quiz->isNotStarted())
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 text-amber-600 font-bold text-xs border border-amber-200 cursor-not-allowed">
                                                    Chưa mở
                                                </span>
                                            @else
                                                <button type="button" 
                                                        @click="askStartQuiz({{ Js::from(route('student.quizzes.take', $quiz->id)) }}, {{ Js::from($quiz->title) }}, {{ (int)$quiz->duration_minutes }})"
                                                        class="inline-flex items-center gap-1 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                                    <span>{{ $attempts->isNotEmpty() ? 'Làm lại' : 'Làm bài thi' }}</span>
                                                </button>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 italic">Đăng ký khóa học để làm</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Modal Xác nhận Bắt đầu Làm bài Kiểm tra -->
                    <div x-show="confirmModalOpen" 
                         x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-950/80 backdrop-blur-sm"
                         style="display: none;">
                        <div @click.away="confirmModalOpen = false"
                             class="w-full max-w-lg bg-neutral-900 border-2 border-pink-600 text-white shadow-2xl p-6 sm:p-8 space-y-6">
                            
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-pink-600/20 border border-pink-500 text-pink-500 flex items-center justify-center shrink-0 text-2xl font-black">
                                    ⚠️
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[10px] uppercase font-black tracking-widest text-pink-400 block mb-1">Xác nhận làm bài kiểm tra</span>
                                    <h3 class="text-lg sm:text-xl font-black text-white leading-snug line-clamp-2" x-text="pendingQuiz.title">
                                        Tên bài kiểm tra
                                    </h3>
                                    <p class="text-xs text-neutral-400 mt-1">
                                        Thời gian: <strong class="text-pink-400" x-text="pendingQuiz.duration + ' phút'"></strong>
                                    </p>
                                </div>
                            </div>

                            <div class="bg-rose-950/50 border-l-4 border-rose-500 border-y border-r border-rose-900/60 p-4 space-y-2">
                                <div class="flex items-center gap-2 text-rose-300 font-black text-xs uppercase tracking-wider">
                                    <svg class="w-4 h-4 shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>Quy chế thi bắt buộc</span>
                                </div>
                                <p class="text-xs text-rose-100 font-semibold leading-relaxed">
                                    Một khi đã bắt đầu thì <strong class="text-white underline decoration-rose-400">bắt buộc phải nộp bài</strong>, <strong class="text-rose-300">không thể hủy giữa chừng</strong>!
                                </p>
                                <p class="text-[11px] text-neutral-300 leading-normal">
                                    Thời gian sẽ bắt đầu đếm ngược ngay lập tức. Trong khi làm bài, nếu chuyển tab hoặc chuyển trang khác, bạn sẽ bắt buộc phải nộp bài.
                                </p>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2 border-t border-white/10">
                                <button type="button" 
                                        @click="confirmModalOpen = false"
                                        class="w-full sm:w-auto px-6 py-2.5 border border-white/20 hover:border-white text-neutral-300 hover:text-white text-xs font-black uppercase tracking-wider transition cursor-pointer text-center">
                                    Không
                                </button>
                                <a :href="pendingQuiz.url"
                                   class="w-full sm:w-auto px-7 py-2.5 bg-pink-600 hover:bg-pink-500 text-white text-xs font-black uppercase tracking-wider transition shadow-lg shadow-pink-600/30 cursor-pointer text-center">
                                    Làm &rarr;
                                </a>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>