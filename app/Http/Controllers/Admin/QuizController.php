<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\QuizJsonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Danh sách bài kiểm tra cần quản lý & duyệt
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Quiz::with(['course', 'teacher'])->withCount('questions')->latest();

        $totalCount = Quiz::count();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $quizzes = $query->get();

        return view('admin.quizzes.index', compact('quizzes', 'status', 'totalCount'));
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
        $status = $request->input('status', 'approved');
        $type = $request->input('type') ?: ($extracted['quiz']['type'] ?? 'quiz');
        if (! in_array($type, ['quiz', 'midterm', 'final'])) {
            $type = 'quiz';
        }

        $courseId = $request->filled('course_id') ? $request->input('course_id') : null;

        $validator = validator([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $title,
            'duration_minutes' => $duration,
            'passing_score' => $passingScore,
            'status' => $status,
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
            'status' => 'required|in:approved,pending,rejected',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
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
            'status' => $status,
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

        $validator = validator([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'status' => $request->status,
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
            'status' => 'required|in:approved,pending,rejected',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $quiz->update([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'randomize_questions' => $request->boolean('randomize_questions'),
            'status' => $request->status,
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
}
