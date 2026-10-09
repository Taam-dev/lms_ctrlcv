<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('instructor.dashboard', ['tab' => 'courses']) }}" class="text-xs font-semibold text-pink-600 hover:underline inline-flex items-center gap-1 mb-1">
                    &larr; Quay lại danh sách khóa học
                </a>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight">
                    Tạo Khóa học Mới
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8">
                
                <form id="teacher-course-create-form" 
                      action="{{ route('teacher.courses.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6"
                      x-data="{ isSubmitting: false }"
                      @submit="if (isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
                    @csrf
                    
                    <!-- Tên khóa học -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tên khóa học <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}" 
                               placeholder="Ví dụ: Lập trình Web Fullstack với Laravel & Vue.js"
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mô tả khóa học -->
                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Mô tả chi tiết khóa học
                        </label>
                        <textarea name="description" id="description" rows="5" 
                                  placeholder="Mô tả nội dung, kiến thức người học sẽ nhận được, các yêu cầu tiên quyết..."
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banner khóa học -->
                    <div class="pt-2">
                        <x-course-banner-picker />
                    </div>

                    <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80 text-xs text-slate-600 flex items-start gap-2.5">
                        
                        <span>Khóa học sau khi tạo sẽ ở trạng thái <strong>Chờ duyệt (Pending)</strong>. Quản trị viên (Admin) sẽ phê duyệt trước khi học viên có thể nhìn thấy trên trang chủ.</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('instructor.dashboard', ['tab' => 'courses']) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" 
                                id="btn-submit-course"
                                :disabled="isSubmitting"
                                :class="{ 'opacity-75 cursor-not-allowed pointer-events-none': isSubmitting }"
                                class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition inline-flex items-center gap-2">
                            
                            <span x-text="isSubmitting ? 'Đang tạo khóa học...' : 'Tạo khóa học ngay &rarr;'">Tạo khóa học ngay &rarr;</span>
                        </button>
                    </div>
                </form>

                <script>
                    (function() {
                        const form = document.getElementById('teacher-course-create-form');
                        const btn = document.getElementById('btn-submit-course');
                        if (!form || !btn) return;

                        let isSubmitted = false;
                        form.addEventListener('submit', function(e) {
                            if (isSubmitted) {
                                e.preventDefault();
                                return false;
                            }
                            isSubmitted = true;
                            btn.disabled = true;
                            btn.style.pointerEvents = 'none';
                            btn.classList.add('opacity-75', 'cursor-not-allowed');
                        });
                    })();
                </script>

            </div>
        </div>
    </div>
</x-teacher-layout>