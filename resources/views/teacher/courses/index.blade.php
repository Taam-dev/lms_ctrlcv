<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    
                    Quản lý Khóa học của tôi (Giảng viên)
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Quản lý các khóa học bạn đã tạo, thêm bài giảng mới và kết nối đề thi trắc nghiệm.
                </p>
            </div>
            <div class="flex flex-col items-start sm:items-end gap-2.5 w-full sm:w-auto shrink-0">
                <!-- Hàng trên: Nút tạo mới -->
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0">
                    <a href="{{ route('teacher.courses.create') }}" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        + Tạo khóa học mới
                    </a>
                </div>

                <!-- Hàng dưới: Thanh tìm kiếm -->
                <form method="GET" action="{{ route('teacher.courses.index') }}" class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <div class="relative flex-1 sm:w-80 lg:w-96">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Tìm kiếm khóa học của bạn..."
                               class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                        @if(request('search'))
                            <a href="{{ route('teacher.courses.index') }}"
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
                @if($courses->isEmpty())
                    <div class="p-12 text-center">
                        
                        <h3 class="font-bold text-lg text-slate-900 mb-1">Bạn chưa tạo khóa học nào</h3>
                        <p class="text-sm text-slate-500 mb-5">Bấm nút tạo khóa học mới để bắt đầu đăng tải bài giảng.</p>
                        <a href="{{ route('teacher.courses.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-pink-600 text-white font-bold text-sm shadow-xs hover:bg-pink-700 transition">
                            Tạo khóa học ngay
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[960px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 min-w-[240px]">Tên khóa học</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số bài học</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Ngày tạo</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($courses as $course)
                                    <tr class="hover:bg-pink-50/30 transition">
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-slate-900 text-base mb-0.5">
                                                {{ $course->title }}
                                            </div>
                                            <div class="text-xs text-slate-500 line-clamp-1 max-w-md">
                                                {{ $course->description ?? 'Chưa có mô tả' }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            @if($course->status === 'pending')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                    Chờ duyệt
                                                </span>
                                            @elseif($course->status === 'approved')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    Đã duyệt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    Từ chối
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 whitespace-nowrap shrink-0">
                                                {{ $course->lessons()->count() }} bài
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ $course->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                <a href="{{ route('instructor.lessons.create', $course->id) }}" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                    + Bài giảng
                                                </a>
                                                <a href="{{ route('instructor.quizzes.create') }}" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 text-xs font-bold transition whitespace-nowrap shrink-0">
                                                    + Tạo Quiz
                                                </a>
                                                <a href="{{ route('instructor.courses.edit', $course->id) }}" class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 transition shrink-0" title="Chỉnh sửa khóa học">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('student.courses.show', $course->id) }}" target="_blank" class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 transition shrink-0" title="Xem trước giao diện">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('instructor.courses.destroy', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khóa học này cùng toàn bộ bài giảng và bài kiểm tra liên quan?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Xóa khóa học">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
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