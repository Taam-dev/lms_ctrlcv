<x-admin-layout breadcrumb="Kết quả làm bài">
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
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Kết Quả Làm Bài: {{ $quiz->title }}
                </h1>
            </div>
            <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-2xs transition">
                &larr; Quay lại danh sách Quizzes
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Tổng lượt nộp bài</span>
                <strong class="text-3xl font-black text-slate-900 mt-1 block">{{ $attempts->count() }}</strong>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Học viên Đạt yêu cầu</span>
                <strong class="text-3xl font-black text-emerald-600 mt-1 block">
                    {{ $attempts->where('is_passed', true)->count() }}
                </strong>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Điểm trung bình</span>
                <strong class="text-3xl font-black text-pink-600 mt-1 block">
                    {{ $attempts->count() > 0 ? number_format($attempts->avg('score'), 1) : '0.0' }} / 10
                </strong>
            </div>
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Điểm chuẩn qua môn</span>
                <strong class="text-3xl font-black text-slate-700 mt-1 block">
                    &ge; {{ $quiz->passing_score }} / 10
                </strong>
            </div>
        </div>

        <!-- Bảng kết quả -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($attempts->isEmpty())
                <div class="p-16 text-center text-slate-500 text-sm">
                    Chưa có học viên nào tham gia làm bài kiểm tra này.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                <th class="py-4 px-6 whitespace-nowrap">Học viên</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Số câu đúng</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Điểm số</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Kết quả</th>
                                <th class="py-4 px-6 text-right whitespace-nowrap">Thời gian nộp bài</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($attempts as $attempt)
                                <tr class="hover:bg-pink-50/20 transition">
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @if($attempt->student->avatar_url)
                                                <img src="{{ $attempt->student->avatar_url }}" alt="{{ $attempt->student->name }}" class="w-8 h-8 rounded-xl object-cover border border-slate-200 shrink-0">
                                            @else
                                                <div class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($attempt->student->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $attempt->student->name }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $attempt->student->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs font-semibold text-slate-600 whitespace-nowrap">
                                        {{ $attempt->correct_answers }} / {{ $attempt->total_questions }} câu
                                    </td>
                                    <td class="py-4 px-4 text-center font-black text-base text-slate-900 whitespace-nowrap">
                                        {{ number_format($attempt->score, 1) }}
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($attempt->is_passed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                ĐẠT
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                CHƯA ĐẠT
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right text-xs text-slate-500 whitespace-nowrap">
                                        {{ $attempt->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-admin-layout>
