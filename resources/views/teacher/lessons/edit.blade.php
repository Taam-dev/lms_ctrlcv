<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $lesson->course_id]) }}" class="text-xs font-semibold text-pink-600 hover:underline inline-flex items-center gap-1 mb-1">
                    &larr; Quay lại danh sách bài giảng
                </a>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    
                    Chỉnh sửa Bài Giảng: {{ $lesson->title }}
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Khóa học: <span class="font-semibold text-slate-700">{{ $lesson->course->title }}</span>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8">
                
                <form action="{{ route('instructor.lessons.update', $lesson->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Tiêu đề bài giảng -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tiêu đề bài giảng <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $lesson->title) }}" 
                               placeholder="Ví dụ: Bài 1: Cài đặt môi trường & Giới thiệu tổng quan" 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2 Cột: Định dạng & Thứ tự -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="content_type" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Định dạng bài giảng <span class="text-rose-500">*</span>
                            </label>
                            <select name="content_type" id="content_type" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                                <option value="video" {{ old('content_type', $lesson->content_type) == 'video' ? 'selected' : '' }}>Video bài giảng (Youtube link)</option>
                                <option value="text" {{ old('content_type', $lesson->content_type) == 'text' ? 'selected' : '' }}>Bài viết văn bản (Text / Markdown)</option>
                            </select>
                            @error('content_type')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="order_number" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Thứ tự bài học <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="order_number" id="order_number" min="1" value="{{ old('order_number', $lesson->order_number) }}" required 
                                   class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                            @error('order_number')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Nội dung bài giảng / link video -->
                    <div>
                        <label for="content" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Nội dung bài học hoặc Đường dẫn Video <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="content" id="content" rows="6" required 
                                  placeholder="Nếu là video: dán link Youtube (ví dụ: https://www.youtube.com/watch?v=...)&#10;Nếu là text: soạn thảo nội dung bài viết..."
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('content', $lesson->content) }}</textarea>
                        @error('content')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('instructor.dashboard', ['tab' => 'lessons', 'course_id' => $lesson->course_id]) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                            Cập nhật bài giảng
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-teacher-layout>
