@php
    $routeBase = request()->routeIs('teacher.*') ? 'teacher' : 'instructor';
    $onDashboard = request()->routeIs('instructor.dashboard') || request()->routeIs('teacher.dashboard');
    $tab = request()->query('tab');

    $panelLabel = 'Giảng viên';
    $panelHome = route($routeBase . '.dashboard');
    $topPending = ($pendingCoursesCount ?? 0) + ($pendingQuizzesCount ?? 0);
    $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'courses', 'status' => 'pending']);

    $navGroups = [
        [
            'title' => 'Tổng quan',
            'items' => [
                [
                    'label' => 'Bảng điều khiển',
                    'href' => route($routeBase . '.dashboard'),
                    'active' => $onDashboard && empty($tab),
                ],
            ],
        ],
        [
            'title' => 'Nội dung giảng dạy',
            'items' => [
                [
                    'label' => 'Khóa học của tôi',
                    'href' => route($routeBase . '.dashboard', ['tab' => 'courses']),
                    'active' => ($onDashboard && $tab === 'courses') || (request()->routeIs($routeBase . '.courses.*') && !request()->routeIs($routeBase . '.courses.create')),
                    'badge' => ($pendingCoursesCount ?? 0) > 0 ? ($pendingCoursesCount . ' chờ') : ($totalCoursesCount ?? null),
                    'badgeWarn' => ($pendingCoursesCount ?? 0) > 0,
                ],
                [
                    'label' => 'Danh sách bài giảng',
                    'href' => route($routeBase . '.dashboard', ['tab' => 'lessons']),
                    'active' => ($onDashboard && $tab === 'lessons') || request()->routeIs($routeBase . '.lessons.*'),
                ],
                [
                    'label' => 'Bài kiểm tra (Quizzes)',
                    'href' => route($routeBase . '.dashboard', ['tab' => 'quizzes']),
                    'active' => ($onDashboard && $tab === 'quizzes') || (request()->routeIs($routeBase . '.quizzes.*') && !request()->routeIs($routeBase . '.quizzes.create') && !request()->routeIs($routeBase . '.quizzes.results*')),
                    'badge' => ($pendingQuizzesCount ?? 0) > 0 ? ($pendingQuizzesCount . ' chờ') : ($totalQuizzesCount ?? null),
                    'badgeWarn' => ($pendingQuizzesCount ?? 0) > 0,
                ],
                [
                    'label' => 'Kết quả học viên',
                    'href' => route($routeBase . '.dashboard', ['tab' => 'attempts']),
                    'active' => ($onDashboard && $tab === 'attempts') || request()->routeIs($routeBase . '.quizzes.results*'),
                ],
            ],
        ],
        [
            'title' => 'Tạo mới',
            'items' => [
                ['label' => 'Tạo khóa học mới', 'href' => route($routeBase . '.courses.create'), 'active' => request()->routeIs($routeBase . '.courses.create')],
                ['label' => 'Tạo bài kiểm tra', 'href' => route($routeBase . '.quizzes.create'), 'active' => request()->routeIs($routeBase . '.quizzes.create')],
            ],
        ],
        [
            'title' => 'Hệ thống',
            'items' => [
                ['label' => 'Quay về trang chủ', 'href' => route('home')],
                ['label' => 'Hồ sơ cá nhân', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
            ],
        ],
    ];
@endphp
@include('layouts.panel')
