<x-admin-layout breadcrumb="Quản lý Khóa học">
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="min-w-0 flex-1 max-w-lg lg:max-w-xl">
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    Đào Tạo & Khóa Học
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    Quản Lý & Duyệt Khóa Học
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Kiểm duyệt nội dung các khóa học do giảng viên đăng tải và thực hiện phê duyệt hiển thị lên trang chủ học viên.
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
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1060px]">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                <th class="py-4 px-6 min-w-[260px]">Tên khóa học</th>
                                <th class="py-4 px-4 whitespace-nowrap">Giảng viên</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Số bài học</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái</th>
                                <th class="py-4 px-4 text-center whitespace-nowrap">Ngày tạo</th>
                                <th class="py-4 px-6 text-right whitespace-nowrap">Hành động duyệt & quản trị</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($courses as $course)
                                <tr class="hover:bg-pink-50/20 transition">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-900 text-base mb-0.5">
                                            {{ $course->title }}
                                        </div>
                                        <div class="text-xs text-slate-500 line-clamp-1 max-w-md">
                                            {{ $course->description ?? 'Chưa có mô tả chi tiết' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2.5">
                                            @if($course->teacher && $course->teacher->avatar_url)
                                                <img src="{{ $course->teacher->avatar_url }}" alt="{{ $course->teacher->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($course->teacher->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-semibold text-slate-800">{{ $course->teacher->name ?? 'Không xác định' }}</div>
                                                <div class="text-xs text-slate-400">{{ $course->teacher->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 whitespace-nowrap shrink-0">
                                            {{ $course->lessons_count }} bài
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        @if($course->status === 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                Chờ duyệt
                                            </span>
                                        @elseif($course->status === 'approved')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                Đã duyệt
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                                Đã loại bỏ / Từ chối
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                        {{ $course->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>