<x-app-layout>
    <!-- Header banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-pink-500 mb-2">Đánh giá năng lực</p>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Bài kiểm tra (Quizzes)</h1>
                <p class="text-sm text-neutral-400 mt-2 max-w-xl leading-relaxed">
                    Kiểm tra và củng cố kiến thức với hệ thống trắc nghiệm chấm điểm tự động cho các khóa học bạn đã đăng ký.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('courses.index') }}" 
                   class="px-5 py-3 text-xs font-extrabold uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white transition">
                    Khám phá thêm khóa học &rarr;
                </a>
            </div>
        </div>
    </section>

    <div class="py-12 min-h-[500px]" x-data="studentQuizzesIndexHandler()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($enrolledQuizzes->isEmpty() && empty($search) && empty($type) && empty($scope))
                <div class="bg-white border border-pink-100 p-12 text-center max-w-xl mx-auto shadow-sm">
                    <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs font-bold uppercase tracking-wider mb-4">
                        Thông báo
                    </span>
                    <h3 class="text-xl font-black text-neutral-900 mb-2">Chưa có bài kiểm tra nào</h3>
                    <p class="text-neutral-500 text-sm mb-6 leading-relaxed">
                        Bạn chưa đăng ký khóa học nào có bài kiểm tra đã được phê duyệt, hoặc giảng viên đang chuẩn bị bộ câu hỏi.
                    </p>
                    <a href="{{ route('courses.index') }}" class="inline-block px-6 py-3 rounded-xl bg-slate-900 hover:bg-pink-600 text-white font-extrabold text-xs uppercase tracking-wider transition">
                        Khám phá khóa học &rarr;
                    </a>
                </div>
            @else
                <!-- Thanh tìm kiếm & lọc bài kiểm tra -->
                <div class="mb-8 bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs">
                    <form method="GET" action="{{ route('student.quizzes.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                        <!-- Ô tìm kiếm -->
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" x-model="search"
                                   placeholder="Tìm kiếm bài kiểm tra theo tên, khóa học, chủ đề..."
                                   class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm border-slate-200 focus:border-pink-600 focus:ring-pink-500 rounded-xl bg-slate-50/50 focus:bg-white transition">
                            <button type="button" x-show="search && search.length > 0" @click="search = ''" style="display: none;"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700" title="Xóa tìm kiếm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Dropdowns lọc -->
                        <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                            <select name="type" x-model="type"
                                    class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white">
                                <option value="all">Tất cả loại bài</option>
                                <option value="quiz">Kiểm tra thường</option>
                                <option value="midterm">Giữa kỳ</option>
                                <option value="final">Cuối kỳ</option>
                            </select>

                            <select name="scope" x-model="scope"
                                    class="text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-slate-50/50 focus:bg-white">
                                <option value="all">Tất cả phạm vi</option>
                                <option value="free">Bài kiểm tra Tự do</option>
                                <option value="course">Theo khóa học</option>
                            </select>

                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition whitespace-nowrap shadow-xs">
                                Tìm kiếm
                            </button>

                            <button type="button" x-show="(search && search !== '') || type !== 'all' || scope !== 'all'" @click="resetFilters()" style="display: none;"
                                    class="px-3 py-2.5 text-xs font-bold text-slate-500 hover:text-pink-600 transition underline whitespace-nowrap">
                                Đặt lại
                            </button>
                        </div>
                    </form>

                    <!-- Kết quả hiển thị / đếm số lượng -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Đang hiển thị <strong class="text-pink-600 font-extrabold" x-text="countVisible()">{{ $enrolledQuizzes->count() }}</strong> bài kiểm tra</span>
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
                    <h3 class="text-lg font-extrabold text-slate-900 mb-1">Không tìm thấy bài kiểm tra nào</h3>
                    <p class="text-slate-500 text-xs mb-4">
                        Không có bài kiểm tra nào khớp với từ khóa tìm kiếm hoặc bộ lọc hiện tại.
                    </p>
                    <button type="button" @click="resetFilters()" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-xs">
                        Xóa bộ lọc
                    </button>
                </div>

                <!-- Danh sách Card bài kiểm tra -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($enrolledQuizzes as $quiz)
                        @php
                            $attempts = $userAttempts->get($quiz->id, collect());
                            $lastAttempt = $attempts->first();
                            $bestScore = $attempts->max('score');
                            $hasPassed = $attempts->contains('is_passed', true);
                            $quizTeacher = $quiz->teacher ?? $quiz->course?->teacher;
                            $teacherName = $quizTeacher?->name ?? 'Ban Giảng Huấn';
                        @endphp
                        <article x-show="matches({{ $quiz->id }})"
                                 class="bg-white rounded-2xl border border-slate-200/80 hover:border-pink-300 hover:shadow-xl hover:shadow-pink-100/50 transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                            <!-- Card Header & Content -->
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-slate-900 text-white shadow-2xs">
                                        {{ $quiz->type_name }}
                                    </span>

                                    @if($attempts->isNotEmpty())
                                        @if($hasPassed)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                <span>Đạt ({{ $bestScore }}/10)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200/60">
                                                <span>Chưa đạt ({{ $bestScore }}/10)</span>
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">
                                            Chưa thi
                                        </span>
                                    @endif
                                </div>

                                <div class="text-[11px] font-bold text-pink-600 uppercase tracking-wider mb-1 line-clamp-1">
                                    {{ $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do' }}
                                </div>

                                <h3 class="text-lg font-extrabold text-slate-900 mb-2 line-clamp-2 group-hover:text-pink-600 transition">
                                    {{ $quiz->title }}
                                </h3>

                                <!-- Thông tin Giảng viên ra đề -->
                                <div class="flex items-center gap-2.5 mb-3.5 pb-3 border-b border-slate-100">
                                    @if($quizTeacher && $quizTeacher->avatar_url)
                                        <img src="{{ $quizTeacher->avatar_url }}" alt="{{ $teacherName }}" class="w-7 h-7 rounded-full object-cover shrink-0 ring-1 ring-pink-200">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-[11px] shrink-0 ring-1 ring-pink-200">
                                            {{ strtoupper(mb_substr($teacherName, 0, 1, 'UTF-8')) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 text-xs">
                                        <span class="block text-slate-400 text-[10px] uppercase font-bold tracking-wider leading-none">Giảng viên</span>
                                        <span class="block font-bold text-slate-800 truncate text-xs mt-0.5" title="{{ $teacherName }}">{{ $teacherName }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500 mb-5 line-clamp-2 leading-relaxed">
                                    {{ $quiz->description ?? 'Bài kiểm tra trắc nghiệm đánh giá kiến thức lý thuyết và thực hành.' }}
                                </p>

                                <!-- Meta Info Grid -->
                                <dl class="grid grid-cols-3 gap-2 bg-slate-50/80 p-2.5 rounded-xl border border-slate-100 text-center text-xs">
                                    <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                        <dt class="text-[10px] uppercase font-bold text-slate-400">Số câu</dt>
                                        <dd class="font-extrabold text-slate-900 mt-0.5">
                                            @if($quiz->question_limit && $quiz->question_limit < $quiz->questions_count)
                                                {{ $quiz->question_limit }} <span class="text-[10px] text-slate-400 font-normal">/{{ $quiz->questions_count }}</span>
                                            @else
                                                {{ $quiz->questions_count }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                        <dt class="text-[10px] uppercase font-bold text-slate-400">Thời gian</dt>
                                        <dd class="font-extrabold text-slate-900 mt-0.5">{{ $quiz->duration_minutes }}p</dd>
                                    </div>
                                    <div class="bg-white rounded-lg p-2 border border-slate-100 shadow-2xs">
                                        <dt class="text-[10px] uppercase font-bold text-slate-400">Điểm đạt</dt>
                                        <dd class="font-extrabold text-pink-600 mt-0.5">&ge; {{ $quiz->passing_score }}</dd>
                                    </div>
                                </dl>

                                @php
                                    $attemptsCount = $userAttempts->get($quiz->id)?->count() ?? 0;
                                    $hasReachedMax = $quiz->max_attempts !== null && $quiz->max_attempts > 0 && $attemptsCount >= $quiz->max_attempts;
                                @endphp

                                @if($quiz->max_attempts)
                                    <div class="mt-3 flex items-center justify-between text-xs bg-slate-50/80 px-3 py-1.5 rounded-xl border border-slate-100">
                                        <span class="text-slate-500">Lượt làm bài: <strong class="{{ $hasReachedMax ? 'text-rose-600' : 'text-slate-800' }}">{{ $attemptsCount }}/{{ $quiz->max_attempts }}</strong></span>
                                        @if($hasReachedMax)
                                            <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Đã hết lượt</span>
                                        @else
                                            <span class="text-[10px] font-medium text-slate-500">Còn {{ $quiz->max_attempts - $attemptsCount }} lượt</span>
                                        @endif
                                    </div>
                                @endif

                                @if($quiz->starts_at || $quiz->ends_at)
                                    <div class="mt-3 text-[11px] text-slate-600 flex items-center gap-1.5 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="truncate">
                                            @if($quiz->starts_at && $quiz->ends_at)
                                                Mở: {{ $quiz->starts_at->format('d/m/Y H:i') }} &rarr; {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                            @elseif($quiz->starts_at)
                                                Bắt đầu: {{ $quiz->starts_at->format('d/m/Y H:i') }}
                                            @else
                                                Hạn chót: {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Action Footer -->
                            <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-3">
                                @if($quiz->isExpired())
                                    @if($lastAttempt)
                                        <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                           class="text-xs font-bold text-slate-600 hover:text-pink-600 underline">
                                            Xem kết quả cũ
                                        </a>
                                    @else
                                        <span class="text-xs text-rose-600 font-semibold">Đã quá hạn</span>
                                    @endif
                                    <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                                        Đã hết hạn
                                    </span>
                                @elseif($quiz->isNotStarted())
                                    <span class="text-xs text-amber-600 font-semibold">
                                        Mở: {{ $quiz->starts_at->format('d/m H:i') }}
                                    </span>
                                    <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider cursor-not-allowed">
                                        Chưa mở
                                    </span>
                                @elseif($hasReachedMax && !auth()->user()->isAdmin() && !auth()->user()->isTeacher())
                                    @if($lastAttempt)
                                        <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                            class="text-xs font-bold text-slate-600 hover:text-pink-600 underline">
                                            Xem kết quả cũ
                                        </a>
                                    @else
                                        <span class="text-xs text-rose-600 font-semibold">Hết lượt làm</span>
                                    @endif
                                    <span class="px-4 py-2 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wider cursor-not-allowed"
                                          title="Bạn đã làm {{ $attemptsCount }}/{{ $quiz->max_attempts }} lần tối đa.">
                                        Hết lượt ({{ $attemptsCount }}/{{ $quiz->max_attempts }})
                                    </span>
                                @elseif($lastAttempt)
                                    <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                        class="text-xs font-bold text-slate-600 hover:text-pink-600 underline">
                                        Xem kết quả cũ
                                    </a>
                                    <button type="button" 
                                        @click="askStartQuiz({{ $quiz->id }})"
                                        class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition cursor-pointer shadow-xs">
                                        Làm lại đề &rarr;
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Sẵn sàng</span>
                                    <button type="button" 
                                        @click="askStartQuiz({{ $quiz->id }})"
                                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition cursor-pointer shadow-xs">
                                        Bắt đầu làm bài &rarr;
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        </div>

        <!-- Modal Xác nhận Bắt đầu Làm bài Kiểm tra (Modern Clean Light Card) -->
        <div x-show="confirmModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" 
             aria-modal="true"
             aria-labelledby="modal-quiz-title"
             @keydown.escape.window="confirmModalOpen = false"
             style="display: none;">
            
            <!-- Backdrop mờ tối sang trọng -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                 x-show="confirmModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="confirmModalOpen = false"></div>

            <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
                <!-- Hộp thoại Modal bo góc mềm mại, sang trọng -->
                <div x-show="confirmModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-100"
                     @click.stop="">
                    
                    <!-- Header Modal với hiệu ứng gradient nhẹ nhàng và nút đóng (X) -->
                    <div class="bg-gradient-to-r from-pink-500/10 via-rose-500/10 to-pink-500/5 px-6 pt-6 pb-5 border-b border-pink-100/70 relative">
                        <button type="button" 
                                @click="confirmModalOpen = false"
                                class="absolute top-5 right-5 w-8 h-8 rounded-full bg-white/80 hover:bg-white text-slate-400 hover:text-slate-700 flex items-center justify-center shadow-xs transition cursor-pointer"
                                title="Đóng">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="flex items-start gap-4 pr-8">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-600 to-rose-600 text-white flex items-center justify-center shadow-lg shadow-pink-500/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-100/80 text-pink-700 uppercase tracking-wider mb-1.5">
                                    Xác nhận làm bài
                                </span>
                                <h3 id="modal-quiz-title" class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug line-clamp-2" x-text="pendingQuiz.title">
                                    Tên bài kiểm tra
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung chi tiết bài thi -->
                    <div class="p-6 space-y-4">
                        <!-- Thông tin Khóa học & Giảng viên + Chỉ số bài thi -->
                        <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-100 space-y-3">
                            <div class="flex items-center justify-between text-xs text-slate-600 flex-wrap gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-slate-400 font-medium shrink-0">Khóa học:</span>
                                    <span class="font-bold text-slate-800 truncate" x-text="pendingQuiz.course"></span>
                                </div>
                                <template x-if="pendingQuiz.teacherName">
                                    <div class="flex items-center gap-1.5 text-slate-700 font-medium shrink-0">
                                        <template x-if="pendingQuiz.teacherAvatar">
                                            <img :src="pendingQuiz.teacherAvatar" class="w-4 h-4 rounded-full object-cover">
                                        </template>
                                        <template x-if="!pendingQuiz.teacherAvatar">
                                            <span class="w-4 h-4 rounded-full bg-pink-100 text-pink-700 text-[9px] font-bold inline-flex items-center justify-center" x-text="pendingQuiz.teacherInitial || 'G'"></span>
                                        </template>
                                        <span>GV: <strong class="text-slate-900 font-semibold" x-text="pendingQuiz.teacherName"></strong></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Thống kê thông số: Thời gian, Số câu hỏi, Điểm đạt, Số lượt làm -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Thời gian</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block" x-text="pendingQuiz.duration + ' phút'"></span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Số câu</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block" x-text="pendingQuiz.questionsCount ? (pendingQuiz.questionsCount + ' câu') : 'Theo đề'"></span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Điểm đạt</span>
                                    <span class="text-sm font-extrabold text-pink-600 mt-0.5 block" x-text="pendingQuiz.passingScore ? ('≥ ' + pendingQuiz.passingScore) : '≥ 5.0'"></span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Lượt làm</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block" x-text="pendingQuiz.maxAttempts ? (pendingQuiz.attemptsCount + '/' + pendingQuiz.maxAttempts) : 'Thoải mái'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Lưu ý & Quy chế thi trang nhã, rõ ràng -->
                        <div class="bg-gradient-to-br from-amber-50/90 to-orange-50/60 border border-amber-200/80 rounded-2xl p-4 space-y-2.5">
                            <div class="flex items-center gap-2 text-amber-900 font-bold text-xs uppercase tracking-wider">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>Lưu ý quan trọng trước khi bắt đầu</span>
                            </div>
                            <ul class="space-y-1.5 text-xs text-amber-950/90 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Thời gian làm bài sẽ đếm ngược <strong>ngay lập tức</strong> khi bạn xác nhận.</span>
                                </li>
                                <template x-if="pendingQuiz.maxAttempts">
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 font-bold leading-tight">•</span>
                                        <span>Đề thi giới hạn tối đa <strong x-text="pendingQuiz.maxAttempts + ' lượt làm bài'"></strong> (bạn đang chuẩn bị thực hiện lượt thứ <strong x-text="pendingQuiz.attemptsCount + 1"></strong>).</span>
                                    </li>
                                </template>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Không thể hủy giữa chừng; lượt thi sẽ được <strong>ghi nhận chính thức</strong>.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Vui lòng không chuyển tab hay rời trang thi để tránh bị tự động nộp bài.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer nút thao tác tự nhiên, lịch sự -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="confirmModalOpen = false"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-white text-slate-600 hover:text-slate-800 text-xs font-bold uppercase tracking-wider transition cursor-pointer text-center">
                            Để sau
                        </button>
                        <a :href="pendingQuiz.url"
                           class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-md shadow-pink-600/25 hover:shadow-pink-600/35 cursor-pointer text-center inline-flex items-center justify-center gap-1.5">
                            <span>Bắt đầu làm bài</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function studentQuizzesIndexHandler() {
            return {
                search: @json($search ?? ''),
                type: @json($type ?? 'all'),
                scope: @json($scope ?? 'all'),
                quizzes: [
                    @foreach($enrolledQuizzes as $q)
                    @php
                        $t = $q->teacher ?? $q->course?->teacher;
                        $tName = $t?->name ?? 'Ban Giảng Huấn';
                        $tAvatar = $t?->avatar_url;
                        $tInitial = strtoupper(mb_substr($tName, 0, 1, 'UTF-8'));
                        $tLower = mb_strtolower($tName, 'UTF-8');
                        $qTitleLower = mb_strtolower($q->title, 'UTF-8');
                        $qDescLower = mb_strtolower($q->description ?? '', 'UTF-8');
                        $qCourseLower = mb_strtolower($q->course ? $q->course->title : 'tự do', 'UTF-8');
                        $qQuestions = $q->question_limit && $q->question_limit < $q->questions_count ? $q->question_limit : $q->questions_count;
                        $qAttemptsCount = (int) ($userAttempts->get($q->id)?->count() ?? 0);
                    @endphp
                    {
                        id: {{ $q->id }},
                        title: @json($qTitleLower),
                        rawTitle: @json($q->title),
                        desc: @json($qDescLower),
                        course: @json($qCourseLower),
                        rawCourse: @json($q->course ? $q->course->title : 'Bài kiểm tra Tự do'),
                        duration: {{ (int)$q->duration_minutes }},
                        questionsCount: {{ (int)$qQuestions }},
                        passingScore: {{ (float)$q->passing_score }},
                        maxAttempts: {{ $q->max_attempts ? (int)$q->max_attempts : 'null' }},
                        attemptsCount: {{ $qAttemptsCount }},
                        url: @json(route('student.quizzes.take', $q->id)),
                        type: @json($q->type),
                        scope: @json($q->course_id ? 'course' : 'free'),
                        teacher: @json($tLower),
                        rawTeacher: @json($tName),
                        teacherAvatar: @json($tAvatar),
                        teacherInitial: @json($tInitial)
                    },
                    @endforeach
                ],
                matches(id) {
                    const item = this.quizzes.find(x => x.id === id);
                    if (!item) return true;
                    if (this.type !== 'all' && item.type !== this.type) return false;
                    if (this.scope !== 'all' && item.scope !== this.scope) return false;
                    if (!this.search || !this.search.trim()) return true;
                    const q = this.search.toLowerCase().trim();
                    return item.title.includes(q) || item.desc.includes(q) || item.course.includes(q) || (item.teacher && item.teacher.includes(q));
                },
                countVisible() {
                    return this.quizzes.filter(item => {
                        if (this.type !== 'all' && item.type !== this.type) return false;
                        if (this.scope !== 'all' && item.scope !== this.scope) return false;
                        if (!this.search || !this.search.trim()) return true;
                        const q = this.search.toLowerCase().trim();
                        return item.title.includes(q) || item.desc.includes(q) || item.course.includes(q) || (item.teacher && item.teacher.includes(q));
                    }).length;
                },
                resetFilters() {
                    this.search = '';
                    this.type = 'all';
                    this.scope = 'all';
                },
                confirmModalOpen: false,
                pendingQuiz: {
                    title: '',
                    duration: 0,
                    questionsCount: 0,
                    passingScore: 0,
                    maxAttempts: null,
                    attemptsCount: 0,
                    course: '',
                    url: '',
                    teacherName: '',
                    teacherAvatar: '',
                    teacherInitial: ''
                },
                askStartQuiz(id) {
                    const item = this.quizzes.find(x => x.id === id);
                    if (item) {
                        this.pendingQuiz = {
                            url: item.url,
                            title: item.rawTitle,
                            duration: item.duration,
                            questionsCount: item.questionsCount,
                            passingScore: item.passingScore,
                            maxAttempts: item.maxAttempts,
                            attemptsCount: item.attemptsCount,
                            course: item.rawCourse,
                            teacherName: item.rawTeacher,
                            teacherAvatar: item.teacherAvatar,
                            teacherInitial: item.teacherInitial
                        };
                        this.confirmModalOpen = true;
                    }
                }
            };
        }
    </script>
</x-app-layout>
