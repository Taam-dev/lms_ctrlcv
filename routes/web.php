<?php

use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Minigame\GoldQuestController;
use App\Http\Controllers\Minigame\MinigameController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Student\WalletController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\QuizController as TeacherQuizController;
use Illuminate\Support\Facades\Route;

// Trợ lý AI Chatbox (Multi-Provider AI & Smart Fallback)
Route::post('/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');
Route::get('/ai/status', [AiChatController::class, 'status'])->name('ai.status');

// sitemap xml
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// trang chủ
Route::get('/', [StudentCourseController::class, 'index'])->name('home');

// danh sách tất cả khóa học
Route::get('/courses', [StudentCourseController::class, 'list'])->name('courses.index');

// chi tiết khóa học
Route::get('/khoa-hoc/{slug}', [StudentCourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{id}', [StudentCourseController::class, 'show'])->name('student.courses.show');

// xem hồ sơ cá nhân
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('users.profile');
Route::get('/users/{user}', [ProfileController::class, 'show'])->name('users.show');

Route::get('/dashboard', function () {
    if (auth()->user()->isInstructor()) {
        return redirect()->route('instructor.dashboard');
    } elseif (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

// các route khi đã đăng nhập
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email.update');
    Route::post('/profile/forgot-password', [ProfileController::class, 'sendPasswordResetLink'])->name('profile.forgot-password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // học viên đăng ký và học bài
    Route::post('/courses/{id}/enroll', [StudentCourseController::class, 'enroll'])->name('student.courses.enroll');
    Route::get('/khoa-hoc/{courseId}/bai-hoc/{lessonId}', [StudentCourseController::class, 'viewLesson'])->name('student.lessons.friendly');
    Route::get('/courses/{courseId}/lessons/{lessonId}', [StudentCourseController::class, 'viewLesson'])->name('student.lessons.show');
    Route::post('/courses/{courseId}/lessons/{lessonId}/complete', [StudentCourseController::class, 'completeLesson'])->name('student.lessons.complete');

    // học viên làm quiz
    Route::get('/quizzes', [StudentQuizController::class, 'index'])->name('student.quizzes.index');
    Route::get('/quizzes/{quiz}/take', [StudentQuizController::class, 'take'])->name('student.quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [StudentQuizController::class, 'submit'])->name('student.quizzes.submit');
    Route::get('/quizzes/{quiz}/result/{attempt}', [StudentQuizController::class, 'result'])->name('student.quizzes.result');

    // đánh giá khóa học
    Route::post('/courses/{course}/reviews', [StudentCourseController::class, 'review'])->name('student.courses.reviews.store');
    Route::delete('/courses/{course}/reviews/{review}', [StudentCourseController::class, 'destroyReview'])->name('student.courses.reviews.destroy');

    // ví tiền và nạp tiền vietqr
    Route::get('/wallet', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [WalletController::class, 'createDeposit'])->name('wallet.deposit.store');
    Route::post('/wallet/transactions/{transaction}/confirm', [WalletController::class, 'confirmDeposit'])->name('wallet.transactions.confirm');
    Route::post('/wallet/transactions/{transaction}/cancel', [WalletController::class, 'cancelDeposit'])->name('wallet.transactions.cancel');

    // gửi báo cáo vi phạm
    Route::post('/users/{user}/report', [ReportController::class, 'storeUserReport'])->name('users.report');
    Route::post('/reviews/{review}/report', [ReportController::class, 'storeReviewReport'])->name('reviews.report');

    // thông báo hệ thống
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/go', [NotificationController::class, 'go'])->name('notifications.go');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// route cho giảng viên
Route::middleware(['auth', 'role:teacher'])->prefix('instructor')->name('instructor.')->group(function () {
    // dashboard giảng viên
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [TeacherDashboardController::class, 'index']);

    // quản lý khóa học
    Route::resource('courses', TeacherCourseController::class);

    // quản lý bài giảng
    Route::get('courses/{course}/lessons/create', [TeacherLessonController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::get('lessons/{lesson}/edit', [TeacherLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');

    Route::post('quizzes/generate-ai-questions', [TeacherQuizController::class, 'generateAiQuestions'])->name('quizzes.generate-ai-questions');
    Route::post('quizzes/fetch-ai-models', [TeacherQuizController::class, 'fetchAiModels'])->name('quizzes.fetch-ai-models');
    Route::post('quizzes/ai-suggest-question', [TeacherQuizController::class, 'suggestOrRefineAiQuestion'])->name('quizzes.ai-suggest-question');
    Route::resource('quizzes', TeacherQuizController::class);
    Route::get('quizzes/{quiz}/questions', [TeacherQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [TeacherQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('quizzes/{quiz}/questions/import', [TeacherQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::put('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::get('quizzes/{quiz}/results', [TeacherQuizController::class, 'results'])->name('quizzes.results');
});

// alias tương thích prefix cũ
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', TeacherCourseController::class);
    Route::get('courses/{course}/lessons/create', [TeacherLessonController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::get('lessons/{lesson}/edit', [TeacherLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');
    Route::post('quizzes/generate-ai-questions', [TeacherQuizController::class, 'generateAiQuestions'])->name('quizzes.generate-ai-questions');
    Route::post('quizzes/fetch-ai-models', [TeacherQuizController::class, 'fetchAiModels'])->name('quizzes.fetch-ai-models');
    Route::post('quizzes/ai-suggest-question', [TeacherQuizController::class, 'suggestOrRefineAiQuestion'])->name('quizzes.ai-suggest-question');
    Route::resource('quizzes', TeacherQuizController::class);
    Route::get('quizzes/{quiz}/questions', [TeacherQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [TeacherQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('quizzes/{quiz}/questions/import', [TeacherQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::put('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::get('quizzes/{quiz}/results', [TeacherQuizController::class, 'results'])->name('quizzes.results');
});

// route cho admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // dashboard admin
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.update-role');
    Route::patch('users/{user}/wallet', [AdminDashboardController::class, 'updateUserWallet'])->name('users.update-wallet');
    Route::delete('users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');
    Route::post('reports/{report}/action', [AdminDashboardController::class, 'updateReportStatus'])->name('reports.action');
    Route::delete('reports/{report}', [AdminDashboardController::class, 'destroyReport'])->name('reports.destroy');
    Route::delete('attempts/{attempt}', [AdminDashboardController::class, 'destroyAttempt'])->name('attempts.destroy');

    // duyệt và quản lý khóa học bài giảng
    Route::resource('courses', AdminCourseController::class)->except(['show']);
    Route::post('courses/{id}/approve', [AdminCourseController::class, 'approve'])->name('courses.approve');
    Route::post('courses/{id}/reject', [AdminCourseController::class, 'reject'])->name('courses.reject');
    Route::post('courses/{id}/remove', [AdminCourseController::class, 'remove'])->name('courses.remove');
    Route::get('courses/{course}/lessons/create', [AdminCourseController::class, 'createLesson'])->name('courses.lessons.create');
    Route::post('courses/{course}/lessons', [AdminCourseController::class, 'storeLesson'])->name('courses.lessons.store');
    Route::get('lessons/{id}/edit', [AdminCourseController::class, 'editLesson'])->name('lessons.edit');
    Route::put('lessons/{id}', [AdminCourseController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('lessons/{id}', [AdminCourseController::class, 'destroyLesson'])->name('lessons.destroy');

    // duyệt và quản lý quiz
    Route::post('quizzes/generate-ai-questions', [AdminQuizController::class, 'generateAiQuestions'])->name('quizzes.generate-ai-questions');
    Route::post('quizzes/fetch-ai-models', [AdminQuizController::class, 'fetchAiModels'])->name('quizzes.fetch-ai-models');
    Route::post('quizzes/ai-suggest-question', [AdminQuizController::class, 'suggestOrRefineAiQuestion'])->name('quizzes.ai-suggest-question');
    Route::resource('quizzes', AdminQuizController::class)->except(['show']);
    Route::get('quizzes/{quiz}/preview', [AdminQuizController::class, 'preview'])->name('quizzes.preview');
    Route::post('quizzes/{id}/approve', [AdminQuizController::class, 'approve'])->name('quizzes.approve');
    Route::post('quizzes/{id}/reject', [AdminQuizController::class, 'reject'])->name('quizzes.reject');
    Route::post('quizzes/{id}/remove', [AdminQuizController::class, 'remove'])->name('quizzes.remove');
    Route::get('quizzes/{quiz}/questions', [AdminQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [AdminQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::put('quizzes/{quiz}/questions/{question}', [AdminQuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
    Route::post('quizzes/{quiz}/questions/import', [AdminQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [AdminQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::get('quizzes/{quiz}/results', [AdminQuizController::class, 'results'])->name('quizzes.results');
    Route::delete('quizzes/{quiz}/attempts/{attempt}', [AdminQuizController::class, 'destroyAttempt'])->name('quizzes.attempts.destroy');
});

// ==========================================
// TÍNH NĂNG MINIGAME (BLOOKET GOLD QUEST)
// Yêu cầu đăng nhập trước khi tham gia minigame
// ==========================================
Route::middleware(['auth'])->prefix('minigame')->name('minigame.')->group(function () {
    // Sảnh chính & tham gia phòng
    Route::get('/', [MinigameController::class, 'index'])->name('index');
    Route::get('/sample-json', [MinigameController::class, 'downloadSampleJson'])->name('sample-json');
    Route::post('/join', [MinigameController::class, 'joinRoom'])->name('join');

    // Quản lý bộ câu hỏi & mở phòng (Giảng viên & Admin)
    Route::middleware(['role:admin,teacher'])->group(function () {
        Route::get('/quizzes/create', [MinigameController::class, 'createQuiz'])->name('quizzes.create');
        Route::post('/quizzes', [MinigameController::class, 'storeQuiz'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}/edit', [MinigameController::class, 'editQuiz'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [MinigameController::class, 'updateQuiz'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [MinigameController::class, 'destroyQuiz'])->name('quizzes.destroy');

        Route::post('/rooms/create', [MinigameController::class, 'createRoom'])->name('rooms.create');
        Route::get('/rooms/{pin}/host', [MinigameController::class, 'lobbyHost'])->name('rooms.host');
        Route::post('/rooms/{pin}/start', [MinigameController::class, 'startRoom'])->name('rooms.start');
        Route::get('/rooms/{pin}/host-screen', [MinigameController::class, 'playHost'])->name('rooms.host.screen');
        Route::post('/rooms/{pin}/end', [MinigameController::class, 'endRoom'])->name('rooms.end');
    });

    // Phòng chờ, Vòng lặp trận đấu & Bảng xếp hạng (Học viên & Host)
    Route::get('/rooms/{pin}/lobby', [MinigameController::class, 'lobbyPlayer'])->name('rooms.lobby');
    Route::get('/rooms/{pin}/poll-lobby', [MinigameController::class, 'pollLobby'])->name('rooms.poll-lobby');
    Route::get('/rooms/{pin}/play', [MinigameController::class, 'playPlayer'])->name('rooms.play');
    Route::post('/rooms/{pin}/answer', [MinigameController::class, 'submitAnswer'])->name('rooms.answer');
    Route::post('/rooms/{pin}/open-chest', [MinigameController::class, 'openChest'])->name('rooms.open-chest');
    Route::get('/rooms/{pin}/poll-host', [MinigameController::class, 'pollHost'])->name('rooms.poll-host');
    Route::get('/rooms/{pin}/poll-player', [MinigameController::class, 'pollPlayer'])->name('rooms.poll-player');
    Route::get('/rooms/{pin}/podium', [MinigameController::class, 'podium'])->name('rooms.podium');
});

// ==========================================
// ĐẠI CHIẾN CƯỚP VÀNG (GOLD QUEST ENGINE)
// Hỗ trợ WebSocket Realtime + Redis ZSET
// ==========================================
Route::prefix('gold-quest')->name('goldquest.')->group(function () {
    Route::post('/join', [GoldQuestController::class, 'join'])->name('join');
    Route::get('/rooms/{pin}/lobby', [GoldQuestController::class, 'playerLobby'])->name('player.lobby');
    Route::get('/rooms/{pin}/play', [GoldQuestController::class, 'play'])->name('play');

    // Các route yêu cầu quyền quản trị / giảng viên hoặc đăng nhập
    Route::middleware(['auth'])->group(function () {
        Route::post('/rooms/create', [GoldQuestController::class, 'createRoom'])->name('rooms.create');
        Route::get('/rooms/{pin}/host-lobby', [GoldQuestController::class, 'hostLobby'])->name('host.lobby');
        Route::get('/rooms/{pin}/host-screen', [GoldQuestController::class, 'hostScreen'])->name('host.screen');
        Route::post('/rooms/{pin}/start', [GoldQuestController::class, 'start'])->name('rooms.start');
        Route::post('/rooms/{pin}/end', [GoldQuestController::class, 'endRoom'])->name('rooms.end');
    });

    // Realtime game loop actions
    Route::post('/rooms/{pin}/answer', [GoldQuestController::class, 'submitAnswer'])->name('rooms.answer');
    Route::post('/rooms/{pin}/open-chest', [GoldQuestController::class, 'openChest'])->name('rooms.open-chest');
    Route::post('/rooms/{pin}/execute-action', [GoldQuestController::class, 'executeAction'])->name('rooms.execute-action');

    // Polling endpoints (hỗ trợ companion cùng WebSocket)
    Route::get('/rooms/{pin}/poll-host', [GoldQuestController::class, 'pollHost'])->name('rooms.poll-host');
    Route::get('/rooms/{pin}/poll-player', [GoldQuestController::class, 'pollPlayer'])->name('rooms.poll-player');
});

// Alias API cho Client / Mobile
Route::prefix('api/gold-quest')->group(function () {
    Route::post('/rooms', [GoldQuestController::class, 'createRoom']);
    Route::post('/join', [GoldQuestController::class, 'join']);
    Route::post('/rooms/{pin}/start', [GoldQuestController::class, 'start']);
    Route::post('/rooms/{pin}/answer', [GoldQuestController::class, 'submitAnswer']);
    Route::post('/rooms/{pin}/open-chest', [GoldQuestController::class, 'openChest']);
    Route::post('/rooms/{pin}/execute-action', [GoldQuestController::class, 'executeAction']);
    Route::post('/rooms/{pin}/end', [GoldQuestController::class, 'endRoom']);
    Route::get('/rooms/{pin}/leaderboard', [GoldQuestController::class, 'pollHost']);
});

// ==========================================
// KHO TÀI LIỆU (LEARNING RESOURCES & DOCS)
// ==========================================
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');

    // Quản lý đăng tải & chỉnh sửa tài liệu (Giảng viên / Admin)
    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
    });

    // Tải xuống tệp tin tài liệu
    Route::get('/files/{file}/download', [DocumentController::class, 'download'])->name('files.download');

    // Chi tiết tài liệu (đặt sau các route cụ thể)
    Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
});

require __DIR__.'/auth.php';
