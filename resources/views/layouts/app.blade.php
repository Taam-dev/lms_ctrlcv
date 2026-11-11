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

            <!-- Floating Centered Toast Notifications (Không đẩy giao diện, hiển thị giữa màn hình) -->
            @if(session('success') || session('error'))
                <div class="fixed top-8 left-1/2 -translate-x-1/2 z-[9999] w-[92%] max-w-md pointer-events-none space-y-3">
                    @if(session('success'))
                        <div x-data="{ show: true, percent: 100 }"
                             x-show="show"
                             x-cloak
                             x-init="
                                 const start = Date.now();
                                 const duration = 4000;
                                 const timer = setInterval(() => {
                                     const elapsed = Date.now() - start;
                                     percent = Math.max(0, 100 - (elapsed / duration) * 100);
                                     if (elapsed >= duration) {
                                         clearInterval(timer);
                                         show = false;
                                     }
                                 }, 25);
                             "
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 -translate-y-6 scale-95"
                             class="pointer-events-auto relative overflow-hidden bg-zinc-900/95 text-white border border-emerald-500/40 rounded-2xl p-4 shadow-2xl shadow-black/80 backdrop-blur-xl"
                             role="alert">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 shrink-0 shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0 pt-0.5">
                                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-400">Thành công</h4>
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-100 mt-0.5 leading-snug">{{ session('success') }}</p>
                                </div>

                                <button type="button"
                                        @click="show = false"
                                        class="w-7 h-7 rounded-lg text-zinc-400 hover:text-white hover:bg-white/10 flex items-center justify-center transition shrink-0 cursor-pointer"
                                        aria-label="Đóng thông báo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Thanh đếm ngược thời gian tự đóng -->
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-zinc-800/80">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-75 ease-linear"
                                     :style="`width: ${percent}%`"></div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true, percent: 100 }"
                             x-show="show"
                             x-cloak
                             x-init="
                                 const start = Date.now();
                                 const duration = 5000;
                                 const timer = setInterval(() => {
                                     const elapsed = Date.now() - start;
                                     percent = Math.max(0, 100 - (elapsed / duration) * 100);
                                     if (elapsed >= duration) {
                                         clearInterval(timer);
                                         show = false;
                                     }
                                 }, 25);
                             "
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 -translate-y-6 scale-95"
                             class="pointer-events-auto relative overflow-hidden bg-zinc-900/95 text-white border border-rose-500/40 rounded-2xl p-4 shadow-2xl shadow-black/80 backdrop-blur-xl"
                             role="alert">
                            
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-rose-500/20 border border-rose-500/40 flex items-center justify-center text-rose-400 shrink-0 shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0 pt-0.5">
                                    <h4 class="text-[11px] font-extrabold uppercase tracking-wider text-rose-400">Thông báo lỗi</h4>
                                    <p class="text-xs sm:text-sm font-semibold text-zinc-100 mt-0.5 leading-snug">{{ session('error') }}</p>
                                </div>

                                <button type="button"
                                        @click="show = false"
                                        class="w-7 h-7 rounded-lg text-zinc-400 hover:text-white hover:bg-white/10 flex items-center justify-center transition shrink-0 cursor-pointer"
                                        aria-label="Đóng thông báo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Thanh đếm ngược thời gian tự đóng -->
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-zinc-800/80">
                                <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 transition-all duration-75 ease-linear"
                                     :style="`width: ${percent}%`"></div>
                            </div>
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

        <!-- Trợ lý AI Chatbox -->
        <x-ai-chatbox />

        @stack('scripts')
    </body>
</html>
