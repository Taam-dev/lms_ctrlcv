<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    
                    Quản lý Bài kiểm tra & Quizzes (Giảng viên)
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tạo các đề thi trắc nghiệm, tùy chỉnh đảo ngẫu nhiên câu hỏi và quản lý câu hỏi cho học viên.
                </p>
            </div>
            <div class="flex flex-col items-start sm:items-end gap-2.5 w-full sm:w-auto shrink-0">
                <!-- Hàng trên: Nút tạo mới -->
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0">
                    <a href="{{ route('teacher.quizzes.create') }}" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        + Tạo bài kiểm tra
                    </a>
                </div>

                <!-- Hàng dưới: Thanh tìm kiếm -->
                <form method="GET" action="{{ route('teacher.quizzes.index') }}" class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <div class="relative flex-1 sm:w-80 lg:w-96">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Tìm kiếm bài kiểm tra của bạn..."
                               class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                        @if(request('search'))
                            <a href="{{ route('teacher.quizzes.index') }}"
                               class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 text-base font-bold leading-none"
                               title="Xóa tìm kiếm">
                                &times;
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-2xs shrink-0">
                        Tìm
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="w-full">

            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs overflow-hidden">
                @if($quizzes->isEmpty())
                    <div class="p-12 text-center">
                        
                        <h3 class="text-lg font-bold text-slate-900 mb-1">Chưa có bài kiểm tra nào</h3>
                        <p class="text-slate-500 text-sm mb-5">Bạn chưa tạo bài kiểm tra trắc nghiệm nào cho các khóa học của mình.</p>
                        <a href="{{ route('teacher.quizzes.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-sm font-bold shadow-xs transition">
                            + Tạo bài kiểm tra đầu tiên
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[980px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 min-w-[240px]">Bài kiểm tra & Khóa học</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số câu hỏi</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Cấu hình Đề</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái duyệt</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Lượt học viên làm</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($quizzes as $quiz)
                                    <tr class="hover:bg-pink-50/30 transition">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border whitespace-nowrap shrink-0 {{ $quiz->type_badge_class }}">
                                                    {{ $quiz->type_name }}
                                                </span>
                                                <div class="font-bold text-slate-900 text-base">
                                                    {{ $quiz->title }}
                                                </div>
                                            </div>
                                            <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                                <span class="whitespace-nowrap">Khóa:</span>
                                                @if($quiz->course)
                                                    <span class="font-semibold text-pink-700 bg-pink-50 px-2 py-0.5 rounded-md border border-pink-100">
                                                        {{ $quiz->course->title }}
                                                    </span>
                                                @else
                                                    <span class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 whitespace-nowrap">
                                                        Tự do (Không theo khóa học nào)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap shrink-0 {{ $quiz->questions_count > 0 ? 'bg-pink-50 text-pink-700 border border-pink-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                {{ $quiz->questions_count }} câu
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-600 whitespace-nowrap">
                                            <div class="whitespace-nowrap">{{ $quiz->duration_minutes }} phút | Điểm đạt &ge; {{ $quiz->passing_score }}/10</div>
                                            <div class="mt-1">
                                                @if($quiz->randomize_questions)
                                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                                        Random câu hỏi
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-slate-400 whitespace-nowrap">Thứ tự cố định</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            @if($quiz->status === 'approved')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    Đã duyệt
                                                </span>
                                            @elseif($quiz->status === 'pending')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                    Chờ admin duyệt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    Từ chối
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            <a href="{{ route('teacher.quizzes.results', $quiz->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-pink-50 hover:text-pink-700 transition whitespace-nowrap shrink-0">
                                                {{ $quiz->attempts_count }} lượt thi
                                            </a>
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-2 flex-nowrap shrink-0">
                                                <a href="{{ route('teacher.quizzes.questions', $quiz->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 transition shadow-xs whitespace-nowrap shrink-0">
                                                    + Câu hỏi
                                                </a>
                                                <a href="{{ route('teacher.quizzes.edit', $quiz->id) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-pink-600 hover:border-pink-300 text-xs font-semibold transition whitespace-nowrap shrink-0">
                                                    Sửa
                                                </a>
                                                <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài kiểm tra này không?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-semibold transition whitespace-nowrap shrink-0">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-teacher-layout>
