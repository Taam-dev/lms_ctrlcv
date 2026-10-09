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

    <div class="py-12 min-h-[500px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if($enrolledQuizzes->isEmpty())
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($enrolledQuizzes as $quiz)
                        @php
                            $attempts = $userAttempts->get($quiz->id, collect());
                            $lastAttempt = $attempts->first();
                            $bestScore = $attempts->max('score');
                            $hasPassed = $attempts->contains('is_passed', true);
                            $quizTeacher = $quiz->course?->teacher ?? $quiz->teacher;
                        @endphp
                        <article class="bg-white border border-pink-100 hover:border-pink-500 hover:shadow-xl hover:shadow-pink-100/60 transition-all duration-300 flex flex-col justify-between">
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
                                        <dd class="font-black text-neutral-900 mt-0.5">{{ $quiz->questions_count }}</dd>
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
                            </div>

                            <!-- Card Action Footer -->
                            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-100 flex items-center justify-between gap-3">
                                @if($lastAttempt)
                                    <a href="{{ route('student.quizzes.result', [$quiz->id, $lastAttempt->id]) }}" 
                                       class="text-xs font-bold text-neutral-600 hover:text-pink-600 underline">
                                        Xem kết quả cũ
                                    </a>
                                    <a href="{{ route('student.quizzes.take', $quiz->id) }}" 
                                       class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition">
                                        Làm lại đề &rarr;
                                    </a>
                                @else
                                    <span class="text-xs text-neutral-400">Sẵn sàng</span>
                                    <a href="{{ route('student.quizzes.take', $quiz->id) }}" 
                                       class="px-5 py-2.5 bg-neutral-950 hover:bg-pink-600 text-white text-xs font-extrabold uppercase tracking-wider transition">
                                        Bắt đầu làm bài &rarr;
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
