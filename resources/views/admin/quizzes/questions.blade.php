<x-admin-layout breadcrumb="Soạn đề thi Quiz">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold border {{ $quiz->type_badge_class }}">
                        {{ $quiz->type_name }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-pink-50 text-pink-700">
                        {{ $quiz->course ? 'Khóa học: ' . $quiz->course->title : 'Bài kiểm tra Tự do' }} | Quản trị viên
                    </span>
                </div>
                <h1 class="font-black text-2xl text-slate-900 tracking-tight">
                    Soạn đề thi: {{ $quiz->title }}
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                    &larr; Quay lại danh sách
                </a>
                <a href="{{ route('admin.quizzes.preview', $quiz->id) }}" class="px-4 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 text-xs font-bold transition">
                    Xem trước đề thi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="adminQuestionsHandler()">

            <!-- Flash alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-semibold flex items-center gap-2">
                    
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm space-y-1">
                    <div class="font-bold">Đã xảy ra lỗi:</div>
                    <ul class="list-disc list-inside text-xs pl-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Cột trái (lg:col-span-5): Form thêm câu hỏi (Tab Thủ công & Tab JSON Import) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-6 sm:p-7 sticky top-24 space-y-5">
                        
                        <!-- Toggle Tab giữa Nhập thủ công và Nhập từ JSON -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl text-xs font-bold">
                                <button type="button" @click="tab = 'manual'"
                                        :class="tab === 'manual' ? 'bg-white text-pink-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3 py-1.5 rounded-lg transition">
                                    Thêm 1 câu
                                </button>
                                <button type="button" @click="tab = 'json'"
                                        :class="tab === 'json' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                                    <span>Nhập từ JSON</span>
                                </button>
                            </div>
                            <span class="text-xs text-pink-600 font-semibold bg-pink-50 px-2 py-1 rounded-md">
                                Admin
                            </span>
                        </div>

                        <!-- TAB 1: THÊM CÂU HỎI THỦ CÔNG -->
                        <div x-show="tab === 'manual'" x-transition>
                            <form action="{{ route('admin.quizzes.questions.store', $quiz->id) }}" method="POST" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="question_text" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                        Nội dung câu hỏi <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea name="question_text" id="question_text" rows="3" required
                                              placeholder="Nhập nội dung câu hỏi trắc nghiệm ở đây..."
                                              class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('question_text') }}</textarea>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                            Các phương án trả lời <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[11px] text-pink-600 font-medium">Tích chọn đáp án ĐÚNG</span>
                                    </div>

                                    <div class="space-y-2.5">
                                        @php $letters = ['A', 'B', 'C', 'D']; @endphp
                                        @foreach($letters as $index => $letter)
                                            <div class="flex items-center gap-2">
                                                <label class="flex items-center gap-1.5 cursor-pointer shrink-0" title="Chọn làm đáp án đúng">
                                                    <input type="radio" name="correct_option" value="{{ $index }}" {{ old('correct_option', 0) == $index ? 'checked' : '' }}
                                                           class="w-4 h-4 text-emerald-600 border-slate-300 focus:ring-emerald-500">
                                                    <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-700">
                                                        {{ $letter }}
                                                    </span>
                                                </label>
                                                <input type="text" name="options[]" required 
                                                       value="{{ old('options.'.$index) }}"
                                                       placeholder="Nội dung phương án {{ $letter }}..."
                                                       class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm py-2">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label for="explanation" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                        Lời giải thích đáp án (Tùy chọn)
                                    </label>
                                    <textarea name="explanation" id="explanation" rows="2" 
                                              placeholder="Giải thích lý do vì sao đáp án này đúng..."
                                              class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-xs">{{ old('explanation') }}</textarea>
                                </div>

                                <button type="submit" class="w-full py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                                    + Thêm câu hỏi vào đề
                                </button>
                            </form>
                        </div>

                        <!-- TAB 2: NHẬP HÀNG LOẠT TỪ JSON (Paste hoặc File) -->
                        <div x-show="tab === 'json'" x-transition class="space-y-4">
                            <form action="{{ route('admin.quizzes.questions.import', $quiz->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf

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
                                    <button type="button" @click="loadSampleQuestions()" class="text-[11px] font-bold text-pink-600 hover:underline">
                                        + Mẫu JSON
                                    </button>
                                </div>

                                <div x-show="jsonSubMode === 'paste'">
                                    <textarea name="json_content" id="json_content" rows="8" x-model="jsonText"
                                              placeholder='[
  {
    "question_text": "Câu hỏi số 1?",
    "options": ["Đáp án A", "Đáp án B", "Đáp án C", "Đáp án D"],
    "correct_option": 0,
    "explanation": "Giải thích..."
  }
]'
                                              class="w-full font-mono text-xs rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 p-3 bg-slate-900 text-emerald-400"></textarea>
                                </div>

                                <div x-show="jsonSubMode === 'file'">
                                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-5 text-center bg-slate-50 hover:bg-pink-50/40 cursor-pointer relative">
                                        <input type="file" name="json_file" id="json_file" accept=".json,application/json,text/plain"
                                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <div class="text-xs font-bold text-slate-700 mb-1">Bấm để chọn tệp .json từ máy tính</div>
                                        <p class="text-[11px] text-slate-400">File chứa mảng câu hỏi trắc nghiệm</p>
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition flex items-center justify-center gap-2">
                                    <span>Nhập các câu hỏi vào đề</span>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                <!-- Cột phải (lg:col-span-7): Danh sách câu hỏi trong đề -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">
                            Danh sách câu hỏi trong đề ({{ $quiz->questions->count() }} câu)
                        </h3>
                        @if($quiz->randomize_questions)
                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-medium flex items-center gap-1">
                                
                                Tự động xáo trộn câu khi thi
                            </span>
                        @endif
                    </div>

                    @if($quiz->questions->isEmpty())
                        <div class="bg-white rounded-3xl border border-pink-100 p-12 text-center shadow-xs">
                            
                            <h4 class="font-bold text-slate-800 text-sm mb-1">Chưa có câu hỏi nào trong đề thi</h4>
                            <p class="text-slate-500 text-xs">Hãy sử dụng form bên trái để nhập câu hỏi đơn lẻ hoặc dán chuỗi JSON để nhập hàng loạt.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($quiz->questions as $index => $question)
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
                                        
                                        <form action="{{ route('admin.quizzes.questions.destroy', [$quiz->id, $question->id]) }}" method="POST"
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này không?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa câu hỏi này">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- 4 Đáp án -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                        @foreach($question->options as $optIndex => $opt)
                                            <div class="flex items-center gap-2 p-2.5 rounded-xl border {{ $opt->is_correct ? 'bg-emerald-50/80 border-emerald-300 text-emerald-950 font-bold' : 'bg-slate-50/60 border-slate-200 text-slate-700' }}">
                                                <span class="w-5 h-5 rounded-md flex items-center justify-center text-[11px] font-bold {{ $opt->is_correct ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                                    {{ chr(65 + $optIndex) }}
                                                </span>
                                                <span class="flex-1">{{ $opt->option_text }}</span>
                                                @if($opt->is_correct)
                                                    <span class="text-[10px] text-emerald-700 bg-emerald-100/80 px-1.5 py-0.5 rounded font-bold">
                                                        Đáp án đúng
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($question->explanation)
                                        <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 bg-slate-50 p-2.5 rounded-xl">
                                            <strong class="text-slate-700">Lời giải thích:</strong> {{ $question->explanation }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminQuestionsHandler() {
            return {
                tab: 'manual', // 'manual' or 'json'
                jsonSubMode: 'paste', // 'paste' or 'file'
                jsonText: '',

                loadSampleQuestions() {
                    const sample = [
                        {
                            "question_text": "HTTP status code nào đại diện cho lỗi Not Found (Không tìm thấy trang)?",
                            "explanation": "404 là mã chuẩn HTTP cho Not Found.",
                            "options": [
                                "200 OK",
                                "403 Forbidden",
                                "404 Not Found",
                                "500 Server Error"
                            ],
                            "correct_option": 2
                        },
                        {
                            "question_text": "Phương thức HTTP nào chuẩn cho việc xóa một tài nguyên RESTful?",
                            "explanation": "DELETE được sử dụng để xóa tài nguyên.",
                            "options": [
                                "GET",
                                "POST",
                                "PUT",
                                "DELETE"
                            ],
                            "correct_option": 3
                        }
                    ];
                    this.jsonText = JSON.stringify(sample, null, 2);
                }
            };
        }
    </script>
</x-admin-layout>
