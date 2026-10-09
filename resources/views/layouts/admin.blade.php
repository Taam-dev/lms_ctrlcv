@php
    $onDashboard = request()->routeIs('admin.dashboard');
    $tab = request()->query('tab');

    $panelLabel = 'Admin Panel';
    $breadcrumb = $breadcrumb ?? 'Bảng Điều Khiển Quản Trị';
    $panelHome = route('admin.dashboard');
    $topPending = ($pendingCoursesCount ?? 0) + ($pendingQuizzesCount ?? 0);
    $topPendingUrl = route('admin.dashboard', ['tab' => 'courses', 'status' => 'pending']);

    $navGroups = [
        [
            'title' => 'Tổng quan',
            'items' => [
                [
                    'label' => 'Dashboard tổng quan',
                    'href' => route('admin.dashboard', ['tab' => 'overview']),
                    'active' => $onDashboard && (!$tab || $tab === 'overview'),
                ],
            ],
        ],
        [
            'title' => 'Đào tạo & duyệt',
            'items' => [
                [
                    'label' => 'Quản lý Khóa học',
                    'href' => route('admin.courses.index'),
                    'active' => request()->routeIs('admin.courses.*') || ($onDashboard && $tab === 'courses'),
                    'badge' => ($pendingCoursesCount ?? 0) > 0 ? ($pendingCoursesCount . ' chờ') : ($totalCoursesCount ?? null),
                    'badgeWarn' => ($pendingCoursesCount ?? 0) > 0,
                ],
                [
                    'label' => 'Bài kiểm tra (Quizzes)',
                    'href' => route('admin.quizzes.index'),
                    'active' => request()->routeIs('admin.quizzes.*') || ($onDashboard && $tab === 'quizzes'),
                    'badge' => ($pendingQuizzesCount ?? 0) > 0 ? ($pendingQuizzesCount . ' chờ') : ($totalQuizzesCount ?? null),
                    'badgeWarn' => ($pendingQuizzesCount ?? 0) > 0,
                ],
                [
                    'label' => 'Tất cả bài giảng',
                    'href' => route('admin.dashboard', ['tab' => 'lessons']),
                    'active' => $onDashboard && $tab === 'lessons',
                    'badge' => $totalLessonsCount ?? null,
                ],
            ],
        ],
        [
            'title' => 'Người dùng',
            'items' => [
                [
                    'label' => 'Tài khoản người dùng',
                    'href' => route('admin.dashboard', ['tab' => 'users']),
                    'active' => $onDashboard && $tab === 'users',
                    'badge' => $totalUsersCount ?? null,
                ],
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
