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

    <div class="w-full" x-data="{ tab: 'manual', jsonSubMode: 'paste', jsonText: '', loadSample() { this.jsonText = JSON.stringify([{'question_text': 'Laravel là gì?', 'explanation': 'Laravel là PHP framework.', 'options': ['Một PHP Framework', 'Một DB', 'Một HĐH', 'Một trình duyệt'], 'correct_option': 0}], null, 2); } }">
        <div class="w-full space-y-6">

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
                <!-- Cột trái (lg:col-span-5): Form thêm câu hỏi mới -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl border border-pink-100 shadow-xs p-6 sm:p-7 sticky top-24 space-y-5">
                        
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
                                GV
                            </span>
                        </div>

                        <!-- TAB 1: THỦ CÔNG -->
                        <div x-show="tab === 'manual'" x-transition>
                            <form action="{{ route('teacher.quizzes.questions.store', $quiz->id) }}" method="POST" class="space-y-4">
                                @csrf

                        <!-- Nội dung câu hỏi -->
                        <div>
                            <label for="question_text" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                Nội dung câu hỏi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="question_text" id="question_text" rows="3" required
                                      placeholder="Nhập nội dung câu hỏi trắc nghiệm ở đây..."
                                      class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-sm">{{ old('question_text') }}</textarea>
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
                                @php
                                    $optionLetters = ['A', 'B', 'C', 'D'];
                                @endphp
                                @foreach($optionLetters as $index => $letter)
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
                            <textarea name="explanation" id="explanation" rows="2" 
                                      placeholder="Giải thích lý do vì sao đáp án này đúng để học viên học hỏi sau khi làm bài..."
                                      class="w-full rounded-xl border-slate-200 focus:border-pink-600 focus:ring-pink-500 text-xs">{{ old('explanation') }}</textarea>
                            @error('explanation')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm shadow-md shadow-pink-500/25 transition">
                            + Lưu câu hỏi vào đề thi
                        </button>
                    </form>
                </div>

                <!-- TAB 2: NHẬP TỪ JSON -->
                <div x-show="tab === 'json'" x-transition class="space-y-4">
                    <form action="{{ route('teacher.quizzes.questions.import', $quiz->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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

            <!-- Cột phải (lg:col-span-7): Danh sách câu hỏi hiện có -->
            <div class="lg:col-span-7 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">
                        Danh sách câu hỏi trong đề ({{ $quiz->questions->count() }} câu)
                    </h3>
                    @if($quiz->randomize_questions)
                        <span class="text-xs text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md font-medium flex items-center gap-1">
                            
                            Sẽ tự động đảo câu hỏi khi thi
                        </span>
                    @endif
                </div>

                @if($quiz->questions->isEmpty())
                    <div class="bg-white rounded-3xl border border-pink-100 p-12 text-center shadow-xs">
                        
                        <h4 class="font-bold text-slate-800 text-sm mb-1">Chưa có câu hỏi nào</h4>
                        <p class="text-slate-500 text-xs">Hãy sử dụng form bên trái để nhập các câu hỏi trắc nghiệm đầu tiên.</p>
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
</x-teacher-layout>
