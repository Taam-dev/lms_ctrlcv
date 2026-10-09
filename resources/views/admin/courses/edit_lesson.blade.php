<x-admin-layout breadcrumb="Chỉnh sửa Bài Giảng">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-pink-50 text-pink-700">
                        Khóa học: {{ $lesson->course->title ?? 'Không rõ' }}
                    </span>
                    @if($lesson->course && $lesson->course->teacher)
                        <span class="text-xs text-slate-400 font-medium">
                            &bull; GV: {{ $lesson->course->teacher->name }}
                        </span>
                    @endif
                </div>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Chỉnh Sửa Bài Giảng: {{ $lesson->title }}
                </h1>
            </div>
            <a href="{{ route('admin.dashboard', ['tab' => 'lessons']) }}" class="text-xs font-bold text-slate-600 hover:text-pink-600 border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap shrink-0 shadow-2xs">
                &larr; Quay lại danh sách bài giảng
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8 space-y-6">

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

            <form id="admin-lesson-edit-form" action="{{ route('admin.lessons.update', $lesson->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Tên bài giảng <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" required 
                           placeholder="Ví dụ: Bài 1 - Giới thiệu tổng quan và cài đặt môi trường" 
                           class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                    @error('title')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="content_type" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Định dạng bài học <span class="text-rose-500">*</span>
                        </label>
                        <select name="content_type" id="content_type" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                            <option value="video" {{ old('content_type', $lesson->content_type) === 'video' ? 'selected' : '' }}>Video bài giảng (Link Youtube / MP4)</option>
                            <option value="text" {{ old('content_type', $lesson->content_type) === 'text' ? 'selected' : '' }}>Văn bản / Hướng dẫn (Text / Markdown)</option>
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
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                        @error('order_number')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="content" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Nội dung bài học / Link video <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="content" id="content" rows="6" required 
                              placeholder="Nếu là video: dán link Youtube (ví dụ: https://www.youtube.com/watch?v=...)&#10;Nếu là text: soạn thảo nội dung bài viết..."
                              class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('content', $lesson->content) }}</textarea>
                    @error('content')
                        <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('admin.dashboard', ['tab' => 'lessons']) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                        Hủy bỏ
                    </a>
                    <button type="submit" id="submit-admin-lesson-edit-btn" class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition inline-flex items-center gap-2">
                        <span>Lưu thay đổi bài giảng</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-admin-layout>
