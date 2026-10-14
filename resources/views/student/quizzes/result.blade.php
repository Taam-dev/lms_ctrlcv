<x-app-layout>
    <!-- Quiz Result Header Banner -->
    <section class="bg-neutral-950 text-white border-b border-white/10" x-data="{ confirmModalOpen: false }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-500/15 text-pink-300 border border-pink-500/25 tracking-wide">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="text-xs text-neutral-400 font-bold">
                        {{ $quiz->course ? 'Khóa học: ' . $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $quiz->title }}
                </h1>
                <div class="text-xs text-neutral-400 mt-2 flex items-center gap-2 flex-wrap">
                    @php
                        $quizTeacher = $quiz->teacher ?? $quiz->course?->teacher;
                        $teacherName = $quizTeacher?->name ?? 'Ban Giảng Huấn';
                    @endphp
                    <div class="inline-flex items-center gap-1.5 text-neutral-200">
                        @if($quizTeacher && $quizTeacher->avatar_url)
                            <img src="{{ $quizTeacher->avatar_url }}" alt="{{ $teacherName }}" class="w-4 h-4 rounded-full object-cover ring-1 ring-pink-500/40">
                        @else
                            <span class="w-4 h-4 rounded-full bg-pink-600/30 text-pink-300 text-[9px] font-bold inline-flex items-center justify-center ring-1 ring-pink-500/40">
                                {{ strtoupper(mb_substr($teacherName, 0, 1, 'UTF-8')) }}
                            </span>
                        @endif
                        <span>Giảng viên: <strong class="text-white">{{ $teacherName }}</strong></span>
                    </div>
                    <span>&bull;</span>
                    <span>Nộp bài lúc: {{ $attempt->completed_at ? $attempt->completed_at->format('H:i d/m/Y') : $attempt->created_at->format('H:i d/m/Y') }}</span>
                    @php
                        $isStaff = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTeacher() || auth()->user()->isInstructor());
                        $studentAttemptsCount = auth()->check() ? $quiz->attempts()->where('student_id', auth()->id())->count() : 0;
                        $hasReachedMax = $quiz->max_attempts !== null && $quiz->max_attempts > 0 && $studentAttemptsCount >= $quiz->max_attempts;
                    @endphp
                    @if($isStaff)
                        <span>&bull;</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-pink-600/30 border border-pink-500/40 text-pink-200 text-xs font-bold">
                            Học viên: <strong class="text-white">{{ $attempt->student->name ?? 'N/A' }}</strong> ({{ $attempt->student->email ?? '' }})
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if($isStaff)
                    @php
                        $isOwnerTeacher = auth()->user()->isTeacher() && ($quiz->teacher_id === auth()->id() || ($quiz->course && $quiz->course->teacher_id === auth()->id()));
                    @endphp
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.quizzes.results', $quiz->id) }}" 
                           class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/10 hover:bg-white/20 text-white border border-white/20 transition">
                            &larr; Bảng điểm học viên
                        </a>
                    @elseif($isOwnerTeacher)
                        <a href="{{ route('instructor.quizzes.results', $quiz->id) }}" 
                           class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/10 hover:bg-white/20 text-white border border-white/20 transition">
                            &larr; Bảng điểm học viên
                        </a>
                    @else
                        <a href="{{ route('student.quizzes.index') }}" 
                           class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-white/10 hover:bg-white/20 text-white border border-white/20 transition">
                            &larr; Quizzes
                        </a>
                    @endif
                @else
                    <a href="{{ route('student.quizzes.index') }}" 
                       class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider border border-white/20 hover:border-white text-neutral-300 hover:text-white transition">
                        &larr; Quizzes
                    </a>
                    @php
                        $studentAttemptsCount = $quiz->attempts()->where('student_id', auth()->id())->count();
                        $hasReachedMax = $quiz->max_attempts !== null && $quiz->max_attempts > 0 && $studentAttemptsCount >= $quiz->max_attempts;
                    @endphp
                    @if($hasReachedMax && !auth()->user()->isAdmin())
                        <span class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-neutral-800 text-neutral-400 border border-neutral-700 cursor-not-allowed"
                              title="Bạn đã đạt số lần làm bài tối đa ({{ $quiz->max_attempts }} lần).">
                            Hết lượt làm lại ({{ $studentAttemptsCount }}/{{ $quiz->max_attempts }})
                        </span>
                    @elseif((!$quiz->isExpired() && !$quiz->isNotStarted()) || auth()->user()->isAdmin())
                        <button type="button" 
                                @click="confirmModalOpen = true"
                                class="px-5 py-2.5 rounded-xl text-xs font-extrabold uppercase tracking-wider bg-pink-600 hover:bg-pink-700 text-white transition cursor-pointer shadow-sm">
                            Làm lại bài thi
                        </button>
                    @elseif($quiz->isExpired())
                        <span class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-neutral-800 text-neutral-400 border border-neutral-700 cursor-not-allowed">
                            Đã hết hạn thi
                        </span>
                    @endif
                @endif
            </div>
        </div>

        @php
            $quizTeacher = $quiz->teacher ?? $quiz->course?->teacher;
            $tName = $quizTeacher?->name ?? 'Ban Giảng Huấn';
            $tAvatar = $quizTeacher?->avatar_url;
            $tInitial = strtoupper(mb_substr($tName, 0, 1, 'UTF-8'));
            $courseTitle = $quiz->course ? $quiz->course->title : 'Bài kiểm tra Tự do';
            $qLimit = $quiz->question_limit && $quiz->question_limit < $quiz->questions_count ? $quiz->question_limit : $quiz->questions_count;
        @endphp

        <!-- Modal Xác nhận Làm lại Bài Kiểm tra (Modern Clean Light Card) -->
        <div x-show="confirmModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" 
             aria-modal="true"
             aria-labelledby="modal-retake-quiz-title"
             @keydown.escape.window="confirmModalOpen = false"
             style="display: none;">
            
            <!-- Backdrop mờ tối sang trọng -->
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                 x-show="confirmModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="confirmModalOpen = false"></div>

            <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
                <!-- Hộp thoại Modal bo góc mềm mại, sang trọng -->
                <div x-show="confirmModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-100"
                     @click.stop="">
                    
                    <!-- Header Modal với hiệu ứng gradient nhẹ nhàng và nút đóng (X) -->
                    <div class="bg-gradient-to-r from-pink-500/10 via-rose-500/10 to-pink-500/5 px-6 pt-6 pb-5 border-b border-pink-100/70 relative">
                        <button type="button" 
                                @click="confirmModalOpen = false"
                                class="absolute top-5 right-5 w-8 h-8 rounded-full bg-white/80 hover:bg-white text-slate-400 hover:text-slate-700 flex items-center justify-center shadow-xs transition cursor-pointer"
                                title="Đóng">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="flex items-start gap-4 pr-8">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-600 to-rose-600 text-white flex items-center justify-center shadow-lg shadow-pink-500/25 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-100/80 text-pink-700 uppercase tracking-wider mb-1.5">
                                    Xác nhận làm lại bài thi
                                </span>
                                <h3 id="modal-retake-quiz-title" class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug line-clamp-2">
                                    {{ $quiz->title }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Nội dung chi tiết bài thi -->
                    <div class="p-6 space-y-4">
                        <!-- Thông tin Khóa học & Giảng viên + Chỉ số bài thi -->
                        <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-100 space-y-3">
                            <div class="flex items-center justify-between text-xs text-slate-600 flex-wrap gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-slate-400 font-medium shrink-0">Khóa học:</span>
                                    <span class="font-bold text-slate-800 truncate">{{ $courseTitle }}</span>
                                </div>
                                @if($tName)
                                    <div class="flex items-center gap-1.5 text-slate-700 font-medium shrink-0">
                                        @if($tAvatar)
                                            <img src="{{ $tAvatar }}" class="w-4 h-4 rounded-full object-cover">
                                        @else
                                            <span class="w-4 h-4 rounded-full bg-pink-100 text-pink-700 text-[9px] font-bold inline-flex items-center justify-center">{{ $tInitial }}</span>
                                        @endif
                                        <span>GV: <strong class="text-slate-900 font-semibold">{{ $tName }}</strong></span>
                                    </div>
                                @endif
                            </div>

                            <!-- Thống kê thông số: Thời gian, Số câu hỏi, Điểm đạt, Lượt làm -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Thời gian</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block">{{ $quiz->duration_minutes }} phút</span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Số câu</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block">{{ $qLimit }} câu</span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Điểm đạt</span>
                                    <span class="text-sm font-extrabold text-pink-600 mt-0.5 block">&ge; {{ $quiz->passing_score }}</span>
                                </div>
                                <div class="bg-white rounded-xl p-2.5 border border-slate-100/80 shadow-2xs">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Lượt làm</span>
                                    <span class="text-sm font-extrabold text-slate-900 mt-0.5 block">
                                        @if($quiz->max_attempts)
                                            {{ $studentAttemptsCount }}/{{ $quiz->max_attempts }}
                                        @else
                                            Thoải mái
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Lưu ý & Quy chế thi trang nhã, rõ ràng -->
                        <div class="bg-gradient-to-br from-amber-50/90 to-orange-50/60 border border-amber-200/80 rounded-2xl p-4 space-y-2.5">
                            <div class="flex items-center gap-2 text-amber-900 font-bold text-xs uppercase tracking-wider">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>Lưu ý quan trọng trước khi bắt đầu</span>
                            </div>
                            <ul class="space-y-1.5 text-xs text-amber-950/90 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Thời gian làm bài sẽ đếm ngược <strong>ngay lập tức</strong> khi bạn xác nhận.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Lần thi mới sẽ được <strong>ghi nhận điểm số riêng biệt</strong> và lưu lại tiến độ.</span>
                                </li>
                                @if($quiz->max_attempts)
                                    <li class="flex items-start gap-2">
                                        <span class="text-amber-600 font-bold leading-tight">•</span>
                                        <span>Bài thi giới hạn tối đa <strong>{{ $quiz->max_attempts }} lần làm</strong> (bạn đã hoàn thành {{ $studentAttemptsCount }} lần, còn lại <strong>{{ max(0, $quiz->max_attempts - $studentAttemptsCount) }}</strong> lượt).</span>
                                    </li>
                                @endif
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-600 font-bold leading-tight">•</span>
                                    <span>Vui lòng không chuyển tab hay rời trang thi để tránh bị tự động nộp bài.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer nút thao tác tự nhiên, lịch sự -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="confirmModalOpen = false"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 hover:bg-white text-slate-600 hover:text-slate-800 text-xs font-bold uppercase tracking-wider transition cursor-pointer text-center">
                            Để sau
                        </button>
                        <a href="{{ route('student.quizzes.take', $quiz->id) }}"
                           class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-extrabold uppercase tracking-wider transition shadow-md shadow-pink-600/25 hover:shadow-pink-600/35 cursor-pointer text-center inline-flex items-center justify-center gap-1.5">
                            <span>Bắt đầu làm bài</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="py-10 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @php
                $canViewResultDetails = $quiz->show_result_after_completion || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTeacher() || auth()->user()->isInstructor()));
            @endphp

            @if($isStaff)
                    <!-- Khối Giám sát vi phạm / Cảnh cáo (Chỉ dành cho Giảng viên & Admin - Ẩn hoàn toàn với học viên) -->
                    <div x-data="{ hideWarningCount: false }" class="bg-white border-2 border-dashed {{ ($attempt->warning_count ?? 0) > 0 ? 'border-amber-400 bg-amber-50/30' : 'border-slate-300 bg-slate-50/40' }} p-6 relative shadow-xs">
                        <!-- Nút icon trắng đen nhỏ ở trên góc bên phải để ẩn/hiện số lần bị cảnh cáo -->
                        <div class="absolute top-4 right-4">
                            <button type="button" 
                                    @click="hideWarningCount = !hideWarningCount"
                                    class="w-7 h-7 rounded bg-black text-white hover:bg-neutral-800 border border-neutral-700 flex items-center justify-center transition cursor-pointer shadow-xs"
                                    :title="hideWarningCount ? 'Hiện lại số lần cảnh cáo' : 'Ẩn số lần cảnh cáo'">
                                <template x-if="!hideWarningCount">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </template>
                                <template x-if="hideWarningCount">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </template>
                            </button>
                        </div>

                        <div class="pr-10">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-neutral-900 text-white rounded">
                                    GIÁM SÁT VI PHẠM (ADMIN &amp; GIẢNG VIÊN)
                                </span>
                                <span class="text-xs text-neutral-500 font-semibold">
                                    Thí sinh: <strong class="text-neutral-900">{{ $attempt->student->name ?? 'N/A' }}</strong> ({{ $attempt->student->email ?? '' }})
                                    @if($attempt->student && ($attempt->student->isTeacher() || $attempt->student->role === 'teacher'))
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-700">Giảng viên</span>
                                    @elseif($attempt->student && ($attempt->student->isAdmin() || $attempt->student->role === 'admin'))
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">Admin</span>
                                    @endif
                                </span>
                            </div>

                            <!-- Khối hiển thị số lần cảnh cáo -->
                            <div class="flex items-center gap-3.5 mt-3">
                                <div class="w-11 h-11 rounded-xl {{ ($attempt->warning_count ?? 0) > 0 ? 'bg-amber-100 text-amber-700 border border-amber-300' : 'bg-emerald-100 text-emerald-700 border border-emerald-300' }} flex items-center justify-center shrink-0">
                                    @if(($attempt->warning_count ?? 0) > 0)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>

                                <div>
                                    <span class="text-xs font-bold text-neutral-600 block">Số lần thí sinh bị cảnh cáo chuyển tab (gian lận):</span>
                                    <div class="mt-0.5 flex items-baseline gap-1.5">
                                        <span x-show="!hideWarningCount" class="text-2xl sm:text-3xl font-black font-mono {{ ($attempt->warning_count ?? 0) > 0 ? 'text-amber-600' : 'text-neutral-900' }}">
                                            :{{ $attempt->warning_count ?? 0 }}
                                        </span>
                                        <span x-show="hideWarningCount" x-cloak class="inline-block px-2.5 py-0.5 bg-neutral-200 text-neutral-600 rounded text-xs font-mono font-bold tracking-widest">
                                            •••• (Đã ẩn)
                                        </span>
                                        <span x-show="!hideWarningCount" class="text-xs text-neutral-500 font-semibold">lần vi phạm</span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[11px] text-neutral-400 mt-2.5">
                                * Lưu ý: Thông tin cảnh cáo vi phạm này được bảo mật trên hệ thống và <strong>không hiển thị cho học viên xem</strong>.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Card Bảng điểm & Tổng quan kết quả -->
                <div class="bg-white border border-pink-100 p-8 sm:p-10 text-center relative shadow-sm">
                    <div class="inline-block px-3 py-1 text-xs font-black uppercase tracking-widest mb-4 {{ $attempt->is_passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $attempt->is_passed ? 'KẾT QUẢ: ĐẠT YÊU CẦU' : 'KẾT QUẢ: CHƯA ĐẠT' }}
                    </div>

                    <div class="flex items-baseline justify-center gap-2 mb-6">
                        <span class="text-6xl sm:text-7xl font-black tracking-tight {{ $attempt->is_passed ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ number_format($attempt->score, 1) }}
                        </span>
                        <span class="text-2xl font-bold text-neutral-400">/ 10 điểm</span>
                    </div>

                    <!-- 3 Thống kê chi tiết -->
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-px bg-neutral-200 border border-neutral-200 max-w-xl mx-auto text-center text-xs">
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Số câu đúng</dt>
                            <dd class="text-base font-black text-neutral-900 mt-1">
                                {{ $attempt->correct_answers }} / {{ $attempt->total_questions }}
                            </dd>
                        </div>
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Tỷ lệ chính xác</dt>
                            <dd class="text-base font-black text-pink-600 mt-1">
                                {{ $attempt->total_questions > 0 ? round(($attempt->correct_answers / $attempt->total_questions) * 100) : 0 }}%
                            </dd>
                        </div>
                        <div class="bg-white p-4">
                            <dt class="text-[10px] uppercase font-bold text-neutral-400">Điểm chuẩn qua môn</dt>
                            <dd class="text-base font-black text-neutral-900 mt-1">
                                &ge; {{ $quiz->passing_score }}/10
                            </dd>
                        </div>
                    </dl>
                </div>

            @if($canViewResultDetails)
                <!-- Chi tiết từng câu hỏi và đối chiếu đáp án -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-pink-100 pb-3">
                    <h2 class="text-lg font-black text-neutral-900 uppercase tracking-wider">
                        Đối chiếu đáp án chi tiết
                    </h2>
                    <span class="text-xs text-neutral-500 font-semibold">
                        {{ $attempt->total_questions ?? $quiz->questions->count() }} câu hỏi
                    </span>
                </div>

                @php
                    $userAnswers = $attempt->answers ?? [];
                    $attemptQuestionIds = array_keys($userAnswers);
                    $resultQuestions = (!empty($attemptQuestionIds) && count($attemptQuestionIds) < $quiz->questions->count())
                        ? $quiz->questions->whereIn('id', $attemptQuestionIds)->values()
                        : $quiz->questions;
                @endphp

                @foreach($resultQuestions as $index => $question)
                    @php
                        $selectedOptionId = $userAnswers[$question->id] ?? null;
                        $correctOption = $question->options->firstWhere('is_correct', true);
                        $isAnswerCorrect = $selectedOptionId && $correctOption && ((int)$selectedOptionId === (int)$correctOption->id);
                    @endphp

                    <div class="bg-white border {{ $isAnswerCorrect ? 'border-emerald-300' : 'border-rose-300' }} p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-4 mb-4 pb-3 border-b border-neutral-100">
                            <div class="flex items-start gap-3">
                                <span class="flex items-center justify-center w-7 h-7 {{ $isAnswerCorrect ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} font-black text-xs shrink-0">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <h3 class="text-base font-black text-neutral-900 leading-snug pt-0.5">
                                    {{ $question->question_text }}
                                </h3>
                            </div>

                            @if($isAnswerCorrect)
                                <span class="shrink-0 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                    Đúng
                                </span>
                            @else
                                <span class="shrink-0 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                    Sai
                                </span>
                            @endif
                        </div>

                        <!-- Các đáp án -->
                        <div class="space-y-2 mb-4">
                            @foreach($question->options as $optIndex => $option)
                                @php
                                    $optionLetter = chr(65 + $optIndex);
                                    $isSelected = ((int)$selectedOptionId === (int)$option->id);
                                    $isThisOptionCorrect = $option->is_correct;
                                @endphp

                                <div class="flex items-center justify-between p-3.5 text-sm border 
                                    @if($isThisOptionCorrect)
                                        bg-emerald-50 border-emerald-400 text-emerald-950 font-medium
                                    @elseif($isSelected && !$isThisOptionCorrect)
                                        bg-rose-50 border-rose-400 text-rose-950 font-medium
                                    @else
                                        bg-neutral-50/50 border-neutral-200 text-neutral-700
                                    @endif
                                ">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 flex items-center justify-center font-black text-xs 
                                            @if($isThisOptionCorrect)
                                                bg-emerald-600 text-white
                                            @elseif($isSelected)
                                                bg-rose-600 text-white
                                            @else
                                                bg-neutral-200 text-neutral-700
                                            @endif
                                        ">
                                            {{ $optionLetter }}
                                        </span>
                                        <span>{{ $option->option_text }}</span>
                                    </div>

                                    <div class="text-xs font-bold">
                                        @if($isThisOptionCorrect)
                                            <span class="text-emerald-700">&bull; Đáp án chính xác</span>
                                        @elseif($isSelected)
                                            <span class="text-rose-600">&bull; Bạn đã chọn</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Lời giải thích của giảng viên -->
                        @if($question->explanation)
                            <div class="mt-4 p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 border-l-4 border-l-amber-500 flex items-start gap-3 shadow-2xs">
                                <div class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5" title="Lời giải thích">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <strong class="text-amber-900 block mb-1 uppercase tracking-wider text-[11px] font-black">
                                        Giải thích chi tiết:
                                    </strong>
                                    <p class="text-slate-800 text-xs sm:text-[13px] leading-relaxed whitespace-pre-line font-normal">
                                        {{ $question->explanation }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @else
                <!-- Thông báo khi cấu hình không cho xem câu hỏi & đáp án chi tiết -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-8 sm:p-10 text-center space-y-3 shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-pink-50 border border-pink-100 text-pink-600 flex items-center justify-center mx-auto text-xl font-bold shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Câu hỏi &amp; đáp án chi tiết không hiển thị
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Theo cài đặt của bài kiểm tra này, học viên chỉ xem điểm số sau khi nộp bài mà không hiển thị lại nội dung câu hỏi và đáp án chi tiết.
                        </p>
                    </div>
                </div>
            @endif

            <div class="text-center pt-4">
                <a href="{{ $quiz->course_id ? route('student.courses.show', $quiz->course_id) : route('student.quizzes.index') }}" 
                   class="inline-block px-8 py-3.5 bg-neutral-950 hover:bg-pink-600 text-white font-extrabold text-xs uppercase tracking-wider transition">
                    &larr; {{ $quiz->course_id ? 'Quay lại khóa học' : 'Quay lại danh sách quizzes' }}
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
