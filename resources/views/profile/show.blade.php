<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-pink-600 mb-1">
                    <a href="{{ route('home') }}" class="hover:underline">Trang chủ</a>
                    <span>&rsaquo;</span>
                    <a href="{{ route('courses.index') }}" class="hover:underline">Khóa học</a>
                    <span>&rsaquo;</span>
                    <span class="text-slate-500">Hồ sơ người dùng</span>
                </div>
                <h2 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    <span>{{ $user->name }}</span>
                    @if($user->isAdmin())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-pink-100 text-pink-700 border border-pink-200">
                            Quản trị viên
                        </span>
                    @elseif($user->isTeacher())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-indigo-100 text-indigo-700 border border-indigo-200">
                            Giảng viên
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                            Học viên
                        </span>
                    @endif
                </h2>
            </div>

            <div class="flex items-center gap-2.5">
                @if(auth()->check() && auth()->id() === $user->id)
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-pink-600/20 transition active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Chỉnh sửa hồ sơ</span>
                    </a>
                    <a href="{{ route('wallet.deposit') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs shadow-md shadow-neutral-900/20 transition active:scale-95">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span>Ví: <strong class="text-emerald-400 font-extrabold">{{ $user->formatted_balance }}</strong></span>
                    </a>
                @elseif(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard', ['tab' => 'users', 'search' => $user->email]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs shadow-xs transition active:scale-95">
                        <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Quản lý trong Admin</span>
                    </a>
                @endif
                <button onclick="window.history.back()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs shadow-2xs transition">
                    &larr; Quay lại
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#fdf2f8]/60 min-h-[calc(100vh-200px)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Profile Hero Card -->
            <div class="relative bg-gradient-to-br from-neutral-950 via-neutral-900 to-slate-900 text-white rounded-3xl p-6 sm:p-10 shadow-2xl overflow-hidden border border-white/10">
                <!-- Decorative Glows -->
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-pink-600/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative flex flex-col md:flex-row items-center md:items-start gap-6 sm:gap-8 z-10">
                    <!-- Avatar -->
                    <div class="relative shrink-0">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}"
                                 alt="{{ $user->name }}"
                                 class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl object-cover ring-4 ring-pink-500/30 shadow-2xl">
                        @else
                            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl bg-gradient-to-tr from-pink-600 to-rose-400 text-white flex items-center justify-center font-black text-4xl sm:text-5xl shadow-2xl ring-4 ring-pink-500/30">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        @if($user->isTeacher() || $user->isAdmin())
                            <span class="absolute -bottom-2 -right-2 p-1.5 rounded-xl bg-pink-600 text-white shadow-lg ring-2 ring-neutral-900" title="{{ $user->role_label }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        @endif
                    </div>

                    <!-- Main Info -->
                    <div class="flex-1 text-center md:text-left space-y-3">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                                {{ $user->name }}
                            </h1>
                            @if(!auth()->check() || auth()->id() !== $user->id)
                                <button type="button"
                                        x-data
                                        @click="$dispatch('open-user-report-modal')"
                                        onclick="window.dispatchEvent(new CustomEvent('open-user-report-modal'))"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-300 font-bold text-xs transition"
                                        title="Báo cáo tài khoản này">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path></svg>
                                    <span>Báo cáo</span>
                                </button>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-xs sm:text-sm text-neutral-300">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>Vai trò: <strong class="text-white">{{ $user->role_label }}</strong></span>
                            </span>

                            <span class="text-neutral-600">&bull;</span>

                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Tham gia từ: <strong class="text-white">{{ $user->created_at->format('d/m/Y') }}</strong></span>
                            </span>

                            @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->isAdmin()))
                                <span class="text-neutral-600">&bull;</span>
                                <span class="inline-flex items-center gap-1.5 text-neutral-400">
                                    <svg class="w-4 h-4 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <span>{{ $user->email }}</span>
                                </span>
                            @endif
                        </div>

                        <p class="text-sm text-neutral-300 leading-relaxed max-w-2xl pt-1">
                            @if($user->isTeacher())
                                Giảng viên chia sẻ kiến thức và đồng hành cùng học viên tại nền tảng học trực tuyến Ctrl C+V.
                            @elseif($user->isAdmin())
                                Quản trị viên phụ trách điều phối, kiểm duyệt nội dung và hỗ trợ vận hành nền tảng Ctrl C+V.
                            @else
                                Học viên tích cực tham gia các khóa học, hoàn thành bài giảng và thử thách trắc nghiệm.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Highlight Stats Bar -->
                <div class="mt-8 pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center sm:text-left">
                    @if($user->isTeacher() || $courses->isNotEmpty())
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Khóa học đã tạo</span>
                            <span class="block text-2xl font-black text-pink-400 mt-0.5">{{ $courses->count() }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Tổng học viên</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ number_format($totalStudents) }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Đánh giá trung bình</span>
                            <span class="block text-2xl font-black text-amber-400 mt-0.5 flex items-center justify-center sm:justify-start gap-1">
                                <span>{{ $averageCourseRating > 0 ? number_format($averageCourseRating, 1) : '5.0' }}</span>
                                <svg class="w-4 h-4 fill-amber-400 text-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Lượt phản hồi</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ number_format($totalReviewsReceived) }}</span>
                        </div>
                    @else
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Khóa học đã đăng ký</span>
                            <span class="block text-2xl font-black text-pink-400 mt-0.5">{{ $enrolledCount }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Bài học hoàn thành</span>
                            <span class="block text-2xl font-black text-emerald-400 mt-0.5">{{ $completedLessonsCount }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Lượt làm Quiz</span>
                            <span class="block text-2xl font-black text-white mt-0.5">{{ $quizAttemptsCount }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-white/5 border border-white/5">
                            <span class="block text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Đánh giá đã gửi</span>
                            <span class="block text-2xl font-black text-amber-400 mt-0.5">{{ $userReviews->count() }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Khóa học của giảng viên (nếu có) -->
            @if($user->isTeacher() || $courses->isNotEmpty())
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                                <span class="w-2.5 h-6 bg-pink-600 rounded-full inline-block"></span>
                                <span>Khóa học của giảng viên</span>
                                <span class="text-sm font-bold text-pink-600 bg-pink-100 px-2.5 py-0.5 rounded-full">
                                    {{ $courses->count() }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Các khóa học chất lượng được biên soạn và giảng dạy trực tiếp bởi {{ $user->name }}.</p>
                        </div>
                    </div>

                    @if($courses->isEmpty())
                        <div class="bg-white rounded-3xl p-12 text-center border border-pink-100 shadow-xs space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center mx-auto">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <h4 class="font-extrabold text-slate-800 text-base">Hiện chưa có khóa học công khai nào</h4>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">Giảng viên đang trong quá trình biên soạn các nội dung mới hoặc khóa học đang chờ kiểm duyệt.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
                            @foreach($courses as $course)
                                <x-course-card :course="$course" :index="$loop->iteration" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Đánh giá / Bình luận gần đây của người dùng -->
            @if($userReviews->isNotEmpty())
                <div class="space-y-6 pt-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                                <span class="w-2.5 h-6 bg-pink-600 rounded-full inline-block"></span>
                                <span>Đánh giá &amp; Nhận xét gần đây</span>
                                <span class="text-sm font-bold text-pink-600 bg-pink-100 px-2.5 py-0.5 rounded-full">
                                    {{ $userReviews->count() }}
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">Những chia sẻ, đóng góp và cảm nhận của {{ $user->name }} đối với các khóa học đã tham gia.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($userReviews as $review)
                            <div class="bg-white rounded-3xl p-6 border border-pink-100 shadow-xs hover:border-pink-300 transition space-y-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <a href="{{ route('courses.show', $review->course->slug ?: $review->course->id) }}"
                                           class="font-extrabold text-sm text-slate-900 hover:text-pink-600 transition-colors line-clamp-1">
                                            {{ $review->course->title ?? 'Khóa học' }}
                                        </a>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            <span>{{ $review->created_at->diffForHumans() }}</span>
                                            @if($review->course && $review->course->teacher)
                                                <span>&bull;</span>
                                                <span>GV: {{ $review->course->teacher->name }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-0.5 text-amber-400 shrink-0">
                                        @for($s = 1; $s <= 5; $s++)
                                            <svg class="w-4 h-4 {{ $s <= $review->rating ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>

                                @if(!empty($review->comment))
                                    <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3.5 rounded-2xl border border-slate-100 italic">
                                        "{!! nl2br(e($review->comment)) !!}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Modal Báo cáo người dùng -->
    @if(!auth()->check() || auth()->id() !== $user->id)
        <div x-data="{ open: false, selectedReason: 'avatar_inappropriate' }"
             @open-user-report-modal.window="open = true"
             x-show="open"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-neutral-950/70 backdrop-blur-xs transition-opacity"
                 @click="open = false"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="open"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-pink-100 p-6 sm:p-8">

                    @auth
                        <form method="POST" action="{{ route('users.report', $user) }}" class="space-y-5">
                            @csrf
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Báo cáo vi phạm tài khoản</h3>
                                        <p class="text-xs text-slate-500 mt-0.5">Người dùng: <strong class="text-slate-800">{{ $user->name }}</strong></p>
                                    </div>
                                </div>
                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                    Lựa chọn nội dung báo cáo: <span class="text-rose-500">*</span>
                                </label>

                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition"
                                           :class="selectedReason === 'avatar_inappropriate' ? 'border-pink-500 bg-pink-50/50 text-pink-900 font-bold ring-1 ring-pink-500/30' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                                        <input type="radio" name="reason" value="avatar_inappropriate" x-model="selectedReason" class="text-pink-600 focus:ring-pink-500" required>
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700 shrink-0 border border-slate-200/80">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs">Avatar không phù hợp (Phản cảm, không đúng thuần phong mỹ tục)</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition"
                                           :class="selectedReason === 'name_inappropriate' ? 'border-pink-500 bg-pink-50/50 text-pink-900 font-bold ring-1 ring-pink-500/30' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                                        <input type="radio" name="reason" value="name_inappropriate" x-model="selectedReason" class="text-pink-600 focus:ring-pink-500">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700 shrink-0 border border-slate-200/80">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs">Tên không phù hợp (Thô tục, xúc phạm, mạo danh)</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition"
                                           :class="selectedReason === 'harassment' ? 'border-pink-500 bg-pink-50/50 text-pink-900 font-bold ring-1 ring-pink-500/30' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                                        <input type="radio" name="reason" value="harassment" x-model="selectedReason" class="text-pink-600 focus:ring-pink-500">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700 shrink-0 border border-slate-200/80">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs">Hành vi xúc phạm / Quấy rối người khác</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition"
                                           :class="selectedReason === 'other' ? 'border-pink-500 bg-pink-50/50 text-pink-900 font-bold ring-1 ring-pink-500/30' : 'border-slate-200 hover:border-slate-300 text-slate-700'">
                                        <input type="radio" name="reason" value="other" x-model="selectedReason" class="text-pink-600 focus:ring-pink-500">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700 shrink-0 border border-slate-200/80">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                            </div>
                                            <span class="text-xs">Lý do khác</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label for="user-report-details" class="block text-xs font-bold text-slate-700">
                                    Lý do:
                                </label>
                                <textarea id="user-report-details"
                                          name="details"
                                          rows="3"
                                          placeholder="Vui lòng nêu rõ lý do bạn báo cáo..."
                                          class="w-full text-xs rounded-2xl border-slate-200 focus:border-pink-500 focus:ring-pink-500 shadow-2xs placeholder-slate-400"></textarea>
                                <p class="text-[11px] text-slate-400">Báo cáo sẽ được quản trị viên kiểm tra và xử lý theo quy định.</p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="open = false"
                                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                                    Hủy bỏ
                                </button>
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition active:scale-95">
                                    Gửi báo cáo
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-4 space-y-4">
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-11a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-slate-800 text-base">Đăng nhập để gửi báo cáo</h4>
                                <p class="text-xs text-slate-500 mt-1">Bạn cần đăng nhập tài khoản để có thể báo cáo vi phạm.</p>
                            </div>
                            <div class="flex items-center justify-center gap-3 pt-2">
                                <button type="button" @click="open = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs">Đóng</button>
                                <a href="{{ route('login') }}" class="px-5 py-2 rounded-xl bg-pink-600 text-white font-bold text-xs shadow-md shadow-pink-600/20 hover:bg-pink-700">Đăng nhập</a>
                            </div>
                        </div>
                    @endauth

                </div>
            </div>
        </div>
    @endif
</x-app-layout>
