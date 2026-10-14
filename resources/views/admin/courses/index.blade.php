<x-admin-layout breadcrumb="Quản lý Khóa học / Bài học">
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="min-w-0 flex-1 max-w-lg lg:max-w-xl">
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    Đào Tạo & Khóa Học
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    Quản Lý & Duyệt Khóa Học / Bài Học
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kiểm duyệt nội dung và bài giảng các khóa học do giảng viên đăng tải và thực hiện phê duyệt hiển thị lên trang chủ học viên.
                </p>
            </div>
            
            <div class="flex flex-col items-start lg:items-end gap-2.5 w-full lg:w-auto shrink-0">
                <!-- Hàng trên: Bộ lọc trạng thái + Nút tạo mới (Cùng 1 hàng, đồng bộ với bên Bài kiểm tra) -->
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0 overflow-x-auto max-w-full">
                    <!-- Status Filter Pills -->
                    <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                        <a href="{{ route('admin.courses.index', array_filter(['search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ empty($status) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Tất cả ({{ $totalCount ?? $courses->count() }})
                        </a>
                        <a href="{{ route('admin.courses.index', array_filter(['status' => 'pending', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                            Chờ duyệt
                        </a>
                        <a href="{{ route('admin.courses.index', array_filter(['status' => 'approved', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                            Đã duyệt
                        </a>
                        <a href="{{ route('admin.courses.index', array_filter(['status' => 'rejected', 'search' => $search])) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                            Đã loại bỏ
                        </a>
                    </div>

                    <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        + Tạo khóa học mới
                    </a>
                </div>

                <!-- Hàng dưới: Thanh tìm kiếm -->
                <form method="GET" action="{{ route('admin.courses.index') }}" class="flex items-center gap-2 w-full lg:w-auto justify-end">
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
                               placeholder="Tìm kiếm khóa học, giảng viên..."
                               class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                        @if(!empty($search))
                            <a href="{{ route('admin.courses.index', array_filter(['status' => $status])) }}"
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
                    <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $courses->count() }} khóa học)</span>
                </div>
                <a href="{{ route('admin.courses.index', array_filter(['status' => $status])) }}"
                   class="font-bold text-slate-500 hover:text-pink-600 transition">
                    &times; Xóa tìm kiếm
                </a>
            </div>
        @endif

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            @if($courses->isEmpty())
                <div class="p-16 text-center">
                    <h3 class="font-extrabold text-slate-800 text-base mb-1">
                        @if(!empty($search))
                            Không tìm thấy khóa học khớp với "{{ $search }}"
                        @else
                            Không tìm thấy khóa học nào
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500 mb-5">
                        @if(!empty($search))
                            Vui lòng thử tìm kiếm bằng từ khóa khác hoặc xóa bộ lọc tìm kiếm.
                        @else
                            Chưa có khóa học nào phù hợp với bộ lọc hiện tại.
                        @endif
                    </p>
                    @if(!empty($search))
                        <a href="{{ route('admin.courses.index', array_filter(['status' => $status])) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                            Xóa tìm kiếm
                        </a>
                    @else
                        <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                            + Tạo khóa học ngay
                        </a>
                    @endif
                </div>
            @else
                <div x-data="{
                    expandedCourse: {{ request()->filled('course_id') ? (int)request('course_id') : (request()->filled('expand') ? (int)request('expand') : 'null') }},
                    isOverflowing: false,
                    checkOverflow() {
                        const el = this.$refs.tableWrapper;
                        if (el) {
                            this.isOverflowing = el.scrollWidth > el.clientWidth + 5;
                        }
                    },
                    scrollTable(delta) {
                        if (this.$refs.tableWrapper) {
                            this.$refs.tableWrapper.scrollBy({ left: delta, behavior: 'smooth' });
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

                    <!-- Thanh scrollbar đồng bộ ở đỉnh (Top Scrollbar) -->
                    <div x-show="isOverflowing" x-cloak
                         x-ref="topScroll"
                         @scroll="if ($refs.tableWrapper) $refs.tableWrapper.scrollLeft = $event.target.scrollLeft"
                         class="overflow-x-auto overflow-y-hidden border-b border-slate-100 bg-slate-50/80 [scrollbar-width:thin] h-2.5">
                        <div :style="`width: ${$refs.tableEl ? $refs.tableEl.offsetWidth : 960}px; height: 1px;`"></div>
                    </div>

                    <div x-ref="tableWrapper" 
                         @scroll="if ($refs.topScroll) $refs.topScroll.scrollLeft = $event.target.scrollLeft; checkOverflow()"
                         class="overflow-x-auto">
                        <table x-ref="tableEl" class="w-full text-left border-collapse min-w-[960px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-3.5 px-4 min-w-[200px]">Tên khóa học</th>
                                    <th class="py-3.5 px-3 whitespace-nowrap">Giảng viên</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Số bài học</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Giá khóa học</th>
                                    <th class="py-3.5 px-3 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Ngày tạo</th>
                                    <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-slate-50/95 backdrop-blur-xs z-20 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                        Hành động duyệt & quản trị
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($courses as $course)
                                    <tr class="hover:bg-pink-50/20 group transition">
                                        <td class="py-3.5 px-4">
                                            <button type="button" @click="expandedCourse = (expandedCourse === {{ $course->id }} ? null : {{ $course->id }})" class="text-left w-full group">
                                                <div class="font-bold text-slate-900 text-sm mb-0.5 group-hover:text-pink-600 transition">
                                                    {{ $course->title }}
                                                </div>
                                                <div class="text-xs text-slate-500 line-clamp-1 max-w-xs">
                                                    {{ $course->description ?? 'Chưa có mô tả chi tiết' }}
                                                </div>
                                            </button>
                                        </td>
                                        <td class="py-3.5 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                @if($course->teacher && $course->teacher->avatar_url)
                                                    <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shrink-0">
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                        {{ strtoupper(substr($course->teacher->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <div class="font-semibold text-slate-800 text-xs">{{ $course->teacher->name ?? 'Không xác định' }}</div>
                                                    <div class="text-[11px] text-slate-400 max-w-[130px] truncate">{{ $course->teacher->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-2.5 text-center whitespace-nowrap">
                                            <button type="button" @click="expandedCourse = (expandedCourse === {{ $course->id }} ? null : {{ $course->id }})" 
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold transition shadow-2xs whitespace-nowrap shrink-0"
                                                    :class="expandedCourse === {{ $course->id }} ? 'bg-pink-600 text-white' : 'bg-pink-50 text-pink-700 hover:bg-pink-100'"
                                                    title="Bấm để xem danh sách bài giảng">
                                                <span>{{ $course->lessons_count }} bài</span>
                                                <svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="expandedCourse === {{ $course->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="py-3.5 px-2.5 text-center whitespace-nowrap">
                                            @if(($course->price ?? 0) > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-neutral-900 text-white shadow-2xs whitespace-nowrap shrink-0">
                                                    {{ number_format($course->price, 0, ',', '.') }} đ
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase whitespace-nowrap shrink-0">
                                                    free
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                            @if($course->status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                    Chờ duyệt
                                                </span>
                                            @elseif($course->status === 'approved')
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
                                            {{ $course->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-[#fff7fa] transition z-10 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                <a href="{{ route('student.courses.show', $course->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-pink-600 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Xem trước khóa học">
                                                    Xem
                                                </a>

                                                <a href="{{ route('admin.courses.lessons.create', $course->id) }}" class="px-2.5 py-1.5 rounded-lg border border-pink-200 bg-pink-50 text-pink-700 hover:bg-pink-100 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Thêm bài giảng vào khóa học">
                                                    + Bài giảng
                                                </a>

                                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Chỉnh sửa thông tin khóa học">
                                                    Sửa
                                                </a>

                                                {{-- 1. KHI GIẢNG VIÊN TẠO MỚI (CHỜ DUYỆT): HIỆN DUYỆT & TỪ CHỐI --}}
                                                @if($course->status === 'pending')
                                                    <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Duyệt
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('admin.courses.reject', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối khóa học mới này?')">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Từ chối
                                                        </button>
                                                    </form>

                                                {{-- 2. KHI ĐÃ ĐƯỢC DUYỆT: HIỆN CHỮ LOẠI BỎ (KHÔNG ĐỂ TỪ CHỐI) --}}
                                                @elseif($course->status === 'approved')
                                                    <form action="{{ route('admin.courses.remove', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn loại bỏ khóa học này khỏi hệ thống? Khóa học sẽ không còn hiển thị cho học viên.')">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Loại bỏ khóa học đã duyệt">
                                                            Loại bỏ
                                                        </button>
                                                    </form>

                                                {{-- 3. KHI ĐÃ BỊ TỪ CHỐI / LOẠI BỎ: HIỆN DUYỆT LẠI VÀ NÚT X ĐỂ XÓA KHỎI DATABASE --}}
                                                @else
                                                    <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Phê duyệt lại bài giảng/khóa học">
                                                            Duyệt lại
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn khóa học/bài giảng này khỏi cơ sở dữ liệu? Dữ liệu sẽ không thể khôi phục!')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition inline-flex items-center gap-1 shrink-0" title="Xóa vĩnh viễn khỏi Database">
                                                            <span>✕</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                <!-- Expandable Row: Danh sách bài giảng của khóa học -->
                                <tr x-show="expandedCourse === {{ $course->id }}" x-cloak class="bg-pink-50/25 border-b-2 border-pink-200">
                                    <td colspan="7" class="p-5">
                                        <div class="bg-white rounded-2xl p-5 border border-pink-100 shadow-xs">
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-3.5 mb-3.5 border-b border-slate-100 gap-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div>
                                                        <h4 class="font-bold text-slate-900 text-sm">
                                                            Giáo trình bài giảng: <span class="text-pink-600">{{ $course->title }}</span>
                                                        </h4>
                                                        <p class="text-xs text-slate-500">
                                                            Tổng cộng {{ $course->lessons->count() }} bài học được sắp xếp theo thứ tự
                                                        </p>
                                                    </div>
                                                </div>

                                                <a href="{{ route('admin.courses.lessons.create', $course->id) }}" 
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                    + Thêm bài giảng vào khóa này
                                                </a>
                                            </div>

                                            @if($course->lessons->isEmpty())
                                                <div class="py-6 text-center text-slate-500 text-xs bg-slate-50/70 rounded-xl border border-dashed border-slate-200">
                                                    <p class="font-bold text-slate-700 mb-1">Khóa học này chưa có bài giảng nào</p>
                                                    <p class="text-slate-400 mb-2">Hãy tạo bài giảng đầu tiên để học viên có thể học tập.</p>
                                                    <a href="{{ route('admin.courses.lessons.create', $course->id) }}" class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-800 font-bold whitespace-nowrap">
                                                        + Bấm vào đây để tạo bài giảng đầu tiên &rarr;
                                                    </a>
                                                </div>
                                            @else
                                                <div class="space-y-1.5">
                                                    @foreach($course->lessons as $lesson)
                                                        <div class="flex items-center justify-between p-2.5 bg-slate-50/70 hover:bg-pink-50/40 rounded-xl transition gap-3">
                                                            <div class="flex items-center gap-3 min-w-0">
                                                                <span class="w-7 h-7 rounded-lg bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                                    #{{ $lesson->order_number }}
                                                                </span>
                                                                <div class="min-w-0">
                                                                    <div class="font-bold text-slate-900 text-xs sm:text-sm truncate">
                                                                        {{ $lesson->title }}
                                                                    </div>
                                                                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                                        @if($lesson->content_type === 'video')
                                                                            <span class="inline-flex items-center gap-1 text-rose-600 font-semibold bg-rose-50 px-1.5 py-0.5 rounded whitespace-nowrap shrink-0">
                                                                                Video
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center gap-1 text-pink-600 font-semibold bg-pink-50 px-1.5 py-0.5 rounded whitespace-nowrap shrink-0">
                                                                                Văn bản
                                                                            </span>
                                                                        @endif
                                                                        <span class="whitespace-nowrap">Tạo: {{ $lesson->created_at->format('d/m/Y') }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                                                                <a href="{{ route('student.lessons.show', ['courseId' => $course->id, 'lessonId' => $lesson->id]) }}" target="_blank" 
                                                                   class="inline-flex items-center justify-center px-2 py-1 rounded-lg border border-slate-200 text-slate-600 hover:text-pink-600 hover:bg-white text-xs font-semibold transition whitespace-nowrap shrink-0" title="Xem trước bài giảng">
                                                                    Xem
                                                                </a>
                                                                <a href="{{ route('admin.lessons.edit', $lesson->id) }}" 
                                                                   class="inline-flex items-center justify-center px-2 py-1 rounded-lg bg-slate-100 hover:bg-pink-50 text-slate-700 hover:text-pink-700 text-xs font-semibold transition whitespace-nowrap shrink-0">
                                                                    Sửa
                                                                </a>
                                                                <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài giảng \'{{ addslashes($lesson->title) }}\' không?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="inline-flex items-center justify-center p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Xóa bài">
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
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