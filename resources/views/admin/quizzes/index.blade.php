<x-admin-layout breadcrumb="Quản lý Bài kiểm tra">
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="min-w-0 flex-1 max-w-lg lg:max-w-xl">
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    Khảo Thí & Đánh Giá
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    Quản Lý & Duyệt Bài Kiểm Tra (Quizzes)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Xem trước nội dung các đề thi trắc nghiệm trực tuyến do giảng viên biên soạn và thực hiện phê duyệt.
                </p>
            </div>
            
            <div class="flex flex-col items-start lg:items-end gap-2.5 w-full lg:w-auto shrink-0">
                <!-- Hàng trên: Bộ lọc trạng thái + Nút tạo mới (Cùng 1 hàng) -->
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0 overflow-x-auto max-w-full">
                    <!-- Bộ lọc trạng thái -->
                    <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                        <a href="{{ route('admin.quizzes.index', array_filter(['search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ empty($status) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Tất cả ({{ $totalCount ?? $quizzes->count() }})
                        </a>
                        <a href="{{ route('admin.quizzes.index', array_filter(['status' => 'pending', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                            Chờ duyệt
                        </a>
                        <a href="{{ route('admin.quizzes.index', array_filter(['status' => 'approved', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                            Đã duyệt
                        </a>
                        <a href="{{ route('admin.quizzes.index', array_filter(['status' => 'rejected', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                            Đã loại bỏ
                        </a>
                    </div>

                    <a href="{{ route('admin.quizzes.create') }}" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        + Tạo Quiz mới
                    </a>
                </div>

                <!-- Hàng dưới: Thanh tìm kiếm -->
                <form method="GET" action="{{ route('admin.quizzes.index') }}" class="flex items-center gap-2 w-full lg:w-auto justify-end">
                    @if(!empty($status))
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="relative flex-1 sm:w-80 lg:w-96">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Tìm kiếm bài kiểm tra, khóa học, GV..."
                               class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                        @if(!empty($search))
                            <a href="{{ route('admin.quizzes.index', array_filter(['status' => $status])) }}"
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

    <div class="space-y-6">
        @if(!empty($search))
            <div class="p-3.5 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                <div class="flex items-center gap-2 text-slate-700">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $quizzes->count() }} bài kiểm tra)</span>
                </div>
                <a href="{{ route('admin.quizzes.index', array_filter(['status' => $status])) }}"
                   class="font-bold text-slate-500 hover:text-pink-600 transition">
                    &times; Xóa tìm kiếm
                </a>
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($quizzes->isEmpty())
                <div class="p-16 text-center">
                    <h3 class="font-extrabold text-slate-800 text-base mb-1">
                        @if(!empty($search))
                            Không tìm thấy bài kiểm tra khớp với "{{ $search }}"
                        @else
                            Không tìm thấy bài kiểm tra nào
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mb-5">
                        @if(!empty($search))
                            Vui lòng thử tìm kiếm bằng từ khóa khác hoặc xóa bộ lọc tìm kiếm.
                        @else
                            Hiện tại không có bài kiểm tra nào trong trạng thái đã chọn.
                        @endif
                    </p>
                    @if(!empty($search))
                        <a href="{{ route('admin.quizzes.index', array_filter(['status' => $status])) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                            Xóa tìm kiếm
                        </a>
                    @else
                        <a href="{{ route('admin.quizzes.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                            + Soạn bài kiểm tra ngay
                        </a>
                    @endif
                </div>
            @else
                <div x-data="{
                    isOverflowing: false,
                    checkOverflow() {
                        const el = this.$refs.tableWrapper;
                        if (el) {
                            this.isOverflowing = el.scrollWidth > el.clientWidth + 5;
                        }
                    }
                }"
                x-init="checkOverflow(); window.addEventListener('resize', () => checkOverflow())"
                class="relative">

                    <!-- thong bao cuon ngang khi man hinh hep -->
                    <div x-show="isOverflowing" x-cloak class="px-4 py-2 bg-gradient-to-r from-pink-50/80 via-white to-pink-50/80 border-b border-pink-100 flex items-center gap-2 text-xs text-slate-600 transition">
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-pink-100 text-pink-700 text-[10px] font-bold">&harr;</span>
                        <span class="text-slate-700 font-medium">Bảng thông tin có thể cuộn ngang:</span>
                        <span class="text-slate-400 text-[11px]">(Cột <strong class="text-pink-600">Hành động</strong> luôn được ghim cố định bên phải)</span>
                    </div>

                    <!-- thanh scrollbar dong bo o dinh -->
                    <div x-show="isOverflowing" x-cloak
                         x-ref="topScroll"
                         @scroll="if ($refs.tableWrapper) $refs.tableWrapper.scrollLeft = $event.target.scrollLeft"
                         class="overflow-x-auto overflow-y-hidden border-b border-slate-100 bg-slate-50/80 [scrollbar-width:thin] h-2.5">
                        <div :style="`width: ${$refs.tableEl ? $refs.tableEl.offsetWidth : 1080}px; height: 1px;`"></div>
                    </div>

                    <div x-ref="tableWrapper" 
                         @scroll="if ($refs.topScroll) $refs.topScroll.scrollLeft = $event.target.scrollLeft; checkOverflow()"
                         class="overflow-x-auto">
                        <table x-ref="tableEl" class="w-full text-left border-collapse min-w-[1080px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-3.5 px-4 min-w-[220px]">Bài kiểm tra</th>
                                    <th class="py-3.5 px-3 whitespace-nowrap">Khóa học</th>
                                    <th class="py-3.5 px-3 whitespace-nowrap">Giảng viên</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Số câu hỏi</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Thời gian & Điểm</th>
                                    <th class="py-3.5 px-3 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Ngày tạo</th>
                                    <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-slate-50/95 backdrop-blur-xs z-20 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                        Hành động duyệt & quản trị
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($quizzes as $quiz)
                                    <tr class="hover:bg-pink-50/20 group transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900 text-sm mb-1 group-hover:text-pink-600 transition">
                                                {{ $quiz->title }}
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $quiz->type_badge_class }} whitespace-nowrap shrink-0">
                                                    {{ $quiz->type_name }}
                                                </span>
                                                @if($quiz->isExpired())
                                                    <span class="inline-flex items-center text-rose-700 font-bold bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded text-[10px] whitespace-nowrap">
                                                        Hết hạn: {{ $quiz->ends_at?->format('d/m/Y H:i') }}
                                                    </span>
                                                @elseif($quiz->isNotStarted())
                                                    <span class="inline-flex items-center text-amber-700 font-bold bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded text-[10px] whitespace-nowrap">
                                                        Mở từ: {{ $quiz->starts_at?->format('d/m/Y H:i') }}
                                                    </span>
                                                @elseif($quiz->ends_at)
                                                    <span class="inline-flex items-center text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded text-[10px] whitespace-nowrap">
                                                        Hạn: {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap">
                                            @if($quiz->course)
                                                <span class="inline-flex items-center font-semibold text-pink-700 bg-pink-50 px-2.5 py-1 rounded-lg border border-pink-100 text-xs max-w-[150px] truncate" title="{{ $quiz->course->title }}">
                                                    {{ $quiz->course->title }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 text-xs">
                                                    Tự do
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                @if($quiz->teacher && $quiz->teacher->avatar_url)
                                                    <img src="{{ $quiz->teacher->avatar_url }}" alt="{{ $quiz->teacher->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shrink-0">
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                        {{ strtoupper(mb_substr($quiz->teacher->name ?? 'G', 0, 1, 'UTF-8')) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-800 text-xs">{{ $quiz->teacher->name ?? 'Không rõ' }}</div>
                                                    <div class="text-[11px] text-slate-400 max-w-[130px] truncate">{{ $quiz->teacher->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-2.5 text-center whitespace-nowrap">
                                            @if($quiz->question_limit)
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 shadow-2xs whitespace-nowrap shrink-0" title="Đề thi lấy ngẫu nhiên {{ $quiz->question_limit }} trong {{ $quiz->questions_count }} câu">
                                                        {{ $quiz->question_limit }}/{{ $quiz->questions_count }} câu
                                                    </span>
                                                    <span class="text-[10px] text-pink-600 font-medium">Ngẫu nhiên</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 shadow-2xs whitespace-nowrap shrink-0">
                                                    {{ $quiz->questions_count }} câu
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-2.5 text-center whitespace-nowrap">
                                            <div class="text-xs font-bold text-slate-800">{{ $quiz->duration_minutes }} phút</div>
                                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">Đạt &ge; {{ $quiz->passing_score }}/10</div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                            @if($quiz->status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                    Chờ duyệt
                                                </span>
                                            @elseif($quiz->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                    Đã duyệt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                                    Đã loại bỏ / Từ chối
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-2.5 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ $quiz->created_at ? $quiz->created_at->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-[#fff7fa] transition z-10 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 text-xs font-bold transition whitespace-nowrap shrink-0" title="Xem trước nội dung đề thi">
                                                    Xem đề
                                                </a>
                                                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition whitespace-nowrap shrink-0" title="Quản lý câu hỏi trắc nghiệm">
                                                    Câu hỏi
                                                </a>
                                                <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition whitespace-nowrap shrink-0" title="Chỉnh sửa cấu hình Quiz">
                                                    Sửa
                                                </a>
                                                <a href="{{ route('admin.quizzes.results', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition whitespace-nowrap shrink-0" title="Xem kết quả học viên">
                                                    Kết quả
                                                </a>

                                                {{-- 1. khi moi tao (cho duyet): hien duyet & tu choi --}}
                                                @if($quiz->status === 'pending')
                                                    <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt bài kiểm tra">
                                                            Duyệt
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('admin.quizzes.reject', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn từ chối đề thi này?')">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Từ chối đề thi">
                                                            Từ chối
                                                        </button>
                                                    </form>

                                                {{-- 2. khi da duyet: hien loai bo --}}
                                                @elseif($quiz->status === 'approved')
                                                    <form action="{{ route('admin.quizzes.remove', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn loại bỏ bài kiểm tra này? Học viên sẽ không thể làm bài này nữa.')">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Loại bỏ đề thi đã duyệt">
                                                            Loại bỏ
                                                        </button>
                                                    </form>

                                                {{-- 3. khi da bi tu choi / loai bo: hien duyet lai va nut xoa --}}
                                                @else
                                                    <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt lại đề thi">
                                                            Duyệt lại
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bài kiểm tra này khỏi database?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 px-2.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold shadow-xs transition inline-flex items-center gap-1 shrink-0" title="Xóa vĩnh viễn khỏi Database">
                                                            <span>✕</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
