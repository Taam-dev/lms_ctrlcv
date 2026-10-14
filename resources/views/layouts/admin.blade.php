@php
    $onDashboard = request()->routeIs('admin.dashboard');
    $tab = request()->query('tab');

    $panelLabel = 'Admin Panel';
    $breadcrumb = $breadcrumb ?? 'Bảng Điều Khiển Quản Trị';
    $panelHome = route('admin.dashboard');
    $topPending = ($pendingCoursesCount ?? 0) + ($pendingQuizzesCount ?? 0) + ($pendingReportsCount ?? 0);
    $topPendingUrl = ($pendingReportsCount ?? 0) > 0 && ($pendingCoursesCount ?? 0) === 0 && ($pendingQuizzesCount ?? 0) === 0
        ? route('admin.dashboard', ['tab' => 'reports', 'status' => 'pending'])
        : route('admin.dashboard', ['tab' => 'courses', 'status' => 'pending']);

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
                    'label' => 'Quản lý Khóa học / Bài học',
                    'href' => route('admin.courses.index'),
                    'active' => request()->routeIs('admin.courses.*') || ($onDashboard && in_array($tab, ['courses', 'lessons'])),
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
                    'label' => 'Kết quả học viên',
                    'href' => route('admin.dashboard', ['tab' => 'attempts']),
                    'active' => ($onDashboard && $tab === 'attempts') || request()->routeIs('admin.quizzes.results*'),
                    'badge' => ($totalAttemptsCount ?? 0) > 0 ? (string) $totalAttemptsCount : null,
                ],
            ],
        ],
        [
            'title' => 'Người dùng & Báo cáo',
            'items' => [
                [
                    'label' => 'Tài khoản người dùng',
                    'href' => route('admin.dashboard', ['tab' => 'users']),
                    'active' => $onDashboard && $tab === 'users',
                    'badge' => $totalUsersCount ?? null,
                ],
                [
                    'label' => 'Báo cáo vi phạm',
                    'href' => route('admin.dashboard', ['tab' => 'reports']),
                    'active' => $onDashboard && $tab === 'reports',
                    'badge' => ($pendingReportsCount ?? 0) > 0 ? ($pendingReportsCount . ' chờ') : null,
                    'badgeWarn' => ($pendingReportsCount ?? 0) > 0,
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
