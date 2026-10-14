<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $quiz->type_badge_class }}">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-pink-50 text-pink-700">
                        {{ $quiz->course ? 'Khóa học: ' . $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight">
                    Kết quả làm bài: {{ $quiz->title }}
                </h2>
            </div>
            <a href="{{ route('instructor.dashboard', ['tab' => 'attempts']) }}" class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition whitespace-nowrap shrink-0">
                &larr; Quay lại danh sách Quizzes
            </a>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="w-full space-y-6">

            <!-- Thống kê tổng quan -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs">
                    <span class="text-xs text-slate-400 font-medium block">Tổng lượt nộp bài</span>
                    <strong class="text-2xl font-black text-slate-900 mt-1 block">{{ $attempts->count() }}</strong>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs">
                    <span class="text-xs text-slate-400 font-medium block">Học viên Đạt yêu cầu</span>
                    <strong class="text-2xl font-black text-emerald-600 mt-1 block">
                        {{ $attempts->where('is_passed', true)->count() }}
                    </strong>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs">
                    <span class="text-xs text-slate-400 font-medium block">Điểm trung bình</span>
                    <strong class="text-2xl font-black text-pink-600 mt-1 block">
                        {{ $attempts->count() > 0 ? number_format($attempts->avg('score'), 1) : '0.0' }} / 10
                    </strong>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-xs">
                    <span class="text-xs text-slate-400 font-medium block">Điểm chuẩn qua môn</span>
                    <strong class="text-2xl font-black text-slate-700 mt-1 block">
                        &ge; {{ $quiz->passing_score }} / 10
                    </strong>
                </div>
            </div>

            <!-- Bảng kết quả -->
            <div x-data="{ openAttemptId: null, hideWarningInModal: false }" class="bg-white rounded-3xl border border-pink-100 shadow-xs overflow-hidden">
                @if($attempts->isEmpty())
                    <div class="p-12 text-center">
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Chưa có học viên nào làm bài</h4>
                        <p class="text-slate-500 text-xs">Khi có học viên hoàn thành bài kiểm tra này, kết quả và bảng điểm sẽ được cập nhật tại đây.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 min-w-[180px]">Học viên</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số câu đúng</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Điểm số</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Kết quả</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Thời gian nộp</th>
                                    <th class="py-4 px-6 text-center whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($attempts as $attempt)
                                    <tr class="hover:bg-pink-50/30 transition">
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <div class="font-bold text-slate-900">{{ $attempt->student->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-500">{{ $attempt->student->email ?? '' }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-center font-medium whitespace-nowrap">
                                            {{ $attempt->correct_answers }} / {{ $attempt->total_questions }} câu
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            <span class="font-black text-base {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ number_format($attempt->score, 1) }}
                                            </span>
                                            <span class="text-xs text-slate-400">/10</span>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            @if($attempt->is_passed)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    &check; ĐẠT
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    &cross; CHƯA ĐẠT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-right text-xs text-slate-500 whitespace-nowrap">
                                            {{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : $attempt->created_at->format('H:i d/m/Y') }}
                                        </td>
                                        <td class="py-4 px-6 text-center whitespace-nowrap">
                                            <button type="button" 
                                                    @click="openAttemptId = {{ $attempt->id }}"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black bg-neutral-900 hover:bg-pink-600 text-white transition shadow-2xs cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span>Xem</span>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Chi tiết Bài làm của Học viên -->
                                    <div x-show="openAttemptId === {{ $attempt->id }}" 
                                         x-cloak
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-950/80 backdrop-blur-sm"
                                         style="display: none;">
                                        <div @click.away="openAttemptId = null"
                                             class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col text-left">
                                            
                                            <!-- Header Modal -->
                                            <div class="px-6 py-4 bg-neutral-950 text-white flex items-center justify-between gap-4 shrink-0">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $attempt->is_passed ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                                                            {{ $attempt->is_passed ? 'ĐẠT' : 'CHƯA ĐẠT' }} &bull; {{ number_format($attempt->score, 1) }}/10
                                                        </span>
                                                        <span class="text-xs text-neutral-400 font-semibold truncate">
                                                            Đúng: {{ $attempt->correct_answers }}/{{ $attempt->total_questions }} câu
                                                        </span>
                                                    </div>
                                                    <h3 class="text-base font-black text-white truncate">
                                                        Bài làm của: {{ $attempt->student->name ?? 'N/A' }}
                                                    </h3>
                                                    <p class="text-xs text-neutral-400">
                                                        Email: {{ $attempt->student->email ?? '' }} &bull; Nộp lúc: {{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : $attempt->created_at->format('H:i d/m/Y') }}
                                                    </p>
                                                </div>

                                                <button type="button" 
                                                        @click="openAttemptId = null"
                                                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition shrink-0 cursor-pointer"
                                                        title="Đóng cửa sổ">
                                                    &times;
                                                </button>
                                            </div>

                                            <!-- Body Modal (Cuộn nội dung) -->
                                            <div class="p-6 space-y-6 overflow-y-auto flex-1">

                                                <!-- Khối Cảnh báo vi phạm (Có nút icon trắng đen góc phải trên để ẩn/hiện) -->
                                                <div class="bg-white border-2 border-dashed {{ ($attempt->warning_count ?? 0) > 0 ? 'border-amber-400 bg-amber-50/30' : 'border-slate-300 bg-slate-50/50' }} p-5 rounded-2xl relative shadow-xs">
                                                    <!-- Nút icon trắng đen nhỏ ở trên góc bên phải -->
                                                    <div class="absolute top-4 right-4">
                                                        <button type="button" 
                                                                @click="hideWarningInModal = !hideWarningInModal"
                                                                class="w-7 h-7 rounded bg-black text-white hover:bg-neutral-800 border border-neutral-700 flex items-center justify-center transition cursor-pointer shadow-xs"
                                                                :title="hideWarningInModal ? 'Hiện lại số lần cảnh cáo' : 'Ẩn số lần cảnh cáo'">
                                                            <template x-if="!hideWarningInModal">
                                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                                                </svg>
                                                            </template>
                                                            <template x-if="hideWarningInModal">
                                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                                </svg>
                                                            </template>
                                                        </button>
                                                    </div>

                                                    <div class="pr-10">
                                                        <span class="text-[10px] font-black uppercase tracking-wider text-neutral-800 bg-neutral-200 px-2 py-0.5 rounded inline-block mb-1.5">
                                                            GIÁM SÁT VI PHẠM (ADMIN &amp; GIẢNG VIÊN)
                                                        </span>
                                                        <div class="flex items-center gap-3">
                                                            <div class="w-10 h-10 rounded-xl {{ ($attempt->warning_count ?? 0) > 0 ? 'bg-amber-100 text-amber-700 border border-amber-300' : 'bg-emerald-100 text-emerald-700 border border-emerald-300' }} flex items-center justify-center shrink-0">
                                                                @if(($attempt->warning_count ?? 0) > 0)
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                    </svg>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <span class="text-xs font-bold text-slate-700 block">Số lần học viên bị cảnh cáo chuyển tab:</span>
                                                                <div class="mt-0.5 flex items-baseline gap-1.5">
                                                                    <span x-show="!hideWarningInModal" class="text-2xl font-black font-mono {{ ($attempt->warning_count ?? 0) > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                                                                        :{{ $attempt->warning_count ?? 0 }}
                                                                    </span>
                                                                    <span x-show="hideWarningInModal" x-cloak class="inline-block px-2.5 py-0.5 bg-neutral-200 text-neutral-600 rounded text-xs font-mono font-bold tracking-widest">
                                                                        •••• (Đã ẩn)
                                                                    </span>
                                                                    <span x-show="!hideWarningInModal" class="text-xs text-slate-500 font-semibold">lần</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="text-[11px] text-slate-400 mt-2">
                                                            * Thông tin này được bảo mật và <strong>không hiển thị cho học viên xem</strong>.
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Danh sách chi tiết câu hỏi & bài làm của học viên -->
                                                <div class="space-y-4">
                                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 border-b border-slate-200 pb-2">
                                                        Đối chiếu từng câu hỏi (Học viên đã làm gì)
                                                    </h4>

                                                    @php
                                                        $userAnswers = $attempt->answers ?? [];
                                                    @endphp

                                                    @foreach($quiz->questions as $qIndex => $q)
                                                        @php
                                                            $selectedOptId = $userAnswers[$q->id] ?? null;
                                                            $correctOpt = $q->options->firstWhere('is_correct', true);
                                                            $isCorrect = $selectedOptId && $correctOpt && ((int)$selectedOptId === (int)$correctOpt->id);
                                                        @endphp
                                                        <div class="p-4 rounded-2xl border {{ $isCorrect ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} space-y-3">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="flex items-start gap-2.5">
                                                                    <span class="w-6 h-6 rounded-lg {{ $isCorrect ? 'bg-emerald-600' : 'bg-rose-600' }} text-white text-xs font-black flex items-center justify-center shrink-0">
                                                                        {{ $qIndex + 1 }}
                                                                    </span>
                                                                    <h5 class="text-sm font-bold text-slate-900">{{ $q->question_text }}</h5>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isCorrect ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                                    {{ $isCorrect ? 'Đúng' : 'Sai' }}
                                                                </span>
                                                            </div>

                                                            <!-- Các lựa chọn đáp án -->
                                                            <div class="space-y-1.5 pl-8 text-xs">
                                                                @foreach($q->options as $optIdx => $opt)
                                                                    @php
                                                                        $optLetter = chr(65 + $optIdx);
                                                                        $isSelected = ((int)$selectedOptId === (int)$opt->id);
                                                                        $isOptCorrect = $opt->is_correct;
                                                                    @endphp
                                                                    <div class="p-2.5 rounded-xl border flex items-center justify-between
                                                                        @if($isOptCorrect)
                                                                            bg-emerald-50 border-emerald-300 text-emerald-950 font-bold
                                                                        @elseif($isSelected && !$isOptCorrect)
                                                                            bg-rose-50 border-rose-300 text-rose-950 font-bold
                                                                        @else
                                                                            bg-white border-slate-200 text-slate-600
                                                                        @endif">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="w-5 h-5 rounded flex items-center justify-center text-[10px] font-black {{ $isOptCorrect ? 'bg-emerald-600 text-white' : ($isSelected ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600') }}">
                                                                                {{ $optLetter }}
                                                                            </span>
                                                                            <span>{{ $opt->option_text }}</span>
                                                                        </div>
                                                                        <div class="text-[11px] font-bold">
                                                                            @if($isOptCorrect)
                                                                                <span class="text-emerald-700">&bull; Đáp án chính xác</span>
                                                                            @elseif($isSelected)
                                                                                <span class="text-rose-600">&bull; Học viên đã chọn</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            @if($q->explanation)
                                                                <div class="ml-8 p-3 rounded-xl bg-amber-50/70 border border-amber-200/80 text-xs text-slate-800">
                                                                    <strong class="text-amber-900 block mb-0.5 uppercase tracking-wider text-[10px] font-black">Giải thích chi tiết:</strong>
                                                                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $q->explanation }}</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                            </div>

                                            <!-- Footer Modal -->
                                            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
                                                <a href="{{ route('student.quizzes.result', [$quiz->id, $attempt->id]) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white rounded-xl text-xs font-bold transition">
                                                    <span>Mở trang chi tiết đầy đủ</span>
                                                    <span>&rarr;</span>
                                                </a>
                                                <button type="button" 
                                                        @click="openAttemptId = null"
                                                        class="px-5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                                                    Đóng
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-teacher-layout>
