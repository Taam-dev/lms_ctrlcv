<x-app-layout>
    <!-- Header banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-pink-500 mb-2">Đánh giá năng lực</p>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Bài kiểm tra (Quizzes)</h1>
                <p class="text-sm text-neutral-400 mt-2 max-w-xl leading-relaxed">
                    Kiểm tra và củng cố kiến thức với hệ thống trắc nghiệm chấm điểm tự động cho các khóa học bạn đã đăng ký.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('courses.index') }}" 
                   class="px-5 py-3 text-xs font-extrabold uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white transition">
                    Khám phá thêm khóa học &rarr;
                </a>
            </div>
        </div>
    </section>

    <div class="py-12 min-h-[500px]" x-data="{
        search: @json($search ?? ''),
        type: @json($type ?? 'all'),
        scope: @json($scope ?? 'all'),
        quizzes: [
            @foreach($enrolledQuizzes as $q)
            {
                id: {{ $q->id }},
                title: @json(mb_strtolower($q->title, 'UTF-8')),
                desc: @json(mb_strtolower($q->description ?? '', 'UTF-8')),
                course: @json(mb_strtolower($q->course ? $q->course->title : 'tự do', 'UTF-8')),
                type: @json($q->type),
                scope: @json($q->course_id ? 'course' : 'free')
            },
            @endforeach
        ],
        matches(id) {
            const item = this.quizzes.find(x => x.id === id);
            if (!item) return true;
            if (this.type !== 'all' && item.type !== this.type) return false;
            if (this.scope !== 'all' && item.scope !== this.scope) return false;
            if (!this.search || !this.search.trim()) return true;
            const q = this.search.toLowerCase().trim();
            return item.title.includes(q) || item.desc.includes(q) || item.course.includes(q);
        },
        countVisible() {
            return this.quizzes.filter(item => {
                if (this.type !== 'all' && item.type !== this.type) return false;
                if (this.scope !== 'all' && item.scope !== this.scope) return false;
                if (!this.search || !this.search.trim()) return true;
                const q = this.search.toLowerCase().trim();
                return item.title.includes(q) || item.desc.includes(q) || item.course.includes(q);
            }).length;
        },
        resetFilters() {
            this.search = '';
            this.type = 'all';
            this.scope = 'all';
        },
        confirmModalOpen: false,
        pendingQuiz: {
            title: '',
            duration: 0,
            course: '',
            url: ''
        },
        askStartQuiz(url, title, duration, course) {
            this.pendingQuiz = { url, title, duration, course };
            this.confirmModalOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($enrolledQuizzes->isEmpty() && empty($search) && empty($type) && empty($scope))
                <div class="bg-white border border-pink-100 p-12 text-center max-w-xl mx-auto shadow-sm">
                    <span class="inline-block px-3 py-1 bg-pink-100 text-pink-700 text-xs font-bold uppercase tracking-wider mb-4">
                        Thông báo
                    </span>
                    <h3 class="text-xl font-black text-neutral-900 mb-2">Chưa có bài kiểm tra nào</h3>
                    <p class="text-neutral-500 text-sm mb-6 leading-relaxed">
                        Bạn chưa đăng ký khóa học nào có bài kiểm tra đã được phê duyệt, hoặc giảng viên đang chuẩn bị bộ câu hỏi.
                    </p>
                    <a href="{{ route('courses.index') }}" class="inline-block px-6 py-3 bg-neutral-950 hover:bg-pink-600 text-white font-extrabold text-xs uppercase tracking-wider transition">
                        Xem danh sách khóa học
                    </a>
                </div>
            @else
                <!-- Thanh tìm kiếm & lọc bài kiểm tra -->
                <div class="mb-8 bg-white border border-pink-100 p-4 sm:p-5 shadow-xs">
                    <form method="GET" action="{{ route('student.quizzes.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                        <!-- Ô tìm kiếm -->
                        <div class="relative flex-grow">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" x-model="search"
                                   placeholder="Tìm kiếm bài kiểm tra theo tên, khóa học, chủ đề..."
                                   class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm border-neutral-200 focus:border-pink-600 focus:ring-pink-500 rounded-none bg-white">
                            <button type="button" x-show="search && search.length > 0" @click="search = ''" style="display: none;"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-neutral-700" title="Xóa tìm kiếm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Dropdowns lọc -->
                        <div class="flex items-center gap-2.5 shrink-0 flex-wrap sm:flex-nowrap">
                            <select name="type" x-model="type"
                                    class="text-xs font-semibold border-neutral-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-white">
                                <option value="all">Tất cả loại bài</option>
                                <option value="quiz">Kiểm tra thường</option>
                                <option value="midterm">Giữa kỳ</option>
                                <option value="final">Cuối kỳ</option>
                            </select>

                            <select name="scope" x-model="scope"
                                    class="text-xs font-semibold border-neutral-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-white">
                                <option value="all">Tất cả phạm vi</option>
                                <option value="free">Bài kiểm tra Tự do</option>
                                <option value="course">Theo khóa học</option>
                            </select>

                            <button type="submit" class="px-4 py-2.5 bg-neutral-950 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition whitespace-nowrap">
                                Tìm kiếm
                            </button>

                            <button type="button" x-show="(search && search !== '') || type !== 'all' || scope !== 'all'" @click="resetFilters()" style="display: none;"
                                    class="px-3 py-2.5 text-xs font-bold text-neutral-500 hover:text-pink-600 transition underline whitespace-nowrap">
                                Đặt lại
                            </button>
                        </div>
                    </form>

                    <!-- Kết quả hiển thị / đếm số lượng -->
                    <div class="mt-3 pt-3 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
                        <span>Đang hiển thị <strong class="text-pink-600 font-extrabold" x-text="countVisible()">{{ $enrolledQuizzes->count() }}</strong> bài kiểm tra</span>
                        <span x-show="search && search.trim() !== ''" style="display: none;" class="text-[11px] text-neutral-400">
                            Lọc theo từ khóa: "<span class="text-neutral-700 font-semibold" x-text="search"></span>"
                        </span>
                    </div>
                </div>

                <!-- Empty state khi không khớp kết quả tìm kiếm -->
                <div x-show="countVisible() === 0" style="display: none;"
                     class="bg-white border border-pink-100 p-12 text-center max-w-xl mx-auto shadow-sm my-6">
                    <div class="w-12 h-12 rounded-full bg-pink-50 text-pink-600 mx-auto flex items-center justify-center mb-3 text-lg font-bold">
                        🔍
                    </div>
                    <h3 class="text-lg font-black text-neutral-900 mb-1">Không tìm thấy bài kiểm tra nào</h3>
                    <p class="text-neutral-500 text-xs mb-4">
                        Không có bài kiểm tra nào khớp với từ khóa tìm kiếm hoặc bộ lọc hiện tại.
                    </p>
                    <button type="button" @click="resetFilters()" class="px-5 py-2.5 bg-neutral-950 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition">
                        Xóa bộ lọc
                    </button>
                </div>

                <!-- Danh sách Card bài kiểm tra -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($enrolledQuizzes as $quiz)
                        @php
                            $attempts = $userAttempts->get($quiz->id, collect());
                            $lastAttempt = $attempts->first();
                            $bestScore = $attempts->max('score');
                            $hasPassed = $attempts->contains('is_passed', true);
                            $quizTeacher = $quiz->course?->teacher ?? $quiz->teacher;
                        @endphp
                        <article x-show="matches({{ $quiz->id }})"
                                 class="bg-white border border-pink-100 hover:border-pink-500 hover:shadow-xl hover:shadow-pink-100/60 transition-all duration-300 flex flex-col justify-between">
                            <!-- Card Header -->
                            <div class="p-6">
                                <div class="flex items-center justify-between gap-2 mb-4">
                                    <span class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider bg-neutral-950 text-white">
                                        {{ $quiz->type_name }}
                                    </span>

                                    @if($attempts->isNotEmpty())
                                        @if($hasPassed)
                                            <span class="px-2.5 py-1 text-xs font-black bg-emerald-100 text-emerald-800">
                                                Đạt ({{ $bestScore }}/10)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-black bg-rose-100 text-rose-800">
                                                Chưa đạt ({{ $bestScore }}/10)
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-bold bg-neutral-100 text-neutral-600">
                                            Chưa thi
                                        </span>
                                    @endif
                                </div>

                                <div class="text-[11px] font-bold text-pink-600 uppercase tracking-wider mb-1">
                                    {{ $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do' }}
                                </div>

                                <h3 class="text-xl font-black text-neutral-900 mb-3 line-clamp-2 hover:text-pink-600 transition">
                                    {{ $quiz->title }}
                                </h3>

                                <p class="text-sm text-neutral-500 mb-6 line-clamp-2 leading-relaxed">
                                    {{ $quiz->description ?? 'Bài kiểm tra trắc nghiệm đánh giá kiến thức lý thuyết và thực hành.' }}
                                </p>

                                <!-- Meta Info Grid -->
                                <dl class="grid grid-cols-3 gap-px bg-neutral-100 border border-neutral-100 text-center text-xs">
                                    <div class="bg-white p-3">
                                        <dt class="text-[10px] uppercase font-bold text-neutral-400">Số câu</dt>
                                        <dd class="font-black text-neutral-900 mt-0.5">
                                            @if($quiz->question_limit && $quiz->question_limit < $quiz->questions_count)
                                                {{ $quiz->question_limit }} <span class="text-[10px] text-neutral-400 font-normal">/{{ $quiz->questions_count }}</span>
                                            @else
                                                {{ $quiz->questions_count }}
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="bg-white p-3">
                                        <dt class="text-[10px] uppercase font-bold text-neutral-400">Thời gian</dt>
                                        <dd class="font-black text-neutral-900 mt-0.5">{{ $quiz->duration_minutes }}p</dd>
                                    </div>
                                    <div class="bg-white p-3">
                                        <dt class="text-[10px] uppercase font-bold text-neutral-400">Điểm đạt</dt>
                                        <dd class="font-black text-pink-600 mt-0.5">&ge; {{ $quiz->passing_score }}</dd>
                                    </div>
                                </dl>

                                @if($quiz->starts_at || $quiz->ends_at)
                                    <div class="mt-3.5 text-[11px] text-neutral-600 flex items-center gap-1.5 bg-neutral-50 px-3 py-2 border border-neutral-200">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="truncate">
                                            @if($quiz->starts_at && $quiz->ends_at)
                                                Mở: {{ $quiz->starts_at->format('d/m/Y H:i') }} &rarr; {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                            @elseif($quiz->starts_at)
                                                Bắt đầu: {{ $quiz->starts_at->format('d/m/Y H:i') }}
                                            @else
                                                Hạn chót: {{ $quiz->ends_at->format('d/m/Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Action Footer -->
                            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-100 flex items-center justify-between gap-3">
                                @if($quiz->isExpired())
                                    @if($lastAttempt)
                                        <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                           class="text-xs font-bold text-neutral-600 hover:text-pink-600 underline">
                                            Xem kết quả cũ
                                        </a>
                                    @else
                                        <span class="text-xs text-rose-600 font-semibold">Đã quá hạn</span>
                                    @endif
                                    <span class="px-4 py-2 bg-neutral-200 text-neutral-500 text-xs font-black uppercase tracking-wider cursor-not-allowed">
                                        Đã hết hạn
                                    </span>
                                @elseif($quiz->isNotStarted())
                                    <span class="text-xs text-amber-600 font-semibold">
                                        Mở: {{ $quiz->starts_at->format('d/m H:i') }}
                                    </span>
                                    <span class="px-4 py-2 bg-neutral-200 text-neutral-500 text-xs font-black uppercase tracking-wider cursor-not-allowed">
                                        Chưa mở
                                    </span>
                                @elseif($lastAttempt)
                                    <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                        class="text-xs font-bold text-neutral-600 hover:text-pink-600 underline">
                                        Xem kết quả cũ
                                    </a>
                                    <button type="button" 
                                        @click="askStartQuiz('{{ route('student.quizzes.take', $quiz->id) }}', '{{ addslashes($quiz->title) }}', {{ (int)$quiz->duration_minutes }}, '{{ addslashes($quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do') }}')"
                                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition cursor-pointer">
                                        Làm lại đề &rarr;
                                    </button>
                                @else
                                    <span class="text-xs text-neutral-400">Sẵn sàng</span>
                                    <button type="button" 
                                        @click="askStartQuiz('{{ route('student.quizzes.take', $quiz->id) }}', '{{ addslashes($quiz->title) }}', {{ (int)$quiz->duration_minutes }}, '{{ addslashes($quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do') }}')"
                                        class="px-5 py-2.5 bg-neutral-950 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition cursor-pointer">
                                        Bắt đầu làm bài &rarr;
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        </div>

        <!-- Modal Xác nhận Bắt đầu Làm bài Kiểm tra -->
        <div x-show="confirmModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-950/80 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="confirmModalOpen = false"
                 class="w-full max-w-lg bg-neutral-900 border-2 border-pink-600 text-white shadow-2xl p-6 sm:p-8 space-y-6">
                
                <!-- Header modal với Icon cảnh báo -->
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-pink-600/20 border border-pink-500 text-pink-500 flex items-center justify-center shrink-0 text-2xl font-black">
                        ⚠️
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] uppercase font-black tracking-widest text-pink-400 block mb-1">Xác nhận làm bài kiểm tra</span>
                        <h3 class="text-lg sm:text-xl font-black text-white leading-snug line-clamp-2" x-text="pendingQuiz.title">
                            Tên bài kiểm tra
                        </h3>
                        <p class="text-xs text-neutral-400 mt-1">
                            <span x-text="pendingQuiz.course"></span> &bull; Thời gian: <strong class="text-pink-400" x-text="pendingQuiz.duration + ' phút'"></strong>
                        </p>
                    </div>
                </div>

                <!-- Cảnh báo quy chế quan trọng -->
                <div class="bg-rose-950/50 border-l-4 border-rose-500 border-y border-r border-rose-900/60 p-4 space-y-2">
                    <div class="flex items-center gap-2 text-rose-300 font-black text-xs uppercase tracking-wider">
                        <svg class="w-4 h-4 shrink-0 text-rose-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Quy chế thi bắt buộc</span>
                    </div>
                    <p class="text-xs text-rose-100 font-semibold leading-relaxed">
                        Một khi đã bắt đầu thì <strong class="text-white underline decoration-rose-400">bắt buộc phải nộp bài</strong>, <strong class="text-rose-300">không thể hủy giữa chừng</strong>!
                    </p>
                    <p class="text-[11px] text-neutral-300 leading-normal">
                        Thời gian sẽ bắt đầu đếm ngược ngay lập tức. Trong khi làm bài, nếu chuyển tab hoặc chuyển trang khác, bạn sẽ bắt buộc phải nộp bài.
                    </p>
                </div>

                <!-- Hai lựa chọn: Làm hoặc Không -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-2 border-t border-white/10">
                    <button type="button" 
                            @click="confirmModalOpen = false"
                            class="w-full sm:w-auto px-6 py-2.5 border border-white/20 hover:border-white text-neutral-300 hover:text-white text-xs font-black uppercase tracking-wider transition cursor-pointer text-center">
                        Không
                    </button>
                    <a :href="pendingQuiz.url"
                       class="w-full sm:w-auto px-7 py-2.5 bg-pink-600 hover:bg-pink-500 text-white text-xs font-black uppercase tracking-wider transition shadow-lg shadow-pink-600/30 cursor-pointer text-center">
                        Làm &rarr;
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
