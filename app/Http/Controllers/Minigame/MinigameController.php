<?php

namespace App\Http\Controllers\Minigame;

use App\Http\Controllers\Controller;
use App\Models\MinigameActivityLog;
use App\Models\MinigameParticipant;
use App\Models\MinigameQuestion;
use App\Models\MinigameQuiz;
use App\Models\MinigameRoom;
use App\Services\Minigame\MinigameService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class MinigameController extends Controller
{
    public function __construct(
        protected MinigameService $minigameService
    ) {}

    /**
     * Dashboard Minigame chính
     */
    public function index(): View
    {
        $user = Auth::user();
        $quizzes = collect();
        $hostedRooms = collect();
        $recentParticipations = collect();

        if ($user) {
            if ($user->canManageMinigames()) {
                $quizzes = MinigameQuiz::withCount('questions')
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->orWhere('is_public', true);
                    })
                    ->latest()
                    ->get();

                $hostedRooms = MinigameRoom::with(['quiz', 'participants'])
                    ->where('host_id', $user->id)
                    ->latest()
                    ->take(10)
                    ->get();
            }

            $recentParticipations = MinigameParticipant::with(['room.quiz'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return view('minigame.index', compact('user', 'quizzes', 'hostedRooms', 'recentParticipations'));
    }

    /**
     * Tải về file mẫu JSON để import
     */
    public function downloadSampleJson(): \Symfony\Component\HttpFoundation\Response
    {
        $sampleData = $this->minigameService->getSampleJson();
        $jsonContent = json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return Response::make($jsonContent, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="mau_cau_hoi_minigame.json"',
        ]);
    }

    /**
     * Giao diện tạo bộ câu hỏi (3 chế độ: Thủ công, Upload/Paste JSON, AI Generate)
     */
    public function createQuiz(): View
    {
        $this->ensureCanManage();

        return view('minigame.quizzes.create');
    }

    /**
     * Xử lý lưu bộ câu hỏi từ một trong 3 phương thức
     */
    public function storeQuiz(Request $request): RedirectResponse
    {
        $this->ensureCanManage();

        $creationMode = $request->input('creation_mode', 'manual');

        try {
            if ($creationMode === 'ai_generate') {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'topic' => 'required|string|max:255',
                    'question_count' => 'required|integer|min:1|max:20',
                    'difficulty' => 'nullable|in:easy,medium,hard',
                ], [
                    'title.required' => 'Vui lòng nhập tên bộ câu hỏi.',
                    'topic.required' => 'Vui lòng nhập chủ đề câu hỏi cho AI.',
                    'question_count.min' => 'Số lượng câu hỏi tối thiểu là 1.',
                    'question_count.max' => 'Số lượng câu hỏi tối đa cho mỗi lần tạo AI là 20.',
                ]);

                $difficulty = $request->input('difficulty', 'medium');

                $generatedQuestions = $this->minigameService->generateQuestionsWithAi(
                    topic: $request->topic,
                    count: (int) $request->question_count,
                    difficulty: $difficulty,
                    course: null
                );

                $quiz = MinigameQuiz::create([
                    'user_id' => Auth::id(),
                    'course_id' => null,
                    'title' => $request->title,
                    'description' => $request->description ?: "Tạo tự động bằng AI từ chủ đề: {$request->topic}",
                    'difficulty' => $difficulty,
                    'is_public' => $request->boolean('is_public', true),
                ]);

                $this->minigameService->saveQuestionsToQuiz($quiz, $generatedQuestions);

                return redirect()->route('minigame.index')->with('success', "AI đã tạo thành công bộ câu hỏi \"{$quiz->title}\" với ".count($generatedQuestions).' câu hỏi!');
            }

            if ($creationMode === 'import_json') {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'difficulty' => 'nullable|in:easy,medium,hard',
                    'json_file' => 'nullable|file|mimes:json,txt|max:2048',
                    'json_text' => 'nullable|string',
                ], [
                    'title.required' => 'Vui lòng nhập tên bộ câu hỏi.',
                ]);

                $rawJson = '';
                if ($request->hasFile('json_file')) {
                    $rawJson = file_get_contents($request->file('json_file')->getRealPath());
                } elseif (! empty($request->json_text)) {
                    $rawJson = $request->json_text;
                } else {
                    return back()->withInput()->with('error', 'Vui lòng tải lên file .json hoặc dán nội dung JSON câu hỏi.');
                }

                $parsedQuestions = $this->minigameService->parseQuestionsJson($rawJson);

                $quiz = MinigameQuiz::create([
                    'user_id' => Auth::id(),
                    'course_id' => null,
                    'title' => $request->title,
                    'description' => $request->description ?: 'Được nhập từ file dữ liệu JSON',
                    'difficulty' => $request->input('difficulty', 'medium'),
                    'is_public' => $request->boolean('is_public', true),
                ]);

                $this->minigameService->saveQuestionsToQuiz($quiz, $parsedQuestions);

                return redirect()->route('minigame.index')->with('success', "Đã nhập thành công bộ câu hỏi \"{$quiz->title}\" gồm ".count($parsedQuestions).' câu hỏi!');
            }

            // Mặc định: Chế độ thủ công (Manual)
            $request->validate([
                'title' => 'required|string|max:255',
                'difficulty' => 'nullable|in:easy,medium,hard',
                'questions' => 'required|array|min:1',
                'questions.*.question' => 'required|string',
                'questions.*.options.A' => 'required|string',
                'questions.*.options.B' => 'required|string',
                'questions.*.correct_answer' => 'required|in:A,B,C,D',
                'questions.*.time_limit' => 'nullable|integer|min:3|max:300',
            ], [
                'title.required' => 'Vui lòng nhập tiêu đề bộ câu hỏi.',
                'questions.required' => 'Vui lòng thêm ít nhất một câu hỏi.',
                'questions.min' => 'Vui lòng thêm ít nhất một câu hỏi.',
                'questions.*.question.required' => 'Nội dung câu hỏi không được để trống.',
                'questions.*.options.A.required' => 'Phương án A không được để trống.',
                'questions.*.options.B.required' => 'Phương án B không được để trống.',
            ]);

            $quiz = MinigameQuiz::create([
                'user_id' => Auth::id(),
                'course_id' => null,
                'title' => $request->title,
                'description' => $request->description,
                'difficulty' => $request->input('difficulty', 'medium'),
                'is_public' => $request->boolean('is_public', true),
            ]);

            $formattedQuestions = [];
            foreach ($request->questions as $qData) {
                $options = array_filter($qData['options'] ?? [], fn ($val) => trim((string) $val) !== '');
                $formattedQuestions[] = [
                    'question' => trim((string) $qData['question']),
                    'options' => $options,
                    'correct_answer' => $qData['correct_answer'] ?? 'A',
                    'time_limit' => (int) ($qData['time_limit'] ?? 20),
                ];
            }

            $this->minigameService->saveQuestionsToQuiz($quiz, $formattedQuestions);

            return redirect()->route('minigame.index')->with('success', "Đã tạo thành công bộ câu hỏi \"{$quiz->title}\" gồm ".count($formattedQuestions).' câu hỏi!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Chỉnh sửa bộ câu hỏi
     */
    public function editQuiz(MinigameQuiz $quiz): View
    {
        $this->ensureCanManageQuiz($quiz);

        $quiz->load('questions');

        return view('minigame.quizzes.edit', compact('quiz'));
    }

    /**
     * Cập nhật bộ câu hỏi
     */
    public function updateQuiz(Request $request, MinigameQuiz $quiz): RedirectResponse
    {
        $this->ensureCanManageQuiz($quiz);

        $request->validate([
            'title' => 'required|string|max:255',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options.A' => 'required|string',
            'questions.*.options.B' => 'required|string',
            'questions.*.correct_answer' => 'required|in:A,B,C,D',
            'questions.*.time_limit' => 'nullable|integer|min:3|max:300',
        ]);

        $quiz->update([
            'title' => $request->title,
            'description' => $request->description,
            'difficulty' => $request->input('difficulty', $quiz->difficulty ?? 'medium'),
            'is_public' => $request->boolean('is_public', true),
        ]);

        $formattedQuestions = [];
        foreach ($request->questions as $qData) {
            $options = array_filter($qData['options'] ?? [], fn ($val) => trim((string) $val) !== '');
            $formattedQuestions[] = [
                'question' => trim((string) $qData['question']),
                'options' => $options,
                'correct_answer' => $qData['correct_answer'] ?? 'A',
                'time_limit' => (int) ($qData['time_limit'] ?? 20),
            ];
        }

        $this->minigameService->saveQuestionsToQuiz($quiz, $formattedQuestions, replaceExisting: true);

        return redirect()->route('minigame.index')->with('success', "Bộ câu hỏi \"{$quiz->title}\" đã được cập nhật thành công!");
    }

    /**
     * Xóa bộ câu hỏi
     */
    public function destroyQuiz(MinigameQuiz $quiz): RedirectResponse
    {
        $this->ensureCanManageQuiz($quiz);

        $title = $quiz->title;
        $quiz->delete();

        return redirect()->route('minigame.index')->with('success', "Đã xóa bộ câu hỏi \"{$title}\".");
    }

    /**
     * Khởi tạo phòng chơi (Host Room) bởi Giảng viên / Admin
     */
    public function createRoom(Request $request): RedirectResponse
    {
        $this->ensureCanManage();

        $request->validate([
            'quiz_id' => 'required|exists:minigame_quizzes,id',
            'duration_minutes' => 'required|integer|min:1|max:60',
        ], [
            'quiz_id.required' => 'Vui lòng chọn một bộ câu hỏi để tạo phòng.',
            'duration_minutes.min' => 'Thời gian trận đấu tối thiểu là 1 phút.',
            'duration_minutes.max' => 'Thời gian trận đấu tối đa là 60 phút.',
        ]);

        $quiz = MinigameQuiz::withCount('questions')->findOrFail($request->quiz_id);

        if ($quiz->questions_count === 0) {
            return back()->with('error', 'Bộ câu hỏi này chưa có câu hỏi nào. Vui lòng thêm câu hỏi trước khi mở phòng!');
        }

        $pin = MinigameRoom::generatePin();

        $room = MinigameRoom::create([
            'host_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'pin_code' => $pin,
            'status' => 'waiting',
            'duration_minutes' => (int) $request->duration_minutes,
        ]);

        return redirect()->route('minigame.rooms.host', ['pin' => $room->pin_code]);
    }

    /**
     * Học viên nhập mã PIN tham gia phòng
     */
    public function joinRoom(Request $request): RedirectResponse
    {
        $request->validate([
            'pin_code' => 'required|string|size:6',
            'nickname' => 'nullable|string|max:30',
        ], [
            'pin_code.required' => 'Vui lòng nhập mã PIN phòng gồm 6 chữ số.',
            'pin_code.size' => 'Mã PIN phòng phải đúng 6 chữ số.',
        ]);

        $pin = trim($request->pin_code);
        $room = MinigameRoom::where('pin_code', $pin)->first();

        if (! $room) {
            return back()->withInput()->with('error', 'Không tìm thấy phòng chơi có mã PIN này. Vui lòng kiểm tra lại!');
        }

        if ($room->status === 'finished') {
            return back()->withInput()->with('error', 'Trận đấu này đã kết thúc!');
        }

        $user = Auth::user();
        $nickname = trim((string) $request->nickname);

        if (empty($nickname)) {
            $nickname = $user ? $user->name : 'Người chơi #'.random_int(100, 999);
        }

        // Tìm hoặc tạo participant trong phòng
        $participant = null;
        if ($user) {
            $participant = MinigameParticipant::where('room_id', $room->id)
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $participant) {
            // Đảm bảo nickname không bị rỗng
            $countSame = MinigameParticipant::where('room_id', $room->id)->where('nickname', $nickname)->count();
            if ($countSame > 0) {
                $nickname .= ' ('.($countSame + 1).')';
            }

            $participant = MinigameParticipant::create([
                'room_id' => $room->id,
                'user_id' => $user?->id,
                'nickname' => $nickname,
                'gold' => 0,
                'last_active_at' => Carbon::now(),
            ]);
        }

        session(["minigame_participant_{$pin}" => $participant->id]);

        if ($room->status === 'playing') {
            return redirect()->route('minigame.rooms.play', ['pin' => $pin]);
        }

        return redirect()->route('minigame.rooms.lobby', ['pin' => $pin]);
    }

    /**
     * Màn hình phòng chờ của Host (Giảng viên / Admin)
     */
    public function lobbyHost(string $pin): View|RedirectResponse
    {
        $this->ensureCanManage();

        $room = MinigameRoom::with(['quiz', 'participants'])->where('pin_code', $pin)->firstOrFail();

        if ($room->host_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Bạn không phải là chủ phòng này.');
        }

        if ($room->status === 'playing') {
            return redirect()->route('minigame.rooms.host.screen', ['pin' => $pin]);
        }

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        return view('minigame.lobby.host', compact('room'));
    }

    /**
     * Màn hình phòng chờ của Học viên
     */
    public function lobbyPlayer(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with('quiz')->where('pin_code', $pin)->firstOrFail();
        $participant = $this->resolveParticipant($room);

        if (! $participant) {
            return redirect()->route('minigame.index')->with('error', 'Vui lòng nhập mã PIN để tham gia phòng.');
        }

        if ($room->status === 'playing') {
            return redirect()->route('minigame.rooms.play', ['pin' => $pin]);
        }

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        return view('minigame.lobby.player', compact('room', 'participant'));
    }

    /**
     * API AJAX Polling phòng chờ
     */
    public function pollLobby(string $pin): JsonResponse
    {
        $room = MinigameRoom::with(['participants' => function ($q) {
            $q->latest();
        }])->where('pin_code', $pin)->first();

        if (! $room) {
            return response()->json(['error' => 'Room not found'], 404);
        }

        return response()->json([
            'status' => $room->status,
            'count' => $room->participants->count(),
            'participants' => $room->participants->map(fn ($p) => [
                'id' => $p->id,
                'nickname' => $p->nickname,
                'avatar' => $p->user?->avatar_url,
            ]),
        ]);
    }

    /**
     * Bắt đầu trận đấu (Host click Bắt đầu)
     */
    public function startRoom(string $pin): RedirectResponse|JsonResponse
    {
        $this->ensureCanManage();

        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->host_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Chỉ chủ phòng mới có thể bắt đầu trận đấu.');
        }

        if ($room->status === 'waiting') {
            $room->update([
                'status' => 'playing',
                'started_at' => Carbon::now(),
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('minigame.rooms.host.screen', ['pin' => $pin]);
    }

    /**
     * Màn hình Host Screen (Giảng viên theo dõi trận đấu thời gian thực)
     */
    public function playHost(string $pin): View|RedirectResponse
    {
        $this->ensureCanManage();

        $room = MinigameRoom::with(['quiz', 'participants'])->where('pin_code', $pin)->firstOrFail();

        if ($room->isFinished()) {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        return view('minigame.play.host', compact('room'));
    }

    /**
     * Màn hình Game Loop cho Học viên (Blooket Gold Quest)
     */
    public function playPlayer(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with(['quiz.questions'])->where('pin_code', $pin)->firstOrFail();
        $participant = $this->resolveParticipant($room);

        if (! $participant) {
            return redirect()->route('minigame.index')->with('error', 'Vui lòng nhập lại mã PIN để tham gia.');
        }

        if ($room->isFinished()) {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        if ($room->isWaiting()) {
            return redirect()->route('minigame.rooms.lobby', ['pin' => $pin]);
        }

        $questions = $room->quiz->questions;

        return view('minigame.play.player', compact('room', 'participant', 'questions'));
    }

    /**
     * Xử lý trả lời câu hỏi của Học viên
     */
    public function submitAnswer(Request $request, string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->isFinished()) {
            return response()->json(['finished' => true, 'redirect' => route('minigame.rooms.podium', ['pin' => $pin])]);
        }

        $participant = $this->resolveParticipant($room);
        if (! $participant) {
            return response()->json(['error' => 'Chưa tham gia phòng'], 403);
        }

        $request->validate([
            'question_id' => 'required|exists:minigame_questions,id',
            'answer' => 'required|string',
        ]);

        $question = MinigameQuestion::where('quiz_id', $room->quiz_id)->findOrFail($request->question_id);
        $answer = trim($request->answer);
        $isCorrect = $question->isCorrect($answer);

        if ($isCorrect) {
            $participant->increment('correct_count');
            $participant->update(['last_active_at' => Carbon::now()]);

            return response()->json([
                'is_correct' => true,
                'message' => 'Chính xác! Hãy mở 1 rương kho báu để nhận phần thưởng!',
                'penalty_seconds' => 0,
            ]);
        }

        $participant->increment('wrong_count');
        $participant->update(['last_active_at' => Carbon::now()]);

        $options = $question->options ?? [];
        $correctKey = $question->correct_answer;
        $correctText = $options[$correctKey] ?? $correctKey;

        return response()->json([
            'is_correct' => false,
            'message' => 'Rất tiếc, bạn đã trả lời chưa chính xác!',
            'correct_answer' => $correctKey,
            'correct_text' => $correctText,
            'penalty_seconds' => 3, // Phạt chờ 3 giây trước khi qua câu tiếp theo
        ]);
    }

    /**
     * Học viên chọn mở một trong 3 rương kho báu bí ẩn
     */
    public function openChest(Request $request, string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->isFinished()) {
            return response()->json(['finished' => true, 'redirect' => route('minigame.rooms.podium', ['pin' => $pin])]);
        }

        $participant = $this->resolveParticipant($room);
        if (! $participant) {
            return response()->json(['error' => 'Chưa tham gia phòng'], 403);
        }

        $chestIndex = (int) $request->input('chest_index', 1);
        $result = $this->minigameService->openMysteryChest($room, $participant, $chestIndex);

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * API AJAX Polling cho Host Screen (Leaderboard + Logs + Time)
     */
    public function pollHost(string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        $isFinished = $room->isFinished();
        $remainingSeconds = $room->remainingSeconds();

        $leaderboard = MinigameParticipant::where('room_id', $room->id)
            ->orderByDesc('gold')
            ->orderByDesc('correct_count')
            ->take(15)
            ->get()
            ->map(function ($p, $idx) {
                return [
                    'rank' => $idx + 1,
                    'id' => $p->id,
                    'nickname' => $p->nickname,
                    'gold' => $p->gold,
                    'correct_count' => $p->correct_count,
                    'avatar' => $p->user?->avatar_url,
                ];
            });

        $activities = MinigameActivityLog::where('room_id', $room->id)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action_type' => $log->action_type,
                'message' => $log->message,
                'time' => $log->created_at->format('H:i:s'),
            ]);

        return response()->json([
            'status' => $room->status,
            'is_finished' => $isFinished,
            'remaining_seconds' => $remainingSeconds,
            'formatted_time' => sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60),
            'leaderboard' => $leaderboard,
            'activities' => $activities,
        ]);
    }

    /**
     * API AJAX Polling cho Player Screen
     */
    public function pollPlayer(string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();
        $participant = $this->resolveParticipant($room);

        $isFinished = $room->isFinished();
        $remainingSeconds = $room->remainingSeconds();

        $gold = $participant ? $participant->fresh()->gold : 0;
        $rank = 1;

        if ($participant) {
            $rank = MinigameParticipant::where('room_id', $room->id)
                ->where('gold', '>', $gold)
                ->count() + 1;
        }

        $totalPlayers = MinigameParticipant::where('room_id', $room->id)->count();

        return response()->json([
            'status' => $room->status,
            'is_finished' => $isFinished,
            'remaining_seconds' => $remainingSeconds,
            'formatted_time' => sprintf('%02d:%02d', floor($remainingSeconds / 60), $remainingSeconds % 60),
            'gold' => $gold,
            'rank' => $rank,
            'total_players' => $totalPlayers,
        ]);
    }

    /**
     * Host kết thúc trận đấu sớm
     */
    public function endRoom(string $pin): RedirectResponse|JsonResponse
    {
        $this->ensureCanManage();

        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();

        if ($room->host_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Chỉ chủ phòng mới có quyền kết thúc trận đấu.');
        }

        $room->update([
            'status' => 'finished',
            'ended_at' => Carbon::now(),
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
    }

    /**
     * Bục vinh danh và Bảng tổng kết kết quả (Podium Top 1, 2, 3)
     */
    public function podium(string $pin): View
    {
        $room = MinigameRoom::with(['quiz', 'host'])->where('pin_code', $pin)->firstOrFail();

        $rankings = MinigameParticipant::where('room_id', $room->id)
            ->orderByDesc('gold')
            ->orderByDesc('correct_count')
            ->get();

        $top1 = $rankings->get(0);
        $top2 = $rankings->get(1);
        $top3 = $rankings->get(2);

        return view('minigame.podium', compact('room', 'rankings', 'top1', 'top2', 'top3'));
    }

    /**
     * Xác thực quyền Admin hoặc Giảng viên
     */
    protected function ensureCanManage(): void
    {
        if (! Auth::check() || ! Auth::user()->canManageMinigames()) {
            abort(403, 'Chỉ Quản trị viên hoặc Giảng viên mới có quyền sử dụng chức năng này.');
        }
    }

    /**
     * Xác thực quyền chỉnh sửa bộ câu hỏi
     */
    protected function ensureCanManageQuiz(MinigameQuiz $quiz): void
    {
        $this->ensureCanManage();

        $user = Auth::user();
        if ($quiz->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Bạn không có quyền chỉnh sửa bộ câu hỏi này.');
        }
    }

    /**
     * Lấy thực thể Participant hiện tại theo session hoặc Auth
     */
    protected function resolveParticipant(MinigameRoom $room): ?MinigameParticipant
    {
        $sessionKey = "minigame_participant_{$room->pin_code}";
        $participantId = session($sessionKey);

        if ($participantId) {
            $participant = MinigameParticipant::where('room_id', $room->id)->find($participantId);
            if ($participant) {
                return $participant;
            }
        }

        if (Auth::check()) {
            $participant = MinigameParticipant::where('room_id', $room->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($participant) {
                session([$sessionKey => $participant->id]);

                return $participant;
            }
        }

        return null;
    }
}
