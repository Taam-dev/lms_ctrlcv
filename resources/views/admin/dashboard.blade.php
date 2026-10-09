<x-admin-layout :breadcrumb="$currentTab === 'overview' ? 'Tổng quan Dashboard' : ($currentTab === 'courses' ? 'Quản lý Khóa học' : ($currentTab === 'quizzes' ? 'Quản lý Bài kiểm tra' : ($currentTab === 'lessons' ? 'Quản lý Bài giảng' : 'Quản lý Người dùng')))">
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
                        <span>Quản Lý & Duyệt Khóa Học</span>
                    @elseif($currentTab === 'quizzes')
                        <span>Quản Lý & Duyệt Bài Kiểm Tra (Quizzes)</span>
                    @elseif($currentTab === 'lessons')
                        <span>Danh Sách Tất Cả Bài Giảng</span>
                    @elseif($currentTab === 'users')
                        <span>Quản Lý Tài Khoản Người Dùng</span>
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    @if($currentTab === 'overview')
                        Tổng hợp chỉ số KPI, danh sách cần phê duyệt cấp thiết và các hoạt động đào tạo mới nhất.
                    @elseif($currentTab === 'courses')
                        Kiểm duyệt và quản trị tất cả các khóa học do giảng viên đăng tải lên nền tảng.
                    @elseif($currentTab === 'quizzes')
                        Xem trước, thẩm định chất lượng và phê duyệt các đề thi trắc nghiệm trực tuyến.
                    @elseif($currentTab === 'lessons')
                        Theo dõi toàn bộ bài giảng video và văn bản trong các khóa học.
                    @elseif($currentTab === 'users')
                        Danh sách tài khoản học viên, giảng viên và Admin trong hệ thống.
                    @endif
                </p>
            </div>

            <!-- Quick Action & Tab Switcher Bar -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if(in_array($currentTab, ['courses', 'quizzes']))
                    <!-- Bộ lọc trạng thái khi đang ở tab khóa học hoặc quiz -->
                    <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold">
                        <a href="{{ route('admin.dashboard', ['tab' => $currentTab]) }}" 
                           class="px-3 py-1.5 rounded-xl transition {{ empty($statusFilter) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            Tất cả ({{ $currentTab === 'courses' ? $stats['total_courses'] : $stats['total_quizzes'] }})
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => $currentTab, 'status' => 'pending']) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                            Chờ duyệt
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => $currentTab, 'status' => 'approved']) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                            Đã duyệt
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => $currentTab, 'status' => 'rejected']) }}" 
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                            Đã loại bỏ
                        </a>
                    </div>
                @endif

                @if(in_array($currentTab, ['overview', 'courses']))
                    <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-700 text-white shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        Tạo khóa học mới
                    </a>
                @endif
                @if(in_array($currentTab, ['overview', 'quizzes']))
                    <a href="{{ route('admin.quizzes.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-700 text-white shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        Tạo Quiz mới
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">

        <!-- ============================================== -->
        <!-- 4 STATISTIC OVERVIEW CARDS (HIỂN THỊ TỔNG QUAN)-->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- 1. Courses Stats -->
            <a href="{{ route('admin.dashboard', ['tab' => 'courses']) }}" 
               class="block bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Khóa học</span>
                    
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_courses'] }}</div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">{{ $stats['approved_courses'] }} đã duyệt</span>
                    @if($stats['pending_courses'] > 0)
                        <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md">{{ $stats['pending_courses'] }} chờ duyệt</span>
                    @endif
                </div>
            </a>

            <!-- 2. Quizzes Stats -->
            <a href="{{ route('admin.dashboard', ['tab' => 'quizzes']) }}" 
               class="block bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Bài kiểm tra (Quizzes)</span>
                    
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_quizzes'] }}</div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md">{{ $stats['approved_quizzes'] }} đã duyệt</span>
                    @if($stats['pending_quizzes'] > 0)
                        <span class="text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-md">{{ $stats['pending_quizzes'] }} chờ duyệt</span>
                    @endif
                </div>
            </a>

            <!-- 3. Lessons Stats -->
            <a href="{{ route('admin.dashboard', ['tab' => 'lessons']) }}" 
               class="block bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:border-pink-300 hover:shadow-md transition group relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Tổng số Bài giảng</span>
                    
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_lessons'] }}</div>
                <div class="text-xs text-slate-500">
                    Bao gồm video bài giảng và tài liệu học tập
                </div>
            </a>

            <!-- 4. Users Stats -->
            <a href="{{ route('admin.dashboard', ['tab' => 'users']) }}" 
               class="block bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md transition group relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 transition">Người dùng hệ thống</span>
                    
                </div>
                <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_users'] }}</div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-pink-700 font-bold bg-pink-50 px-2 py-0.5 rounded-md">{{ $stats['teachers_count'] }} GV</span>
                    <span class="text-slate-700 font-bold bg-slate-100 px-2 py-0.5 rounded-md">{{ $stats['students_count'] }} Học viên</span>
                </div>
            </a>
        </div>

        <!-- ============================================== -->
        <!-- PENDING APPROVAL ALERT BANNER                  -->
        <!-- ============================================== -->
        @php
            $pendingTotal = $stats['pending_courses'] + $stats['pending_quizzes'];
        @endphp
        @if($pendingTotal > 0 && $currentTab === 'overview')
            <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Cần chú ý: Có {{ $pendingTotal }} nội dung đang chờ phê duyệt</h3>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Bao gồm <strong class="text-amber-800">{{ $stats['pending_courses'] }} khóa học</strong> và <strong class="text-amber-800">{{ $stats['pending_quizzes'] }} bài kiểm tra Quiz</strong> do giảng viên gửi lên.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
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
                        <a href="{{ route('admin.dashboard', ['tab' => 'quizzes']) }}" class="text-xs font-bold text-pink-600 hover:text-pink-800 transition">
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
                                    <div class="text-[10px] text-slate-400">GV: {{ $course->teacher->name ?? 'N/A' }} &bull; {{ $course->lessons_count }} bài giảng</div>
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
                @if($courses->isEmpty())
                    <div class="p-16 text-center">
                        
                        <h3 class="font-bold text-slate-800 text-base mb-1">Không tìm thấy khóa học nào</h3>
                        <p class="text-xs text-slate-500">Chưa có khóa học nào phù hợp với bộ lọc hiện tại.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1060px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 whitespace-nowrap">Khóa học</th>
                                    <th class="py-4 px-4 whitespace-nowrap">Giảng viên</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số bài học</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Ngày gửi</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Hành động của Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($courses as $course)
                                    <tr class="hover:bg-pink-50/20 transition">
                                        <td class="py-4 px-6 min-w-[280px]">
                                            <div class="font-bold text-slate-900 text-base mb-1">{{ $course->title }}</div>
                                            <div class="text-xs text-slate-500 line-clamp-1 max-w-md">
                                                {{ $course->description ?? 'Chưa có mô tả' }}
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
                                                    <div class="font-semibold text-slate-800">{{ $course->teacher->name ?? 'Không rõ' }}</div>
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
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Chờ duyệt
                                                </span>
                                            @elseif($course->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Đã duyệt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Đã loại bỏ / Từ chối
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ $course->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
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
                                @endforeach
                            </tbody>
                        </table>
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
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1020px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 whitespace-nowrap">Bài kiểm tra</th>
                                    <th class="py-4 px-4 whitespace-nowrap">Khóa học & Giảng viên</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Số câu</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái</th>
                                    <th class="py-4 px-6 text-right whitespace-nowrap">Hành động của Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($quizzes as $quiz)
                                    <tr class="hover:bg-pink-50/20 transition">
                                        <td class="py-4 px-6 min-w-[240px]">
                                            <div class="font-bold text-slate-900 text-base mb-0.5">{{ $quiz->title }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                {{ $quiz->duration_minutes }} phút | Điểm chuẩn &ge; {{ $quiz->passing_score }}/10
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="font-semibold text-slate-800 text-xs">{{ $quiz->course->title ?? 'N/A' }}</div>
                                            <div class="text-[11px] text-slate-400">GV: {{ $quiz->teacher->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 whitespace-nowrap shrink-0">
                                                {{ $quiz->questions_count }} câu
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            @if($quiz->status === 'pending')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Chờ duyệt
                                                </span>
                                            @elseif($quiz->status === 'approved')
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Đã duyệt
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                    Đã loại bỏ
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-pink-200 text-pink-700 hover:bg-pink-50 text-xs font-bold transition whitespace-nowrap shrink-0">
                                                    Xem đề
                                                </a>
                                                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition whitespace-nowrap shrink-0">
                                                    Câu hỏi
                                                </a>
                                                <a href="{{ route('admin.quizzes.edit', $quiz->id) }}" 
                                                   class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition whitespace-nowrap shrink-0">
                                                    Sửa
                                                </a>

                                                @if($quiz->status === 'pending')
                                                    <form action="{{ route('admin.quizzes.approve', $quiz->id) }}" method="POST" class="inline shrink-0">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Duyệt
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.quizzes.reject', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn từ chối đề thi này?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Từ chối
                                                        </button>
                                                    </form>
                                                @elseif($quiz->status === 'approved')
                                                    <form action="{{ route('admin.quizzes.remove', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc muốn loại bỏ bài kiểm tra này?')">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
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
                                                        <button type="submit" class="inline-flex items-center gap-1 p-1.5 px-2.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0" title="Xóa vĩnh viễn khỏi Database">
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

        <!-- TAB 4: LESSONS (NHÓM THEO KHÓA HỌC DẠNG ACCORDION CÓ MŨI TÊN MỞ) -->
        @elseif($currentTab === 'lessons')
            <div x-data="{ openCourses: [{{ $coursesWithLessons->first()?->id ?? 'null' }}] }">
                <!-- Thanh công cụ điều khiển Accordion -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-600"></span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Danh Sách Bài Giảng Theo Khóa Học</h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-pink-50 text-pink-700 text-xs font-extrabold border border-pink-200/60">
                            {{ $coursesWithLessons->count() }} khóa học &bull; {{ $stats['total_lessons'] }} bài giảng
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-xs font-bold">
                        <button type="button" @click="openCourses = [{{ $coursesWithLessons->pluck('id')->join(',') }}]" class="text-pink-600 hover:text-pink-800 hover:underline cursor-pointer">
                            Mở tất cả
                        </button>
                        <span class="text-slate-300">&bull;</span>
                        <button type="button" @click="openCourses = []" class="text-slate-500 hover:text-slate-800 hover:underline cursor-pointer">
                            Thu gọn tất cả
                        </button>
                    </div>
                </div>

                @if($coursesWithLessons->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-16 text-center text-slate-500 text-sm">
                        Chưa có khóa học hoặc bài giảng nào trong hệ thống.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($coursesWithLessons as $course)
                            <div class="bg-white rounded-2xl border transition duration-200 overflow-hidden shadow-2xs" 
                                 :class="openCourses.includes({{ $course->id }}) ? 'border-pink-300 shadow-sm ring-1 ring-pink-200/50' : 'border-slate-200/80 hover:border-pink-200'">
                                
                                <!-- Accordion Header: Tên khóa học & Mũi tên ấn mở -->
                                <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 cursor-pointer select-none bg-slate-50/60 hover:bg-pink-50/30 transition"
                                     @click="openCourses.includes({{ $course->id }}) ? openCourses = openCourses.filter(id => id !== {{ $course->id }}) : openCourses.push({{ $course->id }})">
                                    
                                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                                        <!-- Mũi tên ấn mở -->
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-transform duration-200 shrink-0 shadow-2xs"
                                             :class="openCourses.includes({{ $course->id }}) ? 'bg-pink-600 text-white rotate-90' : 'bg-white text-slate-500 border border-slate-200'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <h4 class="font-black text-slate-900 text-base hover:text-pink-600 transition">
                                                    {{ $course->title }}
                                                </h4>
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold whitespace-nowrap shrink-0"
                                                      :class="openCourses.includes({{ $course->id }}) ? 'bg-pink-100 text-pink-700' : 'bg-slate-200 text-slate-700'">
                                                    {{ $course->lessons->count() }} bài học
                                                </span>
                                                @if($course->status === 'approved')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đã duyệt
                                                    </span>
                                                @elseif($course->status === 'pending')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Chờ duyệt
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã loại bỏ
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                                                <span>GV: <strong class="text-slate-700 font-semibold">{{ $course->teacher->name ?? 'Không rõ' }}</strong> ({{ $course->teacher->email ?? '' }})</span>
                                                @if($course->description)
                                                    <span class="text-slate-300">&bull;</span>
                                                    <span class="line-clamp-1 max-w-md text-slate-400">{{ $course->description }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0 flex-nowrap" @click.stop>
                                        <!-- Nút thêm bài giảng nhanh -->
                                        <a href="{{ route('admin.courses.lessons.create', $course->id) }}" 
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-pink-200 bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold shadow-2xs transition whitespace-nowrap shrink-0" 
                                           title="Thêm bài giảng vào khóa học này">
                                            + Thêm bài giảng
                                        </a>
                                    </div>
                                </div>

                                <!-- Accordion Content: Danh sách bài học của khóa học này -->
                                <div x-show="openCourses.includes({{ $course->id }})" x-cloak class="border-t border-slate-100 p-5 bg-white space-y-2.5">
                                    @if($course->lessons->isEmpty())
                                        <div class="py-8 text-center text-slate-500 text-xs bg-slate-50/70 rounded-2xl border border-dashed border-slate-200">
                                            <p class="font-bold text-slate-800 text-sm mb-1">Khóa học này chưa có bài giảng nào</p>
                                            <p class="text-slate-400 mb-3">Bạn có thể tạo bài giảng đầu tiên cho khóa học này ngay bây giờ.</p>
                                            <a href="{{ route('admin.courses.lessons.create', $course->id) }}" 
                                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 text-white font-bold text-xs hover:bg-pink-700 shadow-xs transition whitespace-nowrap shrink-0">
                                                + Thêm bài giảng đầu tiên
                                            </a>
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            @foreach($course->lessons as $lesson)
                                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3.5 bg-slate-50/60 hover:bg-pink-50/30 border border-slate-100 rounded-xl gap-3 transition">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <span class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center font-black text-xs shrink-0 shadow-2xs border border-pink-200/60" title="Bài số {{ $loop->iteration }} trong khóa học">
                                                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                                        </span>
                                                        <div class="min-w-0">
                                                            <div class="font-bold text-slate-900 text-sm truncate flex items-center gap-2">
                                                                <span>{{ $lesson->title }}</span>
                                                                @if($lesson->content_type === 'video')
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shrink-0 whitespace-nowrap">
                                                                        Video
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200 shrink-0 whitespace-nowrap">
                                                                        Văn bản
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                                                <span>Bài {{ $loop->iteration }}/{{ $course->lessons->count() }}</span>
                                                                <span class="text-slate-300">&bull;</span>
                                                                <span class="text-slate-500 font-medium">Thứ tự: #{{ $lesson->order_number }}</span>
                                                                <span class="text-slate-300">&bull;</span>
                                                                <span class="text-slate-400">ID: #{{ $lesson->id }}</span>
                                                                <span class="text-slate-300">&bull;</span>
                                                                <span>Cập nhật: {{ $lesson->updated_at->format('d/m/Y') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-1.5 self-end sm:self-center shrink-0 flex-nowrap">
                                                        <!-- Xem trước bài giảng -->
                                                        @if($course->id)
                                                            <a href="{{ route('student.lessons.show', ['courseId' => $course->id, 'lessonId' => $lesson->id]) }}" target="_blank" 
                                                               class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-pink-600 hover:bg-white text-xs font-semibold transition whitespace-nowrap shrink-0" 
                                                               title="Xem trước bài giảng">
                                                                Xem
                                                            </a>
                                                        @endif

                                                        <!-- Chỉnh sửa bài giảng (Admin có thể chỉnh sửa bài học) -->
                                                        <a href="{{ route('admin.lessons.edit', $lesson->id) }}" 
                                                           class="inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold border border-pink-200 transition whitespace-nowrap shrink-0" 
                                                           title="Chỉnh sửa nội dung bài giảng">
                                                            Sửa
                                                        </a>

                                                        <!-- Xóa bài giảng -->
                                                        <form action="{{ route('admin.lessons.destroy', $lesson->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài giảng \'{{ addslashes($lesson->title) }}\' không?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition whitespace-nowrap shrink-0" title="Xóa bài giảng">
                                                                Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach

                                            <!-- Nút thêm bài giảng nhanh ở cuối danh sách -->
                                            <div class="pt-2 text-center sm:text-left">
                                                <a href="{{ route('admin.courses.lessons.create', $course->id) }}" 
                                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-pink-600 hover:text-pink-800 p-2 rounded-lg hover:bg-pink-50 transition whitespace-nowrap">
                                                    + Thêm bài giảng tiếp theo cho khóa học này
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        <!-- TAB 5: USERS -->
        @elseif($currentTab === 'users')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/40">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Danh Sách Tài Khoản & Phân Quyền</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Admin có thể trực tiếp chỉ định và điều chỉnh vai trò (Admin, Giảng viên, Học viên) cho từng tài khoản.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <span class="px-3 py-1 rounded-xl bg-pink-50 text-pink-700 font-bold border border-pink-200/80">Admin: {{ $stats['admins_count'] ?? 0 }}</span>
                        <span class="px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold border border-indigo-200/80">Giảng viên: {{ $stats['teachers_count'] ?? 0 }}</span>
                        <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 font-bold border border-slate-200/80">Học viên: {{ $stats['students_count'] ?? 0 }}</span>
                    </div>
                </div>

                @if($users->isEmpty())
                    <div class="p-16 text-center text-slate-500 text-sm">
                        Chưa có dữ liệu người dùng.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <th class="py-4 px-6 whitespace-nowrap">Họ và tên</th>
                                    <th class="py-4 px-4 whitespace-nowrap">Email</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Vai trò hiện tại</th>
                                    <th class="py-4 px-4 text-center whitespace-nowrap">Ngày đăng ký</th>
                                    <th class="py-4 px-6 text-center whitespace-nowrap">Chỉnh sửa vai trò</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($users as $user)
                                    <tr class="hover:bg-pink-50/20 transition">
                                        <td class="py-4 px-6 whitespace-nowrap">
                                             <div class="flex items-center gap-3">
                                                @if($user->avatar_url)
                                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                                @else
                                                    <div class="w-9 h-9 rounded-xl bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                        {{ $user->name }}
                                                        @if(auth()->id() === $user->id)
                                                            <span class="text-[10px] font-extrabold bg-pink-100 text-pink-700 px-1.5 py-0.5 rounded-md border border-pink-200">Bạn</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-[11px] text-slate-400">ID: #{{ $user->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-slate-600 text-xs font-medium whitespace-nowrap">
                                            {{ $user->email }}
                                        </td>
                                        <td class="py-4 px-4 text-center whitespace-nowrap">
                                            @if($user->role === 'admin')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-100 text-pink-700 border border-pink-200 whitespace-nowrap shrink-0">
                                                    Admin
                                                </span>
                                            @elseif($user->role === 'teacher')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 whitespace-nowrap shrink-0">
                                                    Giảng viên
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap shrink-0">
                                                    Học viên
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                            {{ $user->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-4 px-6 text-center whitespace-nowrap">
                                            <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="inline-flex items-center justify-center gap-2 flex-nowrap shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn cập nhật vai trò cho người dùng \'{{ addslashes($user->name) }}\'?');">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="text-xs font-medium py-1.5 px-3 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 shadow-2xs">
                                                    <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Học viên</option>
                                                    <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Giảng viên</option>
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                                </select>
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition active:scale-95 inline-flex items-center gap-1 cursor-pointer whitespace-nowrap shrink-0" title="Cập nhật vai trò">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Lưu
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100">
                        {{ $users->appends(['tab' => 'users'])->links() }}
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-admin-layout>
