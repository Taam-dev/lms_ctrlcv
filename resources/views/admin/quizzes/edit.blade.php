<x-admin-layout breadcrumb="Cấu hình Quiz">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-pink-50 text-pink-700 mb-1">
                    Khu vực Quản Trị Viên (Admin)
                </span>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Cấu Hình Bài Kiểm Tra / Quiz
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.quizzes.questions', $quiz->id) }}" class="text-xs font-bold text-pink-700 hover:text-pink-900 bg-pink-50 hover:bg-pink-100 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                    Quản lý câu hỏi &rarr;
                </a>
                <a href="{{ route('admin.quizzes.index') }}" class="text-xs font-bold text-slate-600 hover:text-pink-600 border border-slate-200 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                    Quay lại danh sách
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-8 space-y-6">

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm space-y-1">
                    <div class="font-bold">Vui lòng kiểm tra lại các lỗi:</div>
                    <ul class="list-disc list-inside text-xs pl-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="admin-quiz-edit-form" action="{{ route('admin.quizzes.update', $quiz->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="course_id" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Khóa học áp dụng <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                        </label>
                        <select name="course_id" id="course_id" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                            <option value="" {{ old('course_id', $quiz->course_id) ? '' : 'selected' }}>
                                Tự do (Không theo khóa học nào)
                            </option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" {{ old('course_id', $quiz->course_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Loại bài kiểm tra <span class="text-rose-500">*</span>
                        </label>
                        <select name="type" id="type" required class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                            <option value="quiz" {{ old('type', $quiz->type) === 'quiz' ? 'selected' : '' }}>Kiểm tra (Thường kỳ / Luyện tập)</option>
                            <option value="midterm" {{ old('type', $quiz->type) === 'midterm' ? 'selected' : '' }}>Kiểm tra giữa kỳ</option>
                            <option value="final" {{ old('type', $quiz->type) === 'final' ? 'selected' : '' }}>Kiểm tra cuối kỳ</option>
                        </select>
                        @error('type')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Tiêu đề bài kiểm tra <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $quiz->title) }}" required 
                           class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Mô tả / Hướng dẫn
                    </label>
                    <textarea name="description" id="description" rows="3" 
                              class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description', $quiz->description) }}</textarea>
                </div>

                @php
                    $totalQuestionsCount = $quiz->questions()->count();
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <label for="duration_minutes" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Thời gian (Phút) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" max="300" required 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                    </div>

                    <div>
                        <label for="passing_score" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Điểm đạt (Thang 10) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.5" name="passing_score" id="passing_score" value="{{ old('passing_score', $quiz->passing_score) }}" min="0" max="10" required 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                    </div>

                    <div>
                        <label for="question_limit" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center justify-between">
                            <span>Số câu mỗi lượt</span>
                            <span class="text-[10px] font-semibold text-pink-600">Kho: {{ $totalQuestionsCount }}</span>
                        </label>
                        <input type="number" name="question_limit" id="question_limit" value="{{ old('question_limit', $quiz->question_limit) }}" min="1" {{ $totalQuestionsCount > 0 ? "max=$totalQuestionsCount" : '' }}
                               placeholder="Tất cả"
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Trạng thái phê duyệt <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="status" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                            <option value="approved" {{ old('status', $quiz->status) === 'approved' ? 'selected' : '' }}>Đã duyệt (Approved)</option>
                            <option value="pending" {{ old('status', $quiz->status) === 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                            <option value="rejected" {{ old('status', $quiz->status) === 'rejected' ? 'selected' : '' }}>Từ chối / Tạm ẩn (Rejected)</option>
                        </select>
                    </div>
                </div>

                <!-- Thời gian mở và hạn chót làm bài kiểm tra -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="starts_at" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Bắt đầu mở từ ngày <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                        </label>
                        <input type="datetime-local" name="starts_at" id="starts_at" 
                               value="{{ old('starts_at', $quiz->starts_at ? $quiz->starts_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2 bg-white">
                        <p class="text-slate-400 text-xs mt-1">Học viên chỉ có thể vào làm từ thời điểm này (để trống nếu mở ngay).</p>
                        @error('starts_at')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ends_at" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Hạn chót kết thúc <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                        </label>
                        <input type="datetime-local" name="ends_at" id="ends_at" 
                               value="{{ old('ends_at', $quiz->ends_at ? $quiz->ends_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2 bg-white">
                        <p class="text-slate-400 text-xs mt-1">Sau thời gian này đề sẽ quá hạn, học viên không thể vào làm được nữa.</p>
                        @error('ends_at')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="randomize_questions" value="1" {{ old('randomize_questions', $quiz->randomize_questions) ? 'checked' : '' }}
                               class="rounded text-pink-600 border-slate-300 focus:ring-pink-500 w-5 h-5 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">Đảo ngẫu nhiên thứ tự câu hỏi khi học viên làm bài</span>
                            <span class="text-slate-500 text-xs block leading-relaxed mt-0.5">
                                Mỗi học viên khi làm bài sẽ nhận được bộ câu hỏi với thứ tự xáo trộn ngẫu nhiên.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="font-bold text-slate-900 text-sm block">Có thể xem kết quả sau khi hoàn thành</span>
                            <span class="text-slate-500 text-xs block leading-relaxed mt-0.5">
                                Cho phép học viên xem lại kết quả và đáp án chi tiết sau khi nộp bài.
                            </span>
                        </div>
                        <div class="flex items-center gap-4 shrink-0 bg-white px-3.5 py-1.5 rounded-xl border border-pink-200">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="show_result_after_completion" value="1" 
                                       {{ old('show_result_after_completion', $quiz->show_result_after_completion ? '1' : '0') == '1' ? 'checked' : '' }}
                                       class="text-pink-600 border-slate-300 focus:ring-pink-500 w-4 h-4">
                                <span class="text-xs font-bold text-slate-800">Có</span>
                            </label>
                            <span class="text-slate-300 text-xs">|</span>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="show_result_after_completion" value="0" 
                                       {{ old('show_result_after_completion', $quiz->show_result_after_completion ? '1' : '0') == '0' ? 'checked' : '' }}
                                       class="text-pink-600 border-slate-300 focus:ring-pink-500 w-4 h-4">
                                <span class="text-xs font-bold text-slate-800">Không</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.quizzes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                        Hủy bỏ
                    </a>
                    <button type="submit" id="submit-admin-edit-btn" class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-md shadow-pink-500/25 transition">
                        Lưu cấu hình
                    </button>
                </div>
            </form>

            <script>
                (function() {
                    const form = document.getElementById('admin-quiz-edit-form');
                    const btn = document.getElementById('submit-admin-edit-btn');
                    if (!form || !btn) return;

                    form.addEventListener('submit', function(e) {
                        if (form.dataset.submitting === 'true') {
                            e.preventDefault();
                            return false;
                        }
                        form.dataset.submitting = 'true';
                        btn.disabled = true;
                        btn.style.pointerEvents = 'none';
                        btn.classList.add('opacity-75', 'cursor-not-allowed');
                        btn.innerHTML = 'Đang lưu...';
                    });
                })();
            </script>

        </div>
    </div>
</x-admin-layout>
