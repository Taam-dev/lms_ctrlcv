<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $siteBrand = 'Ctrl C+V';
            $defaultTitle = 'Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1';
            $seoTitle = $title ?? (View::hasSection('title') ? View::getSection('title') : $defaultTitle);
            
            $defaultDesc = 'Nền tảng học tập tinh gọn giúp bạn tiếp cận bài giảng chất lượng, làm bài trắc nghiệm tự chấm điểm và nắm bắt tiến độ học tập minh bạch.';
            $seoDesc = $metaDescription ?? (View::hasSection('meta_description') ? View::getSection('meta_description') : $defaultDesc);
            
            $defaultKeywords = 'học trực tuyến, khóa học online, ctrl c+v, quizzes, thi trắc nghiệm, bài giảng, lms';
            $seoKeywords = $metaKeywords ?? (View::hasSection('meta_keywords') ? View::getSection('meta_keywords') : $defaultKeywords);
            
            $seoCanonical = $canonical ?? (View::hasSection('canonical') ? View::getSection('canonical') : url()->current());
        @endphp

        <title>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDesc }}">
        <meta name="keywords" content="{{ $seoKeywords }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ $seoCanonical }}">
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <!-- Open Graph / Facebook / Zalo -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://ctrlcv.io.vn">
        <meta property="og:title" content="Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1">
        <meta property="og:description" content="Nền tảng học tập tinh gọn giúp bạn tiếp cận bài giảng chất lượng, làm bài trắc nghiệm tự chấm điểm và nắm bắt tiến độ học tập minh bạch.">
        <meta property="og:image" content="{{ asset('images/thumbnail.png') }}">

        <!-- Twitter / X Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="https://ctrlcv.io.vn">
        <meta name="twitter:title" content="Ctrl C+V - Nền tảng Học trực tuyến | Đồ án 1">
        <meta name="twitter:description" content="Nền tảng học tập trực tuyến được phát triển bởi nhóm CtrlC+V">
        <meta name="twitter:image" content="{{ asset('images/thumbnail.png') }}">

        <!-- Schema.org EducationalOrganization JSON-LD -->
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'EducationalOrganization',
            'name' => 'Ctrl C+V',
            'url' => 'https://ctrlcv.io.vn',
            'logo' => asset('logo.png'),
            'description' => 'Nền tảng học trực tuyến tinh gọn, học qua bài giảng và luyện tập qua quizzes.',
            'sameAs' => [],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>

        @stack('schema')

        <!-- Fonts (Hỗ trợ tiếng Việt chuẩn, không lỗi font chữ o, ó, ô) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Cropper.js (Image cropping) -->
        <link rel="stylesheet" href="{{ asset('vendor/cropperjs/cropper.min.css') }}">
        <script src="{{ asset('vendor/cropperjs/cropper.min.js') }}"></script>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-[#fdf2f8] text-slate-800 min-h-full flex flex-col">
        <div class="flex-grow flex flex-col">
            <!-- Header Navigation -->
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white border-b border-pink-100 relative">
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-pink-600"></span>
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Global Alert Messages -->
            @if(session('success') || session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full space-y-2">
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show"
                             class="flex items-center justify-between p-4 text-sm text-emerald-800 bg-white border-l-4 border-emerald-500 shadow-sm font-semibold"
                             role="alert">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 px-2 text-lg leading-none" aria-label="Đóng thông báo">&times;</button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show"
                             class="flex items-center justify-between p-4 text-sm text-rose-800 bg-white border-l-4 border-rose-500 shadow-sm font-semibold"
                             role="alert">
                            <span>{{ session('error') }}</span>
                            <button @click="show = false" class="text-rose-700 hover:text-rose-900 px-2 text-lg leading-none" aria-label="Đóng thông báo">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Page Content -->
            <main class="flex-grow">
                {{ $slot }}
            </main>
        </div>

        <!-- Footer -->
        @include('layouts.footer')

        @stack('scripts')
    </body>
</html>
