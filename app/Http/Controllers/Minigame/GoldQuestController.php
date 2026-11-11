<?php

namespace App\Http\Controllers\Minigame;

use App\Http\Controllers\Controller;
use App\Models\MinigameActivityLog;
use App\Models\MinigameParticipant;
use App\Models\MinigameRoom;
use App\Services\GoldQuest\GoldQuestEngine;
use App\Services\GoldQuest\GoldQuestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoldQuestController extends Controller
{
    public function __construct(
        protected GoldQuestService $service,
        protected GoldQuestEngine $engine
    ) {}

    /**
     * Host tạo phòng chơi Gold Quest
     */
    public function createRoom(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:minigame_quizzes,id',
            'duration_minutes' => 'nullable|integer|min:1|max:60',
            'allow_steal' => 'nullable|boolean',
            'allow_swap' => 'nullable|boolean',
        ]);

        $hostId = Auth::id() ?? 1;
        $room = $this->service->createRoom(
            hostId: $hostId,
            quizId: (int) $validated['quiz_id'],
            durationMinutes: (int) ($validated['duration_minutes'] ?? 6),
            settings: [
                'allow_steal' => (bool) ($validated['allow_steal'] ?? true),
                'allow_swap' => (bool) ($validated['allow_swap'] ?? true),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'pin' => $room->pin_code,
                'room' => $room,
            ]);
        }

        return redirect()->route('goldquest.host.lobby', ['pin' => $room->pin_code]);
    }

    /**
     * Màn hình sảnh chờ của Host
     */
    public function hostLobby(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with(['quiz', 'participants'])->where('pin_code', $pin)->firstOrFail();

        if ($room->status === 'playing') {
            return redirect()->route('goldquest.host.screen', ['pin' => $pin]);
        }

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        return view('minigame.goldquest.host_lobby', compact('room'));
    }

    /**
     * Màn hình Host Screen (Máy chiếu hiển thị bảng xếp hạng & hoạt động)
     */
    public function hostScreen(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with(['quiz', 'participants'])->where('pin_code', $pin)->firstOrFail();

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        return view('minigame.goldquest.host_screen', compact('room'));
    }

    /**
     * Màn hình phòng chờ của Người chơi
     */
    public function playerLobby(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with('quiz')->where('pin_code', $pin)->firstOrFail();

        if ($room->status === 'playing') {
            return redirect()->route('goldquest.play', ['pin' => $pin]);
        }

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        $sessionKey = "goldquest_player_{$pin}";
        $playerId = session("{$sessionKey}_id");
        $playerNickname = session("{$sessionKey}_name");
        $playerBlook = session("{$sessionKey}_blook", 'dragon');

        return view('minigame.goldquest.player_lobby', compact('room', 'playerId', 'playerNickname', 'playerBlook'));
    }

    /**
     * Người chơi tham gia phòng (API hoặc Form)
     */
    public function join(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
            'nickname' => 'required|string|max:30',
            'blook' => 'nullable|string|max:30',
        ]);

        $blook = $validated['blook'] ?? 'dragon';
        $result = $this->service->joinRoom(
            pin: $validated['pin'],
            nickname: $validated['nickname'],
            blook: $blook,
            userId: Auth::id()
        );

        // Lưu session cho client
        $sessionKey = "goldquest_player_{$validated['pin']}";
        session([
            "{$sessionKey}_id" => $result['player_id'],
            "{$sessionKey}_participant_id" => $result['participant_id'],
            "{$sessionKey}_name" => $result['nickname'],
            "{$sessionKey}_blook" => $blook,
        ]);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('goldquest.player.lobby', ['pin' => $validated['pin']]);
    }

    /**
     * Host bắt đầu trận đấu
     */
    public function start(Request $request, string $pin): JsonResponse|RedirectResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();
        $this->service->startGame($pin, Auth::id() ?? $room->host_id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('goldquest.host.screen', ['pin' => $pin]);
    }

    /**
     * Màn hình Gameplay cho Học viên
     */
    public function play(string $pin): View|RedirectResponse
    {
        $room = MinigameRoom::with(['quiz.questions'])->where('pin_code', $pin)->firstOrFail();

        if ($room->status === 'finished') {
            return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
        }

        if ($room->status === 'waiting') {
            return redirect()->route('goldquest.player.lobby', ['pin' => $pin]);
        }

        $sessionKey = "goldquest_player_{$pin}";
        $playerId = session("{$sessionKey}_id");
        $nickname = session("{$sessionKey}_name");
        $blook = session("{$sessionKey}_blook", 'dragon');

        if (! $playerId) {
            // Thử tìm theo auth user
            if (Auth::check()) {
                $p = MinigameParticipant::where('room_id', $room->id)->where('user_id', Auth::id())->first();
                if ($p) {
                    $playerId = $p->session_id;
                    $nickname = $p->nickname;
                    $blook = $p->blook;
                    session([
                        "{$sessionKey}_id" => $playerId,
                        "{$sessionKey}_participant_id" => $p->id,
                        "{$sessionKey}_name" => $nickname,
                        "{$sessionKey}_blook" => $blook,
                    ]);
                }
            }
        }

        if (! $playerId) {
            return redirect()->route('minigame.index')->with('error', 'Vui lòng nhập lại mã PIN để tham gia.');
        }

        $questions = $room->quiz->questions;

        return view('minigame.goldquest.play', compact('room', 'playerId', 'nickname', 'blook', 'questions'));
    }

    /**
     * Nộp câu trả lời trắc nghiệm
     */
    public function submitAnswer(Request $request, string $pin): JsonResponse
    {
        $validated = $request->validate([
            'player_id' => 'required|string',
            'question_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $result = $this->service->submitAnswer(
            pin: $pin,
            playerId: $validated['player_id'],
            questionId: (int) $validated['question_id'],
            selectedOption: $validated['answer']
        );

        return response()->json($result);
    }

    /**
     * Mở rương vàng (yêu cầu chest_token từ lượt trả lời đúng)
     */
    public function openChest(Request $request, string $pin): JsonResponse
    {
        $validated = $request->validate([
            'player_id' => 'required|string',
            'chest_token' => 'required|string',
        ]);

        $result = $this->service->openChest(
            pin: $pin,
            playerId: $validated['player_id'],
            token: $validated['chest_token']
        );

        return response()->json($result);
    }

    /**
     * Thực hiện CƯỚP hoặc TRÁO ĐỔI sau khi quay vào ô STEAL / SWAP
     */
    public function executeAction(Request $request, string $pin): JsonResponse
    {
        $validated = $request->validate([
            'player_id' => 'required|string',
            'victim_id' => 'required|string',
        ]);

        $action = $this->engine->consumePendingAction($pin, $validated['player_id']);
        if (! $action) {
            return response()->json(['error' => 'Thời gian chọn mục tiêu đã hết hạn hoặc không hợp lệ!'], 400);
        }

        if ($action === 'STEAL') {
            $stealRate = random_int(10, 30) / 100.0;
            $res = $this->engine->executeSteal($pin, $validated['player_id'], $validated['victim_id'], $stealRate);

            return response()->json(['success' => true, 'action' => 'STEAL', 'result' => $res]);
        }

        if ($action === 'SWAP') {
            $res = $this->engine->executeSwap($pin, $validated['player_id'], $validated['victim_id']);

            return response()->json(['success' => true, 'action' => 'SWAP', 'result' => $res]);
        }

        return response()->json(['error' => 'Hành động không hợp lệ'], 400);
    }

    /**
     * Host kết thúc phòng sớm
     */
    public function endRoom(string $pin): JsonResponse|RedirectResponse
    {
        $podium = $this->service->finalizeAndEndGame($pin);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'podium' => $podium]);
        }

        return redirect()->route('minigame.rooms.podium', ['pin' => $pin]);
    }

    /**
     * AJAX Polling hỗ trợ song song cho Host Screen
     */
    public function pollHost(string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();
        $isFinished = $room->isFinished();
        $remaining = $room->remainingSeconds();

        $leaderboard = $this->engine->getLeaderboard($pin, 10);
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
            'remaining_seconds' => $remaining,
            'formatted_time' => sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60),
            'leaderboard' => $leaderboard,
            'activities' => $activities,
        ]);
    }

    /**
     * AJAX Polling hỗ trợ song song cho Player
     */
    public function pollPlayer(Request $request, string $pin): JsonResponse
    {
        $room = MinigameRoom::where('pin_code', $pin)->firstOrFail();
        $playerId = (string) $request->input('player_id', '');

        $isFinished = $room->isFinished();
        $remaining = $room->remainingSeconds();
        $gold = $playerId ? $this->engine->getGold($pin, $playerId) : 0;
        $rank = $playerId ? $this->engine->getRank($pin, $playerId) : 1;

        return response()->json([
            'status' => $room->status,
            'is_finished' => $isFinished,
            'remaining_seconds' => $remaining,
            'formatted_time' => sprintf('%02d:%02d', floor($remaining / 60), $remaining % 60),
            'gold' => $gold,
            'rank' => $rank,
        ]);
    }
}
