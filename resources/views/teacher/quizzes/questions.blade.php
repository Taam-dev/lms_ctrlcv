<x-teacher-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $quiz->type_badge_class }}">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-pink-50 text-pink-700">
                        {{ $quiz->course ? 'Khóa học: ' . $quiz->course->title : 'Bài kiểm tra Tự do' }}
                    </span>
                </div>
                <h2 class="font-black text-2xl text-slate-900 tracking-tight">
                    Soạn đề thi: {{ $quiz->title }}
                </h2>
            </div>
            <div class="flex items-center gap-3 flex-nowrap shrink-0">
                <a href="{{ route('instructor.dashboard', ['tab' => 'quizzes']) }}" class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition whitespace-nowrap shrink-0">
                    &larr; Quay lại danh sách
                </a>
                <a href="{{ route('teacher.quizzes.edit', $quiz->id) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold transition whitespace-nowrap shrink-0">
                    Cấu hình bài thi
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Hidden form for direct AI questions import -->
    <form id="ai-import-form" action="{{ route('teacher.quizzes.questions.import', $quiz->id) }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="json_content" id="ai-import-json-input">
    </form>

    <div class="w-full" x-data="teacherQuestionsHandler('{{ request('tab', 'manual') }}')" x-init="init()">
        <div class="w-full space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shrink-0">&check;</span>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm space-y-1">
                    <div class="font-bold">Đã có lỗi xảy ra:</div>
                    <ul class="list-disc list-inside text-xs pl-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Cột trái (lg:col-span-5): Form thêm câu hỏi mới / Tạo bằng AI / Nhập từ JSON -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-5 sm:p-6 sticky top-20 max-h-[calc(100vh-5.5rem)] flex flex-col space-y-4">
                        
                        <!-- Thanh chuyển Tab -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-bold">
                                <button type="button" @click="tab = 'manual'"
                                        :class="tab === 'manual' ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    Thêm 1 câu
                                </button>
                                <button type="button" @click="tab = 'ai'"
                                        :class="tab === 'ai' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 text-current shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Tạo bằng AI</span>
                                </button>
                                <button type="button" @click="tab = 'json'"
                                        :class="tab === 'json' ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                    JSON
                                </button>
                            </div>
                            <span class="text-xs text-pink-600 font-semibold bg-pink-50 px-2 py-1 rounded-md shrink-0">
                                GV
                            </span>
                        </div>

                        <!-- TAB 1: THỦ CÔNG (KÈM NÚT TRỢ LÝ AI GỢI Ý ĐÁP ÁN) -->
                        <div x-show="tab === 'manual'" x-transition class="flex-1 flex flex-col min-h-0">
                            <form action="{{ route('teacher.quizzes.questions.store', $quiz->id) }}" method="POST" class="flex-1 flex flex-col min-h-0">
                                @csrf

                                <div class="space-y-3.5 overflow-y-auto pr-1 flex-1 sidebar-scroll [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    <!-- Nội dung câu hỏi -->
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="question_text" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                                Nội dung câu hỏi <span class="text-rose-500">*</span>
                                            </label>
                                            <button type="button" @click="aiSuggestForManual()"
                                                    :disabled="isSuggestingOptions"
                                                    class="text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1.5 shadow-2xs"
                                                    title="AI tự động đọc câu hỏi để sinh 4 đáp án, chọn đáp án đúng và điền giải thích">
                                                <span x-show="!isSuggestingOptions" class="inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                    <span>AI gợi ý 4 đáp án & giải thích</span>
                                                </span>
                                                <span x-show="isSuggestingOptions" class="flex items-center gap-1" style="display: none;">
                                                    <svg class="w-3.5 h-3.5 animate-spin text-slate-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                    <span>Đang suy nghĩ...</span>
                                                </span>
                                            </button>
                                        </div>
                                        <textarea name="question_text" id="question_text" rows="3" required x-model="manualQuestionText"
                                                  placeholder="Nhập nội dung câu hỏi trắc nghiệm ở đây..."
                                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm"></textarea>
                                        @error('question_text')
                                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- 4 Đáp án A, B, C, D -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                                Các phương án trả lời <span class="text-rose-500">*</span>
                                            </label>
                                            <span class="text-[11px] text-pink-600 font-medium">Tích chọn đáp án ĐÚNG</span>
                                        </div>

                                        <div class="space-y-2.5">
                                            <template x-for="(letter, index) in ['A', 'B', 'C', 'D']" :key="index">
                                                <div class="flex items-center gap-2">
                                                    <label class="flex items-center gap-1.5 cursor-pointer shrink-0" title="Chọn làm đáp án đúng">
                                                        <input type="radio" name="correct_option" :value="index"
                                                               :checked="manualCorrectOption == index"
                                                               @change="manualCorrectOption = index"
                                                               class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                                        <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-700"
                                                              x-text="letter"></span>
                                                    </label>
                                                    <input type="text" name="options[]" required 
                                                           x-model="manualOptions[index]"
                                                           :placeholder="'Nội dung phương án ' + letter + '...'"
                                                           class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                                </div>
                                            </template>
                                        </div>
                                        @error('options')
                                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                        @error('correct_option')
                                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Lời giải thích -->
                                    <div>
                                        <label for="explanation" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                            Lời giải thích đáp án (Tùy chọn)
                                        </label>
                                        <textarea name="explanation" id="explanation" rows="2" x-model="manualExplanation"
                                                  placeholder="Giải thích lý do vì sao đáp án này đúng để học viên học hỏi sau khi làm bài..."
                                                  class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-xs"></textarea>
                                        @error('explanation')
                                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 shrink-0 mt-2">
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                                        + Lưu câu hỏi vào đề thi
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- TAB 2: TẠO BẰNG AI (BATCH GENERATOR & IMPORT NGAY) -->
                        <div x-show="tab === 'ai'" x-transition class="flex-1 flex flex-col min-h-0" style="display: none;">
                            <div class="space-y-4 overflow-y-auto pr-1 flex-1 sidebar-scroll [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                <!-- Thông báo lỗi / thành công của AI -->
                                <div x-show="aiError" class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl" x-text="aiError" style="display: none;"></div>
                                <div x-show="aiSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl" x-text="aiSuccess" style="display: none;"></div>

                                <!-- 1. Chọn Nhà cung cấp & Model -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            1. Nhà cung cấp AI
                                        </label>
                                        <select x-model="aiProvider" @change="onProviderChange()"
                                                class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                            <option value="gemini">Google Gemini AI (Nhanh & Free)</option>
                                            <option value="groq">GroqCloud (Siêu tốc LPU)</option>
                                            <option value="openai">OpenAI (ChatGPT)</option>
                                            <option value="openrouter">OpenRouter AI (Đa mô hình)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                                2. Mô hình (Model)
                                            </label>
                                            <button type="button" @click="fetchModelsFromKey(false)"
                                                    :disabled="isFetchingModels || !aiApiKey"
                                                    class="text-[10px] text-pink-600 hover:text-pink-800 font-bold flex items-center gap-1 transition">
                                                <svg class="w-3 h-3" :class="isFetchingModels ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <span>Quét model</span>
                                            </button>
                                        </div>
                                        <select x-model="aiModel"
                                                class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                            <template x-for="m in (providerModels[aiProvider] || [])" :key="m.id">
                                                <option :value="m.id" x-text="m.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <!-- Custom model input nếu chọn custom -->
                                <div x-show="aiModel === 'custom'" style="display: none;">
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nhập tên model tùy chỉnh</label>
                                    <input type="text" x-model="aiCustomModel" placeholder="Nhập mã model tùy chỉnh..."
                                           class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2">
                                </div>

                                <!-- 2. API Key -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                            API Key <span class="text-rose-500">*</span>
                                        </label>
                                        <a :href="providerKeyLinks[aiProvider]" target="_blank" rel="noopener noreferrer"
                                           class="text-[10px] text-pink-600 hover:underline font-semibold flex items-center gap-0.5">
                                            <span>Lấy key miễn phí</span>
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                    <div class="relative">
                                        <input :type="showAiPassword ? 'text' : 'password'" x-model="aiApiKey" @input="onApiKeyInput()"
                                               :placeholder="providerPlaceholders[aiProvider] || 'Nhập mã API Key...'"
                                               class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 pr-10 font-mono">
                                        <button type="button" @click="showAiPassword = !showAiPassword"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                            <span x-text="showAiPassword ? '🙈' : '👁️'" class="text-xs"></span>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Key được mã hóa lưu an toàn trên trình duyệt của bạn (localStorage).</p>
                                </div>

                                <!-- 3. Nội dung / Chủ đề câu hỏi -->
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                        Nội dung bài học hoặc Chủ đề cần tạo câu hỏi <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea rows="4" x-model="aiContent"
                                              placeholder="Dán nội dung bài giảng, tóm tắt tài liệu hoặc nhập chủ đề cụ thể (Ví dụ: Kiến thức về Lịch sử Việt Nam thế kỷ 20, lập trình Laravel Eloquent...)"
                                              class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-2.5"></textarea>
                                </div>

                                <!-- 4. Số câu & Độ khó -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            Số lượng câu
                                        </label>
                                        <input type="number" min="1" step="1" x-model="aiQuestionCount"
                                               class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                            Mức độ khó
                                        </label>
                                        <select x-model="aiDifficulty"
                                                class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                            <option value="easy">Cơ bản (Dễ)</option>
                                            <option value="medium">Trung bình (Chuẩn)</option>
                                            <option value="hard">Nâng cao (Khó)</option>
                                            <option value="mixed">Tổng hợp các mức</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tùy chọn tự động import -->
                                <div class="p-3 bg-pink-50/60 rounded-xl border border-pink-100 flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                                        <input type="checkbox" x-model="aiAutoImport" class="rounded text-pink-600 border-slate-300 focus:ring-pink-500 w-4 h-4">
                                        <span>Tự động lưu thẳng vào đề thi</span>
                                    </label>
                                    <span class="text-[10px] text-pink-700 font-bold bg-pink-100/70 px-2 py-0.5 rounded">Khuyên dùng</span>
                                </div>

                                <!-- Preview các câu hỏi sinh ra nếu không auto import -->
                                <div x-show="!aiAutoImport && aiQuestionsPreview.length > 0" class="pt-3 border-t border-slate-100 space-y-3" style="display: none;">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-800">Đã tạo <span class="text-pink-600" x-text="aiQuestionsPreview.length"></span> câu hỏi xem trước</span>
                                        <button type="button" @click="importPreviewQuestions()"
                                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-xs">
                                            + Lưu vào đề thi ngay
                                        </button>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                                        <template x-for="(pq, pIdx) in aiQuestionsPreview" :key="pIdx">
                                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                                <div class="font-bold text-slate-900 mb-1" x-text="(pIdx + 1) + '. ' + pq.question_text"></div>
                                                <div class="text-[11px] text-slate-500 italic" x-text="pq.explanation ? 'Giải thích: ' + pq.explanation : ''"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Nút Submit AI ghim dưới cùng -->
                            <div class="pt-3 border-t border-slate-100 shrink-0 mt-2">
                                <button type="button" @click="generateBatchAiQuestions()"
                                        :disabled="isGeneratingAi"
                                        class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-sm shadow-md shadow-slate-900/25 transition flex items-center justify-center gap-2">
                                    <span x-show="!isGeneratingAi" class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>Tạo câu hỏi bằng AI</span>
                                    </span>
                                    <span x-show="isGeneratingAi" class="flex items-center gap-2" style="display: none;">
                                        <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>AI đang xử lý & biên soạn...</span>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- TAB 3: NHẬP TỪ JSON -->
                        <div x-show="tab === 'json'" x-transition class="flex-1 flex flex-col min-h-0" style="display: none;">
                            <form action="{{ route('teacher.quizzes.questions.import', $quiz->id) }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
                                @csrf

                                <div class="space-y-4 overflow-y-auto pr-1 flex-1 sidebar-scroll [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                                            <button type="button" @click="jsonSubMode = 'paste'"
                                                    :class="jsonSubMode === 'paste' ? 'text-pink-700 underline font-black' : 'text-slate-500'"
                                                    class="hover:text-pink-600">
                                                Dán chuỗi JSON
                                            </button>
                                            <span class="text-slate-300">|</span>
                                            <button type="button" @click="jsonSubMode = 'file'"
                                                    :class="jsonSubMode === 'file' ? 'text-pink-700 underline font-black' : 'text-slate-500'"
                                                    class="hover:text-pink-600">
                                                Tải file .json
                                            </button>
                                        </div>
                                        <button type="button" @click="loadSample()" class="text-[11px] font-bold text-pink-600 hover:underline">
                                            + Mẫu JSON
                                        </button>
                                    </div>

                                    <div x-show="jsonSubMode === 'paste'">
                                        <textarea name="json_content" id="json_content" rows="8" x-model="jsonText"
                                                  placeholder='[
  {
    "question_text": "Câu hỏi trắc nghiệm?",
    "options": ["Đáp án A", "Đáp án B", "Đáp án C", "Đáp án D"],
    "correct_option": 0,
    "explanation": "Giải thích..."
  }
]'
                                                  class="w-full font-mono text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-3 bg-slate-900 text-emerald-400"></textarea>
                                    </div>

                                    <div x-show="jsonSubMode === 'file'" style="display: none;">
                                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-5 text-center bg-slate-50 hover:bg-pink-50/40 cursor-pointer relative">
                                            <input type="file" name="json_file" id="json_file" accept=".json,application/json,text/plain"
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            <div class="text-xs font-bold text-slate-700 mb-1">Bấm để chọn tệp .json từ máy tính</div>
                                            <p class="text-[11px] text-slate-400">File chứa mảng câu hỏi trắc nghiệm</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 shrink-0 mt-2">
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition flex items-center justify-center gap-2">
                                        <span>Nhập các câu hỏi vào đề</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Cột phải (lg:col-span-7): Danh sách câu hỏi hiện có trong đề -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">
                            Danh sách câu hỏi trong đề ({{ $quiz->questions->count() }} câu)
                        </h3>
                        @if($quiz->randomize_questions)
                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-medium flex items-center gap-1">
                                &check; Sẽ tự động đảo câu hỏi khi thi
                            </span>
                        @endif
                    </div>

                    @if($quiz->questions->isEmpty())
                        <div class="bg-white rounded-3xl border border-pink-100 p-12 text-center shadow-xs">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm mb-1">Chưa có câu hỏi nào</h4>
                            <p class="text-slate-500 text-xs max-w-sm mx-auto">
                                Bạn có thể tự soạn từng câu hỏi, dùng tab <strong>"Tạo bằng AI"</strong> để sinh tự động, hoặc nhập từ file JSON.
                            </p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($quiz->questions as $index => $question)
                                @php
                                    $correctOptionIndex = $question->options->search(fn($opt) => (bool)$opt->is_correct);
                                    if ($correctOptionIndex === false) {
                                        $correctOptionIndex = 0;
                                    }
                                    $questionPayload = [
                                        'id' => $question->id,
                                        'question_text' => $question->question_text,
                                        'explanation' => $question->explanation ?? '',
                                        'options' => $question->options->map(fn($o) => ['option_text' => $o->option_text, 'is_correct' => (bool)$o->is_correct])->values()->all(),
                                        'correct_option' => $correctOptionIndex,
                                        'update_url' => route('teacher.quizzes.questions.update', [$quiz->id, $question->id])
                                    ];
                                @endphp
                                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5 hover:border-pink-200 transition">
                                    <div class="flex items-start justify-between gap-4 pb-3 mb-3 border-b border-slate-100">
                                        <div class="flex items-start gap-3">
                                            <span class="w-7 h-7 rounded-lg bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ $index + 1 }}
                                            </span>
                                            <h4 class="font-bold text-slate-900 text-base leading-snug">
                                                {{ $question->question_text }}
                                            </h4>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <!-- Nút SỬA câu hỏi -->
                                            <button type="button" 
                                                    @click='openEditModal(@json($questionPayload))'
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-pink-600 hover:bg-pink-50 transition" title="Chỉnh sửa câu hỏi này">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <!-- Nút XÓA câu hỏi -->
                                            <form action="{{ route('teacher.quizzes.questions.destroy', [$quiz->id, $question->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa câu hỏi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Danh sách đáp án -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs mb-3">
                                        @foreach($question->options as $optIndex => $option)
                                            <div class="flex items-center gap-2 p-2.5 rounded-lg border {{ $option->is_correct ? 'bg-emerald-50/80 border-emerald-300 text-emerald-950 font-bold' : 'bg-slate-50 border-slate-100 text-slate-700' }}">
                                                <span class="w-5 h-5 rounded flex items-center justify-center font-bold text-[11px] {{ $option->is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                                                    {{ chr(65 + $optIndex) }}
                                                </span>
                                                <span class="flex-grow">{{ $option->option_text }}</span>
                                                @if($option->is_correct)
                                                    <span class="text-emerald-600 font-bold text-xs">&check; Đúng</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Lời giải thích -->
                                    @if($question->explanation)
                                        <div class="p-2.5 rounded-lg bg-pink-50 text-[11px] text-pink-900 border border-pink-100">
                                            <strong class="font-bold">Giải thích:</strong> {{ $question->explanation }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- MODAL CHỈNH SỬA CÂU HỎI TRẮC NGHIỆM -->
        <div x-show="editingQuestion !== null" 
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="closeEditModal()"></div>

                <!-- Modal Dialog -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 sm:p-7 relative z-10 max-h-[92vh] flex flex-col space-y-4"
                     @click.stop="">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </span>
                            <h3 class="font-black text-lg text-slate-900">Chỉnh sửa câu hỏi trắc nghiệm</h3>
                        </div>
                        <button type="button" @click="closeEditModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold">
                            &times;
                        </button>
                    </div>

                    <!-- AI Tools bên trong Modal sửa câu hỏi -->
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2 shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Trợ lý AI hỗ trợ sửa câu hỏi:</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="refineEditQuestion('refine')"
                                        :disabled="isRefiningEdit"
                                        class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                                    <span x-show="!isRefiningEdit" class="inline-flex items-center gap-1.5">
                                        <svg class="w-3 h-3 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span>AI tối ưu câu chữ</span>
                                    </span>
                                    <span x-show="isRefiningEdit" class="flex items-center gap-1" style="display: none;">
                                        <svg class="w-3 h-3 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Đang sửa...</span>
                                    </span>
                                </button>
                                <button type="button" @click="refineEditQuestion('suggest_options')"
                                        :disabled="isRefiningEdit"
                                        class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-xs transition">
                                    <span>Gợi ý lại 4 đáp án</span>
                                </button>
                            </div>
                        </div>
                        <div x-show="editAiSuccess" class="text-[11px] text-emerald-700 font-semibold" x-text="editAiSuccess" style="display: none;"></div>
                        <div x-show="editAiError" class="text-[11px] text-rose-600 font-semibold" x-text="editAiError" style="display: none;"></div>
                    </div>

                    <form :action="editForm.update_url" method="POST" class="flex-1 flex flex-col min-h-0 space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 overflow-y-auto pr-1 flex-1 sidebar-scroll [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <!-- Nội dung câu hỏi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                    Nội dung câu hỏi <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="question_text" rows="3" required x-model="editForm.question_text"
                                          class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm"></textarea>
                            </div>

                            <!-- 4 Đáp án A, B, C, D -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                        Các phương án trả lời <span class="text-rose-500">*</span>
                                    </label>
                                    <span class="text-[11px] text-pink-600 font-semibold">Tích chọn đáp án ĐÚNG</span>
                                </div>

                                <div class="space-y-2">
                                    <template x-for="(letter, idx) in ['A', 'B', 'C', 'D']" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <label class="flex items-center gap-1.5 cursor-pointer shrink-0" title="Chọn làm đáp án đúng">
                                                <input type="radio" name="correct_option" :value="idx"
                                                       :checked="editForm.correct_option == idx"
                                                       @change="editForm.correct_option = idx"
                                                       class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                                <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-700"
                                                      x-text="letter"></span>
                                            </label>
                                            <input type="text" name="options[]" required 
                                                   x-model="editForm.options[idx]"
                                                   :placeholder="'Nội dung phương án ' + letter + '...'"
                                                   class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Lời giải thích -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                    Lời giải thích đáp án (Tùy chọn)
                                </label>
                                <textarea name="explanation" rows="2" x-model="editForm.explanation"
                                          placeholder="Giải thích lý do vì sao đáp án này đúng..."
                                          class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-xs"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 shrink-0 mt-2">
                            <button type="button" @click="closeEditModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
                                Hủy
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- MODAL CẤU HÌNH API KEY NHANH KHI BẤM NÚT AI -->
        <div x-show="showApiKeyModal" 
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showApiKeyModal = false"></div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-md w-full p-6 relative z-10 space-y-4"
                     @click.stop="">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-sm font-bold">🔑</span>
                            <h3 class="font-black text-base text-slate-900">Cấu hình API Key AI</h3>
                        </div>
                        <button type="button" @click="showApiKeyModal = false" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center font-bold">
                            &times;
                        </button>
                    </div>

                    <p class="text-xs text-slate-500">
                        Vui lòng nhập API Key để sử dụng tính năng trợ lý AI (tự động gợi ý đáp án, tối ưu câu hỏi hoặc sinh đề thi).
                    </p>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nhà cung cấp AI</label>
                            <select x-model="aiProvider" @change="onProviderChange()"
                                    class="w-full text-xs font-medium rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                <option value="gemini">Google Gemini AI (Khuyên dùng - Miễn phí)</option>
                                <option value="groq">GroqCloud (LPU Siêu tốc)</option>
                                <option value="openai">OpenAI (ChatGPT)</option>
                                <option value="openrouter">OpenRouter AI</option>
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">API Key</label>
                                <a :href="providerKeyLinks[aiProvider]" target="_blank" rel="noopener noreferrer"
                                   class="text-[11px] text-pink-600 hover:underline font-semibold">
                                    Lấy key miễn phí &rarr;
                                </a>
                            </div>
                            <input type="text" x-model="keyModalApiKey"
                                   :placeholder="providerPlaceholders[aiProvider] || 'Nhập mã API Key...'"
                                   class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showApiKeyModal = false" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                            Hủy
                        </button>
                        <button type="button" @click="saveApiKeyModal()" class="px-5 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs shadow-xs transition">
                            Lưu & Tiếp tục
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Script điều khiển Alpine Component -->
    <script>
    function teacherQuestionsHandler(initialTab = 'manual') {
        return {
            tab: initialTab,
            jsonSubMode: 'paste',
            jsonText: '',

            // Dữ liệu tạo câu hỏi thủ công
            manualQuestionText: @json(old('question_text', '')),
            manualOptions: [
                @json(old('options.0', '')),
                @json(old('options.1', '')),
                @json(old('options.2', '')),
                @json(old('options.3', ''))
            ],
            manualCorrectOption: {{ (int) old('correct_option', 0) }},
            manualExplanation: @json(old('explanation', '')),

            // Cấu hình AI
            aiProvider: 'gemini',
            aiApiKey: '',
            aiModel: 'gemini-1.5-flash',
            aiCustomModel: '',
            aiContent: '',
            aiQuestionCount: 5,
            aiDifficulty: 'medium',
            aiAutoImport: true,
            aiQuestionsPreview: [],

            // Trạng thái xử lý
            isGeneratingAi: false,
            isFetchingModels: false,
            isSuggestingOptions: false,
            isRefiningEdit: false,
            showAiPassword: false,
            aiError: '',
            aiSuccess: '',
            showApiKeyModal: false,
            keyModalApiKey: '',

            // Danh mục Provider & Model
            providerModels: {
                gemini: [
                    { id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash (Khuyên dùng - Nhanh & Miễn phí)' },
                    { id: 'gemini-2.0-flash', name: 'Gemini 2.0 Flash (Thế hệ mới nhất, Siêu tốc)' },
                    { id: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro (Phân tích chuyên sâu)' },
                    { id: 'custom', name: 'Nhập model khác...' }
                ],
                groq: [
                    { id: 'llama-3.1-8b-instant', name: 'Llama 3.1 8B Instant (Khuyên dùng - Cực nhanh & Ổn định)' },
                    { id: 'openai/gpt-oss-120b', name: 'OpenAI GPT-OSS 120B (Thông minh nhất trên Groq)' },
                    { id: 'openai/gpt-oss-20b', name: 'OpenAI GPT-OSS 20B (Nhanh & Tối ưu chi phí)' },
                    { id: 'deepseek-r1-distill-llama-70b', name: 'DeepSeek R1 Distill 70B (Suy luận logic)' },
                    { id: 'custom', name: 'Nhập model khác...' }
                ],
                openai: [
                    { id: 'gpt-4o-mini', name: 'GPT-4o Mini (Khuyên dùng - Rẻ, nhanh & thông minh)' },
                    { id: 'gpt-4o', name: 'GPT-4o (Mô hình mạnh nhất của OpenAI)' },
                    { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo (Mô hình kinh điển)' },
                    { id: 'custom', name: 'Nhập model khác...' }
                ],
                openrouter: [
                    { id: 'google/gemini-2.0-flash-001', name: 'Google: Gemini 2.0 Flash' },
                    { id: 'meta-llama/llama-3.3-70b-instruct', name: 'Meta: Llama 3.3 70B Instruct' },
                    { id: 'deepseek/deepseek-chat', name: 'DeepSeek: DeepSeek-V3 Chat' },
                    { id: 'openai/gpt-4o-mini', name: 'OpenAI: GPT-4o Mini' },
                    { id: 'custom', name: 'Nhập model khác...' }
                ]
            },
            providerPlaceholders: {
                gemini: 'AIzaSy...',
                groq: 'gsk_...',
                openai: 'sk-proj-...',
                openrouter: 'sk-or-v1-...'
            },
            providerKeyLinks: {
                gemini: 'https://aistudio.google.com/app/apikey',
                groq: 'https://console.groq.com/keys',
                openai: 'https://platform.openai.com/api-keys',
                openrouter: 'https://openrouter.ai/keys'
            },

            // Modal Sửa câu hỏi
            editingQuestion: null,
            editForm: {
                id: null,
                question_text: '',
                options: ['', '', '', ''],
                correct_option: 0,
                explanation: '',
                update_url: ''
            },
            editAiSuccess: '',
            editAiError: '',

            init() {
                try {
                    const savedProvider = localStorage.getItem('lms_ai_last_provider') || 'gemini';
                    if (this.providerModels[savedProvider]) {
                        this.aiProvider = savedProvider;
                    }
                } catch (e) {}
                this.loadApiKeyForCurrentProvider();
            },

            loadApiKeyForCurrentProvider() {
                try {
                    this.aiApiKey = localStorage.getItem('lms_ai_key_' + this.aiProvider) || '';
                } catch (e) {
                    this.aiApiKey = '';
                }
                const models = this.providerModels[this.aiProvider] || [];
                if (models.length > 0 && !models.some(m => m.id === this.aiModel)) {
                    this.aiModel = models[0].id;
                }
            },

            onProviderChange() {
                try {
                    localStorage.setItem('lms_ai_last_provider', this.aiProvider);
                } catch (e) {}
                this.loadApiKeyForCurrentProvider();
                this.aiError = '';
                this.aiSuccess = '';
            },

            saveApiKey() {
                try {
                    if (this.aiApiKey.trim()) {
                        localStorage.setItem('lms_ai_key_' + this.aiProvider, this.aiApiKey.trim());
                    }
                } catch (e) {}
            },

            fetchModelTimeout: null,
            onApiKeyInput() {
                this.saveApiKey();
                clearTimeout(this.fetchModelTimeout);
                if (this.aiApiKey.trim().length >= 10) {
                    this.fetchModelTimeout = setTimeout(() => {
                        this.fetchModelsFromKey(true);
                    }, 600);
                }
            },

            async fetchModelsFromKey(isAuto = false) {
                const key = (this.aiApiKey || '').trim();
                if (!key) {
                    if (!isAuto) this.aiError = 'Vui lòng nhập API Key trước khi quét model.';
                    return;
                }
                if (this.isFetchingModels) return;
                this.isFetchingModels = true;
                if (!isAuto) {
                    this.aiError = '';
                    this.aiSuccess = '';
                }
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('teacher.quizzes.fetch-ai-models') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ provider: this.aiProvider, api_key: key })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Không thể quét danh sách mô hình từ API Key.');
                    }
                    if (data.models && data.models.length > 0) {
                        this.providerModels[this.aiProvider] = [
                            ...data.models,
                            { id: 'custom', name: 'Nhập model khác...' }
                        ];
                        if (!data.models.some(m => m.id === this.aiModel)) {
                            this.aiModel = data.models[0].id;
                        }
                        if (!isAuto) {
                            this.aiSuccess = `Đã cập nhật ${data.models.length} mô hình AI khả dụng!`;
                        }
                    }
                } catch (err) {
                    if (!isAuto) this.aiError = err.message || 'Lỗi khi quét danh sách mô hình.';
                } finally {
                    this.isFetchingModels = false;
                }
            },

            // Gợi ý đáp án cho câu hỏi đơn lẻ
            async aiSuggestForManual() {
                if (!this.manualQuestionText || !this.manualQuestionText.trim()) {
                    alert('Vui lòng nhập nội dung câu hỏi trước khi nhờ AI gợi ý!');
                    return;
                }
                if (!this.aiApiKey || !this.aiApiKey.trim()) {
                    this.openApiKeyModal(() => this.aiSuggestForManual());
                    return;
                }
                this.isSuggestingOptions = true;
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('teacher.quizzes.ai-suggest-question') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            provider: this.aiProvider,
                            api_key: this.aiApiKey.trim(),
                            model: this.aiModel === 'custom' ? this.aiCustomModel.trim() : this.aiModel,
                            question_text: this.manualQuestionText.trim(),
                            action: 'suggest_options'
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'AI không thể gợi ý đáp án. Vui lòng kiểm tra lại API Key.');
                    }
                    const q = data.question;
                    if (q.options && q.options.length >= 4) {
                        this.manualOptions = q.options.map(o => o.option_text);
                        const cIdx = q.options.findIndex(o => o.is_correct);
                        this.manualCorrectOption = cIdx >= 0 ? cIdx : 0;
                    }
                    if (q.explanation) {
                        this.manualExplanation = q.explanation;
                    }
                } catch (err) {
                    alert('Lỗi AI: ' + (err.message || 'Không thể nhận phản hồi.'));
                } finally {
                    this.isSuggestingOptions = false;
                }
            },

            // Sinh bộ câu hỏi AI
            async generateBatchAiQuestions() {
                if (!this.aiContent || !this.aiContent.trim()) {
                    this.aiError = 'Vui lòng nhập nội dung bài học hoặc chủ đề muốn tạo câu hỏi.';
                    return;
                }
                if (!this.aiApiKey || !this.aiApiKey.trim()) {
                    this.aiError = 'Vui lòng nhập API Key để tiếp tục.';
                    return;
                }
                const count = parseInt(this.aiQuestionCount, 10);
                if (isNaN(count) || count < 1) {
                    this.aiError = 'Số lượng câu hỏi phải là số nguyên lớn hơn hoặc bằng 1.';
                    return;
                }

                this.isGeneratingAi = true;
                this.aiError = '';
                this.aiSuccess = '';
                this.saveApiKey();

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('teacher.quizzes.generate-ai-questions') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            provider: this.aiProvider,
                            api_key: this.aiApiKey.trim(),
                            model: this.aiModel === 'custom' ? this.aiCustomModel.trim() : this.aiModel,
                            content: this.aiContent.trim(),
                            count: count,
                            difficulty: this.aiDifficulty || 'medium'
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Không thể tạo câu hỏi từ AI.');
                    }
                    const questions = data.questions || [];
                    if (questions.length === 0) {
                        throw new Error('AI không trả về câu hỏi hợp lệ.');
                    }

                    if (this.aiAutoImport) {
                        document.getElementById('ai-import-json-input').value = JSON.stringify(questions);
                        document.getElementById('ai-import-form').submit();
                        return;
                    } else {
                        this.aiQuestionsPreview = questions;
                        this.aiSuccess = `Đã tạo xong ${questions.length} câu hỏi! Bấm "Lưu vào đề thi" để lưu.`;
                    }
                } catch (err) {
                    this.aiError = err.message || 'Đã xảy ra lỗi khi tạo câu hỏi.';
                } finally {
                    this.isGeneratingAi = false;
                }
            },

            importPreviewQuestions() {
                if (this.aiQuestionsPreview.length === 0) return;
                document.getElementById('ai-import-json-input').value = JSON.stringify(this.aiQuestionsPreview);
                document.getElementById('ai-import-form').submit();
            },

            // Mở modal sửa câu hỏi
            openEditModal(q) {
                this.editingQuestion = q;
                this.editForm = {
                    id: q.id,
                    question_text: q.question_text,
                    options: q.options.map(o => o.option_text),
                    correct_option: q.correct_option,
                    explanation: q.explanation || '',
                    update_url: q.update_url
                };
                this.editAiError = '';
                this.editAiSuccess = '';
            },

            closeEditModal() {
                this.editingQuestion = null;
                this.editAiError = '';
                this.editAiSuccess = '';
            },

            async refineEditQuestion(action = 'refine') {
                if (!this.editForm.question_text || !this.editForm.question_text.trim()) {
                    this.editAiError = 'Vui lòng nhập nội dung câu hỏi.';
                    return;
                }
                if (!this.aiApiKey || !this.aiApiKey.trim()) {
                    this.openApiKeyModal(() => this.refineEditQuestion(action));
                    return;
                }

                this.isRefiningEdit = true;
                this.editAiError = '';
                this.editAiSuccess = '';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    const response = await fetch('{{ route('teacher.quizzes.ai-suggest-question') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            provider: this.aiProvider,
                            api_key: this.aiApiKey.trim(),
                            model: this.aiModel === 'custom' ? this.aiCustomModel.trim() : this.aiModel,
                            question_text: this.editForm.question_text.trim(),
                            options: this.editForm.options,
                            explanation: this.editForm.explanation,
                            action: action
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Không thể xử lý câu hỏi bằng AI.');
                    }
                    const q = data.question;
                    if (action === 'refine' && q.question_text) {
                        this.editForm.question_text = q.question_text;
                    }
                    if (q.options && q.options.length >= 4) {
                        this.editForm.options = q.options.map(o => o.option_text);
                        const cIdx = q.options.findIndex(o => o.is_correct);
                        this.editForm.correct_option = cIdx >= 0 ? cIdx : 0;
                    }
                    if (q.explanation) {
                        this.editForm.explanation = q.explanation;
                    }
                    this.editAiSuccess = action === 'refine' ? 'Đã tối ưu câu chữ và đáp án bằng AI thành công!' : 'Đã tạo lại 4 đáp án và giải thích mới!';
                } catch (err) {
                    this.editAiError = err.message || 'Lỗi khi gọi AI.';
                } finally {
                    this.isRefiningEdit = false;
                }
            },

            // Modal API Key
            apiKeyCallback: null,
            openApiKeyModal(callback = null) {
                this.apiKeyCallback = callback;
                this.keyModalApiKey = this.aiApiKey || '';
                this.showApiKeyModal = true;
            },

            saveApiKeyModal() {
                if (!this.keyModalApiKey || !this.keyModalApiKey.trim()) {
                    alert('Vui lòng nhập API Key!');
                    return;
                }
                this.aiApiKey = this.keyModalApiKey.trim();
                this.saveApiKey();
                this.showApiKeyModal = false;
                if (typeof this.apiKeyCallback === 'function') {
                    const cb = this.apiKeyCallback;
                    this.apiKeyCallback = null;
                    cb();
                }
            },

            loadSample() {
                this.jsonText = JSON.stringify([
                    {
                        'question_text': 'Laravel là gì?',
                        'explanation': 'Laravel là PHP framework phổ biến nhất hiện nay.',
                        'options': ['Một PHP Framework', 'Một Cơ sở dữ liệu', 'Một Hệ điều hành', 'Một Trình duyệt web'],
                        'correct_option': 0
                    }
                ], null, 2);
            }
        };
    }
    </script>
</x-teacher-layout>
