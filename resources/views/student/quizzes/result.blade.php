<x-app-layout>
    <!-- Quiz Result Header Banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10">
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
                <a href="{{ route('student.quizzes.take', $quiz->id) }}" 
                   class="px-5 py-2.5 text-xs font-black uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white transition">
                    Làm lại bài thi
                </a>
            </div>
        </div>
    </section>

    <div class="py-10 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

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

            <!-- Chi tiết từng câu hỏi và đối chiếu đáp án -->
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-pink-100 pb-3">
                    <h2 class="text-lg font-black text-neutral-900 uppercase tracking-wider">
                        Đối chiếu đáp án chi tiết
                    </h2>
                    <span class="text-xs text-neutral-500 font-semibold">
                        {{ $quiz->questions->count() }} câu hỏi
                    </span>
                </div>

                @php
                    $userAnswers = $attempt->answers ?? [];
                @endphp

                @foreach($quiz->questions as $index => $question)
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

            <div class="text-center pt-4">
                <a href="{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}" 
                   class="inline-block px-8 py-3.5 bg-neutral-950 hover:bg-pink-600 text-white font-extrabold text-xs uppercase tracking-wider transition">
                    &larr; {{ $quiz->course_id ? 'Quay lại khóa học' : 'Quay lại danh sách quizzes' }}
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
