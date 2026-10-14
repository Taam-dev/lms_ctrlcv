<x-app-layout>
    <!-- Quiz Header Banner (Sticky Top - Clean & Minimalist) -->
    <section class="bg-neutral-950/95 backdrop-blur-md text-white border-b border-white/10 sticky top-16 z-30 shadow-xs transition-all">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
            
            <!-- Left: Quiz Context & Title -->
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-500/15 text-pink-300 border border-pink-500/25 tracking-wide">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="text-xs text-neutral-400 font-medium truncate max-w-[160px] sm:max-w-xs" title="{{ $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do' }}">
                        {{ $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                    @php
                        $quizTeacher = $quiz->teacher ?? $quiz->course?->teacher;
                        $teacherName = $quizTeacher?->name ?? 'Ban Giảng Huấn';
                    @endphp
                    <span class="text-neutral-600 hidden md:inline text-xs">&bull;</span>
                    <div class="hidden md:inline-flex items-center gap-1.5 text-xs text-neutral-400">
                        @if($quizTeacher && $quizTeacher->avatar_url)
                            <img src="{{ $quizTeacher->avatar_url }}" alt="{{ $teacherName }}" class="w-3.5 h-3.5 rounded-full object-cover">
                        @else
                            <span class="w-3.5 h-3.5 rounded-full bg-white/10 text-neutral-300 text-[8px] font-bold inline-flex items-center justify-center">
                                {{ strtoupper(mb_substr($teacherName, 0, 1, 'UTF-8')) }}
                            </span>
                        @endif
                        <span class="text-[11px]">GV: <strong class="text-neutral-300 font-medium">{{ $teacherName }}</strong></span>
                    </div>
                </div>
                <h1 class="text-sm sm:text-base font-extrabold tracking-tight text-white truncate leading-tight">
                    {{ $quiz->title }}
                </h1>
            </div>

            <!-- Right: Timer, Progress Pills & Actions -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <!-- Digital Countdown Timer Pill -->
                <div id="timer-box" class="flex items-center gap-1.5 bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-1.5 rounded-full text-xs transition-colors shrink-0" title="Thời gian còn lại">
                    <svg class="w-3.5 h-3.5 text-pink-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="countdown-timer" class="font-mono font-bold text-xs sm:text-sm text-neutral-100 tracking-wider">
                        {{ sprintf('%02d:00', $quiz->duration_minutes) }}
                    </span>
                </div>

                <!-- Answered Progress Pill -->
                <div class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-3 py-1.5 rounded-full text-xs font-medium text-neutral-300 shrink-0">
                    <span id="progress-indicator-dot" class="w-1.5 h-1.5 bg-pink-500 rounded-full transition-colors shrink-0"></span>
                    <span class="text-xs">
                        Đã làm: <strong id="answered-count" class="text-white font-bold">0</strong>/{{ $questions->count() }}
                    </span>
                    <span id="unanswered-badge" class="hidden text-[10px] font-semibold text-rose-300 bg-rose-500/20 px-1.5 py-0.2 rounded-full border border-rose-500/30">
                        còn <span id="unanswered-count-text">0</span>
                    </span>
                </div>

                <!-- Action Buttons: Exit & Submit -->
                <div class="flex items-center gap-1.5 shrink-0 pl-1 border-l border-white/10">
                    <button type="button" 
                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-neutral-400 hover:text-white hover:bg-white/10 transition cursor-pointer inline-flex items-center gap-1"
                            title="Thoát khỏi bài kiểm tra"
                            onclick="triggerLeaveQuiz('{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}', 'Thoát bài thi')">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden sm:inline">Thoát</span>
                    </button>
                    <button type="button" 
                            id="btn-submit-quiz"
                            class="px-4 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-500 active:scale-95 text-white text-xs font-bold transition shadow-sm hover:shadow-pink-600/30 cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap"
                            onclick="validateAndSubmitQuiz()">
                        <span>Nộp bài</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sleek Thin Progress Track below Header -->
        <div class="h-0.5 w-full bg-white/5 overflow-hidden">
            <div id="top-progress-bar" class="h-full bg-gradient-to-r from-pink-500 to-rose-400 transition-all duration-300 rounded-r-full" style="width: 0%"></div>
        </div>
    </section>

    <!-- Floating Alert Banner khi còn câu hỏi chưa làm (Clean Minimalist Toast) -->
    <div id="quiz-validation-alert" class="hidden fixed top-24 left-1/2 -translate-x-1/2 z-50 max-w-lg w-[92%] sm:w-auto bg-white rounded-2xl border border-rose-200 text-slate-800 p-4 shadow-2xl flex items-center justify-between gap-4 transition-all duration-300">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 font-black text-sm border border-rose-200/60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs font-bold text-rose-600 uppercase tracking-wider flex items-center gap-2">
                    <span>Chưa hoàn thành bài thi</span>
                    <span id="quiz-alert-badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700"></span>
                </div>
                <div id="quiz-alert-message" class="text-xs text-slate-600 mt-0.5 font-medium leading-relaxed">
                    Vui lòng chọn đáp án trước khi nộp bài!
                </div>
            </div>
        </div>
        <button type="button" 
                onclick="document.getElementById('quiz-validation-alert').classList.add('hidden')" 
                class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-sm font-bold shrink-0 transition cursor-pointer" 
                title="Đóng thông báo">
            &times;
        </button>
    </div>

    <div class="py-10 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Quy chế làm bài (Clean Minimalist Card) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800">Quy chế thi trực tuyến</h2>
                </div>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600 font-medium">
                    <li class="flex items-center gap-2">
                        <span class="text-slate-400">&bull;</span>
                        <span>Số lượng: <strong>{{ $questions->count() }} câu hỏi trắc nghiệm</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-slate-400">&bull;</span>
                        <span>Điểm chuẩn qua môn: <strong class="text-pink-600">&ge; {{ $quiz->passing_score }}/10 điểm</strong></span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-slate-400">&bull;</span>
                        <span>Chấm điểm: <strong>Tự động &amp; hiển thị đáp án khi nộp</strong></span>
                    </li>
                    @if($quiz->ends_at)
                        <li class="flex items-center gap-2">
                            <span class="text-slate-400">&bull;</span>
                            <span>Hạn chót: <strong class="text-rose-600">{{ $quiz->ends_at->format('d/m/Y H:i') }}</strong></span>
                        </li>
                    @endif
                    @if($quiz->randomize_questions)
                        <li class="flex items-center gap-2">
                            <span class="text-slate-400">&bull;</span>
                            <span>Thứ tự: <strong>Câu hỏi được đảo ngẫu nhiên</strong></span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Form nộp bài quiz -->
            <form id="quiz-form" action="{{ route('student.quizzes.submit', $quiz->id) }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="warning_count" id="quiz-warning-count" value="0">

                @foreach($questions as $index => $question)
                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                    <fieldset id="question-card-{{ $question->id }}"
                              data-question-id="{{ $question->id }}"
                              data-question-number="{{ $index + 1 }}"
                              class="question-card bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 hover:border-pink-300 transition-all duration-300 scroll-mt-28 sm:scroll-mt-32 shadow-xs">
                        <!-- Tiêu đề câu hỏi -->
                        <legend class="sr-only">Câu hỏi số {{ $index + 1 }}</legend>
                        <div class="flex items-start gap-3.5 mb-5 pb-4 border-b border-slate-100">
                            <span class="question-badge flex items-center justify-center w-8 h-8 rounded-xl bg-slate-900 text-white font-extrabold text-xs shrink-0 transition-colors shadow-2xs">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="flex-grow pt-0.5">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                                    {{ $question->question_text }}
                                </h3>
                            </div>
                        </div>

                        <!-- Cảnh báo chưa chọn đáp án cho câu hỏi này (ẩn mặc định) -->
                        <div id="unanswered-warning-{{ $question->id }}" class="unanswered-warning hidden mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5 animate-pulse">
                            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Câu hỏi số {{ $index + 1 }} chưa được chọn đáp án. Vui lòng khoanh chọn 1 đáp án dưới đây trước khi nộp bài!</span>
                        </div>

                        <!-- Danh sách các lựa chọn đáp án -->
                        <div class="space-y-2.5">
                            @foreach($question->options as $optIndex => $option)
                                @php
                                    $optionLetter = chr(65 + $optIndex);
                                @endphp
                                <label class="group relative flex items-center gap-3.5 p-3.5 sm:p-4 rounded-xl border border-slate-200/80 hover:border-pink-500 hover:bg-pink-50/20 cursor-pointer transition has-[:checked]:border-pink-600 has-[:checked]:bg-pink-50/40 has-[:checked]:ring-1 has-[:checked]:ring-pink-600">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $option->id }}" 
                                           class="w-4 h-4 text-pink-600 border-slate-300 focus:ring-pink-500"
                                           onchange="updateProgress({{ $question->id }})">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center bg-slate-100 group-hover:bg-pink-100 group-has-[:checked]:bg-pink-600 group-has-[:checked]:text-white font-bold text-xs text-slate-700 transition">
                                        {{ $optionLetter }}
                                    </span>
                                    <span class="text-sm sm:text-base text-slate-800 group-has-[:checked]:text-slate-900 font-medium">
                                        {{ $option->option_text }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <!-- Khối xác nhận nộp bài ở cuối bài thi (Tĩnh - Không trôi nổi che tầm nhìn) -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 text-center space-y-4 shadow-xs mt-8">
                    <div class="max-w-md mx-auto space-y-1.5">
                        <h3 class="text-lg font-bold text-slate-900">Hoàn thành bài kiểm tra</h3>
                        <p class="text-xs text-slate-500">
                            Hãy kiểm tra lại tất cả các câu trả lời trước khi nộp. Hệ thống sẽ chấm điểm và hiển thị kết quả ngay lập tức.
                        </p>
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-50 border border-pink-200 text-xs font-bold text-pink-700 mt-1">
                            <span>Tiến độ làm bài:</span>
                            <span><strong id="bottom-answered-count" class="font-extrabold text-pink-600">0</strong> / {{ $questions->count() }} câu</span>
                            <span id="bottom-unanswered-badge" class="hidden text-rose-600 font-extrabold">(Còn <span id="bottom-unanswered-count">0</span> câu chưa làm)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button" 
                                class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider border border-slate-200 hover:bg-slate-50 text-slate-600 transition cursor-pointer"
                                onclick="triggerLeaveQuiz('{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}', 'Rời bài thi')">
                            Rời bài thi
                        </button>
                        <button type="button" 
                                id="bottom-btn-submit-quiz"
                                class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-500 text-white text-xs font-extrabold uppercase tracking-wider transition cursor-pointer active:scale-95 shadow-md shadow-pink-500/25 inline-flex items-center gap-1.5"
                                onclick="validateAndSubmitQuiz()">
                            <span>Nộp bài thi ngay</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- Modal 1: Cảnh báo khi ấn Header hoặc rời trang (Modern Light Card) -->
    <div id="leave-guard-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-7 space-y-5 text-left">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200/80 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 uppercase tracking-wider mb-1">
                        Cảnh báo quy chế thi
                    </span>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug">
                        Bắt buộc nộp bài trước khi rời trang!
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Bạn vừa bấm chuyển sang trang khác trong khi bài thi đang diễn ra.
                    </p>
                </div>
            </div>

            <div class="bg-rose-50/80 border border-rose-200/80 rounded-2xl p-4 space-y-2 text-rose-950">
                <p class="text-xs font-bold leading-relaxed text-rose-900">
                    Một khi đã bắt đầu làm bài thì không thể hủy giữa chừng! Nếu rời trang này, bạn <span class="underline font-black text-rose-700">bắt buộc phải nộp bài</span>.
                </p>
                <p class="text-[11px] text-slate-600 leading-normal">
                    Hệ thống sẽ tự động thu bài và chấm điểm toàn bộ các câu hỏi bạn đã làm cho đến thời điểm hiện tại.
                </p>
                <div id="leave-target-container" class="hidden pt-1.5 border-t border-rose-200/60 text-[11px] text-slate-500">
                    Trang định đến: <span id="leave-target-name" class="font-bold text-slate-800"></span>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" 
                        id="btn-stay-and-quiz"
                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider transition cursor-pointer text-center">
                    Ở lại tiếp tục làm bài
                </button>
                <button type="button" 
                        id="btn-force-submit-and-leave"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-md shadow-rose-600/25 cursor-pointer text-center">
                    Nộp bài &amp; Rời đi &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Cảnh báo khi chuyển qua Tab khác (Modern Light Card) -->
    <div id="tab-switch-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 sm:p-7 space-y-5 text-left">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/80 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 uppercase tracking-wider mb-1">
                        Cảnh báo vi phạm quy chế
                    </span>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug">
                        Phát hiện bạn vừa rời màn hình thi!
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Số lần hệ thống ghi nhận rời bài thi: <strong id="tab-switch-count-text" class="text-amber-600 text-sm font-extrabold">1</strong> lần
                    </p>
                </div>
            </div>

            <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-4 space-y-2 text-amber-950">
                <div class="p-2.5 rounded-xl bg-amber-100/60 border border-amber-200 text-xs text-amber-900 font-semibold flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0 animate-ping"></span>
                    <span>Lần cảnh cáo này đã được hệ thống ghi nhận và lưu lại vào kết quả bài thi.</span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed font-medium">
                    Quy chế thi trực tuyến yêu cầu học viên tập trung trên màn hình làm bài. Nếu tiếp tục chuyển tab hoặc chuyển ứng dụng, bạn sẽ bị buộc nộp bài ngay lập tức.
                </p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" 
                        id="btn-close-tab-warning"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-md shadow-pink-600/25 cursor-pointer text-center">
                    Tôi đã hiểu, ở lại làm tiếp &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Script đếm ngược thời gian, kiểm tra câu hỏi chưa khoanh và cuộn tới câu chưa làm -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('quiz-form');
            const totalQuestions = {{ $questions->count() }};
            let isSubmitting = false;

            // Xóa hiệu ứng cảnh báo của một câu hỏi khi đã chọn đáp án
            window.clearQuestionWarning = function(questionId) {
                const card = document.getElementById('question-card-' + questionId);
                if (card) {
                    card.classList.remove('ring-4', 'ring-rose-500/80', 'border-rose-500', 'bg-rose-50/20');
                    const badge = card.querySelector('.question-badge');
                    if (badge) {
                        badge.classList.remove('bg-rose-600');
                        badge.classList.add('bg-neutral-950');
                    }
                    const warn = document.getElementById('unanswered-warning-' + questionId);
                    if (warn) {
                        warn.classList.add('hidden');
                    }
                }
            };

            // Cập nhật tiến độ đã làm
            window.updateProgress = function(questionId) {
                const answeredRadios = document.querySelectorAll('input[type="radio"]:checked');
                const answeredCount = answeredRadios.length;
                const counter = document.getElementById('answered-count');
                const bottomCounter = document.getElementById('bottom-answered-count');
                const dot = document.getElementById('progress-indicator-dot');
                const unansweredBadge = document.getElementById('unanswered-badge');
                const unansweredCountText = document.getElementById('unanswered-count-text');
                const bottomUnansweredBadge = document.getElementById('bottom-unanswered-badge');
                const bottomUnansweredCount = document.getElementById('bottom-unanswered-count');
                const topBar = document.getElementById('top-progress-bar');

                if (counter) {
                    counter.innerText = answeredCount;
                }
                if (bottomCounter) {
                    bottomCounter.innerText = answeredCount;
                }

                if (topBar && totalQuestions > 0) {
                    topBar.style.width = Math.round((answeredCount / totalQuestions) * 100) + '%';
                }

                const remaining = totalQuestions - answeredCount;
                if (unansweredBadge && unansweredCountText) {
                    if (remaining > 0 && answeredCount > 0) {
                        unansweredBadge.classList.remove('hidden');
                        unansweredCountText.innerText = remaining;
                    } else if (remaining === 0) {
                        unansweredBadge.classList.add('hidden');
                    }
                }
                if (bottomUnansweredBadge && bottomUnansweredCount) {
                    if (remaining > 0 && answeredCount > 0) {
                        bottomUnansweredBadge.classList.remove('hidden');
                        bottomUnansweredCount.innerText = remaining;
                    } else if (remaining === 0) {
                        bottomUnansweredBadge.classList.add('hidden');
                    }
                }

                if (dot) {
                    if (answeredCount === totalQuestions && totalQuestions > 0) {
                        dot.classList.remove('bg-pink-500');
                        dot.classList.add('bg-emerald-400');
                    } else {
                        dot.classList.remove('bg-emerald-400');
                        dot.classList.add('bg-pink-500');
                    }
                }

                // Nếu vừa chọn đáp án cho câu hỏi này, xóa cảnh báo đỏ của câu đó
                if (questionId) {
                    clearQuestionWarning(questionId);
                }
            };

            // Kiểm tra câu hỏi còn thiếu, chuyển tới câu đó và yêu cầu khoanh đáp án
            window.validateAndSubmitQuiz = function() {
                const questionCards = document.querySelectorAll('fieldset[data-question-id]');
                let firstUnansweredCard = null;
                let unansweredNumbers = [];

                questionCards.forEach(card => {
                    const qId = card.getAttribute('data-question-id');
                    const qNum = card.getAttribute('data-question-number');
                    const checked = card.querySelector(`input[name="answers[${qId}]"]:checked`);
                    const warn = document.getElementById('unanswered-warning-' + qId);
                    const badge = card.querySelector('.question-badge');

                    if (!checked) {
                        unansweredNumbers.push(qNum);
                        if (!firstUnansweredCard) {
                            firstUnansweredCard = card;
                        }

                        // Đánh dấu viền đỏ & hiện thông báo dưới câu hỏi
                        card.classList.add('ring-4', 'ring-rose-500/80', 'border-rose-500', 'bg-rose-50/20');
                        if (badge) {
                            badge.classList.remove('bg-slate-900');
                            badge.classList.add('bg-rose-600');
                        }
                        if (warn) {
                            warn.classList.remove('hidden');
                        }
                    } else {
                        clearQuestionWarning(qId);
                    }
                });

                // Nếu còn câu chưa làm: chặn nộp, hiển thị cảnh báo và cuộn đến câu đầu tiên chưa làm
                if (firstUnansweredCard) {
                    const unansweredCount = unansweredNumbers.length;
                    const firstNum = firstUnansweredCard.getAttribute('data-question-number');

                    const alertBanner = document.getElementById('quiz-validation-alert');
                    const alertBadge = document.getElementById('quiz-alert-badge');
                    const alertMsg = document.getElementById('quiz-alert-message');

                    if (alertBanner && alertMsg) {
                        if (alertBadge) {
                            alertBadge.innerText = 'Còn ' + unansweredCount + ' câu';
                        }
                        alertMsg.innerHTML = 'Bạn còn <strong>' + unansweredCount + ' câu hỏi</strong> chưa khoanh đáp án (Câu: ' + unansweredNumbers.join(', ') + ').<br>Hệ thống đã tự động chuyển đến <strong>Câu số ' + firstNum + '</strong>, vui lòng chọn đáp án trước khi nộp bài!';
                        alertBanner.classList.remove('hidden');
                    }

                    // Cuộn mượt đến giữa màn hình câu hỏi chưa làm
                    firstUnansweredCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Focus vào đáp án đầu tiên để học viên thao tác thuận tiện
                    const firstRadio = firstUnansweredCard.querySelector('input[type="radio"]');
                    if (firstRadio) {
                        firstRadio.focus();
                    }

                    return false;
                }

                // Nếu đã khoanh đủ tất cả các câu:
                const alertBanner = document.getElementById('quiz-validation-alert');
                if (alertBanner) {
                    alertBanner.classList.add('hidden');
                }

                if (confirm('Bạn đã hoàn thành đầy đủ tất cả ' + totalQuestions + ' câu hỏi! Bạn có chắc chắn muốn nộp bài kiểm tra ngay bây giờ?')) {
                    isSubmitting = true;
                    form.submit();
                }

                return false;
            };

            // Ngăn submit form trực tiếp nếu còn câu chưa làm
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) return;
                    e.preventDefault();
                    validateAndSubmitQuiz();
                });
            }

            // Khởi tạo trạng thái ban đầu
            updateProgress();

            // Đếm ngược thời gian
            let totalSeconds = {{ $quiz->duration_minutes * 60 }};
            const timerEl = document.getElementById('countdown-timer');
            const timerBox = document.getElementById('timer-box');

            const timerInterval = setInterval(function() {
                totalSeconds--;

                if (totalSeconds <= 0) {
                    clearInterval(timerInterval);
                    timerEl.innerText = "00:00";
                    alert("Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài làm của bạn.");
                    isSubmitting = true;
                    form.submit();
                    return;
                }

                let minutes = Math.floor(totalSeconds / 60);
                let seconds = totalSeconds % 60;

                timerEl.innerText = 
                    (minutes < 10 ? "0" : "") + minutes + ":" + 
                    (seconds < 10 ? "0" : "") + seconds;

                if (totalSeconds < 120) {
                    timerBox.classList.add('border-rose-500/50', 'bg-rose-500/10');
                    timerEl.classList.remove('text-neutral-100');
                    timerEl.classList.add('text-rose-400');
                }
            }, 1000);

            // ========================================================
            // Quản lý Cảnh báo khi ấn Header hoặc Rời trang (Bắt buộc nộp bài)
            // ========================================================
            const leaveGuardModal = document.getElementById('leave-guard-modal');
            const leaveTargetContainer = document.getElementById('leave-target-container');
            const leaveTargetName = document.getElementById('leave-target-name');
            const btnStayAndQuiz = document.getElementById('btn-stay-and-quiz');
            const btnForceSubmitAndLeave = document.getElementById('btn-force-submit-and-leave');
            let pendingLeaveDestination = null;

            window.triggerLeaveQuiz = function(destinationUrl, destinationLabel) {
                pendingLeaveDestination = destinationUrl || null;
                if (destinationLabel && leaveTargetContainer && leaveTargetName) {
                    leaveTargetName.innerText = destinationLabel;
                    leaveTargetContainer.classList.remove('hidden');
                } else if (leaveTargetContainer) {
                    leaveTargetContainer.classList.add('hidden');
                }
                if (leaveGuardModal) {
                    leaveGuardModal.classList.remove('hidden');
                }
            };

            if (btnStayAndQuiz) {
                btnStayAndQuiz.addEventListener('click', function() {
                    if (leaveGuardModal) {
                        leaveGuardModal.classList.add('hidden');
                    }
                    pendingLeaveDestination = null;
                });
            }

            if (btnForceSubmitAndLeave) {
                btnForceSubmitAndLeave.addEventListener('click', function() {
                    // Bắt buộc nộp bài ngay lập tức khi rời đi
                    isSubmitting = true;
                    if (leaveGuardModal) {
                        leaveGuardModal.classList.add('hidden');
                    }
                    form.submit();
                });
            }

            // Chặn tất cả các click chuyển trang (đặc biệt là Header: Logo, Menu, Dropdown...)
            document.addEventListener('click', function(e) {
                if (isSubmitting) return;

                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('#')) {
                    return;
                }

                // Ngăn chặn điều hướng và mở cảnh báo bắt buộc nộp bài
                e.preventDefault();
                e.stopPropagation();

                const label = link.innerText.trim() || link.getAttribute('title') || href;
                triggerLeaveQuiz(link.href, label);
            }, true);

            // ========================================================
            // Quản lý Cảnh báo khi Chuyển sang Tab khác (visibilitychange)
            // ========================================================
            let tabSwitchCount = 0;
            const tabSwitchModal = document.getElementById('tab-switch-modal');
            const tabSwitchCountText = document.getElementById('tab-switch-count-text');
            const btnCloseTabWarning = document.getElementById('btn-close-tab-warning');

            document.addEventListener('visibilitychange', function() {
                if (isSubmitting) return;

                if (document.hidden) {
                    // Học viên vừa rời tab hoặc chuyển sang ứng dụng khác
                    tabSwitchCount++;
                    const hiddenWarningInput = document.getElementById('quiz-warning-count');
                    if (hiddenWarningInput) {
                        hiddenWarningInput.value = tabSwitchCount;
                    }
                } else {
                    // Học viên quay lại bài thi -> Hiện cảnh báo vi phạm quy chế
                    if (tabSwitchCount > 0) {
                        const hiddenWarningInput = document.getElementById('quiz-warning-count');
                        if (hiddenWarningInput) {
                            hiddenWarningInput.value = tabSwitchCount;
                        }
                        if (tabSwitchCountText) {
                            tabSwitchCountText.innerText = tabSwitchCount;
                        }
                        if (tabSwitchModal) {
                            tabSwitchModal.classList.remove('hidden');
                        }
                    }
                }
            });

            if (btnCloseTabWarning) {
                btnCloseTabWarning.addEventListener('click', function() {
                    if (tabSwitchModal) {
                        tabSwitchModal.classList.add('hidden');
                    }
                });
            }

            // Cảnh báo trình duyệt native khi người dùng reload hoặc đóng tab
            window.addEventListener('beforeunload', function(e) {
                if (!isSubmitting) {
                    e.preventDefault();
                    e.returnValue = 'Bài kiểm tra đang diễn ra! Nếu bạn rời đi hoặc chuyển trang, bạn sẽ bắt buộc phải nộp bài.';
                    return e.returnValue;
                }
            });
        });
    </script>
</x-app-layout>
