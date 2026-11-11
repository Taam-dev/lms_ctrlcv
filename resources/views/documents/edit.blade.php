<x-app-layout
    :title="'Chỉnh sửa: ' . $document->title . ' | Ctrl C+V'">

    <section class="bg-neutral-950 text-white border-b border-white/10 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-xs text-neutral-400 mb-3">
                <a href="{{ route('home') }}" class="hover:text-white transition">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('documents.index') }}" class="hover:text-white transition">Kho tài liệu</a>
                <span>/</span>
                <a href="{{ route('documents.show', $document->slug ?: $document->id) }}" class="hover:text-white transition">{{ $document->title }}</a>
                <span>/</span>
                <span class="text-pink-400 font-semibold">Chỉnh sửa</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-pink-600/20 border border-pink-500/30 text-pink-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Chỉnh Sửa Tài Liệu</h1>
                    <p class="text-xs text-neutral-400">Cập nhật thông tin, tải thêm file hoặc gỡ bỏ các file cũ không còn phù hợp.</p>
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

            <form action="{{ route('documents.update', $document->slug ?: $document->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  x-data="documentEditor()"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Card 1: Thông tin chung -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2 border-b border-white/5 pb-3">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        1. Thông tin tài liệu
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
                               value="{{ old('title', $document->title) }}"
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
                                class="w-full px-4 py-3 bg-neutral-900 border border-white/10 rounded-xl text-sm text-white focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 cursor-pointer">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $document->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
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
                                  class="w-full px-4 py-3 bg-neutral-900 border border-white/10 rounded-xl text-sm text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">{{ old('description', $document->description) }}</textarea>
                        @error('description')
                            <p class="text-rose-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(Auth::user()->isAdmin())
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $document->is_featured) ? 'checked' : '' }}
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
                        2. Ảnh bìa tài liệu
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                        <!-- Preview Cover Image -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-neutral-400">Ảnh bìa hiện tại:</label>
                            <div class="relative aspect-video rounded-xl overflow-hidden bg-neutral-900 border border-white/10">
                                <img :src="coverPreviewUrl"
                                     alt="Cover Preview"
                                     class="w-full h-full object-cover">
                            </div>
                        </div>

                        <!-- Upload or Custom URL -->
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-neutral-300">
                                    Thay ảnh bìa mới từ máy tính (Tối đa 5MB)
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

                            <div class="space-y-1.5">
                                <label for="cover_image_url" class="block text-xs font-bold text-neutral-300">
                                    Đổi link ảnh trực tiếp (URL)
                                </label>
                                <input type="url"
                                       name="cover_image_url"
                                       id="cover_image_url"
                                       x-model="customCoverUrl"
                                       @input="if(customCoverUrl) coverPreviewUrl = customCoverUrl"
                                       placeholder="https://..."
                                       class="w-full px-4 py-2.5 bg-neutral-900 border border-white/10 rounded-xl text-xs text-white placeholder-neutral-500 focus:outline-none focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Quản lý tệp tin hiện tại -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <div class="flex items-center justify-between border-b border-white/5 pb-3">
                        <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            3. Tệp tin hiện có ({{ $document->files->count() }} tệp)
                        </h2>
                        <span class="text-xs text-neutral-400">Tích chọn file bạn muốn xóa</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($document->files as $file)
                            <div class="p-3 bg-neutral-900/80 border border-white/10 rounded-xl flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-black flex items-center justify-center text-[10px] uppercase shrink-0">
                                        {{ $file->file_type }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-white truncate">{{ $file->file_name }}</div>
                                        <div class="text-[11px] text-neutral-400">{{ $file->formatted_size }} • {{ number_format($file->download_count) }} lượt tải</div>
                                    </div>
                                </div>

                                <label class="inline-flex items-center gap-2 cursor-pointer bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 px-3 py-1.5 rounded-lg border border-rose-500/20 text-xs transition">
                                    <input type="checkbox"
                                           name="delete_file_ids[]"
                                           value="{{ $file->id }}"
                                           class="rounded bg-neutral-900 border-rose-500/40 text-rose-600 focus:ring-rose-500 w-4 h-4">
                                    <span>Xóa file này</span>
                                </label>
                            </div>
                        @empty
                            <p class="text-xs text-neutral-400 italic">Không có tệp tin nào.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 4: Tải thêm tệp tin mới -->
                <div class="bg-[#141417] border border-white/10 rounded-2xl p-6 sm:p-8 space-y-5 shadow-xl">
                    <h2 class="text-base font-black text-white uppercase tracking-wider flex items-center gap-2 border-b border-white/5 pb-3">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                        4. Tải thêm tệp tin mới (Tùy chọn)
                    </h2>

                    <!-- Multi file input -->
                    <div class="space-y-3">
                        <input type="file"
                               name="new_files[]"
                               id="new_files_input"
                               multiple
                               @change="handleNewFilesSelect($event)"
                               class="w-full text-xs text-neutral-400 file:mr-3 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-pink-600 file:text-white hover:file:bg-pink-500 file:transition file:cursor-pointer cursor-pointer">
                        <p class="text-[11px] text-neutral-400">Chọn 1 hoặc nhiều file để bổ sung thêm vào tài liệu này.</p>
                    </div>

                    <!-- New Files List -->
                    <div x-show="newFiles.length > 0" class="space-y-2 pt-2" style="display: none;">
                        <h4 class="text-xs font-bold text-neutral-300 uppercase tracking-wider">Tệp tin mới sẽ được tải lên:</h4>
                        <div class="divide-y divide-white/5 border border-white/10 rounded-xl overflow-hidden bg-neutral-900/70">
                            <template x-for="(file, index) in newFiles" :key="index">
                                <div class="px-4 py-2.5 flex items-center justify-between gap-3 text-xs">
                                    <div class="truncate text-white font-semibold" x-text="file.name"></div>
                                    <div class="text-[11px] text-neutral-400 shrink-0" x-text="formatSize(file.size)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between gap-4 pt-4">
                    <a href="{{ route('documents.show', $document->slug ?: $document->id) }}"
                       class="px-5 py-3 rounded-xl text-xs font-bold bg-white/5 hover:bg-white/10 text-neutral-300 hover:text-white transition">
                        &larr; Hủy thay đổi
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl font-black text-xs uppercase tracking-wider bg-pink-600 hover:bg-pink-500 text-white shadow-lg shadow-pink-600/30 transition duration-150">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Cập nhật tài liệu</span>
                    </button>
                </div>
            </form>

        </div>
    </main>

    @push('scripts')
    <script>
        function documentEditor() {
            return {
                customCoverUrl: "",
                coverPreviewUrl: "{{ $document->cover_image_url }}",
                newFiles: [],

                handleCoverFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.coverPreviewUrl = URL.createObjectURL(file);
                    }
                },

                handleNewFilesSelect(event) {
                    const input = event.target;
                    if (input.files && input.files.length > 0) {
                        this.newFiles = Array.from(input.files);
                    }
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

