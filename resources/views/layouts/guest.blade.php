<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniLMS') }} - Đăng nhập / Đăng ký</title>

        <!-- Fonts (Tiếng Việt chuẩn) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-[#fdf2f8] min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full {{ $maxWidth }} text-center">
            <div class="inline-block bg-white px-5 py-3 rounded-2xl shadow-sm border border-pink-100 mb-6">
                <a href="/">
                    <x-application-logo />
                </a>
            </div>
        </div>

        <div class="sm:mx-auto sm:w-full {{ $maxWidth }}">
            <div class="bg-white py-8 px-6 shadow-xl shadow-pink-100/60 rounded-3xl sm:px-10 border border-pink-100">
                {{ $slot }}
            </div>
        </div>

        <div class="mt-8 text-center text-xs text-gray-500 font-medium">
            &copy; {{ date('Y') }} MiniLMS. Hệ thống Quản lý Học tập Trực tuyến.
        </div>
    </body>
</html>
