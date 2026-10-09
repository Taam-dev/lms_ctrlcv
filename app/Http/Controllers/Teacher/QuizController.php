<?php

namespace App\Http\Controllers\Teacher;

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
     * Danh sách bài kiểm tra của giảng viên
     */
    public function index(Request $request)
    {
        return redirect()->route('instructor.dashboard', array_merge(['tab' => 'quizzes'], $request->query()));
    }

    /**
     * Form tạo bài kiểm tra mới
     */
    public function create()
    {
        $courses = Course::where('teacher_id', auth()->id())->get();

        return view('teacher.quizzes.create', compact('courses'));
    }

    /**
     * Lưu bài kiểm tra mới (Hỗ trợ Nhập thủ công, Dán JSON, hoặc Tải tệp JSON)
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
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Nếu có chọn khóa học, kiểm tra quyền sở hữu của giảng viên
        if ($courseId) {
            $course = Course::where('id', $courseId)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();
            $courseId = $course->id;
        }

        $quiz = $jsonService->createQuizWithQuestions([
            'course_id' => $courseId,
            'teacher_id' => auth()->id(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'duration_minutes' => $duration,
            'passing_score' => $passingScore,
            'randomize_questions' => $randomize,
            'status' => 'pending', // Mặc định chờ admin duyệt
        ], $extracted['questions']);

        $qCount = count($extracted['questions']);
        $message = $qCount > 0
            ? "Đã tạo bài kiểm tra và lưu thành công {$qCount} câu hỏi!"
            : 'Đã tạo bài kiểm tra thành công! Tiếp tục thêm các câu hỏi trắc nghiệm dưới đây.';

        return redirect()->route('teacher.quizzes.questions', $quiz->id)->with('success', $message);
    }

    /**
     * Form chỉnh sửa bài kiểm tra
     */
    public function edit(Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa bài kiểm tra này.');

        $courses = Course::where('teacher_id', auth()->id())->get();

        return view('teacher.quizzes.edit', compact('quiz', 'courses'));
    }

    /**
     * Cập nhật bài kiểm tra
     */
    public function update(Request $request, Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền chỉnh sửa bài kiểm tra này.');

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
        ], [
            'course_id' => 'nullable|exists:courses,id',
            'type' => 'required|in:quiz,midterm,final',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1|max:300',
            'passing_score' => 'required|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Đảm bảo khóa học chọn thuộc về giảng viên nếu có chọn
        if ($courseId) {
            Course::where('id', $courseId)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();
        }

        $quiz->update([
            'course_id' => $courseId,
            'type' => $type,
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'passing_score' => $request->passing_score,
            'randomize_questions' => $request->boolean('randomize_questions'),
        ]);

        return redirect()->route('instructor.dashboard', ['tab' => 'quizzes'])
            ->with('success', 'Đã cập nhật bài kiểm tra thành công!');
    }

    /**
     * Xóa bài kiểm tra
     */
    public function destroy(Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền xóa bài kiểm tra này.');

        $quiz->delete();

        return back()->with('success', 'Đã xóa bài kiểm tra thành công.');
    }

    /**
     * Giao diện quản lý câu hỏi của bài kiểm tra
     */
    public function questions(Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền quản lý bài kiểm tra này.');

        $quiz->load(['course', 'questions.options']);

        return view('teacher.quizzes.questions', compact('quiz'));
    }

    /**
     * Lưu câu hỏi mới kèm danh sách đáp án
     */
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền quản lý bài kiểm tra này.');

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
     * Nhập thêm câu hỏi từ JSON vào bài kiểm tra (Dán JSON hoặc Tải tệp JSON)
     */
    public function importQuestions(Request $request, Quiz $quiz, QuizJsonService $jsonService)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403, 'Bạn không có quyền quản lý bài kiểm tra này.');

        $source = $request->file('json_file') ?: $request->input('json_content');
        $extracted = $jsonService->extractFromJson($source);

        if (empty($extracted['questions'])) {
            return back()->withErrors(['json_content' => 'Không tìm thấy câu hỏi nào trong dữ liệu JSON.']);
        }

        $count = $jsonService->importQuestionsToQuiz($quiz, $extracted['questions']);

        return back()->with('success', "Đã nhập thành công {$count} câu hỏi từ JSON vào bài kiểm tra!");
    }

    /**
     * Xóa câu hỏi
     */
    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        abort_if($quiz->teacher_id !== auth()->id() || $question->quiz_id !== $quiz->id, 403);

        $question->delete();

        return back()->with('success', 'Đã xóa câu hỏi thành công.');
    }

    /**
     * Xem kết quả các học viên đã làm bài kiểm tra
     */
    public function results(Quiz $quiz)
    {
        abort_if($quiz->teacher_id !== auth()->id(), 403);

        $quiz->load('course');
        $attempts = QuizAttempt::with('student')
            ->where('quiz_id', $quiz->id)
            ->latest()
            ->get();

        return view('teacher.quizzes.results', compact('quiz', 'attempts'));
    }
}
