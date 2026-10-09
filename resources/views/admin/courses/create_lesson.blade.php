<x-admin-layout breadcrumb="Thêm Bài Giảng">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-pink-50 text-pink-700 mb-1">
                    Khóa học: {{ $course->title }}
                </span>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Thêm Bài Giảng Mới
                </h1>
            </div>
            <a href="{{ route('admin.courses.index') }}" class="text-xs font-bold text-slate-600 hover:text-pink-600 border border-slate-200 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                
                Quay lại
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

                <form id="admin-lesson-create-form" action="{{ route('admin.courses.lessons.store', $course->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tên bài giảng <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                               placeholder="Ví dụ: Bài 1 - Giới thiệu tổng quan và cài đặt môi trường" 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                    </div>

                    <div>
                        <label for="content_type" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Định dạng bài học <span class="text-rose-500">*</span>
                        </label>
                        <select name="content_type" id="content_type" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                            <option value="text" {{ old('content_type') === 'text' ? 'selected' : '' }}>Văn bản / Hướng dẫn (Text)</option>
                            <option value="video" {{ old('content_type') === 'video' ? 'selected' : '' }}>Video bài giảng (Link Youtube / MP4)</option>
                        </select>
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Nội dung bài học / Link video <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="content" id="content" rows="6" required 
                                  placeholder="Nhập nội dung bài học hoặc dán link video hướng dẫn..."
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('content') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" id="submit-admin-lesson-btn" class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition inline-flex items-center gap-2">
                            <span>Thêm bài giảng</span>
                        </button>
                    </div>
                </form>

                <script>
                    (function() {
                        const form = document.getElementById('admin-lesson-create-form');
                        const btn = document.getElementById('submit-admin-lesson-btn');
                        if (!form || !btn) return;

                        let isSubmitted = false;

                        form.addEventListener('submit', function (e) {
                            if (isSubmitted) {
                                e.preventDefault();
                                return false;
                            }
                            isSubmitted = true;
                            form.dataset.submitting = 'true';

                            btn.disabled = true;
                            btn.style.pointerEvents = 'none';
                            btn.classList.add('opacity-75', 'cursor-not-allowed');
                            btn.innerHTML = `
                                
                                <span>Đang thêm bài giảng...</span>
                            `;
                        });

                        btn.addEventListener('click', function(e) {
                            if (isSubmitted) {
                                e.preventDefault();
                                return false;
                            }
                        });
                    })();
                </script>

            </div>
    </div>
</x-admin-layout>
