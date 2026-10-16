@php
    $routeBase = request()->routeIs('teacher.*') ? 'teacher' : 'instructor';
    $onDashboard = request()->routeIs('instructor.dashboard') || request()->routeIs('teacher.dashboard');
    $tab = request()->query('tab');

    $panelLabel = 'Giảng viên';
    $panelHome = route($routeBase . '.dashboard');
    $topPending = ($pendingCoursesCount ?? 0) + ($pendingQuizzesCount ?? 0);

    // tao badge cho duyet cu the cho giang vien
    $topPendingBadges = [];
    if (($pendingQuizzesCount ?? 0) > 0) {
        $topPendingBadges[] = [
            'label' => $pendingQuizzesCount . ' quiz chờ duyệt',
            'url' => route($routeBase . '.dashboard', ['tab' => 'quizzes', 'status' => 'pending']),
            'class' => 'bg-pink-600 hover:bg-pink-700',
        ];
    }
    if (($pendingCoursesCount ?? 0) > 0) {
        $topPendingBadges[] = [
            'label' => $pendingCoursesCount . ' khóa học chờ duyệt',
            'url' => route($routeBase . '.dashboard', ['tab' => 'courses', 'status' => 'pending']),
            'class' => 'bg-amber-600 hover:bg-amber-700',
        ];
    }

    if (($pendingQuizzesCount ?? 0) > 0 && ($pendingCoursesCount ?? 0) === 0) {
        $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'quizzes', 'status' => 'pending']);
    } elseif (($pendingCoursesCount ?? 0) > 0 && ($pendingQuizzesCount ?? 0) === 0) {
        $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'courses', 'status' => 'pending']);
    } elseif (($pendingQuizzesCount ?? 0) > 0 && (request()->routeIs($routeBase . '.quizzes.*') || ($onDashboard && $tab === 'quizzes'))) {
        $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'quizzes', 'status' => 'pending']);
    } elseif (($pendingCoursesCount ?? 0) > 0) {
        $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'courses', 'status' => 'pending']);
    } elseif (($pendingQuizzesCount ?? 0) > 0) {
        $topPendingUrl = route($routeBase . '.dashboard', ['tab' => 'quizzes', 'status' => 'pending']);
    } else {
        $topPendingUrl = route($routeBase . '.dashboard');
    }

    $navGroups = [
        [
            'title' => 'Nội dung giảng dạy',
            'items' => [
                [
                    'label' => 'Khóa học / Bài học',
                    'href' => ($pendingCoursesCount ?? 0) > 0 ? route($routeBase . '.dashboard', ['tab' => 'courses', 'status' => 'pending']) : route($routeBase . '.dashboard', ['tab' => 'courses']),
                    'active' => ($onDashboard && (empty($tab) || in_array($tab, ['courses', 'lessons']))) || request()->routeIs($routeBase . '.courses.*') || request()->routeIs($routeBase . '.lessons.*'),
                    'badge' => ($pendingCoursesCount ?? 0) > 0 ? ($pendingCoursesCount . ' chờ duyệt') : ($totalCoursesCount ?? null),
                    'badgeWarn' => ($pendingCoursesCount ?? 0) > 0,
                ],
                [
                    'label' => 'Bài kiểm tra (Quizzes)',
                    'href' => ($pendingQuizzesCount ?? 0) > 0 ? route($routeBase . '.dashboard', ['tab' => 'quizzes', 'status' => 'pending']) : route($routeBase . '.dashboard', ['tab' => 'quizzes']),
                    'active' => ($onDashboard && $tab === 'quizzes') || (request()->routeIs($routeBase . '.quizzes.*') && !request()->routeIs($routeBase . '.quizzes.create') && !request()->routeIs($routeBase . '.quizzes.results*')),
                    'badge' => ($pendingQuizzesCount ?? 0) > 0 ? ($pendingQuizzesCount . ' chờ duyệt') : ($totalQuizzesCount ?? null),
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
                ['label' => 'Tạo bài kiểm tra (quiz)', 'href' => route($routeBase . '.quizzes.create'), 'active' => request()->routeIs($routeBase . '.quizzes.create')],
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
