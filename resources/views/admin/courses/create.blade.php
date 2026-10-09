<x-admin-layout breadcrumb="Tạo khóa học mới">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-pink-50 text-pink-700 mb-1">
                    Khu vực Quản Trị Viên (Admin)
                </span>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-pink-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                        +
                    </span>
                    Tạo Khóa Học Mới (Admin)
                </h1>
            </div>
            <a href="{{ route('admin.courses.index') }}" class="text-xs font-bold text-slate-600 hover:text-pink-600 border border-slate-200 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                
                Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8 space-y-6">

                @if($errors->any())
                    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm space-y-1">
                        <div class="font-bold">Vui lòng kiểm tra lại các lỗi sau:</div>
                        <ul class="list-disc list-inside text-xs pl-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="admin-course-create-form" 
                      action="{{ route('admin.courses.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      class="space-y-6"
                      x-data="{ isSubmitting: false }"
                      @submit="if (isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
                    @csrf

                    <!-- Tiêu đề khóa học -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tên khóa học <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                               placeholder="Ví dụ: Lập trình ứng dụng Web với Laravel 11 từ cơ bản đến nâng cao" 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mô tả khóa học -->
                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Mô tả khóa học
                        </label>
                        <textarea name="description" id="description" rows="4" 
                                  placeholder="Mô tả nội dung tổng quan, mục tiêu khóa học và đối tượng người học..."
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banner khóa học -->
                    <div class="pt-2">
                        <x-course-banner-picker />
                    </div>

                    <!-- Giảng viên phụ trách & Trạng thái duyệt -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="teacher_id" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Giảng viên phụ trách
                            </label>
                            <select name="teacher_id" id="teacher_id" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                                <option value="{{ auth()->id() }}">Tôi (Quản trị viên - {{ auth()->user()->name }})</option>
                                @foreach($teachers as $t)
                                    @if($t->id !== auth()->id())
                                        <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }} ({{ $t->email }} - {{ $t->role }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Gán khóa học cho một giảng viên hoặc giữ dưới tài khoản Admin.</p>
                            @error('teacher_id')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Trạng thái phê duyệt <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                                <option value="approved" selected>Đã duyệt (Hiển thị công khai)</option>
                                <option value="pending">Chờ duyệt (Pending)</option>
                                <option value="rejected">Tạm ẩn / Từ chối (Rejected)</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Do Admin trực tiếp tạo nên mặc định sẽ được duyệt ngay.</p>
                            @error('status')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" 
                                id="btn-admin-submit-course"
                                :disabled="isSubmitting"
                                :class="{ 'opacity-75 cursor-not-allowed pointer-events-none': isSubmitting }"
                                class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition inline-flex items-center gap-2">
                            
                            <span x-text="isSubmitting ? 'Đang tạo...' : 'Tạo khóa học'">Tạo khóa học</span>
                            <span x-show="!isSubmitting">&rarr;</span>
                        </button>
                    </div>
                </form>

                <script>
                    (function() {
                        const form = document.getElementById('admin-course-create-form');
                        const btn = document.getElementById('btn-admin-submit-course');
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
</x-admin-layout>
