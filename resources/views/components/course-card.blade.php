@props(['course', 'index' => null])

@php
    $lessonsCount = $course->lessons_count ?? $course->lessons()->count();
    $teacherName = $course->teacher->name ?? 'Ban Đào Tạo';
    $isEnrolled = (bool) ($course->is_enrolled ?? false);
    $progressPercent = max(0, min(100, (int) ($course->progress_percent ?? 0)));
    $completedCount = (int) ($course->completed_lessons_count ?? 0);
    $circumference = 2 * M_PI * 16; // ~100.53
    $dashoffset = $circumference - ($progressPercent / 100) * $circumference;
@endphp

<article class="group relative bg-white border border-pink-100 hover:border-pink-500 hover:-translate-y-1 hover:shadow-2xl hover:shadow-pink-200/70 transition-all duration-300 flex flex-col overflow-hidden">
    <!-- Course Banner Image -->
    <div class="relative h-48 w-full overflow-hidden bg-neutral-950 shrink-0">
        <img src="{{ $course->banner_url }}" 
             alt="{{ $course->title }}" 
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        <div class="absolute inset-0 bg-gradient-to-t from-neutral-950/80 via-transparent to-black/20"></div>

        <!-- Badges on banner -->
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
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
            @if($index !== null)
                <span class="text-xs font-black px-2 py-0.5 bg-pink-600 text-white shrink-0">
                    #{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}
                </span>
            @endif
        </div>

        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-xs font-bold">
            <span class="flex items-center gap-1.5 text-neutral-200">
                @if($isEnrolled)
                    <span class="text-pink-300 font-black">{{ $completedCount }}/{{ $lessonsCount }} bài đã học</span>
                @else
                    <span>{{ $lessonsCount }} bài giảng</span>
                @endif
            </span>
            <span class="text-neutral-300 text-[11px]">
                {{ $course->created_at->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- Course Content -->
    <div class="p-6 flex-1 flex flex-col justify-between">
        <div>
            <h3 class="font-black text-lg text-neutral-900 leading-snug line-clamp-2 mb-3 group-hover:text-pink-600 transition-colors">
                <a href="{{ route('courses.show', $course->slug ?: $course->id) }}">
                    {{ $course->title }}
                </a>
            </h3>

            <p class="text-sm text-neutral-500 leading-relaxed line-clamp-2 mb-5">
                {{ $course->description ?? 'Chưa có mô tả chi tiết cho khóa học này.' }}
            </p>
        </div>

        <div class="mt-auto pt-4 border-t border-pink-50">
            <div class="flex items-center justify-between gap-3 mb-4">
                <!-- Thông tin Giảng viên (luôn hiển thị cho cả đã đăng ký và chưa đăng ký) -->
                <div class="flex items-center gap-2.5 min-w-0">
                    @if($course->teacher && $course->teacher->avatar_url)
                        <img src="{{ $course->teacher->avatar_url }}" alt="{{ $teacherName }}" class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-pink-100">
                    @else
                        <div class="w-8 h-8 rounded-full bg-neutral-950 text-pink-500 flex items-center justify-center font-black text-xs shrink-0 ring-2 ring-pink-100">
                            {{ strtoupper(substr($teacherName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 text-xs leading-tight">
                        <span class="block text-neutral-400 text-[10px] uppercase font-bold tracking-wider">Giảng viên</span>
                        <span class="block font-bold text-neutral-800 truncate" title="{{ $teacherName }}">{{ $teacherName }}</span>
                    </div>
                </div>

                @if($isEnrolled)
                    <!-- Bên phải: Tiến độ học với hình tròn % -->
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="relative shrink-0" style="width: 38px; height: 38px; min-width: 38px;">
                            <svg class="-rotate-90 transform" style="width: 38px; height: 38px;" viewBox="0 0 38 38">
                                <circle cx="19" cy="19" r="16" stroke="#fce7f3" stroke-width="3.5" fill="transparent" />
                                <circle cx="19" cy="19" r="16" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" fill="transparent"
                                        class="{{ $progressPercent >= 100 ? 'text-emerald-500' : 'text-pink-600' }} transition-all duration-500"
                                        style="stroke-dasharray: {{ $circumference }}; stroke-dashoffset: {{ $dashoffset }};" />
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center font-black text-[11px] {{ $progressPercent >= 100 ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $progressPercent }}%
                            </span>
                        </div>
                        <div class="leading-tight text-right">
                            <span class="block text-[10px] uppercase font-black tracking-wider whitespace-nowrap {{ $progressPercent >= 100 ? 'text-emerald-600' : 'text-pink-600' }}">
                                {{ $progressPercent >= 100 ? 'Hoàn thành' : 'Tiến độ' }}
                            </span>
                            <span class="block text-xs font-bold text-neutral-800 whitespace-nowrap">
                                {{ $completedCount }}/{{ $lessonsCount }} bài
                            </span>
                        </div>
                    </div>
                @else
                    <!-- Bên phải: Số bài giảng -->
                    <div class="shrink-0 text-right">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-neutral-600 bg-neutral-100 rounded-md">
                            <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>{{ $lessonsCount }} bài</span>
                        </span>
                    </div>
                @endif
            </div>

            @if($isEnrolled)
                <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                   class="flex items-center justify-between w-full px-5 py-3 bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-[0.14em] transition-colors duration-300 shadow-md shadow-pink-600/20">
                    <span>{{ $progressPercent >= 100 ? 'Xem lại khóa học' : 'Vào học ngay' }}</span>
                    <span class="transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
                </a>
            @else
                <a href="{{ route('courses.show', $course->slug ?: $course->id) }}"
                   class="flex items-center justify-between w-full px-5 py-3 bg-neutral-950 group-hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-[0.14em] transition-colors duration-300">
                    <span>Xem chi tiết &amp; đăng ký</span>
                    <span class="transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
                </a>
            @endif
        </div>
    </div>
</article>
