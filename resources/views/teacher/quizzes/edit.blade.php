<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-black text-2xl text-slate-900 tracking-tight">
                Chỉnh sửa Bài kiểm tra: {{ $quiz->title }}
            </h2>
            <a href="{{ route('instructor.dashboard', ['tab' => 'quizzes']) }}" class="text-sm font-semibold text-slate-600 hover:text-pink-600 transition">
                &larr; Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8">
                
                <form action="{{ route('teacher.quizzes.update', $quiz->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- 2 Cột: Khóa học & Loại bài kiểm tra -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Khóa học -->
                        <div>
                            <label for="course_id" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Khóa học áp dụng <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                            </label>
                            <select name="course_id" id="course_id" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                                <option value="" {{ old('course_id', $quiz->course_id) ? '' : 'selected' }}>
                                    Tự do (Không theo khóa học nào)
                                </option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id', $quiz->course_id) == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Loại bài kiểm tra -->
                        <div>
                            <label for="type" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Loại bài kiểm tra <span class="text-rose-500">*</span>
                            </label>
                            <select name="type" id="type" required class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                                <option value="quiz" {{ old('type', $quiz->type) === 'quiz' ? 'selected' : '' }}>Kiểm tra (Thường kỳ / Luyện tập)</option>
                                <option value="midterm" {{ old('type', $quiz->type) === 'midterm' ? 'selected' : '' }}>⏱️ Kiểm tra giữa kỳ</option>
                                <option value="final" {{ old('type', $quiz->type) === 'final' ? 'selected' : '' }}>Kiểm tra cuối kỳ</option>
                            </select>
                            @error('type')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tiêu đề Quiz -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tiêu đề bài kiểm tra <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $quiz->title) }}" required 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mô tả bài kiểm tra -->
                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Mô tả / Hướng dẫn
                        </label>
                        <textarea name="description" id="description" rows="3" 
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description', $quiz->description) }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Thời gian & Điểm đạt -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="duration_minutes" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Thời gian làm bài (Phút) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" max="180" required 
                                   class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                            @error('duration_minutes')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="passing_score" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Điểm chuẩn qua môn (Thang điểm 10) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.5" name="passing_score" id="passing_score" value="{{ old('passing_score', $quiz->passing_score) }}" min="0" max="10" required 
                                   class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                            @error('passing_score')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tùy chỉnh đảo ngẫu nhiên câu hỏi (Randomize Question Order) -->
                    <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="randomize_questions" value="1" {{ old('randomize_questions', $quiz->randomize_questions) ? 'checked' : '' }}
                                   class="rounded text-pink-600 border-slate-300 focus:ring-pink-500 w-5 h-5 mt-0.5">
                            <div>
                                <span class="font-bold text-slate-900 text-sm block">Đảo ngẫu nhiên thứ tự câu hỏi khi học viên làm bài</span>
                                <span class="text-slate-500 text-xs block leading-relaxed mt-0.5">
                                    Mỗi học viên khi bấm vào làm bài sẽ nhận được bộ câu hỏi với thứ tự xáo trộn ngẫu nhiên.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <a href="{{ route('teacher.quizzes.questions', $quiz->id) }}" class="text-sm font-bold text-pink-600 hover:underline">
                            Quản lý câu hỏi ({{ $quiz->questions()->count() }} câu) &rarr;
                        </a>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('instructor.dashboard', ['tab' => 'quizzes']) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                                Hủy
                            </a>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                                Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-teacher-layout>
