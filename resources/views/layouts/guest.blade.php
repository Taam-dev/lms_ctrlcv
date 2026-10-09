<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniLMS') }} - Đăng nhập / Đăng ký</title>
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <!-- Fonts (Plus Jakarta Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-neutral-950 text-neutral-200 min-h-full flex flex-col justify-between relative selection:bg-pink-600 selection:text-white">
        <!-- Top accent pink line matching navbar & footer -->
        <div class="h-0.5 bg-pink-600 w-full fixed top-0 left-0 z-50"></div>

        <!-- Ambient Hero Background Layer matching Homepage -->
        <div class="fixed inset-0 z-0 pointer-events-none select-none overflow-hidden">
            <img src="{{ asset('images/hero-bg.jpg') }}" 
                 alt="" 
                 class="w-full h-full object-cover object-center opacity-25 mix-blend-screen scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-neutral-950/80 via-neutral-950/95 to-neutral-950"></div>
            <div class="absolute inset-0 bg-neutral-950/30 backdrop-blur-[2px]"></div>
        </div>

        <!-- Header / Logo -->
        <header class="relative z-10 pt-8 sm:pt-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-2 text-xs font-bold text-neutral-400 hover:text-pink-400 uppercase tracking-wider transition-colors">
                    <span class="group-hover:-translate-x-1 transition-transform">&larr;</span>
                    <span>Về trang chủ</span>
                </a>

                <a href="{{ route('home') }}" class="transition-transform hover:scale-105 inline-block">
                    <x-application-logo :dark="true" />
                </a>

                <div class="w-24 hidden sm:block"></div>
            </div>
        </header>

        <!-- Main Auth Form -->
        <main class="relative z-10 flex-1 flex flex-col justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full {{ $maxWidth ?? 'max-w-md' }} mx-auto">
                <div class="bg-neutral-900/90 backdrop-blur-xl border border-neutral-800 rounded-3xl p-6 sm:p-10 shadow-2xl shadow-black/80 relative overflow-hidden">
                    <!-- Top Pink Glow highlight -->
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-80 h-32 bg-pink-600/20 rounded-full blur-3xl pointer-events-none"></div>

                    {{ $slot }}
                </div>
            </div>
        </main>

        <!-- Bottom Footer -->
        <footer class="relative z-10 py-6 text-center text-xs text-neutral-500 font-medium">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4">
                <span>&copy; {{ date('Y') }} <strong class="text-neutral-400">Ctrl C+V</strong> &bull; MiniLMS</span>
                <span class="hidden sm:inline text-neutral-700">&bull;</span>
                <span>Hệ thống Quản lý Học tập & Luyện thi Quizzes</span>
            </div>
        </footer>
    </body>
</html>
