<?php

namespace App\View\Components;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
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
        $totalCoursesCount = Course::count();
        $totalQuizzesCount = Quiz::count();
        $totalUsersCount = User::count();
        $totalLessonsCount = Lesson::count();

        return view('layouts.admin', [
            'breadcrumb' => $this->breadcrumb,
            'pendingCoursesCount' => $pendingCoursesCount,
            'pendingQuizzesCount' => $pendingQuizzesCount,
            'totalCoursesCount' => $totalCoursesCount,
            'totalQuizzesCount' => $totalQuizzesCount,
            'totalUsersCount' => $totalUsersCount,
            'totalLessonsCount' => $totalLessonsCount,
        ]);
    }
}
