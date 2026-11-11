<x-app-layout>
    <x-slot name="title">Tạo Bộ Đề Minigame - Blooket Gold Quest</x-slot>

    @push('styles')
    <style>
        .mg-input {
            background-color: #0d0d11 !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
            color: #fafafa !important;
        }
        .mg-input:focus {
            border-color: #ec4899 !important;
            box-shadow: 0 0 0 1px #ec4899 !important;
        }
        .mg-card {
            background-color: #141417 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .mg-subcard {
            background-color: #18181b !important;
            border: 1px solid rgba(255, 255, 255, 0.06) !important;
        }
        /* Ẩn hoàn toàn spinner xấu xí mặc định của trình duyệt */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        input[type=number] {
            -moz-appearance: textfield !important;
            appearance: textfield !important;
        }
    </style>
    @endpush

    <div class="py-6 sm:py-8 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)]" x-data="minigameQuizCreateApp()">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            <!-- Toast thông báo nhanh -->
            <div x-show="copyFeedback"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="fixed top-20 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl bg-pink-600 text-white shadow-xl shadow-pink-600/30 text-xs font-bold border border-pink-400/40">
                <svg class="w-4 h-4 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span x-text="copyFeedback"></span>
            </div>

            <!-- Breadcrumb & Header Tinh Gọn -->
            <div class="flex items-center justify-between pb-4 border-b border-zinc-800">
                <div class="flex items-center gap-3">
                    <a href="{{ route('minigame.index') }}" class="p-2.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-800 transition" title="Quay lại">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">Tạo Bộ Câu Hỏi Minigame Mới</h1>
                        <p class="text-xs text-zinc-400 mt-0.5">Tạo bộ đề mới độc lập cho phòng chơi Minigame Gold Quest</p>
                    </div>
                </div>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-1.5 mg-card rounded-2xl border border-zinc-800">
                <button type="button"
                        @click="tab = 'blooket_presets'"
                        :class="tab === 'blooket_presets' ? 'bg-pink-600 text-white shadow-lg shadow-pink-600/25' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60'"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Mẫu Blooket</span>
                </button>

                <button type="button"
                        @click="tab = 'ai_generate'"
                        :class="tab === 'ai_generate' ? 'bg-pink-600 text-white shadow-lg shadow-pink-600/25' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60'"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Tạo Bằng AI</span>
                </button>

                <button type="button"
                        @click="tab = 'import_json'"
                        :class="tab === 'import_json' ? 'bg-pink-600 text-white shadow-lg shadow-pink-600/25' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60'"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import JSON</span>
                </button>

                <button type="button"
                        @click="tab = 'manual'"
                        :class="tab === 'manual' ? 'bg-pink-600 text-white shadow-lg shadow-pink-600/25' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60'"
                        class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Tạo Thủ Công</span>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- TAB 0: CÁC GÓI BỘ ĐỀ MẪU BLOOKET CHUẨN         -->
            <!-- ============================================== -->
            <div x-show="tab === 'blooket_presets'" x-cloak class="mg-card rounded-2xl border border-zinc-800 p-5 sm:p-6 space-y-4">
                <div class="flex items-start gap-3.5 pb-4 border-b border-zinc-800">
                    <div class="w-9 h-9 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">Kho Bộ Đề Mẫu Blooket Tích Hợp Sẵn</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Chọn bất kỳ gói đề thi chuẩn nào bên dưới để nạp tức thì vào trình soạn thảo thủ công.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <template x-for="pack in blooketPacks" :key="pack.id">
                        <div class="mg-subcard rounded-2xl p-4 sm:p-5 border border-zinc-800 hover:border-pink-500/40 transition flex flex-col justify-between space-y-3 group">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-mono uppercase px-2 py-0.5 rounded bg-zinc-800 text-pink-400 font-bold" x-text="pack.count + ' CÂU HỎI'"></span>
                                    <span class="text-[11px] text-zinc-500 font-mono">15-20s / câu</span>
                                </div>
                                <h3 class="text-sm font-bold text-white group-hover:text-pink-400 transition" x-text="pack.name"></h3>
                                <p class="text-xs text-zinc-400 leading-relaxed" x-text="pack.desc"></p>
                            </div>

                            <div class="pt-3 border-t border-zinc-800/80 flex items-center justify-end">
                                <button type="button"
                                        @click="loadPackToManual(pack)"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-500 text-white shadow-lg shadow-pink-600/20 transition">
                                    <span>Nạp Gói Này & Tùy Chỉnh</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 1: TẠO TỰ ĐỘNG BẰNG AI (AI GENERATE)        -->
            <!-- ============================================== -->
            <div x-show="tab === 'ai_generate'" x-cloak class="mg-card rounded-2xl border border-zinc-800 p-5 sm:p-6 space-y-5">
                <div class="flex items-start gap-3.5 pb-4 border-b border-zinc-800">
                    <div class="w-9 h-9 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">Tạo Bộ Đề Tự Động Bằng AI</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">
                            Nhập chủ đề kiến thức, hệ thống AI sẽ tự động phân tích và sinh bộ câu hỏi trắc nghiệm chuẩn format.
                        </p>
                    </div>
                </div>

                <form action="{{ route('minigame.quizzes.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="creation_mode" value="ai_generate">
                    <input type="hidden" name="difficulty" value="medium">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1.5 md:col-span-2">
                            <label for="ai_title" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                Tên Bộ Câu Hỏi <span class="text-pink-500">*</span>
                            </label>
                            <input type="text"
                                   id="ai_title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="VD: Trắc Nghiệm Ôn Tập Kiến Thức PHP & Laravel Nâng Cao"
                                   required
                                   class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">
                        </div>

                        <div class="space-y-1.5 md:col-span-1">
                            <label for="ai_count" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                Số Lượng Câu Hỏi
                            </label>
                            <select id="ai_count"
                                    name="question_count"
                                    class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">
                                <option value="5" {{ old('question_count') == '5' ? 'selected' : '' }}>5 câu (Nhanh)</option>
                                <option value="10" {{ old('question_count', '10') == '10' ? 'selected' : '' }}>10 câu (Khuyên dùng)</option>
                                <option value="15" {{ old('question_count') == '15' ? 'selected' : '' }}>15 câu</option>
                                <option value="20" {{ old('question_count') == '20' ? 'selected' : '' }}>20 câu</option>
                            </select>
                        </div>

                        <div class="space-y-1.5 md:col-span-3">
                            <label for="ai_topic" class="block text-xs font-bold uppercase tracking-wider text-pink-400">
                                Chủ Đề Câu Hỏi Cần AI Sinh <span class="text-pink-500">*</span>
                            </label>
                            <textarea id="ai_topic"
                                      name="topic"
                                      rows="3"
                                      required
                                      placeholder="VD: Lập trình hướng đối tượng OOP trong PHP, Dependency Injection, Service Container trong Laravel, Eloquent ORM và Database Migrations..."
                                      class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">{{ old('topic') }}</textarea>
                            <p class="text-[11px] text-zinc-400">
                                Gợi ý: Cung cấp chủ đề chi tiết giúp AI sinh ra câu hỏi chính xác và thực tế hơn.
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-zinc-800 flex items-center justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold uppercase tracking-wider text-xs bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Sinh Câu Hỏi & Lưu Bộ Đề Ngay</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: IMPORT TỆP HOẶC DÁN JSON (JSON IMPORT)  -->
            <!-- ============================================== -->
            <div x-show="tab === 'import_json'" x-cloak class="mg-card rounded-2xl border border-zinc-800 p-5 sm:p-6 space-y-5">
                <div class="flex items-start justify-between gap-4 pb-4 border-b border-zinc-800 flex-wrap">
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-xl bg-pink-500/10 text-pink-400 border border-pink-500/30 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">Import Từ File Hoặc Dán JSON</h2>
                            <p class="text-xs text-zinc-400 mt-0.5">
                                Hỗ trợ cấu trúc mảng câu hỏi trắc nghiệm gồm <code>question</code>, <code>options</code>, <code>correct_answer</code> và <code>time_limit</code>.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('minigame.sample-json') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-zinc-900 hover:bg-zinc-800 text-zinc-200 border border-zinc-800 transition">
                        <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Tải JSON Mẫu</span>
                    </a>
                </div>

                <form action="{{ route('minigame.quizzes.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="creation_mode" value="import_json">
                    <input type="hidden" name="difficulty" value="medium">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="import_title" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                Tên Bộ Câu Hỏi <span class="text-pink-500">*</span>
                            </label>
                            <input type="text"
                                   id="import_title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="VD: Bộ Đề Kiểm Tra Trắc Nghiệm Tổng Hợp"
                                   required
                                   class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                Tải lên File JSON (.json)
                            </label>
                            <input type="file"
                                   name="json_file"
                                   accept=".json,application/json,text/plain"
                                   class="w-full mg-input border rounded-xl text-zinc-300 text-xs py-2 px-3 focus:outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-pink-400 hover:file:bg-zinc-700">
                        </div>

                        <div class="space-y-1.5 md:col-span-2">
                            <label for="import_json_text" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                Hoặc Dán Trực Tiếp Nội Dung JSON Blooket
                            </label>
                            <textarea id="import_json_text"
                                      name="json_text"
                                      rows="6"
                                      placeholder="[&#10;  {&#10;    &quot;question&quot;: &quot;Nội dung câu hỏi?&quot;,&#10;    &quot;options&quot;: { &quot;A&quot;: &quot;Đáp án A&quot;, &quot;B&quot;: &quot;Đáp án B&quot;, &quot;C&quot;: &quot;Đáp án C&quot;, &quot;D&quot;: &quot;Đáp án D&quot; },&#10;    &quot;correct_answer&quot;: &quot;A&quot;,&#10;    &quot;time_limit&quot;: 20&#10;  }&#10;]"
                                      class="w-full mg-input border rounded-xl text-white font-mono text-xs p-3.5 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-zinc-800 flex items-center justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold uppercase tracking-wider text-xs bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Xác Nhận & Lưu Bộ Đề</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- TAB 3: TẠO THỦ CÔNG TRỰC QUAN (MANUAL BUILDER) -->
            <!-- ============================================== -->
            <div x-show="tab === 'manual'" x-cloak class="space-y-5">
                <form action="{{ route('minigame.quizzes.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="creation_mode" value="manual">
                    <input type="hidden" name="difficulty" value="medium">

                    <!-- THÔNG TIN BỘ ĐỀ TINH GỌN -->
                    <div class="mg-card rounded-2xl p-5 border border-zinc-800 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-white uppercase tracking-wider">Thông Tin Bộ Đề</h2>
                                <p class="text-xs text-zinc-400 mt-0.5">Đặt tên và ghi chú cho bộ câu hỏi</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="copyBlooketJson()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-800 transition"
                                        title="Copy JSON">
                                    <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span class="hidden sm:inline">Copy JSON</span>
                                </button>
                                <button type="button"
                                        @click="showBlooketModal = true"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-pink-600/15 hover:bg-pink-600/25 text-pink-300 border border-pink-500/30 transition">
                                    <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Dán Blooket</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1.5 md:col-span-2">
                                <label for="manual_title" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                    Tên Bộ Câu Hỏi <span class="text-pink-500">*</span>
                                </label>
                                <input type="text"
                                       id="manual_title"
                                       name="title"
                                       x-model="manualTitle"
                                       placeholder="VD: Bộ Đề Khám Phá Kiến Thức Web Fullstack"
                                       required
                                       class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">
                            </div>

                            <div class="space-y-1.5 md:col-span-1">
                                <label for="manual_desc" class="block text-xs font-bold uppercase tracking-wider text-zinc-300">
                                    Ghi Chú / Mô Tả (Tùy chọn)
                                </label>
                                <input type="text"
                                       id="manual_desc"
                                       name="description"
                                       x-model="manualDescription"
                                       placeholder="Mô tả nội dung bộ đề..."
                                       class="w-full mg-input border rounded-xl text-white text-sm py-2.5 px-3.5 focus:outline-none">
                            </div>
                        </div>

                        <!-- Thanh Thiết Lập Thời Gian Hàng Loạt Cao Cấp -->
                        <div class="pt-3.5 border-t border-zinc-800/80 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 text-zinc-300">
                                <div class="w-7 h-7 rounded-lg bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <span class="font-bold text-zinc-200">Thời gian mỗi câu:</span>
                                    <span class="text-zinc-500 text-[11px] block sm:inline sm:ml-1">(Áp dụng đồng loạt toàn bộ câu hỏi)</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <!-- Nhóm mốc thời gian phổ biến (Presets) -->
                                <div class="inline-flex items-center bg-zinc-950/80 border border-zinc-800/90 rounded-xl p-1 gap-1 shadow-inner">
                                    <template x-for="t in [10, 15, 20, 30, 45, 60]" :key="t">
                                        <button type="button"
                                                @click="batchTime = t; applyBatchTime()"
                                                :class="batchTime == t 
                                                    ? 'bg-gradient-to-r from-pink-600 to-rose-600 text-white font-black shadow-md shadow-pink-600/30' 
                                                    : 'text-zinc-400 hover:text-white hover:bg-zinc-800/70 font-semibold'"
                                                class="px-2.5 py-1 rounded-lg text-xs font-mono transition-all duration-150 cursor-pointer"
                                                x-text="t + 's'">
                                        </button>
                                    </template>
                                </div>

                                <!-- Bộ tăng giảm số tùy chỉnh (Custom Stepper) -->
                                <div class="inline-flex items-center bg-zinc-950/90 border border-zinc-700/80 rounded-xl p-1 shadow-inner">
                                    <!-- Nút Giảm 5s -->
                                    <button type="button"
                                            @click="batchTime = Math.max(3, (parseInt(batchTime) || 20) - 5); applyBatchTime()"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 active:scale-95 transition cursor-pointer"
                                            title="Giảm 5 giây">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                    </button>

                                    <!-- Ô Nhập Số -->
                                    <div class="flex items-center px-1.5 justify-center">
                                        <input type="number"
                                               x-model.number="batchTime"
                                               @change="applyBatchTime()"
                                               @keydown.enter.prevent="applyBatchTime()"
                                               min="3"
                                               max="300"
                                               class="w-8 bg-transparent text-pink-400 font-mono font-black text-xs text-center focus:outline-none p-0">
                                        <span class="text-zinc-500 font-mono text-[11px] select-none">s</span>
                                    </div>

                                    <!-- Nút Tăng 5s -->
                                    <button type="button"
                                            @click="batchTime = Math.min(300, (parseInt(batchTime) || 20) + 5); applyBatchTime()"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 active:scale-95 transition cursor-pointer"
                                            title="Tăng 5 giây">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    </button>

                                    <div class="h-4 w-[1px] bg-zinc-800 mx-1"></div>

                                    <!-- Nút Áp Dụng -->
                                    <button type="button"
                                            @click="applyBatchTime()"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold bg-pink-600 hover:bg-pink-500 active:scale-95 text-white shadow-sm shadow-pink-900/40 transition flex items-center gap-1 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Áp Dụng</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- BỘ ĐIỀU HƯỚNG & PHÂN TRANG CHO BỘ ĐỀ NHIỀU CÂU HỎI (100+ CÂU)  -->
                    <!-- ============================================================== -->
                    <div id="questions-section" class="mg-card rounded-2xl p-5 border border-zinc-800 space-y-4">

                        <!-- Hàng Thanh Điều Khiển: Tìm Kiếm, Chọn Số Câu / Trang, Thu Gọn -->
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-3 border-b border-zinc-800/90">
                            <!-- Tiêu đề & Đếm số câu -->
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-xs font-black uppercase tracking-wider text-white">Danh Sách Câu Hỏi</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-pink-500/15 text-pink-400 border border-pink-500/30"
                                      x-text="questions.length + ' câu'"></span>

                                <span class="text-xs text-zinc-400 hidden sm:inline"
                                      x-show="perPage !== 'all' && totalPages > 1"
                                      x-text="'(Trang ' + currentPage + '/' + totalPages + ')'"></span>
                            </div>

                            <!-- Công cụ lọc & Xem -->
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <!-- Ô Tìm Kiếm Nhanh -->
                                <div class="relative">
                                    <input type="text"
                                           x-model="searchQuery"
                                           @input="currentPage = 1"
                                           placeholder="Tìm trong câu hỏi..."
                                           class="w-40 sm:w-52 py-1.5 pl-8 pr-2.5 text-xs rounded-xl bg-zinc-900 border border-zinc-700/80 text-white placeholder-zinc-500 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition">
                                    <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>

                                <!-- Chọn Số Câu / Trang -->
                                <div class="flex items-center gap-1.5 bg-zinc-900 border border-zinc-800 px-2.5 py-1.5 rounded-xl text-zinc-400">
                                    <span>Xem:</span>
                                    <select x-model="perPage"
                                            @change="currentPage = 1"
                                            class="bg-transparent text-white font-bold text-xs p-0 border-0 focus:ring-0 cursor-pointer">
                                        <option value="10" class="bg-zinc-900 text-white">10 câu/trang</option>
                                        <option value="20" class="bg-zinc-900 text-white">20 câu/trang</option>
                                        <option value="50" class="bg-zinc-900 text-white">50 câu/trang</option>
                                        <option value="all" class="bg-zinc-900 text-white">Tất cả</option>
                                    </select>
                                </div>

                                <!-- Nút Thu Gọn / Mở Rộng Tất Cả -->
                                <button type="button"
                                        @click="toggleAllCollapsed()"
                                        class="px-3 py-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-zinc-300 hover:text-white transition font-semibold cursor-pointer">
                                    <span x-text="allCollapsed ? 'Mở Rộng Hết' : 'Thu Gọn Hết'"></span>
                                </button>

                                <!-- Nút Thêm Câu Hỏi Mới -->
                                <button type="button"
                                        @click="addQuestion()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl font-bold bg-gradient-to-r from-pink-600 to-rose-500 hover:from-pink-500 hover:to-rose-400 text-white shadow-md shadow-pink-600/30 transition transform hover:-translate-y-0.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Thêm Câu</span>
                                </button>
                            </div>
                        </div>

                        <!-- THANH ĐIỀU HƯỚNG NHẢY NHANH CÂU HỎI (QUESTION QUICK JUMP GRID) -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-zinc-900/90 border border-zinc-800/90 shadow-xl space-y-3 backdrop-blur-md">
                            <!-- Header của thanh nhảy nhanh: Tiêu đề, thống kê & Ô nhảy nhanh -->
                            <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-pink-500/15 border border-pink-500/30 flex items-center justify-center text-pink-400">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <span class="font-extrabold uppercase tracking-wider text-zinc-200 text-[11px]">Nhảy Nhanh Đến Câu:</span>
                                    
                                    <!-- Badge trạng thái hoàn thiện -->
                                    <div class="hidden sm:flex items-center gap-1.5 text-[11px] text-zinc-400 bg-zinc-800/70 border border-zinc-700/50 px-2.5 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>Hoàn thành:</span>
                                        <span class="font-bold text-zinc-200" x-text="questions.filter(q => isQuestionComplete(q)).length + '/' + questions.length"></span>
                                    </div>
                                </div>

                                <!-- Ô nhảy trực tiếp đến câu số -->
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center bg-zinc-950/80 border border-zinc-700/70 focus-within:border-pink-500 focus-within:ring-2 focus-within:ring-pink-500/20 rounded-xl px-2.5 py-1 transition shadow-inner">
                                        <span class="text-[11px] font-medium text-zinc-400 mr-1.5">Đi tới câu:</span>
                                        <input type="number"
                                               x-model="jumpInput"
                                               @keydown.enter.prevent="jumpTo()"
                                               placeholder="#"
                                               min="1"
                                               :max="questions.length"
                                               class="w-10 text-center text-xs font-mono font-bold bg-transparent border-0 p-0 text-white placeholder-zinc-600 focus:ring-0 focus:outline-none">
                                        <button type="button"
                                                @click="jumpTo()"
                                                class="p-1 rounded-lg bg-pink-600 hover:bg-pink-500 text-white shadow-sm transition ml-1 cursor-pointer"
                                                title="Nhảy đến câu này">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dải Số Thứ Tự Câu Hỏi (Dạng Grid/Flex Cards cực kỳ sắc nét & thẩm mỹ) -->
                            <div class="flex items-center gap-2 overflow-x-auto py-1.5 px-0.5 scrollbar-thin scrollbar-thumb-zinc-700 scrollbar-track-transparent">
                                <template x-for="(q, index) in questions" :key="index">
                                    <button type="button"
                                            @click="goToQuestion(index)"
                                            :class="{
                                                'bg-gradient-to-tr from-pink-600 to-rose-500 text-white font-black shadow-lg shadow-pink-600/40 ring-2 ring-pink-400 scale-105 z-10 border-transparent': activeQuestionIndex === index,
                                                'bg-amber-500/10 text-amber-300 border border-amber-500/40 hover:bg-amber-500/20 font-bold': activeQuestionIndex !== index && !isQuestionComplete(q),
                                                'bg-zinc-800/90 text-zinc-200 border border-zinc-700/80 hover:bg-zinc-700 hover:text-white hover:border-zinc-500 font-semibold shadow-sm': activeQuestionIndex !== index && isQuestionComplete(q) && isQuestionVisible(index),
                                                'bg-zinc-900/60 text-zinc-500 border border-zinc-800/80 hover:bg-zinc-800 hover:text-zinc-300': activeQuestionIndex !== index && !isQuestionVisible(index)
                                            }"
                                            class="relative w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center text-xs font-mono shrink-0 transition-all duration-200 cursor-pointer"
                                            :title="'Câu ' + (index + 1) + (q.question ? ': ' + q.question.substring(0, 35) + '...' : ' (Chưa nhập nội dung)')">
                                        <span x-text="index + 1"></span>
                                        <!-- Chấm tròn cảnh báo nếu câu chưa hoàn tất -->
                                        <span x-show="!isQuestionComplete(q) && activeQuestionIndex !== index"
                                              class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-400 ring-2 ring-zinc-900 animate-pulse"></span>
                                        <!-- Chấm nhỏ biểu thị câu nằm trên trang hiện tại -->
                                        <span x-show="isQuestionVisible(index) && activeQuestionIndex !== index && isQuestionComplete(q)"
                                              class="absolute bottom-1 w-1 h-1 rounded-full bg-pink-400/80"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- THÔNG BÁO TÌM KIẾM (NẾU CÓ) -->
                        <div x-show="searchQuery.trim() !== ''" class="text-xs text-zinc-400 flex items-center justify-between">
                            <span>Đang lọc theo: "<strong class="text-pink-400" x-text="searchQuery"></strong>" (Tìm thấy <span class="text-white font-bold" x-text="filteredList.length"></span> câu)</span>
                            <button type="button" @click="searchQuery = ''" class="text-pink-400 hover:underline">Xóa bộ lọc</button>
                        </div>

                        <!-- ============================================== -->
                        <!-- DANH SÁCH CÁC THẺ CÂU HỎI (HỖ TRỢ THU GỌN)    -->
                        <!-- ============================================== -->
                        <div class="space-y-3 pt-1">
                            <template x-for="(q, index) in questions" :key="index">
                                <div x-show="isQuestionVisible(index)"
                                     :id="'question-card-' + index"
                                     class="mg-subcard rounded-2xl border border-zinc-800 transition-all duration-200">

                                    <!-- HEADER CÂU HỎI (BẤM VÀO ĐỂ THU GỌN / MỞ RỘNG) -->
                                    <div class="p-3.5 sm:p-4 flex items-center justify-between gap-3 cursor-pointer select-none"
                                         @click="q._collapsed = !q._collapsed; activeQuestionIndex = index"
                                         :class="{ 'border-b border-zinc-800/80': !q._collapsed }">

                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="w-7 h-7 rounded-lg bg-pink-600/20 text-pink-400 border border-pink-500/30 flex items-center justify-center text-xs font-bold font-mono shrink-0"
                                                  x-text="index + 1"></span>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold uppercase tracking-wider text-white" x-text="'Câu Hỏi #' + (index + 1)"></span>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-900 border border-zinc-800 text-pink-400" x-text="q.time_limit + 's'"></span>
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                                          x-text="'Đáp án: ' + q.correct_answer"></span>
                                                </div>
                                                <!-- Hiển thị nội dung tóm tắt khi thu gọn -->
                                                <p class="text-xs text-zinc-400 truncate mt-0.5 max-w-xl"
                                                   x-text="q.question ? q.question : '(Chưa nhập nội dung câu hỏi)'"></p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0" @click.stop>
                                            <!-- Chỉnh thời gian câu này (Mini Stepper) -->
                                            <div class="flex items-center bg-zinc-950/80 border border-zinc-800/90 rounded-lg p-0.5 text-xs shadow-inner" title="Thời gian câu hỏi này">
                                                <button type="button"
                                                        @click="q.time_limit = Math.max(3, (parseInt(q.time_limit) || 20) - 5)"
                                                        class="w-5 h-5 rounded flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 active:scale-90 transition cursor-pointer"
                                                        title="Giảm 5 giây">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                                                </button>
                                                <div class="flex items-center px-1">
                                                    <input type="number"
                                                           :name="'questions[' + index + '][time_limit]'"
                                                           x-model.number="q.time_limit"
                                                           min="3"
                                                           max="300"
                                                           class="w-7 bg-transparent text-pink-400 font-mono font-bold text-xs text-center focus:outline-none p-0">
                                                    <span class="text-zinc-500 font-mono text-[10px] select-none">s</span>
                                                </div>
                                                <button type="button"
                                                        @click="q.time_limit = Math.min(300, (parseInt(q.time_limit) || 20) + 5)"
                                                        class="w-5 h-5 rounded flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 active:scale-90 transition cursor-pointer"
                                                        title="Tăng 5 giây">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                </button>
                                            </div>

                                            <!-- Nút Xóa câu -->
                                            <button type="button"
                                                    @click="removeQuestion(index)"
                                                    x-show="questions.length > 1"
                                                    class="p-1.5 rounded-lg text-zinc-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
                                                    title="Xóa câu hỏi này">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>

                                            <!-- Nút Thu gọn / Mở rộng -->
                                            <button type="button"
                                                    @click="q._collapsed = !q._collapsed"
                                                    class="p-1.5 rounded-lg text-zinc-400 hover:text-white transition">
                                                <svg class="w-4 h-4 transition-transform duration-200"
                                                     :class="{ 'rotate-180': !q._collapsed }"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- NỘI DUNG CHI TIẾT (KHI MỞ RỘNG) -->
                                    <div x-show="!q._collapsed" class="p-4 sm:p-5 space-y-4">
                                        <!-- Ô Nhập Nội Dung Câu Hỏi -->
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-bold text-zinc-300">
                                                Nội Dung Câu Hỏi <span class="text-pink-500">*</span>
                                            </label>
                                            <input type="text"
                                                   :name="'questions[' + index + '][question]'"
                                                   x-model="q.question"
                                                   placeholder="Nhập nội dung câu hỏi..."
                                                   required
                                                   class="w-full mg-input border rounded-xl text-white text-sm py-2 px-3.5 focus:outline-none">
                                        </div>

                                        <!-- 4 Ô Đáp Án A, B, C, D -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <!-- Phương án A -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <label class="text-[11px] font-bold text-rose-400 flex items-center gap-1.5">
                                                        <span class="w-4 h-4 rounded bg-rose-500/20 flex items-center justify-center text-[10px] font-bold">A</span>
                                                        <span>Phương án A <span class="text-pink-500">*</span></span>
                                                    </label>
                                                    <label class="text-[10px] text-zinc-400 flex items-center gap-1 cursor-pointer">
                                                        <input type="radio"
                                                               :name="'questions[' + index + '][correct_radio]'"
                                                               value="A"
                                                               :checked="q.correct_answer === 'A'"
                                                               @change="q.correct_answer = 'A'"
                                                               class="text-pink-600 focus:ring-0 w-3 h-3">
                                                        <span :class="q.correct_answer === 'A' ? 'text-pink-400 font-bold' : ''">Đáp án đúng</span>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       :name="'questions[' + index + '][options][A]'"
                                                       x-model="q.options.A"
                                                       required
                                                       placeholder="Nội dung đáp án A"
                                                       class="w-full mg-input border rounded-xl text-white text-xs py-2 px-3 focus:outline-none">
                                            </div>

                                            <!-- Phương án B -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <label class="text-[11px] font-bold text-sky-400 flex items-center gap-1.5">
                                                        <span class="w-4 h-4 rounded bg-sky-500/20 flex items-center justify-center text-[10px] font-bold">B</span>
                                                        <span>Phương án B <span class="text-pink-500">*</span></span>
                                                    </label>
                                                    <label class="text-[10px] text-zinc-400 flex items-center gap-1 cursor-pointer">
                                                        <input type="radio"
                                                               :name="'questions[' + index + '][correct_radio]'"
                                                               value="B"
                                                               :checked="q.correct_answer === 'B'"
                                                               @change="q.correct_answer = 'B'"
                                                               class="text-pink-600 focus:ring-0 w-3 h-3">
                                                        <span :class="q.correct_answer === 'B' ? 'text-pink-400 font-bold' : ''">Đáp án đúng</span>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       :name="'questions[' + index + '][options][B]'"
                                                       x-model="q.options.B"
                                                       required
                                                       placeholder="Nội dung đáp án B"
                                                       class="w-full mg-input border rounded-xl text-white text-xs py-2 px-3 focus:outline-none">
                                            </div>

                                            <!-- Phương án C -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <label class="text-[11px] font-bold text-amber-400 flex items-center gap-1.5">
                                                        <span class="w-4 h-4 rounded bg-amber-500/20 flex items-center justify-center text-[10px] font-bold">C</span>
                                                        <span>Phương án C (Tùy chọn)</span>
                                                    </label>
                                                    <label class="text-[10px] text-zinc-400 flex items-center gap-1 cursor-pointer">
                                                        <input type="radio"
                                                               :name="'questions[' + index + '][correct_radio]'"
                                                               value="C"
                                                               :checked="q.correct_answer === 'C'"
                                                               @change="q.correct_answer = 'C'"
                                                               class="text-pink-600 focus:ring-0 w-3 h-3">
                                                        <span :class="q.correct_answer === 'C' ? 'text-pink-400 font-bold' : ''">Đáp án đúng</span>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       :name="'questions[' + index + '][options][C]'"
                                                       x-model="q.options.C"
                                                       placeholder="Nội dung đáp án C (tùy chọn)"
                                                       class="w-full mg-input border rounded-xl text-white text-xs py-2 px-3 focus:outline-none">
                                            </div>

                                            <!-- Phương án D -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <label class="text-[11px] font-bold text-emerald-400 flex items-center gap-1.5">
                                                        <span class="w-4 h-4 rounded bg-emerald-500/20 flex items-center justify-center text-[10px] font-bold">D</span>
                                                        <span>Phương án D (Tùy chọn)</span>
                                                    </label>
                                                    <label class="text-[10px] text-zinc-400 flex items-center gap-1 cursor-pointer">
                                                        <input type="radio"
                                                               :name="'questions[' + index + '][correct_radio]'"
                                                               value="D"
                                                               :checked="q.correct_answer === 'D'"
                                                               @change="q.correct_answer = 'D'"
                                                               class="text-pink-600 focus:ring-0 w-3 h-3">
                                                        <span :class="q.correct_answer === 'D' ? 'text-pink-400 font-bold' : ''">Đáp án đúng</span>
                                                    </label>
                                                </div>
                                                <input type="text"
                                                       :name="'questions[' + index + '][options][D]'"
                                                       x-model="q.options.D"
                                                       placeholder="Nội dung đáp án D (tùy chọn)"
                                                       class="w-full mg-input border rounded-xl text-white text-xs py-2 px-3 focus:outline-none">
                                            </div>
                                        </div>

                                        <input type="hidden" :name="'questions[' + index + '][correct_answer]'" :value="q.correct_answer">
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- THANH PHÂN TRANG (PAGINATION BAR) -->
                        <div x-show="perPage !== 'all' && totalPages > 1"
                             class="pt-3 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                            <span class="text-zinc-400">
                                Hiển thị trang <strong class="text-white" x-text="currentPage"></strong> / <span x-text="totalPages"></span>
                                (Tổng <span x-text="questions.length"></span> câu)
                            </span>

                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        @click="goToPage(currentPage - 1)"
                                        :disabled="currentPage <= 1"
                                        :class="currentPage <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-zinc-800 text-white'"
                                        class="px-2.5 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 transition">
                                    &larr; Trang Trước
                                </button>

                                <!-- Các nút số trang -->
                                <template x-for="p in totalPages" :key="p">
                                    <button type="button"
                                            x-show="Math.abs(p - currentPage) <= 2 || p === 1 || p === totalPages"
                                            @click="goToPage(p)"
                                            :class="p === currentPage ? 'bg-pink-600 text-white font-bold' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800'"
                                            class="w-7 h-7 rounded-lg text-xs font-mono transition"
                                            x-text="p">
                                    </button>
                                </template>

                                <button type="button"
                                        @click="goToPage(currentPage + 1)"
                                        :disabled="currentPage >= totalPages"
                                        :class="currentPage >= totalPages ? 'opacity-40 cursor-not-allowed' : 'hover:bg-zinc-800 text-white'"
                                        class="px-2.5 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 transition">
                                    Trang Sau &rarr;
                                </button>
                            </div>
                        </div>

                        <!-- Nút Thêm Câu Hỏi Mới Ở Dưới Cùng -->
                        <button type="button"
                                @click="addQuestion()"
                                class="w-full py-3 rounded-xl border border-dashed border-zinc-800 hover:border-pink-500/60 text-zinc-400 hover:text-white text-xs font-bold uppercase tracking-wider transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Thêm Câu Hỏi Tiếp Theo</span>
                        </button>
                    </div>

                    <!-- THANH NÚT LƯU & HỦY (STICKY CUỐI TRANG) -->
                    <div class="sticky bottom-4 z-20 mg-card rounded-2xl p-4 border border-zinc-700/80 shadow-2xl flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-xs text-zinc-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="hidden sm:inline">Tổng số câu hỏi sẽ lưu:</span>
                            <strong class="text-white font-mono" x-text="questions.length + ' câu'"></strong>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('minigame.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-400 hover:text-white bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 transition">
                                Hủy Bỏ
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold uppercase tracking-wider text-xs bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Lưu Toàn Bộ Bộ Đề</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        <!-- MODAL DÁN NỘI DUNG TỪ BLOOKET -->
        <div x-show="showBlooketModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBlooketModal"
                     @click="showBlooketModal = false"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showBlooketModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom mg-card rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-zinc-800 p-6 space-y-4">

                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-pink-500/10 text-pink-400 border border-pink-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Dán Nội Dung Từ Blooket / JSON</h3>
                        </div>
                        <button type="button"
                                @click="showBlooketModal = false"
                                class="w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-700/80 text-zinc-400 hover:text-white inline-flex items-center justify-center p-0 leading-none transition shrink-0"
                                title="Đóng">
                            <svg class="w-4 h-4 block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs text-zinc-400">
                            Bạn có thể dán trực tiếp chuỗi JSON từ Blooket hoặc dạng danh sách văn bản (Mỗi câu gồm dòng tiêu đề và các đáp án A, B, C, D).
                        </p>
                        <textarea x-model="blooketRawText"
                                  rows="8"
                                  placeholder='[&#10;  {&#10;    "question": "Thủ đô của Việt Nam là gì?",&#10;    "options": { "A": "Hà Nội", "B": "TP.HCM", "C": "Đà Nẵng", "D": "Huế" },&#10;    "correct_answer": "A",&#10;    "time_limit": 20&#10;  }&#10;]'
                                  class="w-full mg-input border rounded-xl text-white font-mono text-xs p-3.5 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-zinc-800 flex items-center justify-end gap-3">
                        <button type="button"
                                @click="showBlooketModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-400 hover:text-white bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 transition">
                            Hủy Bỏ
                        </button>
                        <button type="button"
                                @click="parseBlooketRaw()"
                                class="px-5 py-2 rounded-xl font-bold uppercase tracking-wider text-xs bg-pink-600 hover:bg-pink-500 text-white shadow-lg shadow-pink-600/30 transition">
                            Phân Tích & Thêm Vào Đề
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function minigameQuizCreateApp() {
        return {
            tab: '{{ old('creation_mode', 'blooket_presets') }}',
            batchTime: 20,
            showBlooketModal: false,
            blooketRawText: '',
            copyFeedback: '',
            manualTitle: '{{ old('title', '') }}',
            manualDescription: '{{ old('description', '') }}',
            currentPage: 1,
            perPage: '10',
            searchQuery: '',
            jumpInput: '',
            allCollapsed: false,
            activeQuestionIndex: 0,

            isQuestionComplete(q) {
                if (!q) return false;
                const hasText = (q.question || '').trim().length > 0;
                const hasA = (q.options?.A || '').trim().length > 0;
                const hasB = (q.options?.B || '').trim().length > 0;
                return Boolean(hasText && hasA && hasB);
            },
            questions: [
                {
                    question: '',
                    options: { A: '', B: '', C: '', D: '' },
                    correct_answer: 'A',
                    time_limit: 20,
                    _collapsed: false
                }
            ],
            blooketPacks: [
                {
                    id: 'web_dev',
                    name: 'Lập Trình Web Fullstack & Laravel',
                    desc: '10 câu hỏi trắc nghiệm thực chiến về MVC, Eloquent, CSRF, Route & HTTP',
                    count: 10,
                    questions: [
                        { question: 'Trong mô hình MVC của Laravel, chữ C viết tắt của từ gì?', options: { A: 'Controller', B: 'Component', C: 'Connector', D: 'Compiler' }, correct_answer: 'A', time_limit: 20 },
                        { question: 'ORM mặc định được tích hợp sẵn trong Laravel có tên là gì?', options: { A: 'Doctrine', B: 'Eloquent', C: 'Hibernate', D: 'Prisma' }, correct_answer: 'B', time_limit: 20 },
                        { question: 'Directive nào trong Blade template dùng để chống tấn công giả mạo yêu cầu (CSRF)?', options: { A: '{{ "@" }}auth', B: '{{ "@" }}csrf', C: '{{ "@" }}secure', D: '{{ "@" }}token' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Tập tin định nghĩa các route web mặc định trong Laravel là gì?', options: { A: 'app/routes.php', B: 'routes/web.php', C: 'config/routes.php', D: 'public/index.php' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Lệnh Artisan nào dùng để chạy các migration cập nhật cấu trúc database?', options: { A: 'php artisan db:run', B: 'php artisan migrate', C: 'php artisan schema:update', D: 'php artisan make:migration' }, correct_answer: 'B', time_limit: 20 },
                        { question: 'Mã phản hồi HTTP Status Code nào thể hiện yêu cầu thành công (OK)?', options: { A: '200', B: '404', C: '500', D: '301' }, correct_answer: 'A', time_limit: 15 },
                        { question: 'Phương thức HTTP nào được dùng để cập nhật một phần dữ liệu tài nguyên trong REST API?', options: { A: 'GET', B: 'POST', C: 'PATCH', D: 'DELETE' }, correct_answer: 'C', time_limit: 20 },
                        { question: 'Công cụ quản lý gói phụ thuộc thư viện chính thức của ngôn ngữ PHP là gì?', options: { A: 'npm', B: 'Composer', C: 'Pip', D: 'Maven' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Trong Laravel Blade, cú pháp hiển thị dữ liệu với 2 cặp ngoặc nhọn tự động áp dụng hàm bảo vệ nào?', options: { A: 'htmlspecialchars()', B: 'md5()', C: 'strip_tags()', D: 'addslashes()' }, correct_answer: 'A', time_limit: 20 },
                        { question: 'Giao thức nào cung cấp kết nối mạng được mã hóa bảo mật giữa trình duyệt và máy chủ?', options: { A: 'HTTP', B: 'FTP', C: 'HTTPS', D: 'SMTP' }, correct_answer: 'C', time_limit: 15 }
                    ]
                },
                {
                    id: 'computer_science',
                    name: 'Khoa Học Máy Tính & Công Nghệ',
                    desc: '10 câu hỏi nền tảng về CPU, RAM, Binary, DNS, Mạng & Hệ điều hành',
                    count: 10,
                    questions: [
                        { question: 'Bộ phận nào được coi là bộ não xử lý tính toán trung tâm của máy tính?', options: { A: 'RAM', B: 'CPU', C: 'Ổ cứng SSD', D: 'Card đồ họa' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Hệ đếm nhị phân (Binary) chỉ sử dụng hai chữ số nào sau đây?', options: { A: '0 và 1', B: '1 và 2', C: '0 và 9', D: 'A và B' }, correct_answer: 'A', time_limit: 15 },
                        { question: '1 Gigabyte (GB) tiêu chuẩn tương đương với bao nhiêu Megabyte (MB)?', options: { A: '512 MB', B: '1000 MB', C: '1024 MB', D: '2048 MB' }, correct_answer: 'C', time_limit: 15 },
                        { question: 'Giao thức DNS trong mạng Internet đóng vai trò chính là gì?', options: { A: 'Truyền tải email', B: 'Phân giải tên miền thành địa chỉ IP', C: 'Tường lửa bảo vệ', D: 'Mã hóa mật khẩu' }, correct_answer: 'B', time_limit: 20 },
                        { question: 'Hệ điều hành mã nguồn mở nhân Linux phổ biến nhất trên thiết bị di động là gì?', options: { A: 'iOS', B: 'Android', C: 'Windows Phone', D: 'Symbian' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Cấu trúc dữ liệu nào hoạt động theo nguyên lý LIFO (Last In First Out)?', options: { A: 'Queue (Hàng đợi)', B: 'Stack (Ngăn xếp)', C: 'Array (Mảng)', D: 'Linked List' }, correct_answer: 'B', time_limit: 20 },
                        { question: 'Độ phức tạp thời gian trung bình của thuật toán tìm kiếm nhị phân (Binary Search) là?', options: { A: 'O(1)', B: 'O(n)', C: 'O(log n)', D: 'O(n^2)' }, correct_answer: 'C', time_limit: 20 },
                        { question: 'Cổng mặc định (Port) của giao thức duyệt web bảo mật HTTPS là cổng nào?', options: { A: '80', B: '21', C: '443', D: '3306' }, correct_answer: 'C', time_limit: 15 },
                        { question: 'Hệ quản trị cơ sở dữ liệu nào sau đây là cơ sở dữ liệu quan hệ (RDBMS)?', options: { A: 'MongoDB', B: 'MySQL', C: 'Redis', D: 'Neo4j' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Ai được công nhận là lập trình viên máy tính đầu tiên trong lịch sử nhân loại?', options: { A: 'Ada Lovelace', B: 'Alan Turing', C: 'Grace Hopper', D: 'Charles Babbage' }, correct_answer: 'A', time_limit: 20 }
                    ]
                },
                {
                    id: 'it_english',
                    name: 'Tiếng Anh Chuyên Ngành CNTT',
                    desc: '10 thuật ngữ kỹ thuật tiếng Anh thiết yếu: Bug, Deploy, Refactoring, Latency...',
                    count: 10,
                    questions: [
                        { question: 'Thuật ngữ "Bug" trong phát triển phần mềm mang ý nghĩa gì?', options: { A: 'Tính năng mới', B: 'Lỗi phần mềm', C: 'Tài liệu hướng dẫn', D: 'Máy chủ vật lý' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Từ "Deploy" trong quy trình phát hành ứng dụng mang ý nghĩa nào?', options: { A: 'Viết code mới', B: 'Triển khai phần mềm lên máy chủ', C: 'Xóa cơ sở dữ liệu', D: 'Kiểm thử đơn vị' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Khái niệm "Refactoring" trong lập trình có nghĩa là gì?', options: { A: 'Tái cấu trúc code nhằm tối ưu mà không đổi hành vi', B: 'Xóa dự án làm lại', C: 'Viết mã độc hại', D: 'Dịch sang ngôn ngữ khác' }, correct_answer: 'A', time_limit: 20 },
                        { question: 'Từ "Latency" trong mạng máy tính đo lường yếu tố gì?', options: { A: 'Tốc độ quạt tản nhiệt', B: 'Độ trễ truyền tải dữ liệu', C: 'Dung lượng RAM', D: 'Kích thước màn hình' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Trong Git, hành động "Commit" thực hiện nhiệm vụ gì?', options: { A: 'Lưu snapshot các thay đổi vào kho lưu trữ', B: 'Xóa code trên nhánh', C: 'Tải code về máy', D: 'Tạo tài khoản' }, correct_answer: 'A', time_limit: 15 },
                        { question: 'Từ "Vulnerability" trong an toàn thông tin có nghĩa là gì?', options: { A: 'Lỗ hổng bảo mật', B: 'Tường lửa mạng', C: 'Khóa mã hóa', D: 'Mật khẩu mạnh' }, correct_answer: 'A', time_limit: 15 },
                        { question: 'Khái niệm "Cache" trong kiến trúc hệ thống web nhằm mục đích gì?', options: { A: 'Lưu tạm dữ liệu để truy xuất siêu tốc', B: 'Quét mã độc', C: 'Nén hình ảnh', D: 'Gửi thông báo' }, correct_answer: 'A', time_limit: 15 },
                        { question: 'Khái niệm "Scalability" biểu thị đặc tính gì của hệ thống phần mềm?', options: { A: 'Khả năng bảo trì', B: 'Khả năng mở rộng chịu tải', C: 'Khả năng sao lưu', D: 'Tính giao diện' }, correct_answer: 'B', time_limit: 15 },
                        { question: '"Payload" trong gói tin HTTP biểu thị phần nào của thông điệp?', options: { A: 'Header giao thức', B: 'Dữ liệu nội dung chính', C: 'Địa chỉ IP', D: 'Chứng chỉ SSL' }, correct_answer: 'B', time_limit: 20 },
                        { question: 'Cụm từ "Open Source" dịch chuẩn sang tiếng Việt là gì?', options: { A: 'Mã nguồn mở', B: 'Phần mềm độc quyền', C: 'Mã nguồn đóng', D: 'Dịch vụ đám mây' }, correct_answer: 'A', time_limit: 15 }
                    ]
                },
                {
                    id: 'trivia_general',
                    name: 'Đố Vui Trí Tuệ & Khám Phá Thế Giới',
                    desc: '10 câu hỏi đố vui thông minh, kích thích tinh thần cạnh tranh và hứng khởi',
                    count: 10,
                    questions: [
                        { question: 'Hành tinh nào nằm ở vị trí gần Mặt Trời nhất trong Hệ Mặt Trời?', options: { A: 'Sao Kim', B: 'Sao Thủy', C: 'Sao Hỏa', D: 'Trái Đất' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Kim tự tháp Giza vĩ đại nổi tiếng nằm ở quốc gia nào?', options: { A: 'Hy Lạp', B: 'Ai Cập', C: 'Ý', D: 'Mexico' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Khí nào chiếm tỷ lệ lớn nhất trong thành phần bầu khí quyển Trái Đất?', options: { A: 'Oxy (O2)', B: 'Nitơ (N2)', C: 'Cacbonic (CO2)', D: 'Hydro (H2)' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Đỉnh Everest hùng vĩ thuộc dãy núi nào sau đây?', options: { A: 'Dãy Andes', B: 'Dãy Alps', C: 'Dãy Himalaya', D: 'Dãy Rocky' }, correct_answer: 'C', time_limit: 15 },
                        { question: 'Bức danh họa nàng Mona Lisa được vẽ bởi danh họa thiên tài nào?', options: { A: 'Vincent van Gogh', B: 'Leonardo da Vinci', C: 'Pablo Picasso', D: 'Michelangelo' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Đại dương nào có diện tích và độ sâu lớn nhất trên hành tinh Trái Đất?', options: { A: 'Đại Tây Dương', B: 'Thái Bình Dương', C: 'Ấn Độ Dương', D: 'Bắc Băng Dương' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Loài động vật có vú nào to lớn nhất trong lịch sử hành tinh Trái Đất?', options: { A: 'Voi châu Phi', B: 'Cá voi xanh', C: 'Khủng long', D: 'Hà mã' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Ai là nhà phát minh ra bóng đèn dây tóc thương mại ứng dụng thực tế?', options: { A: 'Nikola Tesla', B: 'Thomas Edison', C: 'Alexander Bell', D: 'Isaac Newton' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Quốc gia nào có diện tích lãnh thổ tự nhiên rộng lớn nhất thế giới?', options: { A: 'Canada', B: 'Nga', C: 'Trung Quốc', D: 'Hoa Kỳ' }, correct_answer: 'B', time_limit: 15 },
                        { question: 'Trong thể thức Blooket Gold Quest, mỗi lần trả lời đúng bạn được chọn bao nhiêu rương?', options: { A: '1 trong 2 rương', B: '1 trong 3 rương', C: '1 trong 4 rương', D: '1 trong 5 rương' }, correct_answer: 'B', time_limit: 15 }
                    ]
                }
            ],
            get filteredList() {
                const query = (this.searchQuery || '').toLowerCase().trim();
                return this.questions
                    .map((q, idx) => ({ ...q, originalIndex: idx }))
                    .filter(q => {
                        if (!query) return true;
                        const qText = (q.question || '').toLowerCase();
                        const optA = (q.options?.A || '').toLowerCase();
                        const optB = (q.options?.B || '').toLowerCase();
                        const optC = (q.options?.C || '').toLowerCase();
                        const optD = (q.options?.D || '').toLowerCase();
                        return qText.includes(query) || optA.includes(query) || optB.includes(query) || optC.includes(query) || optD.includes(query);
                    });
            },
            get totalPages() {
                if (this.perPage === 'all') return 1;
                const size = parseInt(this.perPage) || 10;
                return Math.max(1, Math.ceil(this.filteredList.length / size));
            },
            isQuestionVisible(index) {
                const query = (this.searchQuery || '').toLowerCase().trim();
                if (query) {
                    const q = this.questions[index];
                    if (!q) return false;
                    const match = (q.question || '').toLowerCase().includes(query)
                        || (q.options?.A || '').toLowerCase().includes(query)
                        || (q.options?.B || '').toLowerCase().includes(query)
                        || (q.options?.C || '').toLowerCase().includes(query)
                        || (q.options?.D || '').toLowerCase().includes(query);
                    if (!match) return false;
                }

                if (this.perPage === 'all') return true;
                const size = parseInt(this.perPage) || 10;
                const itemIndexInFiltered = this.filteredList.findIndex(item => item.originalIndex === index);
                if (itemIndexInFiltered === -1) return false;
                const start = (this.currentPage - 1) * size;
                const end = start + size;
                return itemIndexInFiltered >= start && itemIndexInFiltered < end;
            },
            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
            },
            goToQuestion(index) {
                this.activeQuestionIndex = index;
                if (this.perPage !== 'all') {
                    const size = parseInt(this.perPage) || 10;
                    const pos = this.filteredList.findIndex(item => item.originalIndex === index);
                    if (pos !== -1) {
                        this.currentPage = Math.floor(pos / size) + 1;
                    }
                }
                if (this.questions[index]) {
                    this.questions[index]._collapsed = false;
                }
                this.$nextTick(() => {
                    const el = document.getElementById('question-card-' + index);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            },
            jumpTo() {
                const num = parseInt(this.jumpInput);
                if (num >= 1 && num <= this.questions.length) {
                    this.goToQuestion(num - 1);
                    this.jumpInput = '';
                }
            },
            toggleAllCollapsed() {
                this.allCollapsed = !this.allCollapsed;
                this.questions.forEach(q => {
                    q._collapsed = this.allCollapsed;
                });
            },
            addQuestion() {
                this.questions.push({
                    question: '',
                    options: { A: '', B: '', C: '', D: '' },
                    correct_answer: 'A',
                    time_limit: parseInt(this.batchTime) || 20,
                    _collapsed: false
                });

                if (this.perPage !== 'all') {
                    this.currentPage = this.totalPages;
                }

                this.$nextTick(() => {
                    this.goToQuestion(this.questions.length - 1);
                });
            },
            removeQuestion(index) {
                if (this.questions.length > 1) {
                    this.questions.splice(index, 1);
                    if (this.currentPage > this.totalPages) {
                        this.currentPage = this.totalPages;
                    }
                }
            },
            applyBatchTime() {
                const time = Math.max(3, Math.min(300, parseInt(this.batchTime) || 20));
                this.batchTime = time;
                this.questions.forEach(q => {
                    q.time_limit = time;
                });
                this.copyFeedback = 'Đã áp dụng ' + time + 's cho tất cả ' + this.questions.length + ' câu hỏi!';
                setTimeout(() => this.copyFeedback = '', 3000);
            },
            loadPackToManual(pack) {
                this.questions = JSON.parse(JSON.stringify(pack.questions)).map(q => ({
                    ...q,
                    _collapsed: false
                }));
                this.manualTitle = pack.name;
                this.manualDescription = pack.desc;
                this.tab = 'manual';
                this.currentPage = 1;
                this.copyFeedback = 'Đã nạp gói "' + pack.name + '" vào trình soạn thảo!';
                setTimeout(() => this.copyFeedback = '', 3500);
            },
            copyBlooketJson() {
                const exportData = this.questions.map(q => ({
                    question: q.question,
                    options: q.options,
                    correct_answer: q.correct_answer,
                    time_limit: q.time_limit
                }));
                const jsonText = JSON.stringify(exportData, null, 2);
                navigator.clipboard.writeText(jsonText).then(() => {
                    this.copyFeedback = 'Đã sao chép Blooket JSON vào Clipboard!';
                    setTimeout(() => this.copyFeedback = '', 3500);
                }).catch(() => {
                    alert('Không thể sao chép tự động.');
                });
            },
            parseBlooketRaw() {
                if (!this.blooketRawText.trim()) return;
                try {
                    let data = JSON.parse(this.blooketRawText.trim());
                    if (data.questions && Array.isArray(data.questions)) data = data.questions;
                    if (!Array.isArray(data)) data = [data];

                    let added = 0;
                    data.forEach(item => {
                        const qText = item.question || item.title || '';
                        if (qText) {
                            let opts = item.options || item.answers || {};
                            let mappedOpts = { A: '', B: '', C: '', D: '' };
                            if (Array.isArray(opts)) {
                                mappedOpts.A = opts[0] || '';
                                mappedOpts.B = opts[1] || '';
                                mappedOpts.C = opts[2] || '';
                                mappedOpts.D = opts[3] || '';
                            } else {
                                mappedOpts.A = opts.A || opts['1'] || '';
                                mappedOpts.B = opts.B || opts['2'] || '';
                                mappedOpts.C = opts.C || opts['3'] || '';
                                mappedOpts.D = opts.D || opts['4'] || '';
                            }
                            this.questions.push({
                                question: qText,
                                options: mappedOpts,
                                correct_answer: (item.correct_answer || item.answer || 'A').toUpperCase(),
                                time_limit: parseInt(item.time_limit) || 20,
                                _collapsed: false
                            });
                            added++;
                        }
                    });

                    if (added > 0) {
                        this.blooketRawText = '';
                        this.showBlooketModal = false;
                        this.copyFeedback = 'Đã thêm thành công ' + added + ' câu hỏi từ Blooket!';
                        setTimeout(() => this.copyFeedback = '', 3500);
                    } else {
                        alert('Không tìm thấy câu hỏi hợp lệ trong dữ liệu dán vào.');
                    }
                } catch (e) {
                    const lines = this.blooketRawText.split('\n').map(l => l.trim()).filter(Boolean);
                    if (lines.length >= 3) {
                        this.questions.push({
                            question: lines[0],
                            options: {
                                A: lines[1] ? lines[1].replace(/^[A-Da-d][\.\:\)]\s*/, '') : 'Đáp án A',
                                B: lines[2] ? lines[2].replace(/^[A-Da-d][\.\:\)]\s*/, '') : 'Đáp án B',
                                C: lines[3] ? lines[3].replace(/^[A-Da-d][\.\:\)]\s*/, '') : '',
                                D: lines[4] ? lines[4].replace(/^[A-Da-d][\.\:\)]\s*/, '') : '',
                            },
                            correct_answer: 'A',
                            time_limit: 20,
                            _collapsed: false
                        });
                        this.blooketRawText = '';
                        this.showBlooketModal = false;
                        this.copyFeedback = 'Đã nhận diện và thêm 1 câu hỏi từ văn bản!';
                        setTimeout(() => this.copyFeedback = '', 3500);
                    } else {
                        alert('Dữ liệu không đúng định dạng. Vui lòng kiểm tra lại!');
                    }
                }
            }
        };
    }
    </script>
    @endpush
</x-app-layout>
