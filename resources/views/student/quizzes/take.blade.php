<x-app-layout>
    <!-- Quiz Header Banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10 sticky top-16 z-30">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-pink-600 text-white">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="text-xs text-neutral-400 font-bold">
                        {{ $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white line-clamp-1">
                    {{ $quiz->title }}
                </h1>
            </div>

            <!-- Digital Countdown Timer -->
            <div id="timer-box" class="flex items-center gap-3 bg-white/5 border border-white/15 px-4 py-2 self-start sm:self-auto shrink-0">
                <div class="w-2 h-2 rounded-full bg-pink-500 animate-pulse"></div>
                <div class="text-left">
                    <span class="text-[9px] text-neutral-400 uppercase font-extrabold tracking-widest block leading-none">Thời gian còn lại</span>
                    <span id="countdown-timer" class="font-mono font-black text-xl text-pink-400 leading-tight">
                        {{ sprintf('%02d:00', $quiz->duration_minutes) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Alert Banner khi còn câu hỏi chưa làm -->
    <div id="quiz-validation-alert" class="hidden fixed top-24 left-1/2 -translate-x-1/2 z-50 max-w-xl w-[92%] sm:w-auto bg-neutral-900 border-2 border-rose-500 text-white px-5 py-4 shadow-2xl rounded-2xl flex items-center justify-between gap-4 transition-all duration-300">
        <div class="flex items-center gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 font-black text-base shadow-xs">
                !
            </div>
            <div>
                <div class="text-xs font-black text-rose-300 uppercase tracking-wider flex items-center gap-2">
                    <span>Chưa hoàn thành bài thi</span>
                    <span id="quiz-alert-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-950 text-rose-200 border border-rose-700"></span>
                </div>
                <div id="quiz-alert-message" class="text-xs text-neutral-200 mt-0.5 font-medium leading-relaxed">
                    Vui lòng chọn đáp án trước khi nộp bài!
                </div>
            </div>
        </div>
        <button type="button" 
                onclick="document.getElementById('quiz-validation-alert').classList.add('hidden')" 
                class="text-neutral-400 hover:text-white text-xl font-bold leading-none p-1 shrink-0 cursor-pointer" 
                title="Đóng thông báo">
            &times;
        </button>
    </div>

    <div class="py-10 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Quy chế làm bài -->
            <div class="bg-white border-l-4 border-pink-600 border-y border-r border-pink-100 p-6 shadow-sm">
                <h2 class="text-xs font-black uppercase tracking-[0.2em] text-neutral-900 mb-2">Quy chế thi trực tuyến</h2>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-neutral-600 font-medium">
                    <li>&bull; Số lượng: <strong>{{ $questions->count() }} câu hỏi trắc nghiệm</strong></li>
                    <li>&bull; Điểm chuẩn qua môn: <strong class="text-pink-600">&ge; {{ $quiz->passing_score }}/10 điểm</strong></li>
                    <li>&bull; Chấm điểm: <strong>Tự động &amp; hiển thị đáp án ngay khi nộp</strong></li>
                    @if($quiz->randomize_questions)
                        <li>&bull; Thứ tự: <strong>Câu hỏi được đảo ngẫu nhiên</strong></li>
                    @endif
                </ul>
            </div>

            <!-- Form nộp bài quiz -->
            <form id="quiz-form" action="{{ route('student.quizzes.submit', $quiz->id) }}" method="POST" class="space-y-6">
                @csrf

                @foreach($questions as $index => $question)
                    <fieldset id="question-card-{{ $question->id }}"
                              data-question-id="{{ $question->id }}"
                              data-question-number="{{ $index + 1 }}"
                              class="question-card bg-white border border-pink-100 p-6 sm:p-8 hover:border-pink-300 transition-all duration-300 scroll-mt-28 sm:scroll-mt-32">
                        <!-- Tiêu đề câu hỏi -->
                        <legend class="sr-only">Câu hỏi số {{ $index + 1 }}</legend>
                        <div class="flex items-start gap-4 mb-5 pb-4 border-b border-neutral-100">
                            <span class="question-badge flex items-center justify-center w-8 h-8 bg-neutral-950 text-white font-black text-xs shrink-0 transition-colors">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="flex-grow pt-0.5">
                                <h3 class="text-base sm:text-lg font-black text-neutral-900 leading-snug">
                                    {{ $question->question_text }}
                                </h3>
                            </div>
                        </div>

                        <!-- Cảnh báo chưa chọn đáp án cho câu hỏi này (ẩn mặc định) -->
                        <div id="unanswered-warning-{{ $question->id }}" class="unanswered-warning hidden mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2.5 animate-pulse">
                            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Câu hỏi số {{ $index + 1 }} chưa được chọn đáp án. Vui lòng khoanh chọn 1 đáp án dưới đây trước khi nộp bài!</span>
                        </div>

                        <!-- Danh sách các lựa chọn đáp án -->
                        <div class="space-y-3">
                            @foreach($question->options as $optIndex => $option)
                                @php
                                    $optionLetter = chr(65 + $optIndex);
                                @endphp
                                <label class="group relative flex items-center gap-4 p-4 border border-neutral-200 hover:border-pink-500 hover:bg-pink-50/30 cursor-pointer transition has-[:checked]:border-pink-600 has-[:checked]:bg-pink-50/50 has-[:checked]:ring-1 has-[:checked]:ring-pink-600">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           value="{{ $option->id }}" 
                                           class="w-4 h-4 text-pink-600 border-neutral-300 focus:ring-pink-500"
                                           onchange="updateProgress({{ $question->id }})">
                                    <span class="w-7 h-7 flex items-center justify-center bg-neutral-100 group-hover:bg-pink-200 group-has-[:checked]:bg-neutral-950 group-has-[:checked]:text-white font-black text-xs text-neutral-700 transition">
                                        {{ $optionLetter }}
                                    </span>
                                    <span class="text-sm sm:text-base text-neutral-800 group-has-[:checked]:text-neutral-950 font-medium">
                                        {{ $option->option_text }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <!-- Thanh xác nhận nộp bài cố định dưới màn hình -->
                <div class="sticky bottom-6 z-20 bg-neutral-950 text-white p-4 sm:p-5 border border-white/10 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3 text-xs uppercase tracking-wider font-bold">
                        <span id="progress-indicator-dot" class="w-2.5 h-2.5 bg-pink-500 rounded-full transition-colors shrink-0"></span>
                        <span>Đã làm: <strong id="answered-count" class="text-pink-400 text-sm font-black">0</strong> / {{ $questions->count() }} câu</span>
                        <span id="unanswered-badge" class="hidden px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                            Còn <span id="unanswered-count-text">0</span> câu chưa khoanh
                        </span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}" 
                           class="w-1/2 sm:w-auto text-center px-5 py-3 text-xs font-bold uppercase tracking-wider border border-white/20 hover:border-white text-neutral-300 hover:text-white transition"
                           onclick="return confirm('Bạn có chắc chắn muốn thoát? Các câu trả lời chưa nộp sẽ bị mất.')">
                            Hủy bỏ
                        </a>
                        <button type="button" 
                                id="btn-submit-quiz"
                                class="w-1/2 sm:w-auto px-7 py-3 bg-pink-600 hover:bg-pink-500 text-white text-xs font-black uppercase tracking-wider transition cursor-pointer active:scale-95 shadow-xs"
                                onclick="validateAndSubmitQuiz()">
                            Nộp bài thi &rarr;
                        </button>
                    </div>
                </div>

            </form>

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
                const dot = document.getElementById('progress-indicator-dot');
                const unansweredBadge = document.getElementById('unanswered-badge');
                const unansweredCountText = document.getElementById('unanswered-count-text');

                if (counter) {
                    counter.innerText = answeredCount;
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
                            badge.classList.remove('bg-neutral-950');
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
                    timerBox.classList.add('border-rose-500', 'bg-rose-950/40');
                    timerEl.classList.remove('text-pink-400');
                    timerEl.classList.add('text-rose-400');
                }
            }, 1000);
        });
    </script>
</x-app-layout>
