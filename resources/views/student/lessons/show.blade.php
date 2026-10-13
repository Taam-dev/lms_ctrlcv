<x-app-layout
    :title="$lesson->title . ' - ' . $course->title . ' | Ctrl C+V'"
    :meta-description="\Illuminate\Support\Str::limit(strip_tags($lesson->content ?? 'Bài giảng ' . $lesson->title . ' thuộc khóa học ' . $course->title), 160)"
    :meta-keywords="$lesson->title . ', ' . $course->title . ', bài giảng trực tuyến, ctrl c+v'"
    :canonical="route('student.lessons.show', [$course->slug ?: $course->id, $lesson->slug ?: $lesson->id])"
    :og-image="$course->banner_url"
    og-type="article">

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
                'name' => 'Khóa học',
                'item' => route('courses.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $course->title,
                'item' => route('courses.show', $course->slug ?: $course->id),
            ],
            [
                '@type' => 'ListItem',
                'position' => 4,
                'name' => $lesson->title,
                'item' => route('student.lessons.show', [$course->slug ?: $course->id, $lesson->slug ?: $lesson->id]),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <a href="{{ route('courses.show', $course->slug ?: $course->id) }}" class="text-xs font-semibold text-pink-600 hover:underline inline-flex items-center gap-1 mb-1">
                    &larr; Về trang thông tin khóa học
                </a>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-start gap-3">
                    <span class="w-8 h-8 min-w-8 min-h-8 rounded-xl {{ $isCompleted ? 'bg-emerald-600 text-white' : 'bg-pink-600 text-white' }} flex items-center justify-center font-bold text-sm shrink-0 aspect-square transition mt-0.5">
                        @if($isCompleted)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        @else
                            {{ $lesson->order_number }}
                        @endif
                    </span>
                    <span class="min-w-0 break-words">{{ $lesson->title }}</span>
                </h2>

                <!-- Thông tin giảng viên & khóa học dưới tên bài học -->
                <div class="flex flex-wrap items-center gap-2.5 sm:gap-3.5 mt-2.5 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        @if($course->teacher_id)
                            <a href="{{ route('users.profile', $course->teacher_id) }}" class="flex items-center gap-2 hover:opacity-90 transition" title="Xem hồ sơ giảng viên {{ $course->teacher->name ?? '' }}">
                        @endif
                        @if($course->teacher && $course->teacher->avatar_url)
                            <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-6 h-6 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                        @else
                            <div class="w-6 h-6 rounded-full bg-neutral-900 text-white flex items-center justify-center font-bold text-[10px] shrink-0">
                                {{ strtoupper(substr($course->teacher->name ?? 'G', 0, 1)) }}
                            </div>
                        @endif
                        <span>Giảng viên: <strong class="text-slate-800 font-bold hover:text-pink-600 transition-colors">{{ $course->teacher->name ?? 'Ban Đào Tạo' }}</strong></span>
                        @if($course->teacher_id)
                            </a>
                        @endif
                    </div>

                    <span>&bull;</span>
                    <span class="text-slate-600">Khóa học: <strong class="text-slate-800 font-bold">{{ $course->title }}</strong></span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold transition">
                    Làm bài Quizzes
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Cột chính (lg:col-span-8): Nội dung bài giảng -->
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white shadow-xs rounded-3xl p-6 sm:p-8 border border-pink-100/90">
                    
                    @if($lesson->content_type === 'video')
                        @php
                            $embedInfo = $lesson->video_embed_info;
                        @endphp

                        <div class="space-y-4">
                            @if($embedInfo && in_array($embedInfo['type'], ['youtube', 'facebook', 'drive', 'vimeo', 'loom', 'direct', 'iframe', 'tiktok']))
                                @if($embedInfo['type'] === 'direct')
                                    <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black shadow-md border border-slate-800">
                                        <video controls class="w-full h-full object-contain" preload="metadata">
                                            <source src="{{ $embedInfo['url'] }}">
                                            Trình duyệt của bạn không hỗ trợ phát thẻ video trực tiếp.
                                        </video>
                                    </div>
                                @elseif($embedInfo['type'] === 'tiktok')
                                    <div class="w-full flex justify-center bg-slate-950/90 rounded-2xl p-4 sm:p-6 border border-slate-800 shadow-md">
                                        <div class="w-full max-w-[360px] sm:max-w-[400px] aspect-[9/16] min-h-[560px] max-h-[720px] relative rounded-2xl overflow-hidden bg-black shadow-2xl ring-1 ring-slate-800">
                                            <iframe class="absolute inset-0 w-full h-full"
                                                    src="{{ $embedInfo['url'] }}"
                                                    title="{{ $lesson->title }}"
                                                    frameborder="0"
                                                    style="border:none;overflow:hidden"
                                                    scrolling="no"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                                                    allowfullscreen>
                                            </iframe>
                                        </div>
                                    </div>
                                @else
                                    <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-slate-950 shadow-md border border-slate-800">
                                        <iframe class="absolute inset-0 w-full h-full"
                                                src="{{ $embedInfo['url'] }}"
                                                title="{{ $lesson->title }}"
                                                frameborder="0"
                                                style="border:none;overflow:hidden"
                                                scrolling="no"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                                                allowfullscreen>
                                        </iframe>
                                    </div>
                                @endif

                                <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-neutral-900 text-white shrink-0 shadow-2xs">
                                            @if($embedInfo['type'] === 'tiktok')
                                                <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                            @else
                                                <svg class="w-3 h-3 fill-white ml-0.5" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            @endif
                                        </span>
                                        <span class="truncate font-medium text-slate-700">
                                            {{ $embedInfo['type'] === 'tiktok' ? 'Video TikTok:' : 'Nguồn video:' }} <a href="{{ $embedInfo['original_url'] }}" target="_blank" rel="noopener noreferrer" class="text-slate-900 hover:text-pink-600 hover:underline font-semibold">{{ $embedInfo['original_url'] }}</a>
                                        </span>
                                    </div>
                                    <a href="{{ $embedInfo['original_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-neutral-900 hover:text-white font-bold transition shrink-0 shadow-2xs">
                                        <span>{{ $embedInfo['type'] === 'tiktok' ? 'Mở trên TikTok' : 'Mở video gốc' }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                            @else
                                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-neutral-900 text-white flex items-center justify-center shrink-0 shadow-xs">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm">Liên kết video bài giảng</h4>
                                        <p class="text-xs text-slate-500 mt-0.5 break-all">{{ $lesson->content }}</p>
                                    </div>
                                    <a href="{{ $lesson->content }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 shadow-md shadow-pink-600/25 transition">
                                        <span>Mở video bài giảng</span>
                                        &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="prose max-w-none text-slate-800 text-base leading-relaxed space-y-4">
                            {!! nl2br(e($lesson->content)) !!}
                        </div>
                    @endif

                </div>

                <!-- Khối hành động: Hoàn thành bài học -->
                @php
                    $currentIndex = $allLessons->search(fn($item) => $item->id === $lesson->id);
                    $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
                    $nextLesson = $currentIndex < ($allLessons->count() - 1) ? $allLessons[$currentIndex + 1] : null;
                @endphp

                <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs">
                    @if(! $isCompleted)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h4 class="font-black text-slate-900 text-base">Học xong bài giảng này?</h4>
                                <p class="text-xs text-slate-500 mt-0.5">Nhấn nút bên dưới để xác nhận hoàn thành và tính vào tiến độ khóa học.</p>
                            </div>
                            <form action="{{ route('student.lessons.complete', [$lesson->course_id, $lesson->id]) }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm shadow-md shadow-emerald-600/25 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    Hoàn thành bài học
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 min-w-10 min-h-10 rounded-2xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center font-black text-lg shrink-0 aspect-square">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-emerald-900 text-base">Bạn đã hoàn thành bài học này!</h4>
                                <p class="text-xs text-emerald-700 mt-0.5">Tiến độ khóa học của bạn đã được ghi nhận thành công.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Thanh chuyển bài Trước / Sau -->
                <div class="flex items-center justify-between gap-4 pt-2">
                    @if($prevLesson)
                        <a href="{{ route('student.lessons.show', [$lesson->course_id, $prevLesson->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-pink-50 hover:text-pink-700 font-bold text-xs shadow-xs transition">
                            &larr; Bài trước: {{ $prevLesson->title }}
                        </a>
                    @else
                        <div></div>
                    @endif

                    @if($nextLesson)
                        @if($isCompleted)
                            <a href="{{ route('student.lessons.show', [$lesson->course_id, $nextLesson->id]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-pink-500/25 transition">
                                <span>Bài tiếp theo: {{ $nextLesson->title }}</span>
                                &rarr;
                            </a>
                        @else
                            <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed" title="Hoàn thành bài này trước để mở bài tiếp theo">
                                <span>Bài tiếp theo: {{ $nextLesson->title }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                        @endif
                    @endif
                </div>

            </div>

            <!-- Cột sidebar (lg:col-span-4): Danh sách bài học của khóa -->
            <div class="lg:col-span-4">
                <div class="bg-white shadow-xs rounded-3xl p-6 border border-pink-100/90 sticky top-24">
                    <h3 class="font-bold text-slate-900 text-base mb-4 pb-3 border-b border-slate-100 flex items-center justify-between">
                        <span>Giáo trình bài học</span>
                        <span class="text-xs text-pink-600 font-semibold bg-pink-50 px-2 py-1 rounded-md">
                            {{ $allLessons->count() }} bài
                        </span>
                    </h3>

                    @php
                        $progress = max(0, min(100, (int) ($progressPercent ?? 0)));
                        $circumference = 2 * M_PI * 16;
                        $dashoffset = $circumference - ($progress / 100) * $circumference;
                    @endphp

                    <!-- Hình tròn tiến độ bên sidebar dạng hàng ngang -->
                    <div class="mb-5 p-3.5 rounded-2xl bg-pink-50/80 border border-pink-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative shrink-0" style="width: 42px; height: 42px; min-width: 42px;">
                                <svg class="-rotate-90 transform" style="width: 42px; height: 42px;" viewBox="0 0 38 38">
                                    <circle cx="19" cy="19" r="16" stroke="#fce7f3" stroke-width="3.5" fill="transparent" />
                                    <circle cx="19" cy="19" r="16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" fill="transparent"
                                            class="{{ $progress >= 100 ? 'text-emerald-500' : 'text-pink-600' }} transition-all duration-500"
                                            style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashoffset }};" />
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center font-black text-[11px] {{ $progress >= 100 ? 'text-emerald-700' : 'text-slate-900' }}">
                                    {{ $progress }}%
                                </span>
                            </div>
                            <div class="leading-tight">
                                <span class="block text-[10px] uppercase font-black text-pink-600 tracking-wider whitespace-nowrap">Tiến độ khóa học</span>
                                <span class="block text-xs font-bold text-slate-800 whitespace-nowrap">{{ $completedCount }}/{{ $totalLessons }} bài hoàn thành</span>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $progress >= 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-pink-100 text-pink-700' }} whitespace-nowrap">
                                {{ $progress }}%
                            </span>
                        </div>
                    </div>

                    <ul class="space-y-2">
                        @foreach($allLessons as $item)
                            @php
                                $isActive = ($item->id === $lesson->id);
                                $isItemDone = in_array($item->id, $completedLessonIds ?? []);
                                $isItemUnlocked = in_array($item->id, $unlockedLessonIds ?? []);
                            @endphp
                            <li>
                                @if($isItemUnlocked)
                                    <a href="{{ route('student.lessons.show', [$lesson->course_id, $item->id]) }}" 
                                       class="flex items-center gap-3 p-3 rounded-2xl transition duration-150 {{ $isActive ? 'bg-pink-600 text-white font-bold shadow-md shadow-pink-500/25' : 'text-slate-700 hover:bg-pink-50/70 hover:text-pink-700' }}">
                                        <span class="w-6 h-6 min-w-6 min-h-6 shrink-0 aspect-square flex items-center justify-center rounded-lg text-xs font-bold {{ $isActive ? 'bg-white/20 text-white' : ($isItemDone ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">
                                            @if($isItemDone)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            @else
                                                {{ $item->order_number }}
                                            @endif
                                        </span>
                                        <span class="text-sm truncate min-w-0 flex-1" title="{{ $item->title }}">
                                            {{ $item->title }}
                                        </span>
                                        @if($isItemDone && !$isActive)
                                            <span class="shrink-0 text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded whitespace-nowrap">
                                                Xong
                                            </span>
                                        @else
                                            <span class="shrink-0 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} whitespace-nowrap">
                                                {{ $item->content_type }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <div class="flex items-center gap-3 p-3 rounded-2xl opacity-60 bg-slate-50 text-slate-400 cursor-not-allowed select-none" title="Cần hoàn thành bài học trước đó theo thứ tự từ dưới lên">
                                        <span class="w-6 h-6 min-w-6 min-h-6 shrink-0 aspect-square flex items-center justify-center rounded-lg text-xs font-bold bg-slate-200 text-slate-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </span>
                                        <span class="text-sm truncate min-w-0 flex-1 font-medium" title="{{ $item->title }}">
                                            {{ $item->title }}
                                        </span>
                                        <span class="shrink-0 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400 whitespace-nowrap">
                                            Khóa
                                        </span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>