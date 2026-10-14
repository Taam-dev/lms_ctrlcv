<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\AiQuizGeneratorService;
use App\Services\QuizJsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Danh sách bài kiểm tra cần quản lý & duyệt
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = Quiz::with(['course', 'teacher'])->withCount('questions')->latest();

        $totalCount = Quiz::count();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('course', function ($c) use ($search) {
                        $c->where('title', 'like', "%{$search}%");
                    });
            });
        }

        $quizzes = $query->get();

        return view('admin.quizzes.index', compact('quizzes', 'status', 'totalCount', 'search'));
    }

    /**
     * Form tạo bài kiểm tra mới cho Admin (Hỗ trợ Nhập thủ công, Dán JSON, hoặc Tải tệp JSON)
     */
    public function create()
    {
        $courses = Course::with('teacher')->latest()->get();

        return view('admin.quizzes.create', compact('courses'));
    }

    /**
     * Lưu bài kiểm tra do Admin tạo
     */
    public function store(Request $request, QuizJsonService $jsonService)
    {
        $hasJson = $request->hasFile('json_file') || $request->filled('json_content');
        $extracted = ['quiz' => [], 'questions' => []];

        if ($hasJson) {
            $extracted = $jsonService->extractFromJson($request->file('json_file') ?: $request->input('json_content'));
        }

        $title = $request->input('title') ?: ($extracted['quiz']['title'] ?? null);
        $description = $request->input('description') ?? ($extracted['quiz']['description'] ?? null);
        $duration = $request->input('duration_minutes') ? (int) $request->input('duration_minutes') : ($extracted['quiz']['duration_minutes'] ?? 15);
        $passingScore = $request->input('passing_score') !== null ? (float) $request->input('passing_score') : ($extracted['quiz']['passing_score'] ?? 5.0);
        $randomize = $request->has('randomize_questions') ? $request->boolean('randomize_questions') : ($extracted['quiz']['randomize_questions'] ?? true);
        $showResult = $request->has('show_result_after_completion') ? $request->boolean('show_result_after_completion') : ($extracted['quiz']['show_result_after_completion'] ?? true);
        $status = $request->input('status', 'approved');
        $type = $request->input('type') ?: ($extracted['quiz']['type'] ?? 'quiz');
        if (! in_array($type, ['quiz', 'midterm', 'final'])) {
            $type = 'quiz';
        }

        $courseId = $request->filled('course_id') ? $request->input('course_id') : null;
        $startsAt = $request->filled('starts_at') ? $request->input('starts_at') : ($extracted['quiz']['starts_at'] ?? null);
        $endsAt = $request->filled('ends_at') ? $request->input('ends_at') : ($extracted['quiz']['ends_at'] ?? null);
        $questionLimit = $request->filled('question_limit') ? (int) $request->input('question_limit') : ($extracted['quiz']['question_limit'] ?? null);

        $validator = validator([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $title,
            'duration_minutes' => $duration,
            'passing_score' => $passingScore,
            'status' => $status,
            'question_limit' => $questionLimit,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
            'status' => 'required|in:approved,pending,rejected',
            'question_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], [
            'ends_at.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'ends_at.date' => 'Thời gian kết thúc không đúng định dạng ngày tháng.',
            'starts_at.date' => 'Thời gian bắt đầu không đúng định dạng ngày tháng.',
        ]);

        $validator->after(function ($validator) use ($startsAt, $endsAt) {
            if ($startsAt && $endsAt && ! $validator->errors()->has('ends_at')) {
                try {
                    $start = Carbon::parse($startsAt);
                    $end = Carbon::parse($endsAt);
                    if ($start->diffInMinutes($end, false) < 60) {
                        $validator->errors()->add('ends_at', 'Thời hạn làm bài ít nhất là 1 tiếng.');
                    }
                } catch (\Exception $e) {
                    // bo qua loi dinh dang
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $qCount = count($extracted['questions']);
        if ($questionLimit !== null && $qCount > 0 && $questionLimit > $qCount) {
            return back()->withErrors([
                'question_limit' => "Số lượng câu hỏi mỗi lượt thi ({$questionLimit}) không được lớn hơn tổng số câu hỏi đã tạo ({$qCount} câu).",
            ])->withInput();
        }

        $teacherId = $request->teacher_id;
        if ($courseId) {
            $course = Course::findOrFail($courseId);
            $teacherId = $teacherId ?: ($course->teacher_id ?: auth()->id());
        } else {
            $teacherId = $teacherId ?: auth()->id();
        }

        $quiz = $jsonService->createQuizWithQuestions([
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'duration_minutes' => $duration,
            'passing_score' => $passingScore,
            'randomize_questions' => $randomize,
            'show_result_after_completion' => $showResult,
            'status' => $status,
            'question_limit' => $questionLimit,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ], $extracted['questions']);

        $qCount = count($extracted['questions']);
        $message = $qCount > 0
            ? "Đã tạo bài kiểm tra và lưu thành công {$qCount} câu hỏi!"
            : 'Đã tạo bài kiểm tra thành công! Tiếp tục thêm các câu hỏi trắc nghiệm dưới đây.';

        return redirect()->route('admin.quizzes.questions', $quiz->id)->with('success', $message);
    }

    /**
     * Xem trước nội dung bài kiểm tra trước khi duyệt
     */
    public function preview(Quiz $quiz)
    {
        $quiz->load(['course', 'teacher', 'questions.options']);

        return view('admin.quizzes.preview', compact('quiz'));
    }

    /**
     * Form chỉnh sửa bài kiểm tra cho Admin
     */
    public function edit(Quiz $quiz)
    {
        $courses = Course::latest()->get();

        return view('admin.quizzes.edit', compact('quiz', 'courses'));
    }

    /**
     * Cập nhật bài kiểm tra
     */
    public function update(Request $request, Quiz $quiz)
    {
        $courseId = $request->filled('course_id') ? $request->input('course_id') : null;
        $type = $request->input('type', 'quiz');
        if (! in_array($type, ['quiz', 'midterm', 'final'])) {
            $type = 'quiz';
        }
        $questionLimit = $request->filled('question_limit') ? (int) $request->input('question_limit') : null;

        $validator = validator([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'question_limit' => $questionLimit,
            'status' => $request->status,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
            'question_limit' => 'nullable|integer|min:1',
            'status' => 'required|in:approved,pending,rejected',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], [
            'ends_at.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'ends_at.date' => 'Thời gian kết thúc không đúng định dạng ngày tháng.',
            'starts_at.date' => 'Thời gian bắt đầu không đúng định dạng ngày tháng.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('starts_at') && $request->filled('ends_at') && ! $validator->errors()->has('ends_at')) {
                try {
                    $start = Carbon::parse($request->starts_at);
                    $end = Carbon::parse($request->ends_at);
                    if ($start->diffInMinutes($end, false) < 60) {
                        $validator->errors()->add('ends_at', 'Thời hạn làm bài ít nhất là 1 tiếng.');
                    }
                } catch (\Exception $e) {
                    // bo qua loi dinh dang
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $currentQuestionCount = $quiz->questions()->count();
        if ($questionLimit !== null && $currentQuestionCount > 0 && $questionLimit > $currentQuestionCount) {
            return back()->withErrors([
                'question_limit' => "Số lượng câu hỏi mỗi lượt thi ({$questionLimit}) không được lớn hơn tổng số câu hỏi hiện có trong đề ({$currentQuestionCount} câu).",
            ])->withInput();
        }

        $quiz->update([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'question_limit' => $questionLimit,
            'randomize_questions' => $request->boolean('randomize_questions'),
            'show_result_after_completion' => $request->boolean('show_result_after_completion'),
            'status' => $request->status,
            'starts_at' => $request->filled('starts_at') ? $request->starts_at : null,
            'ends_at' => $request->filled('ends_at') ? $request->ends_at : null,
        ]);

        return redirect()->route('admin.quizzes.index')
            ->with('success', 'Đã cập nhật bài kiểm tra thành công!');
    }

    /**
     * Xóa bài kiểm tra
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->back(fallback: route('admin.quizzes.index'))
            ->with('success', 'Đã xóa bài kiểm tra vĩnh viễn khỏi cơ sở dữ liệu!');
    }

    /**
     * Giao diện quản lý câu hỏi của bài kiểm tra cho Admin
     */
    public function questions(Quiz $quiz)
    {
        $quiz->load(['course', 'questions.options']);

        return view('admin.quizzes.questions', compact('quiz'));
    }

    /**
     * Thêm câu hỏi trắc nghiệm thủ công (Admin)
     */
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'correct_option' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $quiz) {
            $nextOrder = $quiz->questions()->count() + 1;

            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $request->question_text,
                'explanation' => $request->explanation,
                'order_number' => $nextOrder,
            ]);

            foreach ($request->options as $index => $optionText) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => ((int) $request->correct_option === $index),
                ]);
            }
        });

        return back()->with('success', 'Đã thêm câu hỏi trắc nghiệm mới thành công!');
    }

    /**
     * Nhập thêm câu hỏi từ JSON (Paste hoặc File) cho Admin
     */
    public function importQuestions(Request $request, Quiz $quiz, QuizJsonService $jsonService)
    {
        $source = $request->file('json_file') ?: $request->input('json_content');
        $extracted = $jsonService->extractFromJson($source);

        if (empty($extracted['questions'])) {
            return back()->withErrors(['json_content' => 'Không tìm thấy câu hỏi nào trong dữ liệu JSON.']);
        }

        $count = $jsonService->importQuestionsToQuiz($quiz, $extracted['questions']);

        return back()->with('success', "Đã nhập thành công {$count} câu hỏi từ JSON vào bài kiểm tra!");
    }

    /**
     * Cập nhật nội dung câu hỏi và các phương án trả lời (Admin)
     */
    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        abort_if($question->quiz_id !== $quiz->id, 404);

        $request->validate([
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'correct_option' => 'required|integer|min:0',
        ], [
            'question_text.required' => 'Nội dung câu hỏi không được để trống.',
            'options.required' => 'Vui lòng nhập các phương án trả lời.',
            'options.min' => 'Cần tối thiểu 2 phương án trả lời.',
        ]);

        DB::transaction(function () use ($request, $question) {
            $question->update([
                'question_text' => $request->question_text,
                'explanation' => $request->explanation,
            ]);

            // Cập nhật các đáp án
            $question->options()->delete();

            foreach ($request->options as $index => $optionText) {
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => ((int) $request->correct_option === $index),
                ]);
            }
        });

        return back()->with('success', 'Đã cập nhật câu hỏi trắc nghiệm thành công!');
    }

    /**
     * Xóa câu hỏi (Admin)
     */
    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        abort_if($question->quiz_id !== $quiz->id, 404);

        $question->delete();

        return back()->with('success', 'Đã xóa câu hỏi thành công.');
    }

    /**
     * Xem kết quả học viên làm bài kiểm tra (Admin)
     */
    public function results(Quiz $quiz)
    {
        $quiz->load('course');
        $attempts = QuizAttempt::with('student')
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->get();

        return view('admin.quizzes.results', compact('quiz', 'attempts'));
    }

    /**
     * Duyệt bài kiểm tra
     */
    public function approve($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update(['status' => 'approved']);

        return back()->with('success', 'Đã duyệt bài kiểm tra thành công!');
    }

    /**
     * Từ chối bài kiểm tra (khi mới tạo)
     */
    public function reject($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update(['status' => 'rejected']);

        return back()->with('success', 'Đã từ chối bài kiểm tra!');
    }

    /**
     * Loại bỏ bài kiểm tra (khi đã được duyệt trước đó)
     */
    public function remove($id)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update(['status' => 'rejected']);

        return back()->with('success', 'Đã loại bỏ bài kiểm tra khỏi danh sách hiển thị!');
    }

    /**
     * Tạo câu hỏi tự động bằng mô hình trí tuệ nhân tạo (AI).
     */
    public function generateAiQuestions(Request $request, AiQuizGeneratorService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|in:gemini,openai,openrouter,groq',
            'api_key' => 'required|string|min:5',
            'model' => 'required|string|min:2',
            'content' => 'required|string|min:10',
            'count' => 'required|integer|min:1',
            'difficulty' => 'nullable|string|in:easy,medium,hard,mixed',
        ], [
            'api_key.required' => 'Vui lòng nhập API Key để tiếp tục.',
            'content.required' => 'Vui lòng nhập nội dung tài liệu hoặc chủ đề để AI tạo câu hỏi.',
            'content.min' => 'Nội dung tài liệu cần tối thiểu 10 ký tự.',
            'count.integer' => 'Số lượng câu hỏi phải là số nguyên (không được nhập số thập phân).',
            'count.min' => 'Số lượng câu hỏi phải lớn hơn hoặc bằng 1 (không được nhập số âm).',
        ]);

        $result = $aiService->generate(
            $validated['provider'],
            $validated['api_key'],
            $validated['model'],
            $validated['content'],
            (int) $validated['count'],
            $validated['difficulty'] ?? 'medium'
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Không thể tạo câu hỏi từ mô hình AI.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'questions' => $result['questions'],
            'count' => count($result['questions']),
        ]);
    }

    /**
     * Quét danh sách mô hình AI khả dụng từ nhà cung cấp thông qua API Key.
     */
    public function fetchAiModels(Request $request, AiQuizGeneratorService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|in:gemini,openai,openrouter,groq',
            'api_key' => 'required|string|min:5',
        ], [
            'api_key.required' => 'Vui lòng nhập API Key để quét danh sách mô hình.',
            'api_key.min' => 'API Key cần tối thiểu 5 ký tự.',
        ]);

        try {
            $models = $aiService->fetchAvailableModels($validated['provider'], $validated['api_key']);

            return response()->json([
                'success' => true,
                'models' => $models,
                'count' => count($models),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Dùng AI để gợi ý 4 đáp án hoặc tối ưu hóa/viết lại một câu hỏi trắc nghiệm cụ thể.
     */
    public function suggestOrRefineAiQuestion(Request $request, AiQuizGeneratorService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|in:gemini,openai,openrouter,groq',
            'api_key' => 'required|string|min:5',
            'model' => 'required|string|min:2',
            'question_text' => 'required|string|min:3',
            'options' => 'nullable|array',
            'explanation' => 'nullable|string',
            'action' => 'nullable|string|in:suggest_options,refine',
        ], [
            'api_key.required' => 'Vui lòng cung cấp API Key để sử dụng AI.',
            'question_text.required' => 'Vui lòng nhập nội dung câu hỏi trước khi nhờ AI gợi ý.',
            'question_text.min' => 'Nội dung câu hỏi cần tối thiểu 3 ký tự.',
        ]);

        $result = $aiService->suggestOrRefineQuestion(
            $validated['provider'],
            $validated['api_key'],
            $validated['model'],
            $validated['question_text'],
            $validated['options'] ?? [],
            $validated['explanation'] ?? '',
            $validated['action'] ?? 'suggest_options'
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Không thể nhận phản hồi từ AI cho câu hỏi này.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'question' => $result['question'],
        ]);
    }
}
