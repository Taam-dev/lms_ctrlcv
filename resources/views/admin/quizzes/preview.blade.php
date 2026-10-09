<x-admin-layout breadcrumb="Xem trước Đề thi">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $quiz->type_badge_class }}">
                        {{ $quiz->type_name }}
                    </span>
                    <span>Khóa học: {{ $quiz->course->title ?? 'Tự do (Không thuộc khóa học)' }}</span>
                    <span>&bull;</span>
                    <span>Giảng viên: {{ $quiz->teacher->name ?? 'N/A' }}</span>
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Xem Trước Đề Thi: {{ $quiz->title }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.quizzes.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition whitespace-nowrap shrink-0">
                    &larr; Về danh sách Quizzes
                </a>

                {{-- 1. KHI MỚI TẠO (CHỜ DUYỆT): HIỆN DUYỆT & TỪ CHỐI --}}
                @if($quiz->status === 'pending')
                    <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            &check; Phê duyệt đề thi này
                        </button>
                    </form>

                    <form action="{{ route('admin.quizzes.reject', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn từ chối bài kiểm tra này?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            &cross; Từ chối đề thi
                        </button>
                    </form>

                {{-- 2. KHI ĐÃ ĐƯỢC DUYỆT: HIỆN CHỮ LOẠI BỎ (KHÔNG ĐỂ TỪ CHỐI) --}}
                @elseif($quiz->status === 'approved')
                    <form action="{{ route('admin.quizzes.remove', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn loại bỏ đề thi này khỏi hệ thống? Học viên sẽ không thể làm bài kiểm tra này nữa.')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0" title="Loại bỏ đề thi đã duyệt">
                            
                            Loại bỏ đề thi này
                        </button>
                    </form>

                {{-- 3. KHI ĐÃ BỊ TỪ CHỐI / LOẠI BỎ: HIỆN DUYỆT LẠI --}}
                @else
                    <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            &check; Phê duyệt lại đề thi
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Thông số bài thi -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div>
                <span class="text-xs text-slate-400 block mb-1">Thời gian làm bài</span>
                <strong class="text-base text-slate-900 font-black">{{ $quiz->duration_minutes }} phút</strong>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Điểm đạt qua môn</span>
                <strong class="text-base text-pink-600 font-black">&ge; {{ $quiz->passing_score }}/10</strong>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Xáo trộn câu hỏi</span>
                <strong class="text-base {{ $quiz->randomize_questions ? 'text-emerald-600' : 'text-slate-500' }} font-black">
                    {{ $quiz->randomize_questions ? 'Có (Bật)' : 'Không (Tắt)' }}
                </strong>
            </div>
            <div>
                <span class="text-xs text-slate-400 block mb-1">Trạng thái</span>
                @if($quiz->status === 'pending')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        Chờ duyệt
                    </span>
                @elseif($quiz->status === 'approved')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Đã duyệt
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Đã loại bỏ
                    </span>
                @endif
            </div>
        </div>

        <!-- Danh sách câu hỏi và đáp án chi tiết -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>Nội dung chi tiết {{ $quiz->questions->count() }} câu hỏi trong đề thi</span>
                </h3>
                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="text-xs font-bold text-pink-600 hover:text-pink-800 transition">
                    + Chỉnh sửa câu hỏi &rarr;
                </a>
            </div>

            @if($quiz->questions->isEmpty())
                <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center text-slate-500">
                    <p class="text-sm font-semibold mb-3">Bài kiểm tra này chưa có câu hỏi nào.</p>
                    <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                        + Soạn câu hỏi ngay
                    </a>
                </div>
            @else
                @foreach($quiz->questions as $index => $question)
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-900 text-base leading-snug">
                                    {{ $question->question_text }}
                                </h4>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs pt-1">
                            @foreach($question->options as $optIndex => $option)
                                <div class="flex items-center gap-2.5 p-3 rounded-2xl border transition {{ $option->is_correct ? 'bg-emerald-50/80 border-emerald-300 text-emerald-950 font-bold shadow-2xs' : 'bg-slate-50/70 border-slate-200/70 text-slate-700' }}">
                                    <span class="w-6 h-6 rounded-lg flex items-center justify-center font-black text-[11px] {{ $option->is_correct ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-200 text-slate-700' }}">
                                        {{ chr(65 + $optIndex) }}
                                    </span>
                                    <span class="flex-grow">{{ $option->option_text }}</span>
                                    @if($option->is_correct)
                                        <span class="text-emerald-700 font-extrabold text-xs">&check; Đáp án Đúng</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($question->explanation)
                            <div class="p-3 rounded-xl bg-pink-50/70 text-xs text-pink-900 border border-pink-100 flex items-start gap-2">
                                <span class="font-bold shrink-0">Giải thích:</span>
                                <span>{{ $question->explanation }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

    </div>
</x-admin-layout>
