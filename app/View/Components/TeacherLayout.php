<?php

namespace App\View\Components;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TeacherLayout extends Component
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
        $teacherId = auth()->id();

        return view('layouts.teacher', [
            'breadcrumb' => $this->breadcrumb,
            'totalCoursesCount' => Course::where('teacher_id', $teacherId)->count(),
            'totalQuizzesCount' => Quiz::where('teacher_id', $teacherId)->count(),
            'pendingCoursesCount' => Course::where('teacher_id', $teacherId)->where('status', 'pending')->count(),
            'pendingQuizzesCount' => Quiz::where('teacher_id', $teacherId)->where('status', 'pending')->count(),
        ]);
    }
}
