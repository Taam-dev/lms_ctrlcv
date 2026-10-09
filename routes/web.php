<?php

use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\QuizController as StudentQuizController;
use App\Http\Controllers\Teacher\CourseController as TeacherCourseController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\LessonController as TeacherLessonController;
use App\Http\Controllers\Teacher\QuizController as TeacherQuizController;
use Illuminate\Support\Facades\Route;

// Sơ đồ trang web Sitemap XML động chuẩn Google Search Console
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Trang chủ hiển thị danh sách khóa học
Route::get('/', [StudentCourseController::class, 'index'])->name('home');

// Trang riêng hiển thị tất cả khóa học
Route::get('/courses', [StudentCourseController::class, 'list'])->name('courses.index');

// Route xem chi tiết khóa học thân thiện chuẩn SEO (công khai cho cả khách & bot tìm kiếm)
Route::get('/khoa-hoc/{slug}', [StudentCourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{id}', [StudentCourseController::class, 'show'])->name('student.courses.show');

Route::get('/dashboard', function () {
    if (auth()->user()->isInstructor()) {
        return redirect()->route('instructor.dashboard');
    } elseif (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

// Route chung cho người dùng đã đăng nhập (profile, học viên học bài và làm bài trắc nghiệm)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route học viên đăng ký khóa học và học bài giảng (hỗ trợ cả slug chuẩn SEO và ID)
    Route::post('/courses/{id}/enroll', [StudentCourseController::class, 'enroll'])->name('student.courses.enroll');
    Route::get('/khoa-hoc/{courseId}/bai-hoc/{lessonId}', [StudentCourseController::class, 'viewLesson'])->name('student.lessons.friendly');
    Route::get('/courses/{courseId}/lessons/{lessonId}', [StudentCourseController::class, 'viewLesson'])->name('student.lessons.show');
    Route::post('/courses/{courseId}/lessons/{lessonId}/complete', [StudentCourseController::class, 'completeLesson'])->name('student.lessons.complete');

    // Route học viên làm bài trắc nghiệm (Quizzes)
    Route::get('/quizzes', [StudentQuizController::class, 'index'])->name('student.quizzes.index');
    Route::get('/quizzes/{quiz}/take', [StudentQuizController::class, 'take'])->name('student.quizzes.take');
    Route::post('/quizzes/{quiz}/submit', [StudentQuizController::class, 'submit'])->name('student.quizzes.submit');
    Route::get('/quizzes/{quiz}/result/{attempt}', [StudentQuizController::class, 'result'])->name('student.quizzes.result');
});

// Route cho Giảng viên (GV Portal)
Route::middleware(['auth', 'role:teacher'])->prefix('instructor')->name('instructor.')->group(function () {
    // Bảng điều khiển riêng của Giảng viên
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [TeacherDashboardController::class, 'index']);

    // Quản lý khóa học (Courses CRUD)
    Route::resource('courses', TeacherCourseController::class);

    // Quản lý bài giảng (Lessons CRUD)
    Route::get('courses/{course}/lessons/create', [TeacherLessonController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::get('lessons/{lesson}/edit', [TeacherLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');

    // Quản lý bài kiểm tra (Quizzes CRUD & questions & results)
    Route::resource('quizzes', TeacherQuizController::class);
    Route::get('quizzes/{quiz}/questions', [TeacherQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [TeacherQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('quizzes/{quiz}/questions/import', [TeacherQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::get('quizzes/{quiz}/results', [TeacherQuizController::class, 'results'])->name('quizzes.results');
});

// Alias cho prefix 'teacher' để tương thích ngược với các liên kết cũ
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::resource('courses', TeacherCourseController::class);
    Route::get('courses/{course}/lessons/create', [TeacherLessonController::class, 'create'])->name('lessons.create');
    Route::post('courses/{course}/lessons', [TeacherLessonController::class, 'store'])->name('lessons.store');
    Route::get('lessons/{lesson}/edit', [TeacherLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('lessons/{lesson}', [TeacherLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [TeacherLessonController::class, 'destroy'])->name('lessons.destroy');
    Route::resource('quizzes', TeacherQuizController::class);
    Route::get('quizzes/{quiz}/questions', [TeacherQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [TeacherQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('quizzes/{quiz}/questions/import', [TeacherQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [TeacherQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::get('quizzes/{quiz}/results', [TeacherQuizController::class, 'results'])->name('quizzes.results');
});

// Route cho Quản trị viên (cần đăng nhập + role admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Trang quản trị tổng quan
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::patch('users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.update-role');

    // Quản lý & duyệt khóa học, bài giảng
    Route::resource('courses', AdminCourseController::class)->except(['show']);
    Route::post('courses/{id}/approve', [AdminCourseController::class, 'approve'])->name('courses.approve');
    Route::post('courses/{id}/reject', [AdminCourseController::class, 'reject'])->name('courses.reject');
    Route::post('courses/{id}/remove', [AdminCourseController::class, 'remove'])->name('courses.remove');
    Route::get('courses/{course}/lessons/create', [AdminCourseController::class, 'createLesson'])->name('courses.lessons.create');
    Route::post('courses/{course}/lessons', [AdminCourseController::class, 'storeLesson'])->name('courses.lessons.store');
    Route::get('lessons/{id}/edit', [AdminCourseController::class, 'editLesson'])->name('lessons.edit');
    Route::put('lessons/{id}', [AdminCourseController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('lessons/{id}', [AdminCourseController::class, 'destroyLesson'])->name('lessons.destroy');

    // Quản lý & duyệt bài kiểm tra (Quizzes)
    Route::resource('quizzes', AdminQuizController::class)->except(['show']);
    Route::get('quizzes/{quiz}/preview', [AdminQuizController::class, 'preview'])->name('quizzes.preview');
    Route::post('quizzes/{id}/approve', [AdminQuizController::class, 'approve'])->name('quizzes.approve');
    Route::post('quizzes/{id}/reject', [AdminQuizController::class, 'reject'])->name('quizzes.reject');
    Route::post('quizzes/{id}/remove', [AdminQuizController::class, 'remove'])->name('quizzes.remove');
    Route::get('quizzes/{quiz}/questions', [AdminQuizController::class, 'questions'])->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', [AdminQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
    Route::post('quizzes/{quiz}/questions/import', [AdminQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
    Route::delete('quizzes/{quiz}/questions/{question}', [AdminQuizController::class, 'destroyQuestion'])->name('quizzes.questions.destroy');
    Route::get('quizzes/{quiz}/results', [AdminQuizController::class, 'results'])->name('quizzes.results');
});

require __DIR__.'/auth.php';
