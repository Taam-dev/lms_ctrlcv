<?php

namespace App\View\Components;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Report;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $breadcrumb = null
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $pendingCoursesCount = Course::where('status', 'pending')->count();
        $pendingQuizzesCount = Quiz::where('status', 'pending')->count();
        $pendingReportsCount = Report::where('status', 'pending')->count();
        $totalCoursesCount = Course::count();
        $totalQuizzesCount = Quiz::count();
        $totalUsersCount = User::count();
        $totalLessonsCount = Lesson::count();
        $adminQuizIds = auth()->check() ? Quiz::where('teacher_id', auth()->id())->pluck('id') : collect();
        $totalAttemptsCount = $adminQuizIds->isNotEmpty() ? QuizAttempt::whereIn('quiz_id', $adminQuizIds)->count() : 0;

        return view('layouts.admin', [
            'breadcrumb' => $this->breadcrumb,
            'pendingCoursesCount' => $pendingCoursesCount,
            'pendingQuizzesCount' => $pendingQuizzesCount,
            'pendingReportsCount' => $pendingReportsCount,
            'totalCoursesCount' => $totalCoursesCount,
            'totalQuizzesCount' => $totalQuizzesCount,
            'totalUsersCount' => $totalUsersCount,
            'totalLessonsCount' => $totalLessonsCount,
            'totalAttemptsCount' => $totalAttemptsCount,
        ]);
    }
}
