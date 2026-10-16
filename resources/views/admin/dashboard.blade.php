<x-admin-layout :breadcrumb="$currentTab === 'overview' ? 'Tổng quan Dashboard' : ($currentTab === 'courses' ? 'Quản lý Khóa học / Bài học' : ($currentTab === 'quizzes' ? 'Quản lý Bài kiểm tra' : ($currentTab === 'users' ? 'Quản lý Người dùng' : 'Quản lý Báo cáo vi phạm')))">
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    Trung Tâm Quản Trị Hệ Thống Ctrl C+V
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    @if($currentTab === 'overview')
                        <span>Bảng Điều Khiển Quản Trị (Admin Portal)</span>
                    @elseif($currentTab === 'courses')
                        <span>Quản Lý & Duyệt Khóa Học / Bài Học</span>
                    @elseif($currentTab === 'quizzes')
                        <span>Quản Lý & Duyệt Bài Kiểm Tra (Quizzes)</span>
                    @elseif($currentTab === 'users')
                        <span>Quản Lý Tài Khoản Người Dùng</span>
                    @elseif($currentTab === 'reports')
                        <span>Quản Lý Báo Cáo Vi Phạm (Reports)</span>
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    @if($currentTab === 'overview')
                        Tổng hợp chỉ số KPI, danh sách cần phê duyệt cấp thiết và các hoạt động đào tạo mới nhất.
                    @elseif($currentTab === 'courses')
                        Kiểm duyệt và quản trị tất cả các khóa học và bài học do giảng viên đăng tải lên nền tảng.
                    @elseif($currentTab === 'quizzes')
                        Xem trước, thẩm định chất lượng và phê duyệt các đề thi trắc nghiệm trực tuyến.
                    @elseif($currentTab === 'attempts')
                        Xem kết quả và lịch sử làm bài trắc nghiệm của học viên đối với các bài kiểm tra do bạn biên soạn.
                    @elseif($currentTab === 'users')
                        Danh sách tài khoản học viên, giảng viên và Admin trong hệ thống.
                    @elseif($currentTab === 'reports')
                        Xem xét, xác thực và xử lý các báo cáo vi phạm tài khoản hoặc bình luận do người dùng gửi lên.
                    @endif
                </p>
            </div>

            <!-- Quick Action & Tab Switcher Bar -->
            <div class="flex flex-col items-start lg:items-end gap-2.5 w-full lg:w-auto shrink-0">
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0 overflow-x-auto max-w-full">
                    @if(in_array($currentTab, ['courses', 'quizzes']))
                        <!-- Bộ lọc trạng thái khi đang ở tab khóa học hoặc quiz -->
                        <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => $currentTab, 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ empty($statusFilter) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Tất cả ({{ $currentTab === 'courses' ? $stats['total_courses'] : $stats['total_quizzes'] }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => $currentTab, 'status' => 'pending', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                Chờ duyệt
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => $currentTab, 'status' => 'approved', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                Đã duyệt
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => $currentTab, 'status' => 'rejected', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                                Đã loại bỏ
                            </a>
                        </div>
                    @elseif($currentTab === 'reports')
                        <!-- Bộ lọc trạng thái khi đang ở tab Báo cáo vi phạm -->
                        <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => 'reports', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ empty($statusFilter) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Tất cả ({{ $stats['total_reports'] }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => 'reports', 'status' => 'pending', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                Chờ xử lý ({{ $stats['pending_reports'] }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => 'reports', 'status' => 'resolved', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'resolved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'resolved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                Đã xử lý ({{ $stats['resolved_reports'] }})
                            </a>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => 'reports', 'status' => 'dismissed', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'dismissed' ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-800 hover:bg-slate-100' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'dismissed' ? 'bg-white' : 'bg-slate-500' }}"></span>
                                Đã bác bỏ ({{ $stats['dismissed_reports'] }})
                            </a>
                        </div>
                    @elseif($currentTab === 'users')
                        <!-- Huy hiệu thống kê người dùng -->
                        <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                            <span class="px-3 py-1.5 rounded-xl bg-pink-50 text-pink-700 flex items-center gap-1.5 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-600"></span>
                                {{ $stats['total_users'] }} tài khoản ({{ $stats['admins_count'] }} Admin, {{ $stats['teachers_count'] }} GV, {{ $stats['students_count'] }} Học viên)
                            </span>
                        </div>
                    @endif

                    @if(in_array($currentTab, ['overview', 'courses']))
                        <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-700 text-white shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            + Tạo khóa học mới
                        </a>
                    @endif
                    @if(in_array($currentTab, ['overview', 'quizzes']))
                        <a href="{{ route('admin.quizzes.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-700 text-white shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            + Tạo bài kiểm tra (quiz)
                        </a>
                    @endif
                </div>

                @if(in_array($currentTab, ['courses', 'quizzes', 'attempts', 'users', 'reports']))
                    <!-- Thanh tìm kiếm ở DƯỚI (Vị trí tương tự bài kiểm tra, khóa học) -->
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 w-full lg:w-auto justify-end">
                        <input type="hidden" name="tab" value="{{ $currentTab }}">
                        @if(!empty($statusFilter))
                            <input type="hidden" name="status" value="{{ $statusFilter }}">
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
                                   placeholder="{{ $currentTab === 'courses' ? 'Tìm khóa học, bài học, giảng viên...' : ($currentTab === 'quizzes' ? 'Tìm bài kiểm tra, khóa học, GV...' : ($currentTab === 'attempts' ? 'Tìm học viên, email, đề thi, điểm (vd: 5, 8.5, >=8)...' : ($currentTab === 'reports' ? 'Tìm theo lý do, chi tiết, người báo cáo...' : 'Tìm theo tên, email, vai trò...')))}}"
                                   class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                            @if(!empty($search))
                                <a href="{{ route('admin.dashboard', array_filter(['tab' => $currentTab, 'status' => $statusFilter])) }}"
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
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">

        <!-- ============================================== -->
        <!-- 5 STATISTIC OVERVIEW CARDS (HIỂN THỊ TỔNG QUAN)-->
        <!-- ============================================== -->
        @if($currentTab === 'overview')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- 1. Courses Stats -->
                <a href="{{ route('admin.dashboard', ['tab' => 'courses']) }}" 
                   class="block bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Khóa học</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_courses'] }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">{{ $stats['approved_courses'] }} đã duyệt</span>
                        @if($stats['pending_courses'] > 0)
                            <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md">{{ $stats['pending_courses'] }} chờ</span>
                        @endif
                    </div>
                </a>

                <!-- 2. Quizzes Stats -->
                <a href="{{ $stats['pending_quizzes'] > 0 ? route('admin.dashboard', ['tab' => 'quizzes', 'status' => 'pending']) : route('admin.dashboard', ['tab' => 'quizzes']) }}" 
                   class="block bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Bài kiểm tra</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_quizzes'] }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">{{ $stats['approved_quizzes'] }} đã duyệt</span>
                        @if($stats['pending_quizzes'] > 0)
                            <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md">{{ $stats['pending_quizzes'] }} chờ</span>
                        @endif
                    </div>
                </a>

                <!-- 3. Lessons Stats -->
                <a href="{{ route('admin.courses.index') }}" 
                   class="block bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Bài giảng</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_lessons'] }}</div>
                    <div class="text-xs text-slate-500 truncate">
                        Video & tài liệu
                    </div>
                </a>

                <!-- 4. Users Stats -->
                <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" 
                   class="block bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md transition group relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 transition">Người dùng</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_users'] }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-pink-700 font-bold bg-pink-50 px-2 py-0.5 rounded-md">{{ $stats['teachers_count'] }} GV</span>
                        <span class="text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-md">{{ $stats['students_count'] }} Học viên</span>
                    </div>
                </a>

                <!-- 5. Reports Stats -->
                <a href="{{ route('admin.dashboard', ['tab' => 'reports']) }}" 
                   class="block bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-md transition group relative overflow-hidden {{ $stats['pending_reports'] > 0 ? 'ring-2 ring-rose-500/20' : '' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-rose-600 transition">Báo cáo vi phạm</span>
                        <span class="p-1 rounded-lg {{ $stats['pending_reports'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_reports'] }}</div>
                    <div class="flex items-center gap-2 text-xs">
                        @if($stats['pending_reports'] > 0)
                            <span class="text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-md">{{ $stats['pending_reports'] }} chờ xử lý</span>
                        @else
                            <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">Không có vi phạm chờ</span>
                        @endif
                    </div>
                </a>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- PENDING APPROVAL ALERT BANNER                  -->
        <!-- ============================================== -->
        @php
            $pendingTotal = $stats['pending_courses'] + $stats['pending_quizzes'] + $stats['pending_reports'];
        @endphp
        @if($pendingTotal > 0 && $currentTab === 'overview')
            <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-500/10 via-rose-500/5 to-transparent border border-amber-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Cần chú ý: Có {{ $pendingTotal }} nội dung đang chờ phê duyệt & xử lý</h3>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Bao gồm
                            @if($stats['pending_courses'] > 0)
                                <strong class="text-amber-800">{{ $stats['pending_courses'] }} khóa học</strong>,
                            @endif
                            @if($stats['pending_quizzes'] > 0)
                                <strong class="text-amber-800">{{ $stats['pending_quizzes'] }} bài kiểm tra</strong>,
                            @endif
                            @if($stats['pending_reports'] > 0)
                                <strong class="text-rose-800">{{ $stats['pending_reports'] }} báo cáo vi phạm</strong>
                            @endif
                            cần quản trị viên kiểm tra.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if($stats['pending_courses'] > 0)
                        <a href="{{ route('admin.dashboard', ['tab' => 'courses', 'status' => 'pending']) }}" 
                           class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                            Duyệt khóa học ({{ $stats['pending_courses'] }})
                        </a>
                    @endif
                    @if($stats['pending_quizzes'] > 0)
                        <a href="{{ route('admin.dashboard', ['tab' => 'quizzes', 'status' => 'pending']) }}" 
                           class="px-3.5 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                            Duyệt Quizzes ({{ $stats['pending_quizzes'] }})
                        </a>
                    @endif
                    @if($stats['pending_reports'] > 0)
                        <a href="{{ route('admin.dashboard', ['tab' => 'reports', 'status' => 'pending']) }}" 
                           class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition">
                            Xử lý báo cáo ({{ $stats['pending_reports'] }})
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- ============================================== -->
        <!-- TAB CONTENT RENDERING                          -->
        <!-- ============================================== -->

        <!-- TAB 1: OVERVIEW (TỔNG QUAN HỆ THỐNG) -->
        @if($currentTab === 'overview')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Cột trái: Khóa học chờ duyệt / Mới nhất -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Khóa học cần xét duyệt</h3>
                                <p class="text-[11px] text-slate-400">Các khóa học đang chờ ban quản trị phê duyệt</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.dashboard', ['tab' => 'courses']) }}" class="text-xs font-bold text-pink-600 hover:text-pink-800 transition">
                            Xem tất cả &rarr;
                        </a>
                    </div>

                    @if($pendingCourses->isEmpty())
                        <div class="py-10 text-center text-slate-400 text-xs">
                            
                            Không có khóa học nào đang chờ duyệt. Mọi khóa học đều đã được xử lý!
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($pendingCourses as $course)
                                <div class="p-3.5 rounded-2xl border border-slate-100 hover:border-pink-200 bg-slate-50/50 hover:bg-pink-50/20 transition flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-900 truncate">{{ $course->title }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            <span>GV: {{ $course->teacher->name ?? 'Không rõ' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $course->lessons->count() }} bài học</span>
                                            <span>&bull;</span>
                                            @if(($course->price ?? 0) > 0)
                                                <span class="font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ number_format($course->price, 0, ',', '.') }} đ</span>
                                            @else
                                                <span class="font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded text-[10px] uppercase border border-emerald-200">free</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                                        <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" class="inline shrink-0">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt khóa học">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.courses.reject', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn từ chối khóa học này?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-xs transition whitespace-nowrap shrink-0" title="Từ chối khóa học">
                                                Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cột phải: Đề thi Quiz chờ duyệt / Mới nhất -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Đề thi Quiz cần xét duyệt</h3>
                                <p class="text-[11px] text-slate-400">Các đề thi trắc nghiệm chờ kiểm duyệt nội dung</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.dashboard', ['tab' => 'quizzes', 'status' => 'pending']) }}" class="text-xs font-bold text-pink-600 hover:text-pink-800 transition">
                            Xem tất cả &rarr;
                        </a>
                    </div>

                    @if($pendingQuizzes->isEmpty())
                        <div class="py-10 text-center text-slate-400 text-xs">
                            
                            Không có đề thi nào đang chờ duyệt. Đã xử lý tất cả bài kiểm tra!
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($pendingQuizzes as $quiz)
                                <div class="p-3.5 rounded-2xl border border-slate-100 hover:border-pink-200 bg-slate-50/50 hover:bg-pink-50/20 transition flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-xs text-slate-900 truncate">{{ $quiz->title }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            <span>GV: {{ $quiz->teacher->name ?? 'N/A' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $quiz->questions_count }} câu hỏi</span>
                                            <span>&bull;</span>
                                            <span>{{ $quiz->duration_minutes }} phút</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 flex-nowrap">
                                        <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="px-2.5 py-1.5 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 font-bold text-xs transition whitespace-nowrap shrink-0">
                                            Xem đề
                                        </a>
                                        <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt bài kiểm tra">
                                                Duyệt
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hàng thứ 2: Người dùng mới nhất & Khóa học đã xuất bản gần đây -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Người dùng mới -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Người dùng mới gia nhập</h3>
                                <p class="text-[11px] text-slate-400">Các thành viên mới đăng ký tài khoản gần đây</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition">
                            Tất cả người dùng &rarr;
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($recentUsers as $user)
                            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-xl object-cover border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-xs text-slate-900">{{ $user->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($user->role === 'admin')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-pink-100 text-pink-700">Admin</span>
                                    @elseif($user->role === 'teacher')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-pink-100 text-pink-700">Giảng viên</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-700">Học viên</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400">{{ $user->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Khóa học mới đăng tải gần đây -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Khóa học đăng tải mới nhất</h3>
                                <p class="text-[11px] text-slate-400">Các khóa học được khởi tạo trong hệ thống</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.courses.index') }}" class="text-xs font-bold text-pink-600 hover:text-pink-800 transition">
                            Tất cả khóa học &rarr;
                        </a>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($recentCourses as $course)
                            <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition">
                                <div class="min-w-0">
                                    <div class="font-bold text-xs text-slate-900 truncate">{{ $course->title }}</div>
                                    <div class="text-[10px] text-slate-400">
                                        GV: {{ $course->teacher->name ?? 'N/A' }} &bull; {{ $course->lessons_count }} bài giảng &bull; 
                                        @if(($course->price ?? 0) > 0)
                                            <strong class="text-slate-700 font-bold">{{ number_format($course->price, 0, ',', '.') }} đ</strong>
                                        @else
                                            <strong class="text-emerald-600 font-bold uppercase">free</strong>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($course->status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">Đã duyệt</span>
                                    @elseif($course->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">Chờ duyệt</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">Từ chối</span>
                                    @endif
                                    <a href="{{ route('student.courses.show', $course->id) }}" target="_blank" class="p-1 rounded-lg text-slate-400 hover:text-pink-600" title="Xem khóa học">
                                        
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        <!-- TAB 2: KHÓA HỌC (COURSES) -->
        @elseif($currentTab === 'courses')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <!-- Search Toolbar -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="hidden" name="tab" value="courses">
                        @if(!empty($statusFilter))
                            <input type="hidden" name="status" value="{{ $statusFilter }}">
                        @endif
                        <div class="relative flex-1 sm:w-[440px]">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Tìm kiếm khóa học theo tên, giảng viên..."
                                   class="w-full pl-9 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                            @if(!empty($search))
                                <a href="{{ route('admin.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter])) }}"
                                   class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-sm font-bold"
                                   title="Xóa tìm kiếm">
                                    &times;
                                </a>
                            @endif
                        </div>
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-2xs shrink-0">
                            Tìm
                        </button>
                    </form>

                    @if(!empty($search))
                        <div class="text-xs text-slate-500 flex items-center gap-2">
                            <span>Kết quả cho <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $courses->count() }} khóa học)</span>
                            <a href="{{ route('admin.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter])) }}"
                               class="font-bold text-pink-600 hover:underline">Xóa</a>
                        </div>
                    @endif
                </div>

                @if($courses->isEmpty())
                    <div class="p-16 text-center">
                        <h3 class="font-bold text-slate-800 text-base mb-1">
                            @if(!empty($search))
                                Không tìm thấy khóa học khớp với "{{ $search }}"
                            @else
                                Không tìm thấy khóa học nào
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500">
                            @if(!empty($search))
                                Vui lòng thử tìm kiếm bằng từ khóa khác hoặc xóa bộ lọc tìm kiếm.
                            @else
                                Chưa có khóa học nào phù hợp với bộ lọc hiện tại.
                            @endif
                        </p>
                        @if(!empty($search))
                            <div class="mt-4">
                                <a href="{{ route('admin.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter])) }}"
                                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                                    Xóa tìm kiếm
                                </a>
                            </div>
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
                                        <th class="py-3.5 px-4 min-w-[200px]">Khóa học</th>
                                        <th class="py-3.5 px-3 whitespace-nowrap">Giảng viên</th>
                                        <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Số bài học</th>
                                        <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Giá khóa học</th>
                                        <th class="py-3.5 px-3 text-center whitespace-nowrap">Trạng thái</th>
                                        <th class="py-3.5 px-2.5 text-center whitespace-nowrap">Ngày gửi</th>
                                        <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-slate-50/95 backdrop-blur-xs z-20 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                            Hành động của Admin
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($courses as $course)
                                        <tr class="hover:bg-pink-50/20 group transition">
                                            <td class="py-3.5 px-4">
                                                <button type="button" @click="expandedCourse = (expandedCourse === {{ $course->id }} ? null : {{ $course->id }})" class="text-left w-full group">
                                                    <div class="font-bold text-slate-900 text-sm mb-0.5 group-hover:text-pink-600 transition">{{ $course->title }}</div>
                                                    <div class="text-xs text-slate-500 line-clamp-1 max-w-xs">
                                                        {{ $course->description ?? 'Chưa có mô tả' }}
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
                                                        <div class="font-semibold text-slate-800 text-xs">{{ $course->teacher->name ?? 'Không rõ' }}</div>
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
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        Chờ duyệt
                                                    </span>
                                                @elseif($course->status === 'approved')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Đã duyệt
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        Đã loại bỏ / Từ chối
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-2.5 text-center text-xs text-slate-500 whitespace-nowrap">
                                                {{ $course->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-[#fff7fa] transition z-10 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                                <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                <!-- Xem trước khóa học -->
                                                <a href="{{ route('student.courses.show', $course->id) }}" target="_blank" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-pink-600 hover:border-pink-200 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Xem trước khóa học">
                                                    Xem
                                                </a>

                                                <a href="{{ route('admin.courses.lessons.create', $course->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-pink-200 bg-pink-50 text-pink-700 hover:bg-pink-100 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Thêm bài giảng vào khóa học">
                                                    + Bài giảng
                                                </a>

                                                <a href="{{ route('admin.courses.edit', $course->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold transition whitespace-nowrap shrink-0" title="Chỉnh sửa khóa học">
                                                    Sửa
                                                </a>

                                                @if($course->status === 'pending')
                                                    <!-- Nút Duyệt -->
                                                    <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Duyệt
                                                        </button>
                                                    </form>

                                                    <!-- Nút Từ chối -->
                                                    <form action="{{ route('admin.courses.reject', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối khóa học này?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Từ chối
                                                        </button>
                                                    </form>
                                                @elseif($course->status === 'approved')
                                                    <form action="{{ route('admin.courses.remove', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn loại bỏ khóa học này?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Gỡ bỏ khóa học">
                                                            Loại bỏ
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.courses.approve', $course->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt lại">
                                                            Duyệt lại
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn khóa học này khỏi database?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 p-1.5 px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Xóa vĩnh viễn khỏi Database">
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

        <!-- TAB 3: QUIZZES -->
        @elseif($currentTab === 'quizzes')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                @if($quizzes->isEmpty())
                    <div class="p-16 text-center">
                        
                        <h3 class="font-bold text-slate-800 text-base mb-1">Không tìm thấy bài kiểm tra nào</h3>
                        <p class="text-xs text-slate-500">Chưa có đề thi trắc nghiệm nào phù hợp với bộ lọc hiện tại.</p>
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
                                            Hành động của Admin
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
                                                        Đã loại bỏ
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

                                                    @if($quiz->status === 'pending')
                                                        <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt bài kiểm tra">
                                                                Duyệt
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.quizzes.reject', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn từ chối đề thi này?')">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Từ chối đề thi">
                                                                Từ chối
                                                            </button>
                                                        </form>
                                                    @elseif($quiz->status === 'approved')
                                                        <form action="{{ route('admin.quizzes.remove', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn loại bỏ bài kiểm tra này?')">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Loại bỏ đề thi đã duyệt">
                                                                Loại bỏ
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Duyệt lại">
                                                                Duyệt lại
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('admin.quizzes.destroy', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bài kiểm tra này khỏi database?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center gap-1 p-1.5 px-2.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Xóa vĩnh viễn khỏi Database">
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

        <!-- TAB: ATTEMPTS (KẾT QUẢ HỌC VIÊN CỦA ADMIN) -->
        @elseif($currentTab === 'attempts')
            @if(!empty($search))
                <div class="p-3.5 mb-5 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                    <div class="flex items-center gap-2 text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                        <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $attempts->total() }} lượt nộp bài khớp)</span>
                    </div>
                    <a href="{{ route('admin.dashboard', ['tab' => 'attempts']) }}"
                       class="font-bold text-slate-500 hover:text-pink-600 transition">
                        &times; Xóa tìm kiếm
                    </a>
                </div>
            @endif

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/40">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Kết Quả Học Viên (Quizzes Của Bạn)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Theo dõi lịch sử làm bài, số câu đúng và điểm số của học viên trên các bài kiểm tra do bạn biên soạn.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-xl bg-pink-50 text-pink-700 text-xs font-bold border border-pink-200/80">
                            {{ $attempts->total() }} lượt nộp bài
                        </span>
                    </div>
                </div>

                @if($attempts->isEmpty())
                    <div class="p-16 text-center">
                        <h4 class="font-bold text-slate-800 text-base mb-1">
                            @if(!empty($search))
                                Không tìm thấy kết quả làm bài nào khớp với "{{ $search }}"
                            @else
                                Chưa có học viên nào làm bài kiểm tra của bạn
                            @endif
                        </h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            @if(!empty($search))
                                Vui lòng thử tìm kiếm bằng tên học viên, email, đề thi hoặc điểm số khác (ví dụ: "5", "8.5", ">=8", "đạt").
                            @else
                                Khi học viên tham gia và hoàn thành các bài trắc nghiệm do bạn tạo, kết quả và điểm số chi tiết sẽ hiển thị tại đây.
                            @endif
                        </p>
                        @if(!empty($search))
                            <div class="pt-3">
                                <a href="{{ route('admin.dashboard', ['tab' => 'attempts']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                                    Xóa tìm kiếm
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 min-w-[200px]">Học viên</th>
                                    <th class="py-4 px-4 min-w-[220px]">Bài kiểm tra & Khóa học</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số câu đúng</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Điểm số</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Kết quả</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Thời gian nộp</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($attempts as $attempt)
                                    <tr class="hover:bg-pink-50/20 transition">
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                @if($attempt->student && $attempt->student->avatar_url)
                                                    <img src="{{ $attempt->student->avatar_url }}" alt="{{ $attempt->student->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                                @else
                                                    <div class="w-8 h-8 rounded-lg bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                        {{ strtoupper(substr($attempt->student->name ?? 'H', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $attempt->student->name ?? 'N/A' }}</div>
                                                    <div class="text-xs text-slate-500">{{ $attempt->student->email ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="font-semibold text-slate-900">{{ $attempt->quiz->title ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ $attempt->quiz->course ? 'Khóa: ' . $attempt->quiz->course->title : 'Bài kiểm tra Tự do' }}
                                            </div>
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
                                                    ĐẠT
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    CHƯA ĐẠT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ $attempt->completed_at ? $attempt->completed_at->format('d/m/Y H:i') : $attempt->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap">
                                                <a href="{{ route('admin.quizzes.results', $attempt->quiz_id) }}" class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition whitespace-nowrap shrink-0 shadow-2xs" title="Xem bảng điểm bài kiểm tra này">
                                                    Xem bảng điểm
                                                </a>
                                                <form action="{{ route('admin.attempts.destroy', $attempt->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa điểm/lượt làm bài này của học viên {{ addslashes($attempt->student->name ?? 'này') }}? Thao tác này không thể hoàn tác.');" 
                                                      class="inline-block shrink-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="w-7 h-7 inline-flex items-center justify-center rounded-xl text-xs font-black bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 hover:border-rose-600 transition shadow-2xs cursor-pointer active:scale-95 shrink-0"
                                                            title="Xóa kết quả làm bài của học viên">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
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
                    <div class="p-4 border-t border-slate-100">
                        {{ $attempts->appends(array_filter(['tab' => 'attempts', 'search' => $search]))->links() }}
                    </div>
                @endif
            </div>

        <!-- TAB 5: USERS -->
        @elseif($currentTab === 'users')
            @if(!empty($search))
                <div class="p-3.5 mb-5 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                    <div class="flex items-center gap-2 text-slate-700">
                        <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                        <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $users->total() }} tài khoản khớp)</span>
                    </div>
                    <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}"
                       class="font-bold text-slate-500 hover:text-pink-600 transition">
                        &times; Xóa tìm kiếm
                    </a>
                </div>
            @endif

            <div x-data="{
                walletModal: false,
                selectedUser: { id: null, name: '', balance: 0 },
                newBalance: 0,
                note: '',
                openWalletModal(id, name, balance) {
                    this.selectedUser = { id: id, name: name, balance: balance };
                    this.newBalance = balance;
                    this.note = '';
                    this.walletModal = true;
                },
                addAmount(amount) {
                    this.newBalance = Math.max(0, parseInt(this.newBalance || 0) + amount);
                },
                formatNumber(num) {
                    return new Intl.NumberFormat('vi-VN').format(num || 0);
                }
            }" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden relative">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/40">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Danh Sách Tài Khoản & Phân Quyền</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Admin có thể quản lý vai trò, kiểm tra số dư ví và điều chỉnh trực tiếp ví tiền cho từng tài khoản.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <span class="px-3 py-1 rounded-xl bg-pink-50 text-pink-700 font-bold border border-pink-200/80">Admin: {{ $stats['admins_count'] ?? 0 }}</span>
                        <span class="px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold border border-indigo-200/80">Giảng viên: {{ $stats['teachers_count'] ?? 0 }}</span>
                        <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200/80">Học viên: {{ $stats['students_count'] ?? 0 }}</span>
                    </div>
                </div>

                @if($users->isEmpty())
                    <div class="p-16 text-center text-slate-500 text-sm">
                        @if(!empty($search))
                            <div class="max-w-md mx-auto space-y-3">
                                <p class="font-bold text-slate-700">Không tìm thấy tài khoản người dùng khớp với "{{ $search }}"</p>
                                <p class="text-xs text-slate-400">Vui lòng thử tìm kiếm bằng tên, email hoặc vai trò khác (ví dụ: "admin", "giảng viên", "học viên").</p>
                                <div class="pt-2">
                                    <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                                        Xóa tìm kiếm
                                    </a>
                                </div>
                            </div>
                        @else
                            Chưa có dữ liệu người dùng.
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <th class="py-3 px-3.5 whitespace-nowrap">Họ và tên</th>
                                    <th class="py-3 px-2.5 whitespace-nowrap">Email</th>
                                    <th class="py-3 px-2 text-center whitespace-nowrap">Vai trò hiện tại</th>
                                    <th class="py-3 px-2 text-center whitespace-nowrap">Ví (Số dư)</th>
                                    <th class="py-3 px-2 text-center whitespace-nowrap">Ngày đăng ký</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Chỉnh sửa vai trò</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($users as $user)
                                    <tr class="hover:bg-pink-50/20 transition">
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                             <div class="flex items-center gap-2.5">
                                                <a href="{{ route('users.profile', $user) }}" target="_blank" class="shrink-0 transition-transform hover:scale-105" title="Xem trang cá nhân của {{ $user->name }}">
                                                    @if($user->avatar_url)
                                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-lg bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </a>
                                                <div>
                                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                        <a href="{{ route('users.profile', $user) }}" target="_blank" class="hover:text-pink-600 transition-colors flex items-center gap-1" title="Xem trang cá nhân">
                                                            <span>{{ $user->name }}</span>
                                                            <svg class="w-3 h-3 text-slate-300 hover:text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                        </a>
                                                        @if(auth()->id() === $user->id)
                                                            <span class="text-[10px] font-extrabold bg-pink-100 text-pink-700 px-1.5 py-0.5 rounded-md border border-pink-200">Bạn</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">ID: #{{ $user->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2.5 text-slate-600 text-xs font-medium max-w-[170px] truncate" title="{{ $user->email }}">
                                            {{ $user->email }}
                                        </td>
                                        <td class="py-3 px-2 text-center whitespace-nowrap">
                                            @if($user->role === 'admin')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-700 border border-pink-200 whitespace-nowrap shrink-0">
                                                    Admin
                                                </span>
                                            @elseif($user->role === 'teacher')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 whitespace-nowrap shrink-0">
                                                    Giảng viên
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap shrink-0">
                                                    Học viên
                                                </span>
                                            @endif
                                        </td>
                                        <!-- Cột Ví (Số dư) & Nút sửa -->
                                        <td class="py-3 px-2 text-center whitespace-nowrap">
                                            <div class="inline-flex items-center justify-center gap-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-black {{ ($user->balance ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-500 border border-slate-200/60' }}">
                                                    <svg class="w-3.5 h-3.5 {{ ($user->balance ?? 0) > 0 ? 'text-emerald-600' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                    </svg>
                                                    <span>{{ number_format($user->balance ?? 0, 0, ',', '.') }} đ</span>
                                                </span>
                                                <button type="button"
                                                        @click="openWalletModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ (int) ($user->balance ?? 0) }})"
                                                        class="p-1 rounded text-slate-400 hover:text-pink-600 hover:bg-pink-50 transition cursor-pointer border border-transparent hover:border-pink-200 shrink-0"
                                                        title="Chỉnh sửa số dư ví của {{ $user->name }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2 text-center text-xs whitespace-nowrap">
                                            <span class="font-medium text-slate-700">{{ $user->created_at->format('d/m/Y') }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $user->created_at->format('H:i') }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @if($user->role === 'admin')
                                                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 text-xs font-semibold border border-slate-200" title="{{ $user->id === auth()->id() ? 'Tài khoản của bạn' : 'Vai trò Quản trị viên cố định' }}">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                    </svg>
                                                    <span>{{ $user->id === auth()->id() ? 'Tài khoản của bạn' : 'Cố định' }}</span>
                                                </div>
                                            @else
                                                <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="inline-flex items-center justify-center gap-1.5 flex-nowrap shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật vai trò cho người dùng \'{{ addslashes($user->name) }}\'?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="role" class="text-xs font-medium py-1 pl-2 pr-6 w-24 sm:w-28 rounded-xl border border-slate-200 bg-white text-slate-800 cursor-pointer focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 shadow-2xs">
                                                        <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Học viên</option>
                                                        <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Giảng viên</option>
                                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                    </select>
                                                    <button type="submit" class="px-2.5 py-1 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition active:scale-95 inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shrink-0" title="Cập nhật vai trò">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span>Lưu</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @if($user->role === 'admin')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-400 text-xs font-semibold border border-slate-200/60" title="Không thể xóa tài khoản Quản trị viên">
                                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                    </svg>
                                                    <span>Bảo vệ</span>
                                                </span>
                                            @else
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản \'{{ addslashes($user->name) }}\' ({{ addslashes($user->email) }})? Mọi dữ liệu liên quan sẽ bị xóa.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 hover:border-transparent text-xs font-bold transition inline-flex items-center gap-1 shrink-0 cursor-pointer shadow-2xs active:scale-95" title="Xóa tài khoản này">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        <span>Xóa</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100">
                        {{ $users->appends(array_filter(['tab' => 'users', 'search' => $search]))->links() }}
                    </div>
                @endif

                <!-- Modal Chỉnh sửa số dư Ví người dùng -->
                <div x-show="walletModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                     style="display: none;"
                     @keydown.escape.window="walletModal = false">

                    <div @click.away="walletModal = false"
                         x-show="walletModal"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-3xl shadow-2xl border border-slate-100 w-full max-w-lg overflow-hidden">

                        <!-- Modal Header -->
                        <div class="p-6 bg-gradient-to-r from-pink-500 to-rose-600 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-base leading-tight">Chỉnh Sửa Ví Người Dùng</h4>
                                    <p class="text-xs text-white/80 mt-0.5" x-text="selectedUser.name + ' (#' + selectedUser.id + ')'"></p>
                                </div>
                            </div>
                            <button type="button" @click="walletModal = false" class="text-white/80 hover:text-white transition p-1 cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <!-- Modal Body Form -->
                        <form :action="'{{ url('admin/users') }}/' + selectedUser.id + '/wallet'" method="POST" class="p-6 space-y-5">
                            @csrf
                            @method('PATCH')

                            <!-- Số dư hiện tại -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Số dư hiện tại</span>
                                    <span class="text-sm font-extrabold text-slate-700" x-text="formatNumber(selectedUser.balance) + ' đ'"></span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Chênh lệch</span>
                                    <span class="text-sm font-black" :class="(newBalance - selectedUser.balance) >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                          x-text="((newBalance - selectedUser.balance) >= 0 ? '+' : '') + formatNumber(newBalance - selectedUser.balance) + ' đ'">
                                    </span>
                                </div>
                            </div>

                            <!-- Ô nhập số tiền mới -->
                            <div>
                                <label for="admin_wallet_balance" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">
                                    Số dư ví mới (VNĐ) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number"
                                           id="admin_wallet_balance"
                                           name="balance"
                                           x-model.number="newBalance"
                                           min="0"
                                           max="1000000000"
                                           step="1000"
                                           required
                                           class="w-full px-4 py-3 text-lg font-black text-slate-900 rounded-2xl border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 focus:outline-none transition pr-12">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">đ</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-1">
                                    <span>Đọc số:</span>
                                    <strong class="text-pink-600 font-extrabold" x-text="formatNumber(newBalance) + ' đồng'"></strong>
                                </p>
                            </div>

                            <!-- Phím tắt cộng nhanh -->
                            <div>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Phím tắt cộng/đặt nhanh</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="addAmount(50000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-pink-100 hover:text-pink-700 text-slate-700 text-xs font-bold transition cursor-pointer">+50.000 đ</button>
                                    <button type="button" @click="addAmount(100000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-pink-100 hover:text-pink-700 text-slate-700 text-xs font-bold transition cursor-pointer">+100.000 đ</button>
                                    <button type="button" @click="addAmount(200000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-pink-100 hover:text-pink-700 text-slate-700 text-xs font-bold transition cursor-pointer">+200.000 đ</button>
                                    <button type="button" @click="addAmount(500000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-pink-100 hover:text-pink-700 text-slate-700 text-xs font-bold transition cursor-pointer">+500.000 đ</button>
                                    <button type="button" @click="addAmount(1000000)" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-pink-100 hover:text-pink-700 text-slate-700 text-xs font-bold transition cursor-pointer">+1.000.000 đ</button>
                                    <button type="button" @click="newBalance = 0" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition cursor-pointer">Về 0 đ</button>
                                </div>
                            </div>

                            <!-- Ghi chú điều chỉnh -->
                            <div>
                                <label for="admin_wallet_note" class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">
                                    Ghi chú / Lý do điều chỉnh (Tùy chọn)
                                </label>
                                <input type="text"
                                       id="admin_wallet_note"
                                       name="note"
                                       x-model="note"
                                       maxlength="255"
                                       placeholder="Ví dụ: Nạp bù hệ thống, thưởng sự kiện, hoàn tiền..."
                                       class="w-full px-4 py-2.5 text-xs text-slate-800 rounded-2xl border border-slate-200 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 focus:outline-none transition">
                            </div>

                            <!-- Actions -->
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                                <button type="button" @click="walletModal = false" class="px-5 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                                    Hủy bỏ
                                </button>
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold shadow-md shadow-pink-600/30 transition cursor-pointer flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Lưu số dư ví</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <!-- TAB 5: REPORTS (QUẢN LÝ BÁO CÁO VI PHẠM) -->
        @elseif($currentTab === 'reports')
            <div x-data="{
                actionModal: false,
                reportId: null,
                actionUrl: '',
                actionType: 'resolve',
                reporterName: '',
                targetTypeLabel: '',
                targetName: '',
                targetContent: '',
                targetDetails: '',
                reasonLabel: '',
                userAvatarUrl: '',
                isUserReport: false,
                isReviewReport: false,
                adminNotes: '',
                resetAvatar: false,
                deleteContent: true,

                openAction(report, targetType, targetName, targetContent, reasonLabel, detailsText, avatarUrl) {
                    this.reportId = report.id;
                    this.actionUrl = '{{ url('/admin/reports') }}/' + report.id + '/action';
                    this.actionType = 'resolve';
                    this.reporterName = report.reporter ? report.reporter.name : 'Người dùng ẩn danh';
                    this.targetTypeLabel = targetType;
                    this.targetName = targetName;
                    this.targetContent = targetContent;
                    this.reasonLabel = reasonLabel;
                    this.targetDetails = detailsText;
                    this.userAvatarUrl = avatarUrl || '';
                    this.isUserReport = (report.reportable_type === 'App\\Models\\User' || report.reportable_type === 'user');
                    this.isReviewReport = (report.reportable_type === 'App\\Models\\CourseReview' || report.reportable_type === 'review');
                    this.adminNotes = report.admin_notes || '';
                    this.resetAvatar = false;
                    this.deleteContent = true;
                    this.actionModal = true;
                }
            }" class="space-y-6">

                <!-- Header Info & Statistics Cards for Reports Tab -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tổng báo cáo</span>
                        <div class="text-2xl font-black text-slate-900">{{ $stats['total_reports'] }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Từ người dùng toàn hệ thống</div>
                    </div>
                    <div class="bg-white rounded-3xl p-5 border border-amber-200/80 shadow-2xs bg-amber-50/20">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block mb-1">Đang chờ xử lý</span>
                        <div class="text-2xl font-black text-amber-600">{{ $stats['pending_reports'] }}</div>
                        <div class="text-[11px] text-amber-600/80 mt-1">Cần xem xét kiểm duyệt</div>
                    </div>
                    <div class="bg-white rounded-3xl p-5 border border-emerald-200/80 shadow-2xs bg-emerald-50/20">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block mb-1">Đã giải quyết</span>
                        <div class="text-2xl font-black text-emerald-600">{{ $stats['resolved_reports'] }}</div>
                        <div class="text-[11px] text-emerald-600/80 mt-1">Đã xử lý thỏa đáng</div>
                    </div>
                    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Đã bác bỏ</span>
                        <div class="text-2xl font-black text-slate-700">{{ $stats['dismissed_reports'] }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Không vi phạm tiêu chuẩn</div>
                    </div>
                </div>

                <!-- Main Reports Table Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="font-extrabold text-base text-slate-900 tracking-tight flex items-center gap-2">
                                <span>Danh sách Báo cáo vi phạm</span>
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-pink-100 text-pink-700">
                                    {{ $reports->total() }} bản ghi
                                </span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Kiểm tra các nội dung hoặc tài khoản bị khiếu nại để giữ môi trường học tập lành mạnh.</p>
                        </div>
                    </div>

                    @if($reports->isEmpty())
                        <div class="p-16 text-center space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="font-extrabold text-slate-800 text-sm">Không tìm thấy báo cáo vi phạm nào</h3>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">
                                {{ !empty($search) || !empty($statusFilter) ? 'Không có báo cáo nào khớp với điều kiện lọc hiện tại.' : 'Hệ thống hiện không có báo cáo vi phạm nào cần xử lý.' }}
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100 text-[11px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/70">
                                        <th class="py-3.5 px-5">ID & Thời gian</th>
                                        <th class="py-3.5 px-5">Người báo cáo</th>
                                        <th class="py-3.5 px-5">Đối tượng bị báo cáo</th>
                                        <th class="py-3.5 px-5">Lý do & Chi tiết</th>
                                        <th class="py-3.5 px-5 text-center">Trạng thái</th>
                                        <th class="py-3.5 px-5 text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs">
                                    @foreach($reports as $report)
                                        <tr class="hover:bg-pink-50/30 transition">
                                            <!-- ID & Thời gian -->
                                            <td class="py-4 px-5 align-top whitespace-nowrap">
                                                <div class="font-extrabold text-slate-900">#{{ $report->id }}</div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $report->created_at->diffForHumans() }}</div>
                                            </td>

                                            <!-- Người báo cáo -->
                                            <td class="py-4 px-5 align-top">
                                                @if($report->reporter)
                                                    <div class="flex items-center gap-2.5">
                                                        <a href="{{ route('users.profile', $report->reporter->id) }}" class="shrink-0 hover:opacity-80 transition" title="Xem hồ sơ">
                                                            @if($report->reporter->avatar_url)
                                                                <img src="{{ $report->reporter->avatar_url }}" alt="{{ $report->reporter->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                                            @else
                                                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                                                    {{ strtoupper(substr($report->reporter->name, 0, 1)) }}
                                                                </div>
                                                            @endif
                                                        </a>
                                                        <div class="min-w-0">
                                                            <a href="{{ route('users.profile', $report->reporter->id) }}" class="font-bold text-slate-900 hover:text-pink-600 transition truncate block">
                                                                {{ $report->reporter->name }}
                                                            </a>
                                                            <span class="text-[11px] text-slate-400 block truncate">{{ $report->reporter->email }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="italic text-slate-400">Người dùng đã xóa</span>
                                                @endif
                                            </td>

                                            <!-- Đối tượng bị báo cáo (Polymorphic) -->
                                            <td class="py-4 px-5 align-top max-w-xs">
                                                @if($report->reportable_type === 'App\Models\User' || $report->reportable_type === 'user')
                                                    <div class="space-y-1.5">
                                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                            <span>Tài khoản người dùng</span>
                                                        </span>
                                                        @if($report->reportable)
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <a href="{{ route('users.profile', $report->reportable->id) }}" class="shrink-0 hover:opacity-80 transition" title="Xem hồ sơ người bị báo cáo">
                                                                    @if($report->reportable->avatar_url)
                                                                        <img src="{{ $report->reportable->avatar_url }}" alt="{{ $report->reportable->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-indigo-200">
                                                                    @else
                                                                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs border border-indigo-200">
                                                                            {{ strtoupper(substr($report->reportable->name, 0, 1)) }}
                                                                        </div>
                                                                    @endif
                                                                </a>
                                                                <div class="min-w-0">
                                                                    <a href="{{ route('users.profile', $report->reportable->id) }}" class="font-bold text-slate-900 hover:text-pink-600 transition block truncate">
                                                                        {{ $report->reportable->name }}
                                                                    </a>
                                                                    <div class="text-[11px] text-slate-400 truncate">{{ $report->reportable->email }}</div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="text-[11px] italic text-slate-400">Tài khoản này đã bị xóa khỏi hệ thống.</div>
                                                        @endif
                                                    </div>
                                                @elseif($report->reportable_type === 'App\Models\CourseReview' || $report->reportable_type === 'review')
                                                    <div class="space-y-1.5">
                                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                            <span>Bình luận / Đánh giá</span>
                                                        </span>
                                                        @if($report->reportable)
                                                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 space-y-1 text-[11px]">
                                                                <div class="flex items-center justify-between gap-1">
                                                                    <span class="font-bold text-slate-800 truncate">
                                                                        Tác giả: {{ $report->reportable->user->name ?? 'Người dùng' }}
                                                                    </span>
                                                                    <span class="text-amber-500 font-extrabold shrink-0">{{ $report->reportable->rating }} ★</span>
                                                                </div>
                                                                @if(!empty($report->reportable->comment))
                                                                    <p class="text-slate-600 italic line-clamp-2">"{!! e($report->reportable->comment) !!}"</p>
                                                                @endif
                                                                @if($report->reportable->course)
                                                                    <div class="pt-1 text-[10px] text-slate-400 truncate">
                                                                        Khóa học: <a href="{{ route('courses.show', $report->reportable->course->slug ?: $report->reportable->course->id) }}" target="_blank" class="text-pink-600 hover:underline font-semibold">{{ $report->reportable->course->title }}</a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="text-[11px] italic text-slate-400">Bình luận này đã bị xóa hoặc không còn tồn tại.</div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-slate-500">{{ $report->type_label }} (#{{ $report->reportable_id }})</span>
                                                @endif
                                            </td>

                                            <!-- Lý do & Chi tiết -->
                                            <td class="py-4 px-5 align-top max-w-xs">
                                                <div class="space-y-1.5">
                                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                                        <span>{{ $report->reason_label }}</span>
                                                    </div>
                                                    @if(!empty($report->details))
                                                        <div class="p-2.5 rounded-xl bg-rose-50/50 border border-rose-100 text-rose-900 text-[11px] leading-relaxed">
                                                            <span class="font-bold text-rose-700">Lý do từ người báo cáo:</span>
                                                            <p class="mt-0.5 whitespace-pre-wrap">{{ $report->details }}</p>
                                                        </div>
                                                    @else
                                                        <span class="text-[11px] text-slate-400 italic">Không có mô tả chi tiết thêm.</span>
                                                    @endif

                                                    @if(!empty($report->admin_notes))
                                                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-700">
                                                            <span class="font-bold text-slate-900">Ghi chú của Admin:</span>
                                                            <p class="mt-0.5 italic">{{ $report->admin_notes }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- Trạng thái -->
                                            <td class="py-4 px-5 align-top text-center whitespace-nowrap">
                                                @if($report->status === 'pending')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        Chờ xử lý
                                                    </span>
                                                @elseif($report->status === 'resolved')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Đã xử lý
                                                    </span>
                                                    @if($report->resolvedBy)
                                                        <div class="text-[10px] text-slate-400 mt-1">Bởi: {{ $report->resolvedBy->name }}</div>
                                                        <div class="text-[10px] text-slate-400">{{ $report->resolved_at?->diffForHumans() }}</div>
                                                    @endif
                                                @elseif($report->status === 'dismissed')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                        Đã bác bỏ
                                                    </span>
                                                    @if($report->resolvedBy)
                                                        <div class="text-[10px] text-slate-400 mt-1">Bởi: {{ $report->resolvedBy->name }}</div>
                                                        <div class="text-[10px] text-slate-400">{{ $report->resolved_at?->diffForHumans() }}</div>
                                                    @endif
                                                @endif
                                            </td>

                                            <!-- Thao tác -->
                                            <td class="py-4 px-5 align-top text-right whitespace-nowrap">
                                                @php
                                                    $targetName = '';
                                                    $targetContent = '';
                                                    $avatarUrl = '';
                                                    if ($report->reportable instanceof \App\Models\User) {
                                                        $targetName = $report->reportable->name;
                                                        $avatarUrl = $report->reportable->avatar_url ?? '';
                                                    } elseif ($report->reportable instanceof \App\Models\CourseReview) {
                                                        $targetName = $report->reportable->user->name ?? 'Người dùng';
                                                        $targetContent = $report->reportable->comment ?? '';
                                                    }
                                                @endphp

                                                <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                    <button type="button"
                                                            @click="openAction({{ Js::from($report) }}, '{{ $report->type_label }}', '{{ addslashes($targetName) }}', '{{ addslashes($targetContent) }}', '{{ addslashes($report->reason_label) }}', '{{ addslashes($report->details ?? '') }}', '{{ $avatarUrl }}')"
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold text-xs transition {{ $report->status === 'pending' ? 'bg-pink-600 hover:bg-pink-700 text-white shadow-xs cursor-pointer' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer' }}">
                                                        <span>{{ $report->status === 'pending' ? 'Xử lý vi phạm' : 'Xem chi tiết' }}</span>
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                    </button>

                                                    <form method="POST" action="{{ route('admin.reports.destroy', $report->id) }}" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi báo cáo #{{ $report->id }} này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center justify-center p-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition cursor-pointer"
                                                                title="Xóa báo cáo này">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

                        <div class="p-5 border-t border-slate-100">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>

                <!-- Modal Xử lý / Xem chi tiết Báo cáo vi phạm -->
                <div x-show="actionModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     style="display: none;">
                    <div class="fixed inset-0 bg-neutral-950/70 backdrop-blur-xs transition-opacity"
                         @click="actionModal = false"
                         x-show="actionModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"></div>

                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div x-show="actionModal"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-pink-100 p-6 sm:p-8">

                            <form method="POST" :action="actionUrl" class="space-y-5">
                                @csrf
                                <div class="flex items-start justify-between gap-4 pb-4 border-b border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Xử lý báo cáo vi phạm</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">Báo cáo #<span x-text="reportId"></span> &bull; Người gửi: <strong class="text-slate-800" x-text="reporterName"></strong></p>
                                        </div>
                                    </div>
                                    <button type="button" @click="actionModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition cursor-pointer">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <!-- Nội dung bị khiếu nại tóm tắt -->
                                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-700" x-text="targetTypeLabel"></span>
                                        <span class="px-2 py-0.5 rounded-md font-extrabold text-[10px] uppercase bg-pink-100 text-pink-700" x-text="reasonLabel"></span>
                                    </div>
                                    <div class="font-bold text-slate-900">
                                        Đối tượng: <span class="text-pink-600" x-text="targetName"></span>
                                    </div>
                                    <template x-if="targetContent">
                                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 italic text-slate-700" x-text="'&ldquo;' + targetContent + '&rdquo;'"></div>
                                    </template>
                                    <template x-if="targetDetails">
                                        <div class="pt-1 text-[11px] text-slate-600">
                                            <strong class="text-slate-800">Lý do chi tiết:</strong>
                                            <p class="mt-0.5" x-text="targetDetails"></p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Quyết định xử lý của Admin -->
                                <div class="space-y-3">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        Quyết định của Quản trị viên: <span class="text-rose-500">*</span>
                                    </label>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
                                               :class="actionType === 'resolve' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold ring-1 ring-emerald-500/30' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                            <input type="radio" name="action" value="resolve" x-model="actionType" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" required>
                                            <div>
                                                <span class="text-xs block font-bold">Xác nhận vi phạm &amp; Giải quyết</span>
                                                <span class="text-[11px] font-normal text-slate-500 block mt-0.5">Xử lý đối tượng vi phạm và khép lại báo cáo.</span>
                                            </div>
                                        </label>

                                        <label class="flex items-start gap-3 p-3.5 rounded-2xl border cursor-pointer transition"
                                               :class="actionType === 'dismiss' ? 'border-slate-500 bg-slate-50 text-slate-900 font-bold ring-1 ring-slate-500/30' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                                            <input type="radio" name="action" value="dismiss" x-model="actionType" class="mt-0.5 text-slate-700 focus:ring-slate-500">
                                            <div>
                                                <span class="text-xs block font-bold">Bác bỏ báo cáo</span>
                                                <span class="text-[11px] font-normal text-slate-500 block mt-0.5">Nội dung hợp lệ, không vi phạm tiêu chuẩn.</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Tùy chọn xử lý đi kèm khi xác nhận vi phạm -->
                                <div x-show="actionType === 'resolve'" class="space-y-2.5 p-4 rounded-2xl bg-amber-50/50 border border-amber-200">
                                    <span class="block text-xs font-bold text-amber-900 uppercase tracking-wider">Hành động khắc phục:</span>

                                    <!-- Nếu là User và có avatar -->
                                    <template x-if="isUserReport">
                                        <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer">
                                            <input type="checkbox" name="reset_avatar" value="1" x-model="resetAvatar" class="rounded text-pink-600 focus:ring-pink-500">
                                            <span>Gỡ bỏ ảnh đại diện (avatar) của tài khoản này về mặc định</span>
                                        </label>
                                    </template>

                                    <!-- Nếu là Review -->
                                    <template x-if="isReviewReport">
                                        <label class="flex items-center gap-2.5 text-xs text-slate-800 cursor-pointer">
                                            <input type="checkbox" name="delete_content" value="1" x-model="deleteContent" class="rounded text-rose-600 focus:ring-rose-500">
                                            <span>Xóa bình luận / nhận xét vi phạm này khỏi khóa học</span>
                                        </label>
                                    </template>
                                </div>

                                <!-- Ghi chú phản hồi / lý do xử lý của Admin -->
                                <div class="space-y-1.5">
                                    <label for="admin_report_notes" class="block text-xs font-bold text-slate-700">
                                        Ghi chú xử lý của Quản trị viên (Tùy chọn):
                                    </label>
                                    <textarea id="admin_report_notes"
                                              name="admin_notes"
                                              x-model="adminNotes"
                                              rows="2"
                                              placeholder="Ví dụ: Đã gỡ avatar phản cảm và nhắc nhở thành viên..."
                                              class="w-full text-xs rounded-2xl border-slate-200 focus:border-pink-500 focus:ring-pink-500 shadow-2xs placeholder-slate-400"></textarea>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-100">
                                    <button type="submit"
                                            form="delete-report-modal-form"
                                            class="px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-bold text-xs hover:bg-rose-100 hover:text-rose-800 transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Xóa báo cáo</span>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="actionModal = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition cursor-pointer">
                                            Hủy bỏ
                                        </button>
                                        <button type="submit"
                                                :class="actionType === 'resolve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20' : 'bg-slate-800 hover:bg-slate-900 text-white shadow-slate-800/20'"
                                                class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition active:scale-95 cursor-pointer">
                                            <span x-text="actionType === 'resolve' ? 'Lưu & Giải quyết vi phạm' : 'Lưu & Bác bỏ báo cáo'"></span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Form xóa báo cáo độc lập từ modal -->
                            <form id="delete-report-modal-form" method="POST" :action="'{{ url('/admin/reports') }}/' + reportId" class="hidden" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi báo cáo này?')">
                                @csrf
                                @method('DELETE')
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</x-admin-layout>
