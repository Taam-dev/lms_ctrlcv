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
                            <option value="midterm" {{ old('type', $quiz->type) === 'midterm' ? 'selected' : '' }}>⏱️ Kiểm tra giữa kỳ</option>
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

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
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
