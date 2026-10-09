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
                                <div class="flex items-center justify-between mb-2">
                                    <label for="course_id" class="block text-sm font-bold text-slate-900">
                                        1. Áp dụng cho Khóa học nào?
                                    </label>
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        Hỗ trợ Tự do
                                    </span>
                                </div>
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
                                <p class="text-xs text-slate-400 mt-1.5">
                                    Chọn <strong>Tự do</strong> để mở cho tất cả học viên làm bài mà không bắt buộc phải đăng ký khóa học.
                                </p>
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
                                        <span class="text-lg mb-0.5"></span>
                                        <span class="text-xs leading-tight">Kiểm tra</span>
                                    </label>

                                    <!-- Option 2: Giữa kỳ -->
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                           :class="quizType === 'midterm' ? 'border-amber-600 bg-amber-50/80 ring-2 ring-amber-500/20 text-amber-900 font-bold' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700 font-medium'">
                                        <input type="radio" name="type" value="midterm" x-model="quizType" class="sr-only">
                                        <span class="text-lg mb-0.5">⏱️</span>
                                        <span class="text-xs leading-tight">Giữa kỳ</span>
                                    </label>

                                    <!-- Option 3: Cuối kỳ -->
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                           :class="quizType === 'final' ? 'border-rose-600 bg-rose-50/80 ring-2 ring-rose-500/20 text-rose-900 font-bold' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700 font-medium'">
                                        <input type="radio" name="type" value="final" x-model="quizType" class="sr-only">
                                        <span class="text-lg mb-0.5"></span>
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

                            <!-- Selector 3 tabs -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Option 1: Paste JSON -->
                                <button type="button" @click="setMode('paste')"
                                        :class="mode === 'paste' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-4 rounded-2xl border text-left transition flex items-start gap-3">
                                    <div :class="mode === 'paste' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0">
                                        
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Dán mã JSON</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Paste chuỗi JSON trực tiếp</div>
                                    </div>
                                </button>

                                <!-- Option 2: Upload File JSON -->
                                <button type="button" @click="setMode('file')"
                                        :class="mode === 'file' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-4 rounded-2xl border text-left transition flex items-start gap-3">
                                    <div :class="mode === 'file' ? 'bg-pink-600 text-white' : 'bg-slate-200 text-slate-600'" class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0">
                                        
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm">Tải file .JSON</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Upload tệp .json từ máy</div>
                                    </div>
                                </button>

                                <!-- Option 3: Manual Input -->
                                <button type="button" @click="setMode('manual')"
                                        :class="mode === 'manual' ? 'border-pink-600 bg-pink-50/60 ring-2 ring-pink-500/20 text-pink-900' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 text-slate-700'"
                                        class="p-4 rounded-2xl border text-left transition flex items-start gap-3">
                                    
                                    <div>
                                        <div class="font-bold text-sm">Nhập thủ công</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">Soạn trực tiếp khi tạo</div>
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
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
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
                randomizeQuestions: true,
                parsedQuestions: [],
                parseError: '',
                timer: null,
                isSubmitting: false,
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
                    if (this.jsonText) {
                        this.parseJsonString(this.jsonText);
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
                        if (quizObj.randomize_questions !== undefined) {
                            this.randomizeQuestions = Boolean(quizObj.randomize_questions);
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
