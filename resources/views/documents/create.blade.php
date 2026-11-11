<x-app-layout
    title="Đăng tải tài liệu học tập mới | Ctrl C+V"
    meta-description="Đăng tải bài giảng, slide tóm tắt, đề thi và mã nguồn mẫu lên Kho Tài Liệu Ctrl C+V.">

    <section class="bg-neutral-950 text-white border-b border-white/10 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-3">
                <a href="{{ route('home') }}" class="hover:text-white transition">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('documents.index') }}" class="hover:text-white transition">Kho tài liệu</a>
                <span>/</span>
                <span class="text-pink-400 font-semibold">Đăng tải mới</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-600/20 border border-pink-500/30 text-pink-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Đăng Tải Tài Liệu Học Tập</h1>
                    <p class="text-xs text-neutral-400">Chia sẻ giáo trình, slide bài giảng, đề ôn tập và mã nguồn dự án đến sinh viên & học viên.</p>
                </div>
            </div>
        </div>
    </section>

    <main class="py-10 bg-[#09090b] text-neutral-100 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                    <div class="font-bold text-rose-400 text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Vui lòng kiểm tra lại thông tin:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('documents.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  x-data="documentUploader()"
                  class="space-y-6">
                @csrf

                <!-- Card 1: Thông tin cơ bản -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2 border-b border-white/5 pb-3">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        1. Thông tin chung về tài liệu
                    </h2>

                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label for="title" class="block text-xs font-bold uppercase tracking-wider text-neutral-300">
                            Tiêu đề tài liệu <span class="text-pink-500">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               id="title"
                               required
                               value="{{ old('title') }}"
                               placeholder="Ví dụ: Giáo trình Lập trình Web Fullstack Laravel 11 từ A-Z..."
                               class="w-full px-4 py-3 bg-neutral-900 border border-white/10 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">
                        @error('title')
                            <p class="text-rose-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="space-y-1.5">
                        <label for="category" class="block text-xs font-bold uppercase tracking-wider text-neutral-300">
                            Chuyên mục / Lĩnh vực <span class="text-pink-500">*</span>
                        </label>
                        <select name="category"
                                id="category"
                                required
                                x-model="selectedCategory"
                                @change="updateDefaultCover()"
                                class="w-full px-4 py-3 bg-neutral-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 cursor-pointer">
                            <option value="">-- Chọn chuyên mục tài liệu --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-rose-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label for="description" class="block text-xs font-bold uppercase tracking-wider text-neutral-300">
                            Mô tả chi tiết & Hướng dẫn sử dụng
                        </label>
                        <textarea name="description"
                                  id="description"
                                  rows="4"
                                  placeholder="Mô tả nội dung tài liệu, đối tượng học viên nên đọc, mục tiêu kiến thức đạt được, phiên bản công nghệ sử dụng..."
                                  class="w-full px-4 py-3 bg-neutral-900 border border-white/10 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-rose-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(Auth::user()->isAdmin())
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                       class="rounded bg-neutral-900 border-white/20 text-pink-600 focus:ring-pink-500 w-4 h-4">
                                <span class="text-xs font-bold text-neutral-200">Đánh dấu là tài liệu nổi bật (Ghim lên trang đầu)</span>
                            </label>
                        </div>
                    @endif
                </div>

                <!-- Card 2: Ảnh bìa (Cover Image) -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2 border-b border-white/5 pb-3">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        2. Ảnh bìa tài liệu (Cover Image)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Preview Cover Image -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-neutral-400">Xem trước ảnh bìa:</label>
                            <div class="relative aspect-video rounded-xl overflow-hidden bg-neutral-900 border border-white/10 flex items-center justify-center">
                                <img :src="coverPreviewUrl"
                                     alt="Cover Preview"
                                     class="w-full h-full object-cover">
                                <div class="absolute bottom-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold bg-black/70 backdrop-blur-sm text-white">
                                    Xem trước
                                </div>
                            </div>
                            <p class="text-[11px] text-neutral-500">Tự động dùng ảnh minh họa theo chuyên mục nếu bạn không tải ảnh riêng.</p>
                        </div>

                        <!-- Upload or Custom URL -->
                        <div class="space-y-4">
                            <!-- Custom File Upload -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-neutral-300">
                                    Tải ảnh bìa từ máy tính (Tối đa 5MB)
                                </label>
                                <input type="file"
                                       name="cover_image_file"
                                       accept="image/png,image/jpeg,image/webp"
                                       @change="handleCoverFileSelect($event)"
                                       class="w-full text-xs text-neutral-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-pink-600/20 file:text-pink-300 hover:file:bg-pink-600 hover:file:text-white file:transition file:cursor-pointer cursor-pointer">
                            </div>

                            <div class="flex items-center gap-2 text-xs text-neutral-500">
                                <div class="flex-grow h-px bg-white/10"></div>
                                <span>HOẶC</span>
                                <div class="flex-grow h-px bg-white/10"></div>
                            </div>

                            <!-- Custom URL -->
                            <div class="space-y-1.5">
                                <label for="cover_image_url" class="block text-xs font-bold text-neutral-300">
                                    Dán link ảnh trực tiếp (URL)
                                </label>
                                <input type="url"
                                       name="cover_image_url"
                                       id="cover_image_url"
                                       x-model="customCoverUrl"
                                       @input="if(customCoverUrl) coverPreviewUrl = customCoverUrl"
                                       placeholder="https://images.unsplash.com/..."
                                       class="w-full px-4 py-2.5 bg-neutral-900 border border-white/10 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Tải lên danh sách tệp tin đính kèm (Multiple Files) -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3">
                        <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                            3. Tệp tin đính kèm (Hỗ trợ nhiều file) <span class="text-pink-500">*</span>
                        </h2>
                        <span class="text-xs font-bold text-neutral-400" x-text="selectedFiles.length + ' tệp đã chọn'">0 tệp đã chọn</span>
                    </div>

                    <!-- Drag & Drop Zone -->
                    <div class="relative border-2 border-dashed border-white/20 hover:border-pink-500 rounded-2xl p-8 text-center transition group bg-neutral-900/40 hover:bg-neutral-900/80 cursor-pointer"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleFilesDrop($event)"
                         :class="{ 'border-pink-500 bg-pink-500/10': isDragging }">

                        <input type="file"
                               name="files[]"
                               id="files_input"
                               multiple
                               required
                               @change="handleFilesSelect($event)"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                        <div class="space-y-3 pointer-events-none">
                            <div class="w-14 h-14 rounded-2xl bg-pink-600/20 border border-pink-500/30 text-pink-400 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">
                                    Kéo thả các tệp tin vào đây hoặc <span class="text-pink-400 underline">Bấm để chọn file</span>
                                </p>
                                <p class="text-xs text-neutral-400 mt-1">
                                    Hỗ trợ tài liệu PDF, DOCX, PPTX, XLSX, mã nguồn ZIP, RAR, MP4, hình ảnh... (Tối đa 50MB mỗi tệp)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Files List -->
                    <div x-show="selectedFiles.length > 0" class="space-y-2 pt-2" style="display: none;">
                        <h4 class="text-xs font-bold text-neutral-300 uppercase tracking-wider">Danh sách tệp tin chuẩn bị đăng tải:</h4>
                        <div class="divide-y divide-white/5 border border-white/10 rounded-xl overflow-hidden bg-neutral-900/70">
                            <template x-for="(file, index) in selectedFiles" :key="index">
                                <div class="px-4 py-3 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-7 h-7 rounded-lg bg-pink-600/20 text-pink-400 font-black flex items-center justify-center text-[10px] uppercase shrink-0"
                                              x-text="getFileExtension(file.name)">
                                            FILE
                                        </span>
                                        <div class="min-w-0">
                                            <div class="font-bold text-white truncate" x-text="file.name"></div>
                                            <div class="text-[11px] text-neutral-400" x-text="formatSize(file.size)"></div>
                                        </div>
                                    </div>
                                    <button type="button"
                                            @click="removeFile(index)"
                                            class="text-neutral-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-white/5 transition"
                                            title="Bỏ tệp này">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Submit Action Buttons -->
                <div class="flex items-center justify-between gap-4 pt-4">
                    <a href="{{ route('documents.index') }}"
                       class="px-5 py-3 rounded-xl text-xs font-bold bg-white/5 hover:bg-white/10 text-neutral-300 hover:text-white transition">
                        &larr; Quay lại danh sách
                    </a>

                    <button type="submit"
                            :disabled="selectedFiles.length === 0"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl font-black text-xs uppercase tracking-wider bg-pink-600 hover:bg-pink-500 disabled:opacity-50 disabled:cursor-not-allowed text-white shadow-lg shadow-pink-600/30 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Đăng tài liệu ngay</span>
                    </button>
                </div>
            </form>

        </div>
    </main>

    @push('scripts')
    <script>
        function documentUploader() {
            return {
                selectedCategory: "{{ old('category', '') }}",
                customCoverUrl: "{{ old('cover_image_url', '') }}",
                defaultCovers: @json($defaultCovers),
                coverPreviewUrl: "{{ old('cover_image_url') ?: ($defaultCovers[old('category')] ?? $defaultCovers['Tài liệu khác']) }}",
                selectedFiles: [],
                isDragging: false,

                init() {
                    this.updateDefaultCover();
                },

                updateDefaultCover() {
                    if (this.customCoverUrl) {
                        this.coverPreviewUrl = this.customCoverUrl;
                        return;
                    }
                    if (this.selectedCategory && this.defaultCovers[this.selectedCategory]) {
                        this.coverPreviewUrl = this.defaultCovers[this.selectedCategory];
                    } else {
                        this.coverPreviewUrl = this.defaultCovers['Tài liệu khác'];
                    }
                },

                handleCoverFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.coverPreviewUrl = URL.createObjectURL(file);
                    }
                },

                handleFilesSelect(event) {
                    const input = event.target;
                    if (input.files && input.files.length > 0) {
                        this.selectedFiles = Array.from(input.files);
                    }
                },

                handleFilesDrop(event) {
                    this.isDragging = false;
                    const dt = event.dataTransfer;
                    if (dt.files && dt.files.length > 0) {
                        const input = document.getElementById('files_input');
                        input.files = dt.files;
                        this.selectedFiles = Array.from(dt.files);
                    }
                },

                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    const dt = new DataTransfer();
                    this.selectedFiles.forEach(file => dt.items.add(file));
                    document.getElementById('files_input').files = dt.files;
                },

                getFileExtension(filename) {
                    const parts = filename.split('.');
                    return parts.length > 1 ? parts.pop().toUpperCase() : 'FILE';
                },

                formatSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

