<x-admin-layout breadcrumb="Chỉnh sửa Khóa học">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-pink-50 text-pink-700 mb-1">
                    Khu vực Quản Trị Viên (Admin)
                </span>
                <h1 class="font-black text-2xl sm:text-3xl text-slate-900 tracking-tight">
                    Chỉnh Sửa Khóa Học
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

                <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Tên khóa học <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required 
                               class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                        @error('title')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Mô tả khóa học
                        </label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('description', $course->description) }}</textarea>
                        @error('description')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Banner khóa học -->
                    <div class="pt-2">
                        <x-course-banner-picker :course="$course" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="teacher_id" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Giảng viên phụ trách
                            </label>
                            <select name="teacher_id" id="teacher_id" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                                @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" {{ old('teacher_id', $course->teacher_id) == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->email }} - {{ $t->role }})
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Trạng thái phê duyệt <span class="text-rose-500">*</span>
                            </label>
                            <select name="status" id="status" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                                <option value="approved" {{ old('status', $course->status) === 'approved' ? 'selected' : '' }}>Đã duyệt (Approved)</option>
                                <option value="pending" {{ old('status', $course->status) === 'pending' ? 'selected' : '' }}>Chờ duyệt (Pending)</option>
                                <option value="rejected" {{ old('status', $course->status) === 'rejected' ? 'selected' : '' }}>Từ chối / Tạm ẩn (Rejected)</option>
                            </select>
                            @error('status')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.courses.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>

            </div>
    </div>
</x-admin-layout>
