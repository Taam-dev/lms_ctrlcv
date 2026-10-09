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
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl {{ $isCompleted ? 'bg-emerald-600 text-white' : 'bg-pink-600 text-white' }} flex items-center justify-center font-bold text-sm shrink-0 transition">
                        @if($isCompleted)
                            &check;
                        @else
                            {{ $lesson->order_number }}
                        @endif
                    </span>
                    {{ $lesson->title }}
                </h2>
            </div>
            
            <div class="flex items-center gap-3">
                @php
                    $progress = max(0, min(100, (int) ($progressPercent ?? 0)));
                    $circumference = 2 * M_PI * 16;
                    $dashoffset = $circumference - ($progress / 100) * $circumference;
                @endphp
                <div class="flex items-center gap-3 bg-white border border-pink-100 px-3 py-1.5 rounded-2xl shadow-xs">
                    <div class="relative w-9 h-9 flex items-center justify-center shrink-0" title="Tiến độ: {{ $progress }}%">
                        <svg class="w-9 h-9 -rotate-90 transform" viewBox="0 0 38 38">
                            <circle cx="19" cy="19" r="16" stroke="currentColor" stroke-width="3.5" fill="transparent" class="text-pink-100" />
                            <circle cx="19" cy="19" r="16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" fill="transparent"
                                    class="{{ $progress >= 100 ? 'text-emerald-500' : 'text-pink-600' }} transition-all duration-500"
                                    style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashoffset }};" />
                        </svg>
                        <span class="absolute font-black text-[10px] {{ $progress >= 100 ? 'text-emerald-700' : 'text-slate-900' }}">
                            {{ $progress }}%
                        </span>
                    </div>
                    <div class="text-left text-xs leading-tight pr-1 hidden sm:block">
                        <span class="block text-[9px] uppercase font-black text-pink-600 tracking-wider">Tiến độ</span>
                        <span class="font-bold text-slate-800">{{ $completedCount }}/{{ $totalLessons }} bài</span>
                    </div>
                </div>

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
                            // Chuyển đổi link youtube thông thường sang link embed nếu có
                            $embedUrl = null;
                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $lesson->content, $matches)) {
                                $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                            }
                        @endphp

                        <div class="space-y-4">
                            @if($embedUrl)
                                <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-slate-900 shadow-md">
                                    <iframe class="absolute inset-0 w-full h-full" src="{{ $embedUrl }}" title="{{ $lesson->title }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            @endif

                            <div class="p-4 rounded-2xl bg-pink-50/80 border border-pink-200/80">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-xs text-slate-600">
                                        <span class="font-bold text-slate-800 block">Liên kết video bài giảng:</span>
                                        <a href="{{ $lesson->content }}" target="_blank" class="text-pink-600 hover:underline break-all font-medium mt-0.5 block">
                                            {{ $lesson->content }}
                                        </a>
                                    </div>
                                    <a href="{{ $lesson->content }}" target="_blank" class="shrink-0 px-3 py-1.5 rounded-xl bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 transition">
                                        Mở video &rarr;
                                    </a>
                                </div>
                            </div>
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
                            <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center font-black text-lg shrink-0">
                                &check;
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
                    @else
                        <a href="{{ route('student.courses.show', $lesson->course_id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            &check; Đã xem hết bài giảng
                        </a>
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
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold {{ $isActive ? 'bg-white/20 text-white' : ($isItemDone ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">
                                            @if($isItemDone)
                                                &check;
                                            @else
                                                {{ $item->order_number }}
                                            @endif
                                        </span>
                                        <span class="text-sm truncate flex-grow">
                                            {{ $item->title }}
                                        </span>
                                        @if($isItemDone && !$isActive)
                                            <span class="text-[10px] font-black uppercase text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded whitespace-nowrap">
                                                Xong
                                            </span>
                                        @else
                                            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} whitespace-nowrap">
                                                {{ $item->content_type }}
                                            </span>
                                        @endif
                                    </a>
                                @else
                                    <div class="flex items-center gap-3 p-3 rounded-2xl opacity-60 bg-slate-50 text-slate-400 cursor-not-allowed select-none" title="Cần hoàn thành bài học trước đó theo thứ tự từ dưới lên">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg text-xs font-bold bg-slate-200 text-slate-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </span>
                                        <span class="text-sm truncate flex-grow font-medium">
                                            {{ $item->title }}
                                        </span>
                                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-400 whitespace-nowrap">
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