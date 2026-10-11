<x-app-layout>
    <!-- Quiz Result Header Banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10" x-data="{ confirmModalOpen: false }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider bg-pink-600 text-white">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="text-xs text-neutral-400 font-bold">
                        {{ $quiz->course ? 'Khóa học: ' . $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $quiz->title }}
                </h1>
                <p class="text-xs text-neutral-400 mt-1">
                    Nộp bài lúc: {{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : $attempt->created_at->format('H:i d/m/Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('student.quizzes.index') }}" 
                   class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider border border-white/20 hover:border-white text-neutral-300 hover:text-white transition">
                    &larr; Quizzes
                </a>
                @if(!$quiz->isExpired() && !$quiz->isNotStarted())
                    <button type="button" 
                            @click="confirmModalOpen = true"
                            class="px-5 py-2.5 text-xs font-black uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white transition cursor-pointer">
                        Làm lại bài thi
                    </button>
                @elseif($quiz->isExpired())
                    <span class="px-4 py-2.5 text-xs font-black uppercase tracking-wider bg-neutral-800 text-neutral-400 border border-neutral-700 cursor-not-allowed">
                        Đã hết hạn thi
                    </span>
                @endif
            </div>
        </div>

        <!-- Modal Xác nhận Làm lại Bài Kiểm tra -->
        <div x-show="confirmModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-950/80 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="confirmModalOpen = false"
                 class="w-full max-w-lg bg-neutral-900 border-2 border-pink-600 text-white shadow-2xl p-6 sm:p-8 space-y-6 text-left">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-pink-600/20 border border-pink-500 text-pink-500 flex items-center justify-center shrink-0 text-2xl font-black">
                        ⚠️
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] uppercase font-black tracking-widest text-pink-400 block mb-1">Xác nhận làm lại đề thi</span>
                        <h3 class="text-lg sm:text-xl font-black text-white leading-snug line-clamp-2">
                            {{ $quiz->title }}
                        </h3>
                        <p class="text-xs text-neutral-400 mt-1">
                            Thời gian: <strong class="text-pink-400">{{ $quiz->duration_minutes }} phút</strong>
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
                    <a href="{{ route('student.quizzes.take', $quiz->id) }}"
                       class="w-full sm:w-auto px-7 py-2.5 bg-pink-600 hover:bg-pink-500 text-white text-xs font-black uppercase tracking-wider transition shadow-lg shadow-pink-600/30 cursor-pointer text-center">
                        Làm &rarr;
                    </a>
                </div>

            </div>
        </div>
    </section>

    <div class="py-10 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @php
                $canViewResultDetails = $quiz->show_result_after_completion || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTeacher() || auth()->user()->isInstructor()));
            @endphp

            @if($canViewResultDetails)
                <!-- Card Bảng điểm & Tổng quan kết quả -->
                <div class="bg-white border border-pink-100 p-8 sm:p-10 text-center relative shadow-sm">
                    <div class="inline-block px-3 py-1 text-xs font-black uppercase tracking-widest mb-4 {{ $attempt->is_passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $attempt->is_passed ? 'KẾT QUẢ: ĐẠT YÊU CẦU' : 'KẾT QUẢ: CHƯA ĐẠT' }}
                    </div>

                    <div class="flex items-baseline justify-center gap-2 mb-6">
                        <span class="text-6xl sm:text-7xl font-black tracking-tight {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ number_format($attempt->score, 1) }}
                        </span>
                        <span class="text-2xl font-bold text-neutral-400">/ 10 điểm</span>
                    </div>

                    <!-- 3 Thống kê chi tiết -->
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-px bg-neutral-200 border border-neutral-200 max-w-xl mx-auto text-center text-xs">
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Số câu đúng</dt>
                            <dd class="text-base font-black text-neutral-900 mt-1">
                                {{ $attempt->correct_answers }} / {{ $attempt->total_questions }}
                            </dd>
                        </div>
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Tỷ lệ chính xác</dt>
                            <dd class="text-base font-black text-pink-600 mt-1">
                                {{ $attempt->total_questions > 0 ? round(($attempt->correct_answers / $attempt->total_questions) * 100) : 0 }}%
                            </dd>
                        </div>
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Điểm chuẩn qua môn</dt>
                            <dd class="text-base font-black text-neutral-900 mt-1">
                                &ge; {{ $quiz->passing_score }}/10
                            </dd>
                        </div>
                    </dl>
                </div>
            @else
                <!-- Thông báo bảo mật kết quả khi giảng viên chọn Không cho xem kết quả -->
                <div class="bg-white border-2 border-dashed border-pink-200 p-8 sm:p-12 text-center space-y-4 shadow-sm">
                    <div class="inline-block px-3.5 py-1 text-xs font-black uppercase tracking-widest bg-blue-100 text-blue-800 rounded-full">
                        ĐÃ NỘP BÀI THÀNH CÔNG
                    </div>
                    <div class="w-16 h-16 rounded-2xl bg-pink-50 border border-pink-200 text-pink-600 flex items-center justify-center mx-auto text-3xl font-black shadow-xs">
                        🔒
                    </div>
                    <div class="max-w-md mx-auto space-y-1.5">
                        <h2 class="text-xl font-black text-neutral-900">
                            Kết quả bài thi được bảo mật
                        </h2>
                        <p class="text-xs text-neutral-600 leading-relaxed">
                            Giảng viên đã cài đặt <strong class="text-neutral-900">không công khai kết quả và đáp án chi tiết</strong> sau khi hoàn thành cho bài kiểm tra này.
                        </p>
                        <p class="text-[11px] text-neutral-400 leading-relaxed">
                            Toàn bộ bài làm của bạn đã được ghi nhận an toàn trên hệ thống và sẽ được giảng viên tổng kết sau.
                        </p>
                    </div>
                </div>
            @endif

            @if($canViewResultDetails)
                <!-- Chi tiết từng câu hỏi và đối chiếu đáp án -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-pink-100 pb-3">
                    <h2 class="text-lg font-black text-neutral-900 uppercase tracking-wider">
                        Đối chiếu đáp án chi tiết
                    </h2>
                    <span class="text-xs text-neutral-500 font-semibold">
                        {{ $attempt->total_questions ?? $quiz->questions->count() }} câu hỏi
                    </span>
                </div>

                @php
                    $userAnswers = $attempt->answers ?? [];
                    $attemptQuestionIds = array_keys($userAnswers);
                    $resultQuestions = (!empty($attemptQuestionIds) && count($attemptQuestionIds) < $quiz->questions->count())
                        ? $quiz->questions->whereIn('id', $attemptQuestionIds)->values()
                        : $quiz->questions;
                @endphp

                @foreach($resultQuestions as $index => $question)
                    @php
                        $selectedOptionId = $userAnswers[$question->id] ?? null;
                        $correctOption = $question->options->firstWhere('is_correct', true);
                        $isAnswerCorrect = $selectedOptionId && $correctOption && ((int)$selectedOptionId === (int)$correctOption->id);
                    @endphp

                    <div class="bg-white border {{ $isAnswerCorrect ? 'border-emerald-300' : 'border-rose-300' }} p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-4 mb-4 pb-3 border-b border-neutral-100">
                            <div class="flex items-start gap-3">
                                <span class="flex items-center justify-center w-7 h-7 {{ $isAnswerCorrect ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} font-black text-xs shrink-0">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <h3 class="text-base font-black text-neutral-900 leading-snug pt-0.5">
                                    {{ $question->question_text }}
                                </h3>
                            </div>

                            @if($isAnswerCorrect)
                                <span class="shrink-0 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                    Đúng
                                </span>
                            @else
                                <span class="shrink-0 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                    Sai
                                </span>
                            @endif
                        </div>

                        <!-- Các đáp án -->
                        <div class="space-y-2 mb-4">
                            @foreach($question->options as $optIndex => $option)
                                @php
                                    $optionLetter = chr(65 + $optIndex);
                                    $isSelected = ((int)$selectedOptionId === (int)$option->id);
                                    $isThisOptionCorrect = $option->is_correct;
                                @endphp

                                <div class="flex items-center justify-between p-3.5 text-sm border 
                                    @if($isThisOptionCorrect)
                                        bg-emerald-50 border-emerald-400 text-emerald-950 font-medium
                                    @elseif($isSelected && !$isThisOptionCorrect)
                                        bg-rose-50 border-rose-400 text-rose-950 font-medium
                                    @else
                                        bg-neutral-50/50 border-neutral-200 text-neutral-700
                                    @endif
                                ">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 flex items-center justify-center font-black text-xs 
                                            @if($isThisOptionCorrect)
                                                bg-emerald-600 text-white
                                            @elseif($isSelected)
                                                bg-rose-600 text-white
                                            @else
                                                bg-neutral-200 text-neutral-700
                                            @endif
                                        ">
                                            {{ $optionLetter }}
                                        </span>
                                        <span>{{ $option->option_text }}</span>
                                    </div>

                                    <div class="text-xs font-bold">
                                        @if($isThisOptionCorrect)
                                            <span class="text-emerald-700">&bull; Đáp án chính xác</span>
                                        @elseif($isSelected)
                                            <span class="text-rose-600">&bull; Bạn đã chọn</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Lời giải thích của giảng viên -->
                        @if($question->explanation)
                            <div class="p-3.5 bg-neutral-950 text-white text-xs border-l-2 border-pink-500">
                                <strong class="text-pink-400 block mb-0.5 uppercase tracking-wider text-[10px]">Giải thích:</strong>
                                <p class="text-neutral-300 leading-relaxed">{{ $question->explanation }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            <div class="text-center pt-4">
                <a href="{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}" 
                   class="inline-block px-8 py-3.5 bg-neutral-950 hover:bg-pink-600 text-white font-extrabold text-xs uppercase tracking-wider transition">
                    &larr; {{ $quiz->course_id ? 'Quay lại khóa học' : 'Quay lại danh sách quizzes' }}
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
