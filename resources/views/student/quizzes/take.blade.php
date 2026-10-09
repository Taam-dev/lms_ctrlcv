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
                    <fieldset class="bg-white border border-pink-100 p-6 sm:p-8 hover:border-pink-300 transition-colors">
                        <!-- Tiêu đề câu hỏi -->
                        <legend class="sr-only">Câu hỏi số {{ $index + 1 }}</legend>
                        <div class="flex items-start gap-4 mb-6 pb-4 border-b border-neutral-100">
                            <span class="flex items-center justify-center w-8 h-8 bg-neutral-950 text-white font-black text-xs shrink-0">
                                {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <div class="flex-grow pt-0.5">
                                <h3 class="text-base sm:text-lg font-black text-neutral-900 leading-snug">
                                    {{ $question->question_text }}
                                </h3>
                            </div>
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
                                           onchange="updateProgress()">
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
                    <div class="flex items-center gap-3 text-xs uppercase tracking-wider font-bold">
                        <span class="w-2.5 h-2.5 bg-pink-500"></span>
                        <span>Đã làm: <strong id="answered-count" class="text-pink-400 text-sm font-black">0</strong> / {{ $questions->count() }} câu</span>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}" 
                           class="w-1/2 sm:w-auto text-center px-5 py-3 text-xs font-bold uppercase tracking-wider border border-white/20 hover:border-white text-neutral-300 hover:text-white transition"
                           onclick="return confirm('Bạn có chắc chắn muốn thoát? Các câu trả lời chưa nộp sẽ bị mất.')">
                            Hủy bỏ
                        </a>
                        <button type="submit" 
                                class="w-1/2 sm:w-auto px-7 py-3 bg-pink-600 hover:bg-pink-500 text-white text-xs font-black uppercase tracking-wider transition cursor-pointer"
                                onclick="return confirm('Bạn có chắc chắn muốn nộp bài kiểm tra ngay bây giờ?')">
                            Nộp bài thi &rarr;
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- Script đếm ngược thời gian và cập nhật tiến độ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.updateProgress = function() {
                const answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
                const counter = document.getElementById('answered-count');
                if (counter) {
                    counter.innerText = answeredCount;
                }
            };
            updateProgress();

            let totalSeconds = {{ $quiz->duration_minutes * 60 }};
            const timerEl = document.getElementById('countdown-timer');
            const timerBox = document.getElementById('timer-box');
            const form = document.getElementById('quiz-form');

            const timerInterval = setInterval(function() {
                totalSeconds--;

                if (totalSeconds <= 0) {
                    clearInterval(timerInterval);
                    timerEl.innerText = "00:00";
                    alert("Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài làm của bạn.");
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
