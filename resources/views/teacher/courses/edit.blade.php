<x-teacher-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('instructor.dashboard', ['tab' => 'courses']) }}" class="text-xs font-semibold text-pink-600 hover:underline inline-flex items-center gap-1 mb-1">
                    &larr; Quay lại Bảng điều khiển Giảng viên
                </a>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight flex items-center gap-2.5">
                    
                    Chỉnh sửa Khóa học: {{ $course->title }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-8">
                
                <form action="{{ route('instructor.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <!-- Tên khóa học -->
                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tên khóa học <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" required value="{{ old('title', $course->title) }}" 
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
                                  placeholder="Mô tả nội dung, kiến thức người học sẽ nhận được..."
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banner khóa học -->
                    <div class="pt-2">
                        <x-course-banner-picker :course="$course" />
                    </div>

                    <!-- Trạng thái khóa học hiện tại -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-700">Trạng thái hiện tại:</span>
                            @if($course->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Đã duyệt
                                </span>
                            @elseif($course->status === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Chờ duyệt
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Bị từ chối
                                </span>
                            @endif
                        </div>
                        <span class="text-slate-400">Tạo ngày: {{ $course->created_at->format('d/m/Y') }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('instructor.dashboard', ['tab' => 'courses']) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                            Cập nhật khóa học
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-teacher-layout>
