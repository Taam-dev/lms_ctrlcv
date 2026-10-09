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
            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs overflow-hidden">
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
