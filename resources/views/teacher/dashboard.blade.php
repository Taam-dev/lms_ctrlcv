<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-pink-600 uppercase tracking-wider mb-1">
                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                    Cổng Giảng Viên (Teacher Portal)
                </div>
                <h2 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-3">
                    Bảng Điều Khiển Giảng Viên
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    @if(in_array($currentTab, ['courses', 'lessons']))
                        Quản lý toàn diện các khóa học, soạn thảo bài giảng theo khóa học và theo dõi phê duyệt.
                    @elseif($currentTab === 'quizzes')
                        Tạo đề thi trắc nghiệm, cấu hình câu hỏi và theo dõi trạng thái kiểm duyệt đề thi.
                    @elseif($currentTab === 'attempts')
                        Xem kết quả và lịch sử làm bài trắc nghiệm của học viên đối với các bài kiểm tra của bạn.
                    @else
                        Quản lý toàn diện các khóa học, soạn thảo bài giảng và tổ chức các bài kiểm tra trắc nghiệm của bạn.
                    @endif
                </p>
            </div>

            <!-- Quick Action Buttons & Search -->
            <div class="flex flex-col items-start lg:items-end gap-2.5 w-full lg:w-auto shrink-0">
                <!-- Hàng trên: Bộ lọc trạng thái + Nút tạo mới (Cùng 1 hàng giống admin panel) -->
                <div class="flex items-center gap-2 sm:gap-3 flex-nowrap shrink-0 overflow-x-auto max-w-full">
                    @if(in_array($currentTab, ['courses', 'quizzes']))
                        <!-- Quick Filter by Status -->
                        <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs text-xs font-bold shrink-0">
                            <a href="{{ route('instructor.dashboard', array_filter(['tab' => $currentTab, 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition whitespace-nowrap {{ empty($statusFilter) ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Tất cả ({{ $currentTab === 'courses' ? $stats['total_courses'] : $stats['total_quizzes'] }})
                            </a>
                            <a href="{{ route('instructor.dashboard', array_filter(['tab' => $currentTab, 'status' => 'pending', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-amber-700 hover:bg-amber-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'pending' ? 'bg-white' : 'bg-amber-500' }}"></span>
                                Chờ duyệt
                            </a>
                            <a href="{{ route('instructor.dashboard', array_filter(['tab' => $currentTab, 'status' => 'approved', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-emerald-700 hover:bg-emerald-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'approved' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                                Đã duyệt
                            </a>
                            <a href="{{ route('instructor.dashboard', array_filter(['tab' => $currentTab, 'status' => 'rejected', 'search' => $search])) }}" 
                               class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-rose-700 hover:bg-rose-50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusFilter === 'rejected' ? 'bg-white' : 'bg-rose-500' }}"></span>
                                Đã loại bỏ
                            </a>
                        </div>
                    @endif

                    @if(in_array($currentTab, ['courses', 'overview', '']))
                        <a href="{{ route('instructor.courses.create') }}" 
                           class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            + Tạo khóa học mới
                        </a>
                    @endif

                    @if(in_array($currentTab, ['quizzes', 'overview', '']))
                        <a href="{{ route('instructor.quizzes.create') }}" 
                           class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            + Tạo bài kiểm tra
                        </a>
                    @endif
                </div>

                <!-- Hàng dưới: Thanh tìm kiếm -->
                <form method="GET" action="{{ route('instructor.dashboard') }}" class="flex items-center gap-2 w-full lg:w-auto justify-end">
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
                               placeholder="{{ $currentTab === 'courses' ? 'Tìm kiếm khóa học, bài học của bạn...' : ($currentTab === 'quizzes' ? 'Tìm kiếm bài kiểm tra của bạn...' : ($currentTab === 'attempts' ? 'Tìm học viên, email, đề thi, điểm (vd: 5, 8.5, >=8)...' : 'Tìm kiếm...')) }}"
                               class="w-full pl-10 pr-9 py-2 bg-white border border-slate-200/90 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition shadow-2xs">
                        @if(!empty($search))
                            <a href="{{ route('instructor.dashboard', array_filter(['tab' => $currentTab, 'status' => $statusFilter])) }}"
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
        <div class="w-full space-y-8">

            <!-- 4 Statistic Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Courses Stats -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:border-pink-200 hover:shadow-md transition">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-pink-50/80 rounded-full group-hover:scale-125 transition duration-300 pointer-events-none -z-0"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Khóa học của tôi</span>
                                
                            </div>
                            <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_courses'] }}</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs pt-2">
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 border border-emerald-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $stats['approved_courses'] }} đã duyệt
                            </span>
                            <span class="inline-flex items-center gap-1 text-amber-700 font-bold bg-amber-50 border border-amber-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                {{ $stats['pending_courses'] }} chờ duyệt
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Lessons Stats -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:border-pink-200 hover:shadow-md transition">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-pink-50/80 rounded-full group-hover:scale-125 transition duration-300 pointer-events-none -z-0"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Tổng số Bài giảng</span>
                                
                            </div>
                            <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_lessons'] }}</div>
                        </div>
                        <div class="text-xs text-slate-500 pt-2">
                            Bài giảng trong các khóa học bạn quản lý
                        </div>
                    </div>
                </div>

                <!-- Quizzes Stats -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:border-pink-200 hover:shadow-md transition">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-pink-50/80 rounded-full group-hover:scale-125 transition duration-300 pointer-events-none -z-0"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-pink-600 transition">Đề thi Trắc nghiệm</span>
                                
                            </div>
                            <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_quizzes'] }}</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs pt-2">
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 border border-emerald-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $stats['approved_quizzes'] }} đã duyệt
                            </span>
                            <span class="inline-flex items-center gap-1 text-amber-700 font-bold bg-amber-50 border border-amber-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                {{ $stats['pending_quizzes'] }} chờ duyệt
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Enrollments & Submissions Stats -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm relative overflow-hidden group hover:border-emerald-200 hover:shadow-md transition">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-emerald-50/80 rounded-full group-hover:scale-125 transition duration-300 pointer-events-none -z-0"></div>
                    <div class="relative z-10 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-600 transition">Học viên & Lượt nộp bài</span>
                                
                            </div>
                            <div class="text-3xl font-black text-slate-900 tracking-tight mb-2">{{ $stats['total_enrollments'] }}</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-xs pt-2">
                            <span class="inline-flex items-center gap-1 text-pink-700 font-bold bg-pink-50 border border-pink-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                {{ $stats['total_enrollments'] }} lượt đăng ký
                            </span>
                            <span class="inline-flex items-center gap-1 text-pink-700 font-bold bg-pink-50 border border-pink-100/80 px-2 py-0.5 rounded-md whitespace-nowrap shrink-0">
                                {{ $stats['total_attempts'] }} lượt thi quiz
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Tabs Navigation -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="border-b border-slate-200/80 px-6 pt-4 flex flex-wrap gap-4 sm:gap-8 bg-slate-50/50">
                    <!-- Tab 1: Khóa học / Bài học gộp chung giống admin panel -->
                    <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter, 'search' => $search])) }}"
                       class="pb-4 text-sm font-bold flex items-center gap-2 border-b-2 transition whitespace-nowrap shrink-0 {{ in_array($currentTab, ['courses', 'lessons']) ? 'border-pink-600 text-pink-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        Quản lý Khóa học / Bài học
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold whitespace-nowrap shrink-0 {{ in_array($currentTab, ['courses', 'lessons']) ? 'bg-pink-100 text-pink-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $courses->count() }} khóa ({{ $stats['total_lessons'] }} bài)
                        </span>
                    </a>

                    <!-- Tab 2: Quizzes -->
                    <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'quizzes', 'status' => $statusFilter, 'search' => $search])) }}"
                       class="pb-4 text-sm font-bold flex items-center gap-2 border-b-2 transition whitespace-nowrap shrink-0 {{ $currentTab === 'quizzes' ? 'border-pink-600 text-pink-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        Đề thi Trắc nghiệm (Quizzes)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold whitespace-nowrap shrink-0 {{ $currentTab === 'quizzes' ? 'bg-pink-100 text-pink-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $quizzes->count() }}
                        </span>
                    </a>

                    <!-- Tab 3: Kết quả học viên -->
                    <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'attempts', 'search' => $search])) }}"
                       class="pb-4 text-sm font-bold flex items-center gap-2 border-b-2 transition whitespace-nowrap shrink-0 {{ $currentTab === 'attempts' ? 'border-pink-600 text-pink-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        Kết quả Học viên
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold whitespace-nowrap shrink-0 {{ $currentTab === 'attempts' ? 'bg-pink-100 text-pink-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $attempts->total() }}
                        </span>
                    </a>
                </div>

                <!-- TAB CONTENT -->
                <div>
                    <!-- TAB 1: KHÓA HỌC CỦA TÔI -->
                    @if($currentTab === 'courses')
                        @if(!empty($search))
                            <div class="p-3.5 mx-6 mt-4 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                                    <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $courses->count() }} khóa học khớp)</span>
                                </div>
                                <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter])) }}"
                                   class="font-bold text-slate-500 hover:text-pink-600 transition">
                                    &times; Xóa tìm kiếm
                                </a>
                            </div>
                        @endif

                        @if($courses->isEmpty())
                            <div class="p-16 text-center">
                                <h3 class="font-bold text-slate-800 text-base mb-1">
                                    @if(!empty($search))
                                        Không tìm thấy khóa học khớp với "{{ $search }}"
                                    @else
                                        Chưa có khóa học nào
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-500 mb-4">
                                    @if(!empty($search))
                                        Vui lòng thử tìm kiếm bằng từ khóa khác hoặc xóa bộ lọc tìm kiếm.
                                    @else
                                        Bạn chưa tạo khóa học nào hoặc bộ lọc hiện tại không có kết quả.
                                    @endif
                                </p>
                                @if(!empty($search))
                                    <div class="mt-4">
                                        <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'courses', 'status' => $statusFilter])) }}"
                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                                            Xóa tìm kiếm
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('instructor.courses.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 text-white font-bold text-xs shadow-xs hover:bg-pink-700 transition whitespace-nowrap shrink-0">
                                        + Tạo khóa học đầu tiên
                                    </a>
                                @endif
                            </div>
                        @else
                            <div x-data="{ expandedCourse: {{ request('expand') ?: 'null' }} }" class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[980px]">
                                    <thead>
                                        <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                            <th class="py-4 px-6 min-w-[260px]">Khóa học</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Bài giảng (Nhấp để xem)</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Đề thi Quiz</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Học viên</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Trạng thái duyệt</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Ngày tạo</th>
                                            <th class="py-4 px-6 text-right whitespace-nowrap sticky right-0 bg-slate-50/95 backdrop-blur-xs z-20 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @foreach($courses as $course)
                                            <tr class="hover:bg-pink-50/20 transition" :class="expandedCourse === {{ $course->id }} ? 'bg-pink-50/30' : ''">
                                                <td class="py-4 px-6">
                                                    <button type="button" @click="expandedCourse = (expandedCourse === {{ $course->id }} ? null : {{ $course->id }})" 
                                                            class="text-left group flex items-start gap-2 focus:outline-none">
                                                        
                                                        <div>
                                                            <div class="font-bold text-slate-900 text-base group-hover:text-pink-700 transition">
                                                                {{ $course->title }}
                                                            </div>
                                                            <div class="text-xs text-slate-500 line-clamp-1 max-w-md mt-0.5">
                                                                {{ $course->description ?? 'Chưa có mô tả' }}
                                                            </div>
                                                        </div>
                                                    </button>
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    <button type="button" @click="expandedCourse = (expandedCourse === {{ $course->id }} ? null : {{ $course->id }})" 
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition shadow-2xs whitespace-nowrap shrink-0"
                                                            :class="expandedCourse === {{ $course->id }} ? 'bg-pink-600 text-white' : 'bg-pink-50 text-pink-700 hover:bg-pink-100'"
                                                            title="Bấm để xem danh sách bài giảng">
                                                        <span>{{ $course->lessons_count }} bài</span>
                                                        <svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="expandedCourse === {{ $course->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-pink-50 text-pink-700 whitespace-nowrap shrink-0">
                                                        {{ $course->quizzes_count }} đề
                                                    </span>
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 whitespace-nowrap shrink-0">
                                                        {{ $course->enrollments_count }} bạn
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
                                                            Từ chối
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-4 text-center text-xs text-slate-500 whitespace-nowrap">
                                                    {{ $course->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="py-4 px-6 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-[#fff7fa] transition z-10 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]" :class="expandedCourse === {{ $course->id }} ? 'bg-[#fff2f7]' : ''">
                                                    <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                        <!-- Thêm bài giảng -->
                                                        <a href="{{ route('instructor.lessons.create', $course->id) }}" 
                                                           class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold transition whitespace-nowrap shrink-0" title="Thêm bài giảng vào khóa">
                                                            + Bài giảng
                                                        </a>

                                                        <!-- Chỉnh sửa -->
                                                        <a href="{{ route('instructor.courses.edit', $course->id) }}" 
                                                           class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 transition shrink-0" title="Chỉnh sửa khóa học">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </a>

                                                        <!-- Xem trước -->
                                                        <a href="{{ route('student.courses.show', $course->id) }}" target="_blank" 
                                                           class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 transition shrink-0" title="Xem trước giao diện">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                        </a>

                                                        <!-- Xóa khóa học -->
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

                                                            <a href="{{ route('instructor.lessons.create', $course->id) }}" 
                                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                                
                                                                + Thêm bài giảng vào khóa này
                                                            </a>
                                                        </div>

                                                        @if($course->lessons->isEmpty())
                                                            <div class="py-6 text-center text-slate-500 text-xs bg-slate-50/70 rounded-xl border border-dashed border-slate-200">
                                                                <p class="font-bold text-slate-700 mb-1">Khóa học này chưa có bài giảng nào</p>
                                                                <p class="text-slate-400 mb-2">Hãy tạo bài giảng đầu tiên để học viên có thể học tập.</p>
                                                                <a href="{{ route('instructor.lessons.create', $course->id) }}" class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-800 font-bold whitespace-nowrap">
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
                                                                            <a href="{{ route('instructor.lessons.edit', $lesson->id) }}" 
                                                                               class="inline-flex items-center justify-center px-2 py-1 rounded-lg bg-slate-100 hover:bg-pink-50 text-slate-700 hover:text-pink-700 text-xs font-semibold transition whitespace-nowrap shrink-0">
                                                                                Sửa
                                                                            </a>
                                                                            <form action="{{ route('instructor.lessons.destroy', $lesson->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài giảng này?')">
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
                        @endif

                    <!-- TAB 2: BÀI KIỂM TRA (QUIZZES) -->
                    @elseif($currentTab === 'quizzes')
                        @if(!empty($search))
                            <div class="p-3.5 mx-6 mt-4 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                                    <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $quizzes->count() }} bài kiểm tra khớp)</span>
                                </div>
                                <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'quizzes', 'status' => $statusFilter])) }}"
                                   class="font-bold text-slate-500 hover:text-pink-600 transition">
                                    &times; Xóa tìm kiếm
                                </a>
                            </div>
                        @endif

                        @if($quizzes->isEmpty())
                            <div class="p-16 text-center">
                                <h3 class="font-bold text-slate-800 text-base mb-1">
                                    @if(!empty($search))
                                        Không tìm thấy bài kiểm tra khớp với "{{ $search }}"
                                    @else
                                        Chưa có bài kiểm tra nào
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-500 mb-4">
                                    @if(!empty($search))
                                        Vui lòng thử tìm kiếm bằng từ khóa khác hoặc xóa bộ lọc tìm kiếm.
                                    @else
                                        Bạn chưa tạo bài kiểm tra trắc nghiệm nào cho khóa học của mình.
                                    @endif
                                </p>
                                @if(!empty($search))
                                    <a href="{{ route('instructor.dashboard', array_filter(['tab' => 'quizzes', 'status' => $statusFilter])) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                                        Xóa tìm kiếm
                                    </a>
                                @else
                                    <a href="{{ route('instructor.quizzes.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-pink-600 text-white font-bold text-xs shadow-xs hover:bg-pink-700 transition whitespace-nowrap shrink-0">
                                        + Tạo bài kiểm tra đầu tiên
                                    </a>
                                @endif
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
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Lượt thi</th>
                                            <th class="py-4 px-6 text-right whitespace-nowrap sticky right-0 bg-slate-50/95 backdrop-blur-xs z-20 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @foreach($quizzes as $quiz)
                                            <tr class="hover:bg-pink-50/20 transition">
                                                <td class="py-4 px-6">
                                                    <div class="font-bold text-slate-900 text-base mb-0.5">{{ $quiz->title }}</div>
                                                    <div class="text-xs text-pink-600 font-medium flex items-center gap-1">
                                                        <span>Khóa:</span>
                                                        <span class="font-semibold text-slate-700">{{ $quiz->course->title ?? 'N/A' }}</span>
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap shrink-0 {{ $quiz->questions_count > 0 ? 'bg-pink-50 text-pink-700 border border-pink-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                                        {{ $quiz->questions_count }} câu
                                                    </span>
                                                </td>
                                                <td class="py-4 px-4 text-center text-xs text-slate-600 whitespace-nowrap">
                                                    <div>{{ $quiz->duration_minutes }} phút | Điểm đạt &ge; {{ $quiz->passing_score }}/10</div>
                                                    <div class="mt-1">
                                                        @if($quiz->randomize_questions)
                                                            <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md whitespace-nowrap">
                                                                Random câu hỏi
                                                            </span>
                                                        @else
                                                            <span class="text-[11px] text-slate-400 whitespace-nowrap">Thứ tự cố định</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    @if($quiz->status === 'approved')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                            Đã duyệt
                                                        </span>
                                                    @elseif($quiz->status === 'pending')
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                                            Chờ duyệt
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                                            Từ chối
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 whitespace-nowrap shrink-0">
                                                        {{ $quiz->attempts_count }} lượt
                                                    </span>
                                                </td>
                                                <td class="py-4 px-6 text-right whitespace-nowrap sticky right-0 bg-white group-hover:bg-[#fff7fa] transition z-10 shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)]">
                                                    <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap shrink-0">
                                                        <!-- Soạn câu hỏi -->
                                                        <a href="{{ route('instructor.quizzes.questions', $quiz->id) }}" 
                                                           class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition whitespace-nowrap shrink-0">
                                                            Soạn câu hỏi
                                                        </a>

                                                        <!-- Xem kết quả -->
                                                        <a href="{{ route('instructor.quizzes.results', $quiz->id) }}" 
                                                           class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition whitespace-nowrap shrink-0" title="Xem kết quả học viên">
                                                            Kết quả
                                                        </a>

                                                        <!-- Chỉnh sửa -->
                                                        <a href="{{ route('instructor.quizzes.edit', $quiz->id) }}" 
                                                           class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-slate-100 transition shrink-0" title="Chỉnh sửa đề thi">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </a>

                                                        <!-- Xóa đề thi -->
                                                        <form action="{{ route('instructor.quizzes.destroy', $quiz->id) }}" method="POST" class="inline shrink-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài kiểm tra này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Xóa bài kiểm tra">
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

                    <!-- TAB 4: KẾT QUẢ HỌC VIÊN -->
                    @elseif($currentTab === 'attempts')
                        @if(!empty($search))
                            <div class="p-3.5 mx-6 mt-4 rounded-2xl bg-white border border-pink-100 flex items-center justify-between gap-3 shadow-2xs text-xs">
                                <div class="flex items-center gap-2 text-slate-700">
                                    <span class="w-2 h-2 rounded-full bg-pink-600"></span>
                                    <span>Kết quả tìm kiếm cho: <strong class="text-pink-600">"{{ $search }}"</strong> ({{ $attempts->total() }} lượt nộp bài khớp)</span>
                                </div>
                                <a href="{{ route('instructor.dashboard', ['tab' => 'attempts']) }}"
                                   class="font-bold text-slate-500 hover:text-pink-600 transition">
                                    &times; Xóa tìm kiếm
                                </a>
                            </div>
                        @endif

                        @if($attempts->isEmpty())
                            <div class="p-16 text-center">
                                <h3 class="font-bold text-slate-800 text-base mb-1">
                                    @if(!empty($search))
                                        Không tìm thấy kết quả làm bài nào khớp với "{{ $search }}"
                                    @else
                                        Chưa có kết quả làm bài nào
                                    @endif
                                </h3>
                                <p class="text-xs text-slate-500">
                                    @if(!empty($search))
                                        Vui lòng thử tìm kiếm bằng tên học viên, email, đề thi hoặc điểm số khác (ví dụ: "5", "8.5", ">=8", "đạt").
                                    @else
                                        Khi học viên hoàn thành các bài trắc nghiệm của bạn, kết quả chi tiết sẽ hiển thị tại đây.
                                    @endif
                                </p>
                                @if(!empty($search))
                                    <div class="pt-3">
                                        <a href="{{ route('instructor.dashboard', ['tab' => 'attempts']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
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
                                            <th class="py-4 px-6 min-w-[180px]">Học viên</th>
                                            <th class="py-4 px-4 min-w-[200px]">Bài kiểm tra & Khóa học</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Điểm số</th>
                                            <th class="py-4 px-4 text-center whitespace-nowrap">Kết quả</th>
                                            <th class="py-4 px-6 text-right whitespace-nowrap">Thời gian nộp</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-sm">
                                        @foreach($attempts as $attempt)
                                            <tr class="hover:bg-emerald-50/20 transition">
                                                <td class="py-4 px-6 whitespace-nowrap">
                                                    <div class="font-bold text-slate-900">{{ $attempt->student->name ?? 'N/A' }}</div>
                                                    <div class="text-xs text-slate-500">{{ $attempt->student->email ?? '' }}</div>
                                                </td>
                                                <td class="py-4 px-4 whitespace-nowrap">
                                                    <div class="font-semibold text-slate-800">{{ $attempt->quiz->title ?? 'N/A' }}</div>
                                                    <div class="text-xs text-slate-400">{{ $attempt->quiz->course->title ?? '' }}</div>
                                                </td>
                                                <td class="py-4 px-4 text-center font-black text-base text-slate-900 whitespace-nowrap">
                                                    {{ number_format($attempt->score, 1) }}/10
                                                </td>
                                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                                    @if($attempt->score >= ($attempt->quiz->passing_score ?? 5.0))
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                                                            
                                                            Đạt
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap shrink-0">
                                                            
                                                            Không đạt
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
                            <div class="p-4 border-t border-slate-100">
                                {{ $attempts->appends(array_filter(['tab' => 'attempts', 'search' => $search]))->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-teacher-layout>
