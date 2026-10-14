<x-admin-layout breadcrumb="Tạo Quiz mới">
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
                    Tạo Bài Kiểm Tra (Quiz) Mới
                </h1>
            </div>
            <a href="{{ route('admin.quizzes.index') }}" class="text-xs font-bold text-slate-600 hover:text-pink-600 border border-slate-200 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                
                Quay lại danh sách
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6" x-data="adminQuizCreateHandler()">

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm font-semibold flex items-center gap-2">
                    
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm space-y-1">
                    <div class="font-bold flex items-center gap-2">
                        
                        Vui lòng kiểm tra lại các lỗi sau:
                    </div>
                    <ul class="list-disc list-inside text-xs pl-2 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

                <form id="admin-quiz-create-form" action="{{ route('admin.quizzes.store') }}" method="POST" enctype="multipart/form-data" @submit="handleFormSubmit($event)" class="space-y-6">
                    @csrf

                    <!-- Card 1: Chọn Khóa học & Loại bài kiểm tra -->
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-6 sm:p-7 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 1. Chọn Khóa học -->
                            <div>
                                <label for="course_id" class="block text-sm font-bold text-slate-900 mb-2">
                                    1. Áp dụng cho Khóa học nào?
                                </label>
                                <select name="course_id" id="course_id" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                                    <option value="" {{ old('course_id') === '' || !old('course_id') ? 'selected' : '' }}>
                                        Tự do (Không theo khóa học nào)
                                    </option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }} (Giảng viên: {{ $course->teacher->name ?? 'Admin' }}) - [{{ $course->status === 'approved' ? 'Đã duyệt' : 'Chờ duyệt' }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Loại bài kiểm tra -->
                            <div>
                                <label class="block text-sm font-bold text-slate-900 mb-2">
                                    Loại bài kiểm tra <span class="text-rose-500">*</span>
                                </label>
                                <div class="grid grid-cols-3 gap-2">
                                    <!-- Option 1: Kiểm tra -->
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                           :class="quizType === 'quiz' ? 'border-pink-600 bg-pink-50/80 ring-2 ring-pink-500/20 text-pink-900 font-bold' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700 font-medium'">
                                        <input type="radio" name="type" value="quiz" x-model="quizType" class="sr-only">
                                        <span class="text-xs leading-tight">Kiểm tra</span>
                                    </label>

                                    <!-- Option 2: Giữa kỳ -->
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                           :class="quizType === 'midterm' ? 'border-amber-600 bg-amber-50/80 ring-2 ring-amber-500/20 text-amber-900 font-bold' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700 font-medium'">
                                        <input type="radio" name="type" value="midterm" x-model="quizType" class="sr-only">
                                        <span class="text-xs leading-tight">Giữa kỳ</span>
                                    </label>

                                    <!-- Option 3: Cuối kỳ -->
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                           :class="quizType === 'final' ? 'border-rose-600 bg-rose-50/80 ring-2 ring-rose-500/20 text-rose-900 font-bold' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700 font-medium'">
                                        <input type="radio" name="type" value="final" x-model="quizType" class="sr-only">
                                        <span class="text-xs leading-tight">Cuối kỳ</span>
                                    </label>
                                </div>
                                <p class="text-xs text-slate-400 mt-1.5" x-text="quizType === 'midterm' ? 'Đề thi kiểm tra giữa kỳ đánh giá quá trình.' : (quizType === 'final' ? 'Đề thi kiểm tra cuối kỳ / kết thúc học phần.' : 'Đề kiểm tra thường kỳ / bài tập trắc nghiệm.')"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: 2 LỰA CHỌN TẠO QUIZ (Paste JSON hoặc File JSON) HOẶC Nhập thủ công -->
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-6 sm:p-7 space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-900 mb-1">
                                2. Phương thức tạo đề thi (Hỗ trợ 2 lựa chọn JSON siêu tốc)
                            </label>
                            <p class="text-xs text-slate-500 mb-4">
                                Bạn có thể dán nội dung JSON, tải tệp tin JSON (.json) hoặc nhập biểu mẫu thủ công.
                            </p>

                            <!-- Selector 4 tabs -->
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <!-- Option 1: Paste JSON -->
                                <button type="button" @click="setMode('paste')"
                                        :class="mode === 'paste' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-3.5 rounded-2xl border text-left transition flex items-start gap-2.5">
                                    <div :class="mode === 'paste' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs">Dán mã JSON</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Paste chuỗi JSON</div>
                                    </div>
                                </button>

                                <!-- Option 2: Upload File JSON -->
                                <button type="button" @click="setMode('file')"
                                        :class="mode === 'file' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-3.5 rounded-2xl border text-left transition flex items-start gap-2.5">
                                    <div :class="mode === 'file' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs">Tải file .JSON</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Upload từ máy tính</div>
                                    </div>
                                </button>

                                <!-- Option 3: Tạo câu hỏi AI -->
                                <button type="button" @click="setMode('ai')"
                                        :class="mode === 'ai' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-3.5 rounded-2xl border text-left transition flex items-start gap-2.5">
                                    <div :class="mode === 'ai' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" 
                                         class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs">Tạo bằng AI</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Gemini, OpenAI, Groq</div>
                                    </div>
                                </button>

                                <!-- Option 4: Manual Input -->
                                <button type="button" @click="setMode('manual')"
                                        :class="mode === 'manual' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-3.5 rounded-2xl border text-left transition flex items-start gap-2.5">
                                    <div :class="mode === 'manual' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs">Nhập thủ công</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">Soạn từng câu hỏi</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- PANEL 1: DÁN MÃ JSON -->
                        <div x-show="mode === 'paste'" x-transition class="space-y-4 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <label for="json_content" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Nội dung JSON của đề thi & câu hỏi
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="loadSampleJson()" class="px-3 py-1 text-xs font-bold rounded-lg bg-pink-50 text-pink-700 hover:bg-pink-100 transition flex items-center gap-1">
                                        <span></span> Nạp JSON mẫu
                                    </button>
                                    <button type="button" @click="clearJson()" class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                                        Xóa ô
                                    </button>
                                </div>
                            </div>

                            <textarea name="json_content" id="json_content" rows="12" x-model="jsonText" @input="debounceParseJson()"
                                      placeholder='Dán nội dung JSON vào đây (hỗ trợ cả đối tượng chứa title, duration, questions hoặc mảng questions trực tiếp)...'
                                      class="w-full font-mono text-xs rounded-2xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-4 bg-slate-900 text-emerald-400 placeholder:text-slate-500"></textarea>

                            <p class="text-[11px] text-slate-400">
                                Khi dán JSON hợp lệ, hệ thống sẽ tự động trích xuất tiêu đề, thời lượng, điểm đạt và toàn bộ danh sách câu hỏi.
                            </p>
                        </div>

                        <!-- PANEL 2: TẢI FILE JSON -->
                        <div x-show="mode === 'file'" x-transition class="space-y-4 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Chọn tệp tin .JSON từ máy tính
                                </label>
                                <button type="button" @click="downloadSampleJson()" class="px-3 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition flex items-center gap-1">
                                    
                                    Tải file JSON mẫu
                                </button>
                            </div>

                            <div class="border-2 border-dashed border-slate-300 rounded-3xl p-8 text-center bg-slate-50/50 hover:bg-pink-50/30 hover:border-pink-300 transition cursor-pointer relative"
                                 @dragover.prevent="" @drop.prevent="handleFileDrop($event)">
                                <input type="file" name="json_file" id="json_file" accept=".json,application/json,text/plain"
                                       @change="handleFileSelect($event)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <div class="font-bold text-slate-800 text-sm mb-1" x-text="fileName ? 'Đã chọn file: ' + fileName : 'Kéo thả tệp .json vào đây hoặc bấm để chọn tệp'"></div>
                                <p class="text-xs text-slate-400" x-text="fileSizeText || 'Hỗ trợ tệp tin định dạng JSON chuẩn UTF-8'"></p>
                            </div>
                        </div>

                        <!-- LIVE PREVIEW PARSED QUESTIONS -->
                        <div x-show="parsedQuestions.length > 0" x-transition class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                    <h4 class="font-bold text-emerald-900 text-sm">
                                        Đã nhận diện thành công <span class="text-emerald-700 font-black" x-text="parsedQuestions.length"></span> câu hỏi trắc nghiệm!
                                    </h4>
                                </div>
                                <span class="text-xs text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-md font-semibold">
                                    Sẽ tự động nhập vào đề thi
                                </span>
                            </div>

                            <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                                <template x-for="(q, idx) in parsedQuestions" :key="idx">
                                    <div class="p-3 bg-white rounded-xl border border-emerald-100 text-xs space-y-1.5 shadow-xs">
                                        <div class="font-bold text-slate-800 flex items-start gap-2">
                                            <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0" x-text="idx + 1"></span>
                                            <span x-text="q.question_text"></span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-7">
                                            <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                                                <div :class="opt.is_correct ? 'bg-emerald-50 text-emerald-800 font-bold border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                                     class="px-2 py-1 rounded-lg border text-[11px] flex items-center justify-between">
                                                    <span x-text="String.fromCharCode(65 + oIdx) + '. ' + opt.option_text"></span>
                                                    <span x-show="opt.is_correct" class="text-emerald-600 text-[10px]">✓ Đúng</span>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="q.explanation" class="pl-7 text-[11px] text-slate-500 italic">
                                            Giải thích: <span x-text="q.explanation"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Parse error alert -->
                        <div x-show="parseError" x-transition class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                            
                            <div>
                                <span class="font-bold">Lỗi cú pháp JSON: </span>
                                <span x-text="parseError"></span>
                            </div>
                        </div>

                        <!-- PANEL 3: SOẠN CÂU HỎI THỦ CÔNG KHI TẠO -->
                        <div x-show="mode === 'manual'" x-transition class="space-y-4 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                        
                                        Soạn câu hỏi trắc nghiệm trực tiếp
                                    </h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Nhập nội dung câu hỏi và tích chọn đáp án đúng. Bạn có thể thêm nhiều câu hỏi trước khi bấm Lưu.
                                    </p>
                                </div>
                                <button type="button" @click="addManualQuestion()" class="px-3 py-1.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 shrink-0">
                                    
                                    + Thêm câu hỏi
                                </button>
                            </div>

                            <!-- Danh sách câu hỏi thủ công -->
                            <div class="space-y-4 max-h-[520px] overflow-y-auto pr-1">
                                <template x-for="(q, qIndex) in manualQuestions" :key="qIndex">
                                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 border border-slate-200 space-y-3 relative group">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-pink-700 bg-pink-100/80 px-2.5 py-1 rounded-lg">
                                                <span class="w-1.5 h-1.5 rounded-full bg-pink-600"></span>
                                                Câu hỏi #<span x-text="qIndex + 1"></span>
                                            </span>
                                            <button type="button" @click="removeManualQuestion(qIndex)" 
                                                    x-show="manualQuestions.length > 1"
                                                    class="text-xs text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2 py-1 rounded-lg transition font-medium flex items-center gap-1" title="Xóa câu hỏi này">
                                                
                                                Xóa câu này
                                            </button>
                                        </div>

                                        <!-- Nội dung câu hỏi -->
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                                Nội dung câu hỏi <span class="text-rose-500">*</span>
                                            </label>
                                            <textarea x-model="q.question_text" rows="2"
                                                      placeholder="Ví dụ: Laravel là gì? hoặc chọn câu trả lời đúng..."
                                                      class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-2.5 bg-white"></textarea>
                                        </div>

                                        <!-- 4 phương án -->
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[11px] font-bold text-slate-700">Các phương án trả lời <span class="text-rose-500">*</span></span>
                                                <span class="text-[10px] text-pink-600 font-semibold">Tích chọn nút tròn cho đáp án ĐÚNG</span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <template x-for="(opt, optIndex) in q.options" :key="optIndex">
                                                    <div class="flex items-center gap-2 p-1.5 rounded-xl border transition"
                                                         :class="opt.is_correct ? 'bg-emerald-50/80 border-emerald-300 ring-1 ring-emerald-400/40' : 'bg-white border-slate-200'">
                                                        <label class="flex items-center gap-1.5 cursor-pointer shrink-0 pl-1" title="Chọn làm đáp án đúng">
                                                            <input type="radio" :name="'admin_manual_correct_' + qIndex" 
                                                                   :checked="opt.is_correct"
                                                                   @change="setCorrectOption(qIndex, optIndex)"
                                                                   class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                                            <span class="w-5 h-5 rounded-md flex items-center justify-center font-bold text-[10px]"
                                                                  :class="opt.is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
                                                                  x-text="String.fromCharCode(65 + optIndex)"></span>
                                                        </label>
                                                        <input type="text" x-model="opt.option_text"
                                                               :placeholder="'Phương án ' + String.fromCharCode(65 + optIndex) + '...'"
                                                               class="w-full text-xs py-1.5 px-2 rounded-lg border-0 bg-transparent focus:ring-0 text-slate-800 font-medium placeholder:text-slate-400">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Lời giải thích -->
                                        <div>
                                            <label class="block text-[11px] font-medium text-slate-500 mb-1">
                                                Lời giải thích đáp án (Tùy chọn)
                                            </label>
                                            <input type="text" x-model="q.explanation"
                                                   placeholder="Giải thích vì sao đáp án này đúng..."
                                                   class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-2 bg-white text-slate-700 placeholder:text-slate-400">
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <button type="button" @click="addManualQuestion()" class="text-xs text-pink-600 hover:text-pink-800 font-bold flex items-center gap-1">
                                    
                                    Thêm câu hỏi tiếp theo
                                </button>
                                <span class="text-xs text-slate-500 font-semibold" x-text="manualQuestions.length + ' câu hỏi đã soạn'"></span>
                            </div>
                        </div>

                        <!-- PANEL 4: TẠO CÂU HỎI TỰ ĐỘNG BẰNG AI -->
                        <div x-show="mode === 'ai'" x-transition class="space-y-5 pt-3 border-t border-slate-100">
                            <!-- Khối 1: Chọn Nhà cung cấp AI & Mô hình -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>1. Nhà cung cấp AI (Provider) <span class="text-rose-500">*</span></span>
                                    </label>
                                    <select x-model="aiProvider" @change="onProviderChange()"
                                            class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-white">
                                        <option value="gemini">Google Gemini AI (Khuyên dùng - Miễn phí & Nhanh)</option>
                                        <option value="openai">OpenAI (ChatGPT - gpt-4o, gpt-4o-mini)</option>
                                        <option value="groq">GroqCloud (Siêu tốc LPU - Llama 3.1 & GPT-OSS)</option>
                                        <option value="openrouter">OpenRouter AI (Đa mô hình Claude, DeepSeek...)</option>
                                    </select>
                                    <p class="text-[11px] text-slate-400 mt-1" x-text="providerDescriptions[aiProvider]"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <span>2. Mô hình AI (Model) <span class="text-rose-500">*</span></span>
                                            <span x-show="isFetchingModels" class="text-[10px] text-pink-600 bg-pink-50 font-medium px-2 py-0.5 rounded-full flex items-center gap-1 animate-pulse" title="Đang tự động quét model">
                                                <svg class="w-3 h-3 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                                Tự động quét...
                                            </span>
                                        </span>
                                        <button type="button" @click="fetchModelsFromKey(false)"
                                                :disabled="isFetchingModels || !aiApiKey"
                                                :class="!aiApiKey ? 'text-slate-400 cursor-not-allowed' : 'text-pink-600 hover:text-pink-700 cursor-pointer'"
                                                class="text-[11px] normal-case font-semibold flex items-center gap-1 transition"
                                                title="Quét lại danh sách mô hình từ API Key">
                                            <svg class="w-3.5 h-3.5" :class="isFetchingModels ? 'animate-spin text-pink-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span x-text="isFetchingModels ? 'Đang quét...' : 'Quét lại'"></span>
                                        </button>
                                    </label>
                                    <select x-model="aiModel"
                                            class="w-full text-xs font-semibold rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 bg-white">
                                        <template x-for="m in (providerModels[aiProvider] || [])" :key="m.id">
                                            <option :value="m.id" x-text="m.name"></option>
                                        </template>
                                    </select>
                                    <!-- Input model tùy chỉnh nếu chọn custom -->
                                    <div x-show="aiModel === 'custom'" class="mt-2">
                                        <input type="text" x-model="aiCustomModel" placeholder="Nhập mã model tùy chỉnh..."
                                               class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                    </div>
                                </div>
                            </div>

                            <!-- Khối 2: Nhập API Key -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label for="ai_api_key_input" class="block text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        <span>Gắn API Key của bạn <span class="text-rose-500">*</span></span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <template x-if="aiProvider === 'gemini'">
                                            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer"
                                               class="text-[11px] text-pink-600 hover:text-pink-700 underline font-semibold flex items-center gap-1">
                                                Lấy Gemini API Key miễn phí &rarr;
                                            </a>
                                        </template>
                                        <template x-if="aiProvider === 'openai'">
                                            <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer"
                                               class="text-[11px] text-pink-600 hover:text-pink-700 underline font-semibold flex items-center gap-1">
                                                Lấy OpenAI API Key &rarr;
                                            </a>
                                        </template>
                                        <template x-if="aiProvider === 'groq'">
                                            <a href="https://console.groq.com/keys" target="_blank" rel="noopener noreferrer"
                                               class="text-[11px] text-pink-600 hover:text-pink-700 underline font-semibold flex items-center gap-1">
                                                Lấy Groq API Key &rarr;
                                            </a>
                                        </template>
                                        <template x-if="aiProvider === 'openrouter'">
                                            <a href="https://openrouter.ai/keys" target="_blank" rel="noopener noreferrer"
                                               class="text-[11px] text-pink-600 hover:text-pink-700 underline font-semibold flex items-center gap-1">
                                                Lấy OpenRouter Key &rarr;
                                            </a>
                                        </template>
                                    </div>
                                </div>

                                <div class="relative">
                                    <input :type="aiShowApiKey ? 'text' : 'password'"
                                           id="ai_api_key_input"
                                           x-model="aiApiKey"
                                           @input="onApiKeyInput()"
                                           @change="onApiKeyChange()"
                                           @paste="onApiKeyPaste()"
                                           :placeholder="providerPlaceholders[aiProvider] || 'Dán mã API Key của bạn vào đây...'"
                                           class="w-full font-mono text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2.5 pl-3.5 pr-20 bg-white shadow-2xs">
                                    <button type="button" @click="aiShowApiKey = !aiShowApiKey"
                                            class="absolute inset-y-0 right-2 px-2.5 my-1.5 flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 rounded-lg">
                                        <span x-text="aiShowApiKey ? 'Ẩn' : 'Hiện'"></span>
                                    </button>
                                </div>

                                <!-- Trạng thái quét tự động -->
                                <div x-show="isFetchingModels" x-transition class="text-[11px] text-pink-600 font-medium flex items-center gap-1.5 pt-0.5">
                                    <svg class="w-3.5 h-3.5 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span>Đang tự động kết nối và quét danh sách mô hình từ API Key của bạn...</span>
                                </div>
                                <div x-show="!isFetchingModels && lastFetchedSuccess" x-transition class="text-[11px] text-emerald-600 font-medium flex items-center gap-1.5 pt-0.5">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <span>Đã tự động nạp <strong x-text="fetchedModelsCount"></strong> mô hình khả dụng từ tài khoản của bạn.</span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500">
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" x-model="aiSaveKeyToBrowser" class="rounded text-pink-600 border-slate-300 focus:ring-pink-500 w-3.5 h-3.5">
                                        <span>Ghi nhớ API Key trên trình duyệt của tôi (chỉ lưu trong LocalStorage máy bạn)</span>
                                    </label>
                                    <span class="text-slate-400">Không lưu vĩnh viễn trên máy chủ</span>
                                </div>
                            </div>

                            <!-- Khối 3: Nội dung tài liệu bài giảng / Chủ đề -->
                            <div>
                                <label for="ai_content_input" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                    <span>Nội dung tài liệu / Chủ đề cần tạo câu hỏi <span class="text-rose-500">*</span></span>
                                    <span class="text-[11px] text-slate-400 font-normal" x-text="aiContent.length + ' ký tự'"></span>
                                </label>
                                <textarea id="ai_content_input" rows="7" x-model="aiContent"
                                          placeholder="Dán đề cương, nội dung tóm tắt bài giảng, slide bài học, hoặc các kiến thức bạn muốn kiểm tra... Ví dụ:&#10;- Các khái niệm cơ bản về Eloquent ORM trong Laravel&#10;- Quan hệ HasOne, HasMany, BelongsTo, BelongsToMany&#10;- Eager Loading với phương thức with() để chống lỗi N+1 Query..."
                                          class="w-full text-xs rounded-2xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-3.5 bg-white shadow-2xs placeholder:text-slate-400"></textarea>
                            </div>

                            <!-- Khối 4: Cấu hình số lượng & mức độ câu hỏi AI -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-pink-50/50 border border-pink-100">
                                <div>
                                    <label for="ai_question_count_input" class="block text-xs font-bold text-slate-800 mb-1">
                                        Số lượng câu hỏi muốn tạo
                                    </label>
                                    <input type="number" 
                                           id="ai_question_count_input"
                                           min="1" 
                                           step="1"
                                           x-model.number="aiQuestionCount"
                                           @keydown="if(['-','+','e','E','.',','].includes($event.key)){ $event.preventDefault(); }"
                                           @input="if(aiQuestionCount !== '' && aiQuestionCount !== null) { aiQuestionCount = Math.max(1, Math.floor(Math.abs(aiQuestionCount) || 1)); }"
                                           placeholder="Nhập số câu (VD: 10, 20...)"
                                           class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                    <p class="text-[10px] text-slate-400 mt-1">Số nguyên dương (không nhập số âm, float).</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">
                                        Độ khó câu hỏi
                                    </label>
                                    <select x-model="aiDifficulty" class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                        <option value="medium" selected>Tiêu chuẩn / Trung bình</option>
                                        <option value="easy">Cơ bản / Nhận biết</option>
                                        <option value="hard">Nâng cao / Vận dụng cao</option>
                                        <option value="mixed">Đa dạng (Cơ bản đến nâng cao)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-800 mb-1">
                                        Chế độ nạp câu hỏi
                                    </label>
                                    <select x-model="aiAppendMode" class="w-full text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 py-2 bg-white">
                                        <option :value="false">Thay thế danh sách câu hỏi hiện tại</option>
                                        <option :value="true">Thêm tiếp vào câu hỏi đã có (Tạo thêm)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Banner Thông báo lỗi AI nếu có -->
                            <div x-show="aiError" x-transition class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <span class="font-bold">Lỗi tạo câu hỏi: </span>
                                        <span x-text="aiError"></span>
                                    </div>
                                </div>
                                <button type="button" @click="aiError = ''" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
                            </div>

                            <!-- Banner Thông báo thành công -->
                            <div x-show="aiSuccess" x-transition class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <div>
                                        <span class="font-bold">Thành công! </span>
                                        <span x-text="aiSuccess"></span>
                                    </div>
                                </div>
                                <button type="button" @click="aiSuccess = ''" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
                            </div>

                            <!-- Nút hành động Bắt đầu tạo câu hỏi với AI -->
                            <div class="flex items-center justify-between pt-2">
                                <span class="text-xs text-slate-500 font-medium">
                                    Mẹo: Bạn có thể tạo nhiều đợt câu hỏi từ các tài liệu khác nhau để tạo ngân hàng đề phong phú.
                                </span>
                                <button type="button" @click="generateWithAi()" :disabled="isGeneratingAi"
                                        :class="isGeneratingAi ? 'opacity-70 cursor-not-allowed' : 'hover:bg-pink-700 active:scale-[0.99]'"
                                        class="px-6 py-2.5 rounded-xl bg-pink-600 text-white font-bold text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center">
                                    <template x-if="isGeneratingAi">
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Đang tạo câu hỏi với AI...</span>
                                        </span>
                                    </template>
                                    <template x-if="!isGeneratingAi">
                                        <span>Tạo câu hỏi với AI</span>
                                    </template>
                                </button>
                            </div>

                            <!-- LIVE PREVIEW PARSED QUESTIONS KHI TẠO XONG TỪ AI -->
                            <div x-show="parsedQuestions.length > 0" x-transition class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-4 pt-4 mt-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                        <h4 class="font-bold text-emerald-900 text-sm">
                                            Đã nạp thành công <span class="text-emerald-700 font-black" x-text="parsedQuestions.length"></span> câu hỏi trắc nghiệm vào đề thi!
                                        </h4>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="setMode('manual')" class="text-xs text-pink-600 hover:text-pink-800 underline font-semibold">
                                            Chuyển sang sửa thủ công &rarr;
                                        </button>
                                        <span class="text-slate-300">|</span>
                                        <button type="button" @click="setMode('paste')" class="text-xs text-pink-600 hover:text-pink-800 underline font-semibold">
                                            Xem mã JSON &rarr;
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                                    <template x-for="(q, idx) in parsedQuestions" :key="idx">
                                        <div class="p-3 bg-white rounded-xl border border-emerald-100 text-xs space-y-1.5 shadow-xs">
                                            <div class="font-bold text-slate-800 flex items-start gap-2">
                                                <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0" x-text="idx + 1"></span>
                                                <span x-text="q.question_text"></span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 pl-7">
                                                <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                                                    <div :class="opt.is_correct ? 'bg-emerald-50 text-emerald-800 font-bold border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200'"
                                                         class="px-2 py-1 rounded-lg border text-[11px] flex items-center justify-between">
                                                        <span x-text="String.fromCharCode(65 + oIdx) + '. ' + opt.option_text"></span>
                                                        <span x-show="opt.is_correct" class="text-emerald-600 text-[10px]">✓ Đúng</span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div x-show="q.explanation" class="pl-7 text-[11px] text-slate-500 italic">
                                                Giải thích: <span x-text="q.explanation"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Chi tiết đề thi -->
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-6 sm:p-7 space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-bold text-slate-900">
                                3. Thông số đề thi & Trạng thái duyệt
                            </h3>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-pink-50 text-pink-700">
                                Quyền Quản trị viên
                            </span>
                        </div>

                        <!-- Tiêu đề Quiz -->
                        <div>
                            <label for="title" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Tiêu đề bài kiểm tra <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="title" x-model="quizTitle" required 
                                   placeholder="Ví dụ: Kiểm tra kiến thức đánh giá cuối kỳ" 
                                   class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2.5">
                            @error('title')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mô tả bài kiểm tra -->
                        <div>
                            <label for="description" class="block text-sm font-bold text-slate-900 mb-1.5">
                                Mô tả / Lưu ý cho thí sinh
                            </label>
                            <textarea name="description" id="description" rows="3" x-model="quizDescription"
                                      placeholder="Nhập nội dung tóm tắt hoặc lưu ý cho sinh viên khi làm bài..."
                                      class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm"></textarea>
                            @error('description')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 3 Cột: Thời gian làm bài, Điểm đạt & Trạng thái duyệt -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <div>
                                <label for="duration_minutes" class="block text-sm font-bold text-slate-900 mb-1.5">
                                    Thời gian (Phút) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="duration_minutes" id="duration_minutes" x-model="durationMinutes" min="1" max="300" required 
                                       class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                <p class="text-slate-400 text-xs mt-1">Đếm ngược khi thi.</p>
                                @error('duration_minutes')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="passing_score" class="block text-sm font-bold text-slate-900 mb-1.5">
                                    Điểm đạt (Thang 10) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" step="0.5" name="passing_score" id="passing_score" x-model="passingScore" min="0" max="10" required 
                                       class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                <p class="text-slate-400 text-xs mt-1">Điểm tối thiểu để Đạt.</p>
                                @error('passing_score')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="question_limit" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center justify-between">
                                    <span>Số câu mỗi lượt</span>
                                    <span class="text-[11px] font-semibold text-pink-600" x-show="totalQuestions > 0" x-text="'Kho: ' + totalQuestions"></span>
                                </label>
                                <input type="number" name="question_limit" id="question_limit" x-model="questionLimit" min="1" :max="totalQuestions > 0 ? totalQuestions : ''"
                                       placeholder="Tất cả (mặc định)"
                                       :class="isQuestionLimitInvalid ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500 bg-rose-50/30' : 'border-slate-200 focus:border-pink-600 focus:ring-pink-500'"
                                       class="w-full rounded-xl text-sm py-2">
                                <p class="text-slate-400 text-xs mt-1" x-show="!isQuestionLimitInvalid">
                                    Để trống để thi tất cả câu.
                                </p>
                                <p class="text-rose-500 text-xs mt-1 font-semibold" x-show="isQuestionLimitInvalid" x-cloak>
                                    ⚠️ Vượt quá <span x-text="totalQuestions"></span> câu!
                                </p>
                                @error('question_limit')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-bold text-slate-900 mb-1.5">
                                    Trạng thái bài thi <span class="text-rose-500">*</span>
                                </label>
                                <select name="status" id="status" class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                    <option value="approved" selected>Đã duyệt (Hiển thị ngay)</option>
                                    <option value="pending">Chờ duyệt (Pending)</option>
                                    <option value="rejected">Từ chối / Tạm ẩn</option>
                                </select>
                                <p class="text-slate-400 text-xs mt-1">Mặc định đã duyệt.</p>
                                @error('status')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Thời gian mở và hạn chót làm bài kiểm tra -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="starts_at" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Bắt đầu mở từ ngày <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                                </label>
                                <input type="datetime-local" name="starts_at" id="starts_at" x-model="startsAt"
                                       class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2 bg-white">
                                <p class="text-slate-400 text-xs mt-1">Học viên chỉ có thể vào làm từ thời điểm này (để trống nếu mở ngay).</p>
                                @error('starts_at')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ends_at" class="block text-sm font-bold text-slate-900 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Hạn chót kết thúc <span class="text-xs font-normal text-slate-400">(Tùy chọn)</span>
                                </label>
                                <input type="datetime-local" name="ends_at" id="ends_at" x-model="endsAt"
                                       class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2 bg-white">
                                <p class="text-slate-400 text-xs mt-1">Sau thời gian này đề sẽ quá hạn, học viên không thể vào làm được nữa.</p>
                                <p x-cloak x-show="startsAt && endsAt && new Date(endsAt) < new Date(startsAt)" class="text-rose-500 text-xs mt-1 font-medium">Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.</p>
                                <p x-cloak x-show="startsAt && endsAt && new Date(endsAt) >= new Date(startsAt) && (new Date(endsAt) - new Date(startsAt)) < 3600000" class="text-rose-500 text-xs mt-1 font-medium">Thời hạn làm bài ít nhất là 1 tiếng.</p>
                                @error('ends_at')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Đảo câu hỏi -->
                        <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="randomize_questions" value="1" x-model="randomizeQuestions"
                                       class="rounded text-pink-600 border-slate-300 focus:ring-pink-500 w-5 h-5 mt-0.5">
                                <div>
                                    <span class="font-bold text-slate-900 text-sm block">Đảo ngẫu nhiên thứ tự câu hỏi khi học viên làm bài</span>
                                    <span class="text-slate-500 text-xs block leading-relaxed mt-0.5">
                                        Tự động xáo trộn thứ tự câu hỏi cho từng học viên nhằm chống gian lận và tăng tính khách quan.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <!-- Có thể xem kết quả sau khi hoàn thành (Có hoặc Không) -->
                        <div class="p-4 rounded-2xl bg-pink-50/70 border border-pink-200/80">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <span class="font-bold text-slate-900 text-sm block">Có thể xem kết quả sau khi hoàn thành</span>
                                    <span class="text-slate-500 text-xs block leading-relaxed mt-0.5">
                                        Cho phép học viên xem lại kết quả và đáp án chi tiết sau khi nộp bài.
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 shrink-0 bg-white px-3.5 py-1.5 rounded-xl border border-pink-200">
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="show_result_after_completion" value="1" x-model="showResultAfterCompletion"
                                               class="text-pink-600 border-slate-300 focus:ring-pink-500 w-4 h-4">
                                        <span class="text-xs font-bold text-slate-800">Có</span>
                                    </label>
                                    <span class="text-slate-300 text-xs">|</span>
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="show_result_after_completion" value="0" x-model="showResultAfterCompletion"
                                               class="text-pink-600 border-slate-300 focus:ring-pink-500 w-4 h-4">
                                        <span class="text-xs font-bold text-slate-800">Không</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('admin.quizzes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-sm font-semibold transition">
                            Hủy bỏ
                        </a>
                        <button type="submit" id="submit-admin-quiz-btn" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-75 cursor-not-allowed pointer-events-none' : ''" class="px-7 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 hover:shadow-pink-500/40 transition flex items-center gap-2">
                            <span x-show="!isSubmitting">Lưu đề thi (Admin)</span>
                            <span x-show="isSubmitting" class="inline-flex items-center gap-2">
                                
                                <span>Đang xử lý tạo bài thi...</span>
                            </span>
                            <span x-show="!isSubmitting && parsedQuestions.length > 0" class="bg-pink-500 px-2 py-0.5 rounded-full text-xs" x-text="'+ ' + parsedQuestions.length + ' câu hỏi'"></span>
                            <span x-show="!isSubmitting">&rarr;</span>
                        </button>
                    </div>
                </form>
        </div>

    <!-- Alpine.js Script xử lý JSON Paste & Upload -->
    <script>
        function adminQuizCreateHandler() {
            return {
                mode: 'paste', // 'paste', 'file', 'manual'
                jsonText: @json(old('json_content', '')),
                fileName: '',
                fileSizeText: '',
                quizType: @json(old('type', 'quiz')),
                quizTitle: @json(old('title', '')),
                quizDescription: @json(old('description', '')),
                durationMinutes: @json(old('duration_minutes', 15)),
                passingScore: @json(old('passing_score', 5.0)),
                questionLimit: @json(old('question_limit', '')),
                startsAt: @json(old('starts_at', '')),
                endsAt: @json(old('ends_at', '')),
                randomizeQuestions: true,
                showResultAfterCompletion: @json(old('show_result_after_completion', '1')),
                parsedQuestions: [],
                parseError: '',
                timer: null,
                isSubmitting: false,

                // Cấu hình AI tạo câu hỏi
                aiProvider: 'gemini',
                aiModel: 'gemini-2.0-flash',
                aiCustomModel: '',
                aiApiKey: '',
                aiContent: '',
                aiQuestionCount: 10,
                aiDifficulty: 'medium',
                aiAppendMode: false,
                aiShowApiKey: false,
                aiSaveKeyToBrowser: true,
                isGeneratingAi: false,
                isFetchingModels: false,
                lastFetchedKey: '',
                lastFetchedSuccess: false,
                fetchedModelsCount: 0,
                apiKeyDebounceTimer: null,
                aiError: '',
                aiSuccess: '',

                providerDescriptions: {
                    gemini: 'Mô hình tiên tiến của Google, tốc độ cao, hỗ trợ tiếng Việt mượt mà và hoàn toàn miễn phí.',
                    openai: 'Mô hình GPT-4o / GPT-4o-mini tiêu chuẩn chất lượng cao của OpenAI.',
                    groq: 'Nền tảng xử lý AI siêu tốc Groq LPU (Llama 3.1, GPT-OSS 120B... phản hồi hàng trăm token/s).',
                    openrouter: 'Cổng AI đa mô hình toàn cầu (DeepSeek V3, Claude 3.5, Llama 3.3, Gemini...).'
                },

                providerPlaceholders: {
                    gemini: 'Dán Google Gemini API Key (Bắt đầu bằng AIzaSy...)',
                    openai: 'Dán OpenAI API Key (Bắt đầu bằng sk-...)',
                    groq: 'Dán Groq API Key (Bắt đầu bằng gsk_...)',
                    openrouter: 'Dán OpenRouter API Key (Bắt đầu bằng sk-or-v1-...)'
                },

                providerModels: {
                    gemini: [
                        { id: 'gemini-2.0-flash', name: 'Gemini 2.0 Flash (Khuyên dùng - Siêu nhanh & Thông minh)' },
                        { id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash (Tiêu chuẩn, hạn mức cao)' },
                        { id: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro (Phân tích chuyên sâu)' },
                        { id: 'custom', name: 'Nhập model khác...' }
                    ],
                    openai: [
                        { id: 'gpt-4o-mini', name: 'GPT-4o Mini (Khuyên dùng - Nhanh & Tiết kiệm)' },
                        { id: 'gpt-4o', name: 'GPT-4o (Đỉnh cao phân tích lý luận)' },
                        { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo (Cơ bản)' },
                        { id: 'custom', name: 'Nhập model khác...' }
                    ],
                    groq: [
                        { id: 'llama-3.1-8b-instant', name: 'Llama 3.1 8B Instant (Khuyên dùng - Cực nhanh & Ổn định)' },
                        { id: 'openai/gpt-oss-120b', name: 'OpenAI GPT-OSS 120B (Thông minh nhất trên Groq)' },
                        { id: 'openai/gpt-oss-20b', name: 'OpenAI GPT-OSS 20B (Nhanh & Tối ưu chi phí)' },
                        { id: 'deepseek-r1-distill-llama-70b', name: 'DeepSeek R1 Distill 70B (Suy luận logic)' },
                        { id: 'gemma2-9b-it', name: 'Google Gemma 2 9B (Nhẹ & Phản hồi nhanh)' },
                        { id: 'custom', name: 'Nhập model khác...' }
                    ],
                    openrouter: [
                        { id: 'deepseek/deepseek-chat', name: 'DeepSeek V3 (Rất thông minh)' },
                        { id: 'google/gemini-2.0-flash-exp:free', name: 'Gemini 2.0 Flash (Free qua OpenRouter)' },
                        { id: 'meta-llama/llama-3.3-70b-instruct', name: 'Meta Llama 3.3 70B' },
                        { id: 'anthropic/claude-3.5-sonnet', name: 'Claude 3.5 Sonnet' },
                        { id: 'custom', name: 'Nhập model khác...' }
                    ]
                },

                get totalQuestions() {
                    if (this.mode === 'manual') {
                        return this.manualQuestions.filter(q => q.question_text && q.question_text.trim() !== '').length;
                    }
                    return this.parsedQuestions.length;
                },

                get isQuestionLimitInvalid() {
                    if (!this.questionLimit) return false;
                    const limit = parseInt(this.questionLimit, 10);
                    const total = this.totalQuestions;
                    return total > 0 && limit > total;
                },

                manualQuestions: [
                    {
                        question_text: '',
                        explanation: '',
                        options: [
                            { option_text: '', is_correct: true },
                            { option_text: '', is_correct: false },
                            { option_text: '', is_correct: false },
                            { option_text: '', is_correct: false }
                        ]
                    }
                ],

                init() {
                    this.loadSavedApiKey();

                    if (this.jsonText) {
                        this.parseJsonString(this.jsonText);
                    }
                },

                loadSavedApiKey() {
                    try {
                        const savedKey = localStorage.getItem('lms_ai_key_' + this.aiProvider);
                        if (savedKey) {
                            this.aiApiKey = savedKey;
                            this.$nextTick(() => {
                                if (this.aiApiKey && this.aiApiKey.trim().length >= 10) {
                                    this.fetchModelsFromKey(true);
                                }
                            });
                        } else {
                            this.aiApiKey = '';
                            this.lastFetchedKey = '';
                            this.lastFetchedSuccess = false;
                        }
                    } catch (e) {
                        // ignore local storage restrictions
                    }
                },

                onProviderChange() {
                    const models = this.providerModels[this.aiProvider];
                    if (models && models.length > 0) {
                        this.aiModel = models[0].id;
                    }
                    this.lastFetchedKey = '';
                    this.lastFetchedSuccess = false;
                    this.loadSavedApiKey();
                    this.aiError = '';
                    this.aiSuccess = '';
                },

                onApiKeyInput() {
                    this.lastFetchedSuccess = false;
                    clearTimeout(this.apiKeyDebounceTimer);
                    const key = (this.aiApiKey || '').trim();
                    if (!key || key.length < 15) {
                        return;
                    }
                    this.apiKeyDebounceTimer = setTimeout(() => {
                        this.fetchModelsFromKey(true);
                    }, 600);
                },

                onApiKeyChange() {
                    clearTimeout(this.apiKeyDebounceTimer);
                    const key = (this.aiApiKey || '').trim();
                    if (key && key.length >= 10) {
                        this.fetchModelsFromKey(false);
                    }
                },

                onApiKeyPaste() {
                    this.$nextTick(() => {
                        clearTimeout(this.apiKeyDebounceTimer);
                        const key = (this.aiApiKey || '').trim();
                        if (key && key.length >= 10) {
                            this.fetchModelsFromKey(false);
                        }
                    });
                },

                syncParsedToJson() {
                    const payload = {
                        title: this.quizTitle || 'Bài kiểm tra trắc nghiệm AI',
                        description: this.quizDescription || '',
                        duration_minutes: parseInt(this.durationMinutes, 10) || 15,
                        passing_score: parseFloat(this.passingScore) || 5.0,
                        question_limit: this.questionLimit ? parseInt(this.questionLimit, 10) : null,
                        randomize_questions: this.randomizeQuestions,
                        show_result_after_completion: this.showResultAfterCompletion === '1',
                        questions: this.parsedQuestions
                    };
                    this.jsonText = JSON.stringify(payload, null, 2);
                },

                syncParsedToManual() {
                    if (this.parsedQuestions && this.parsedQuestions.length > 0) {
                        this.manualQuestions = this.parsedQuestions.map(q => ({
                            question_text: q.question_text || '',
                            explanation: q.explanation || '',
                            options: (q.options || []).map(o => ({
                                option_text: o.option_text || '',
                                is_correct: Boolean(o.is_correct)
                            }))
                        }));
                    }
                },

                async generateWithAi() {
                    this.aiError = '';
                    this.aiSuccess = '';

                    if (!this.aiApiKey || !this.aiApiKey.trim()) {
                        this.aiError = 'Vui lòng nhập API Key để kết nối với dịch vụ AI.';
                        return;
                    }

                    if (!this.aiContent || !this.aiContent.trim()) {
                        this.aiError = 'Vui lòng nhập nội dung tài liệu hoặc chủ đề cần tạo câu hỏi.';
                        return;
                    }

                    if (this.aiModel === 'custom' && (!this.aiCustomModel || !this.aiCustomModel.trim())) {
                        this.aiError = 'Vui lòng nhập mã mô hình tùy chỉnh.';
                        return;
                    }

                    const rawCount = Number(this.aiQuestionCount);
                    if (!this.aiQuestionCount || isNaN(rawCount) || rawCount <= 0 || !Number.isInteger(rawCount)) {
                        this.aiError = 'Số lượng câu hỏi phải là số nguyên dương hợp lệ (từ 1 trở lên), không được nhập số âm hay số thập phân.';
                        return;
                    }

                    if (this.aiSaveKeyToBrowser) {
                        try {
                            localStorage.setItem('lms_ai_key_' + this.aiProvider, this.aiApiKey.trim());
                        } catch (e) {
                            // ignore local storage error
                        }
                    }

                    this.isGeneratingAi = true;

                    const requestPayload = {
                        provider: this.aiProvider,
                        api_key: this.aiApiKey.trim(),
                        model: this.aiModel === 'custom' ? this.aiCustomModel.trim() : this.aiModel,
                        content: this.aiContent.trim(),
                        count: Math.floor(rawCount),
                        difficulty: this.aiDifficulty || 'medium'
                    };

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const response = await fetch('{{ route('admin.quizzes.generate-ai-questions') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(requestPayload)
                        });

                        const data = await response.json();

                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Không thể tạo câu hỏi từ AI. Vui lòng kiểm tra lại API Key hoặc nội dung.');
                        }

                        const newQuestions = data.questions || [];
                        if (newQuestions.length === 0) {
                            throw new Error('AI không trả về câu hỏi hợp lệ nào. Vui lòng thử lại với nội dung rõ ràng hơn.');
                        }

                        if (this.aiAppendMode && this.parsedQuestions.length > 0) {
                            this.parsedQuestions = [...this.parsedQuestions, ...newQuestions];
                        } else {
                            this.parsedQuestions = newQuestions;
                        }

                        if (data.quiz_title && !this.quizTitle) {
                            this.quizTitle = data.quiz_title;
                        }

                        this.syncParsedToJson();
                        this.syncParsedToManual();

                        this.aiSuccess = `Đã tạo thành công ${newQuestions.length} câu hỏi bằng AI! Tổng ngân hàng hiện có: ${this.parsedQuestions.length} câu hỏi.`;
                    } catch (err) {
                        this.aiError = err.message || 'Đã xảy ra lỗi không xác định khi kết nối với AI.';
                    } finally {
                        this.isGeneratingAi = false;
                    }
                },

                async fetchModelsFromKey(isAuto = false) {
                    const key = (this.aiApiKey || '').trim();
                    if (!key) {
                        if (!isAuto) {
                            this.aiError = 'Vui lòng nhập API Key trước khi quét danh sách model.';
                        }
                        return;
                    }

                    const cacheKey = this.aiProvider + ':::' + key;
                    if (this.lastFetchedKey === cacheKey && isAuto) {
                        return; // Đã quét thành công cho key này rồi thì không quét lại khi auto
                    }

                    if (this.isFetchingModels) {
                        return;
                    }

                    this.isFetchingModels = true;
                    if (!isAuto) {
                        this.aiError = '';
                        this.aiSuccess = '';
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const response = await fetch('{{ route('admin.quizzes.fetch-ai-models') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                provider: this.aiProvider,
                                api_key: key
                            })
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw new Error(data.message || 'Không thể quét danh sách model từ API Key.');
                        }

                        if (data.models && data.models.length > 0) {
                            this.lastFetchedKey = cacheKey;
                            this.lastFetchedSuccess = true;
                            this.fetchedModelsCount = data.models.length;

                            this.providerModels[this.aiProvider] = [
                                ...data.models,
                                { id: 'custom', name: 'Nhập model khác...' }
                            ];
                            const currentExists = data.models.some(m => m.id === this.aiModel);
                            if (!currentExists || this.aiModel === 'custom') {
                                const preferred = data.models.find(m => m.id === 'llama-3.1-8b-instant')
                                    || data.models.find(m => m.id.includes('llama-3.1-8b'))
                                    || data.models.find(m => m.id.includes('gpt-oss-120b'))
                                    || data.models[0];
                                this.aiModel = preferred ? preferred.id : data.models[0].id;
                            }
                            this.aiError = '';

                            if (this.aiSaveKeyToBrowser) {
                                try {
                                    localStorage.setItem('lms_ai_key_' + this.aiProvider, key);
                                } catch (e) {
                                    // ignore storage restriction
                                }
                            }

                            if (!isAuto) {
                                this.aiSuccess = `Đã tìm thấy ${data.models.length} model khả dụng trong tài khoản ${this.aiProvider.toUpperCase()} của bạn!`;
                            }
                        } else {
                            throw new Error('Không tìm thấy model nào khả dụng cho API Key này.');
                        }
                    } catch (err) {
                        this.lastFetchedSuccess = false;
                        if (!isAuto) {
                            this.aiError = err.message || 'Lỗi khi quét danh sách model từ API Key.';
                        } else if (key.length >= 25) {
                            this.aiError = err.message || 'Không thể quét model từ API Key này.';
                        }
                    } finally {
                        this.isFetchingModels = false;
                    }
                },

                setMode(newMode) {
                    this.mode = newMode;
                },

                addManualQuestion() {
                    this.manualQuestions.push({
                        question_text: '',
                        explanation: '',
                        options: [
                            { option_text: '', is_correct: true },
                            { option_text: '', is_correct: false },
                            { option_text: '', is_correct: false },
                            { option_text: '', is_correct: false }
                        ]
                    });
                },

                removeManualQuestion(index) {
                    if (this.manualQuestions.length > 1) {
                        this.manualQuestions.splice(index, 1);
                    }
                },

                setCorrectOption(qIdx, optIdx) {
                    this.manualQuestions[qIdx].options.forEach((opt, idx) => {
                        opt.is_correct = (idx === optIdx);
                    });
                },

                handleFormSubmit(e) {
                    if (this.isSubmitting) {
                        e.preventDefault();
                        return false;
                    }

                    if (this.isQuestionLimitInvalid) {
                        alert(`Số câu mỗi lượt thi (${this.questionLimit}) không thể lớn hơn tổng số câu hỏi đang có (${this.totalQuestions} câu)!`);
                        e.preventDefault();
                        return false;
                    }

                    if (this.startsAt && this.endsAt) {
                        const diffMs = new Date(this.endsAt) - new Date(this.startsAt);
                        if (diffMs < 0) {
                            alert('Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.');
                            e.preventDefault();
                            return false;
                        }
                        if (diffMs < 3600000) {
                            alert('Thời hạn làm bài ít nhất là 1 tiếng.');
                            e.preventDefault();
                            return false;
                        }
                    }

                    if (this.mode === 'manual') {
                        const valid = this.manualQuestions.filter(q => q.question_text && q.question_text.trim() !== '');
                        if (valid.length > 0) {
                            for (let i = 0; i < valid.length; i++) {
                                const filledOpts = valid[i].options.filter(o => o.option_text && o.option_text.trim() !== '');
                                if (filledOpts.length < 2) {
                                    alert('Câu hỏi #' + (i + 1) + ' cần có ít nhất 2 phương án trả lời.');
                                    e.preventDefault();
                                    return false;
                                }
                                const hasCorrect = valid[i].options.some(o => o.is_correct && o.option_text && o.option_text.trim() !== '');
                                if (!hasCorrect) {
                                    const firstFilled = valid[i].options.find(o => o.option_text && o.option_text.trim() !== '');
                                    if (firstFilled) firstFilled.is_correct = true;
                                }
                            }
                            this.jsonText = JSON.stringify(valid);
                        } else {
                            this.jsonText = '';
                        }
                    }

                    this.isSubmitting = true;
                },

                loadSampleJson() {
                    const sample = {
                        "title": "Đề thi đánh giá chuyên sâu Laravel Framework",
                        "description": "Bài kiểm tra trắc nghiệm tổng hợp kiến thức nâng cao dành cho học viên.",
                        "duration_minutes": 30,
                        "passing_score": 6.0,
                        "randomize_questions": true,
                        "questions": [
                            {
                                "question_text": "Eloquent trong Laravel đóng vai trò gì?",
                                "explanation": "Eloquent là thư viện ORM mạnh mẽ mặc định của Laravel.",
                                "options": [
                                    "ORM (Object-Relational Mapping)",
                                    "Template Engine",
                                    "Routing Engine",
                                    "Database Driver"
                                ],
                                "correct_option": 0
                            },
                            {
                                "question_text": "Tập tin nào lưu trữ cấu hình môi trường bao gồm kết nối cơ sở dữ liệu?",
                                "explanation": "Tệp .env lưu biến môi trường nhạy cảm và cấu hình máy chủ.",
                                "options": [
                                    "config/database.php",
                                    ".env",
                                    "composer.json",
                                    "routes/web.php"
                                ],
                                "correct_option": 1
                            },
                            {
                                "question_text": "Middleware trong Laravel có nhiệm vụ chính là gì?",
                                "explanation": "Middleware lọc các HTTP request gửi đến ứng dụng trước khi chuyển tới Controller.",
                                "options": [
                                    "Chỉ dùng để kết nối database",
                                    "Lọc và kiểm tra các HTTP request trước khi tới Controller",
                                    "Biên dịch mã CSS/JS",
                                    "Chạy máy chủ web ảo"
                                ],
                                "correct_option": 1
                            }
                        ]
                    };

                    this.jsonText = JSON.stringify(sample, null, 2);
                    this.parseJsonString(this.jsonText);
                },

                clearJson() {
                    this.jsonText = '';
                    this.parsedQuestions = [];
                    this.parseError = '';
                },

                downloadSampleJson() {
                    this.loadSampleJson();
                    const blob = new Blob([this.jsonText], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'de_thi_admin_laravel.json';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                },

                debounceParseJson() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => {
                        this.parseJsonString(this.jsonText);
                    }, 300);
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.readFile(file);
                },

                handleFileDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (!file) return;
                    const input = document.getElementById('json_file');
                    if (input) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        input.files = dataTransfer.files;
                    }
                    this.readFile(file);
                },

                readFile(file) {
                    this.fileName = file.name;
                    this.fileSizeText = (file.size / 1024).toFixed(1) + ' KB';

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const text = e.target.result;
                        this.jsonText = text;
                        this.parseJsonString(text);
                    };
                    reader.readAsText(file);
                },

                parseJsonString(text) {
                    this.parseError = '';
                    if (!text || !text.trim()) {
                        this.parsedQuestions = [];
                        return;
                    }

                    try {
                        const data = JSON.parse(text);
                        let questionsRaw = [];
                        let quizObj = {};

                        if (Array.isArray(data)) {
                            questionsRaw = data;
                        } else if (typeof data === 'object' && data !== null) {
                            quizObj = data.quiz || data;
                            if (Array.isArray(data.questions)) {
                                questionsRaw = data.questions;
                            }
                        }

                        if (quizObj.title && !this.quizTitle) {
                            this.quizTitle = quizObj.title;
                        }
                        if (quizObj.type) {
                            const rawType = String(quizObj.type).toLowerCase().trim();
                            if (rawType.includes('midterm') || rawType.includes('giua')) {
                                this.quizType = 'midterm';
                            } else if (rawType.includes('final') || rawType.includes('cuoi')) {
                                this.quizType = 'final';
                            } else {
                                this.quizType = 'quiz';
                            }
                        }
                        if (quizObj.description && !this.quizDescription) {
                            this.quizDescription = quizObj.description;
                        }
                        if (quizObj.duration_minutes) {
                            this.durationMinutes = quizObj.duration_minutes;
                        }
                        if (quizObj.passing_score !== undefined) {
                            this.passingScore = quizObj.passing_score;
                        }
                        if (quizObj.question_limit !== undefined && !this.questionLimit) {
                            this.questionLimit = quizObj.question_limit;
                        }
                        if (quizObj.randomize_questions !== undefined) {
                            this.randomizeQuestions = Boolean(quizObj.randomize_questions);
                        }
                        if (quizObj.show_result_after_completion !== undefined) {
                            this.showResultAfterCompletion = quizObj.show_result_after_completion ? '1' : '0';
                        }
                        if (quizObj.starts_at && !this.startsAt) {
                            this.startsAt = quizObj.starts_at;
                        }
                        if (quizObj.ends_at && !this.endsAt) {
                            this.endsAt = quizObj.ends_at;
                        }

                        const parsed = [];
                        questionsRaw.forEach((q, idx) => {
                            if (!q || typeof q !== 'object') return;
                            const qText = q.question_text || q.question || q.title || '';
                            const rawOpts = q.options || q.answers || [];
                            const explain = q.explanation || q.explain || '';
                            
                            let correctIdx = 0;
                            if (q.correct_option !== undefined) {
                                if (typeof q.correct_option === 'number') {
                                    correctIdx = q.correct_option;
                                } else if (typeof q.correct_option === 'string') {
                                    const l = q.correct_option.trim().toUpperCase();
                                    const map = { 'A': 0, 'B': 1, 'C': 2, 'D': 3, 'E': 4 };
                                    if (map[l] !== undefined) correctIdx = map[l];
                                    else if (!isNaN(parseInt(l))) correctIdx = parseInt(l);
                                }
                            }

                            const opts = [];
                            const optItems = Array.isArray(rawOpts) ? rawOpts : Object.values(rawOpts);
                            optItems.forEach((o, oIdx) => {
                                let optText = '';
                                let isCorrect = (oIdx === correctIdx);
                                if (typeof o === 'object' && o !== null) {
                                    optText = o.option_text || o.text || o.content || '';
                                    if (o.is_correct) isCorrect = true;
                                } else {
                                    optText = String(o);
                                }
                                opts.push({
                                    option_text: optText,
                                    is_correct: isCorrect
                                });
                            });

                            if (qText && opts.length >= 2) {
                                parsed.push({
                                    question_text: qText,
                                    explanation: explain,
                                    options: opts
                                });
                            }
                        });

                        this.parsedQuestions = parsed;
                    } catch (e) {
                        this.parseError = e.message;
                        this.parsedQuestions = [];
                    }
                }
            };
        }
    </script>
</x-admin-layout>
