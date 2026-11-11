<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ctrl C+V - {{ $panelLabel }}</title>

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
    <style>
        .sidebar-scroll {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .sidebar-scroll::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#fdf2f8] text-slate-800 min-h-full" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex w-full">

        <!-- Mobile backdrop -->
        <div x-show="sidebarOpen"
             x-transition.opacity
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/70 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- ================= SIDEBAR ================= -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-neutral-950 text-neutral-300 flex flex-col transition-transform duration-300 ease-in-out select-none border-r border-white/5">

            <!-- Accent line -->
            <div class="h-1 shrink-0 bg-pink-600"></div>

            <!-- Brand -->
            <div class="px-5 py-6 flex items-center justify-between border-b border-white/10">
                <a href="{{ $panelHome }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Ctrl C+V" class="h-10 w-10 object-contain rounded-lg bg-white p-0.5">
                    <div class="leading-none">
                        <span class="block text-lg font-black tracking-tight text-white">Ctrl C+V</span>
                        <span class="block mt-1.5 text-[10px] font-extrabold uppercase tracking-[0.2em] text-pink-500">{{ $panelLabel }}</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-neutral-400 hover:text-white px-2 text-lg leading-none" aria-label="Đóng menu">&times;</button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-7 sidebar-scroll [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                @foreach($navGroups as $group)
                    <div>
                        <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-[0.18em] text-neutral-500">
                            {{ $group['title'] }}
                        </div>
                        <div class="space-y-0.5">
                            @foreach($group['items'] as $item)
                                @php $isActive = $item['active'] ?? false; @endphp
                                <a href="{{ $item['href'] }}"
                                   class="flex items-center justify-between gap-2 px-3 py-2.5 text-[13px] font-semibold border-l-2 transition-colors duration-150 {{ $isActive ? 'border-pink-500 bg-white/[0.07] text-white' : 'border-transparent text-neutral-400 hover:text-white hover:bg-white/[0.04] hover:border-pink-500/50' }}">
                                    <span class="truncate min-w-0 flex-1">{{ $item['label'] }}</span>
                                    @if(isset($item['badge']) && $item['badge'] !== null)
                                        <span class="shrink-0 whitespace-nowrap min-w-[1.5rem] text-center px-1.5 py-0.5 text-[10px] font-black {{ ($item['badgeWarn'] ?? false) ? 'bg-pink-600 text-white' : 'bg-white/10 text-neutral-400' }}">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <!-- User card -->
            <div class="p-3 border-t border-white/10">
                <div class="p-3 bg-white/[0.04] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0" />
                        @else
                            <div class="w-9 h-9 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-sm shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 leading-tight">
                            <div class="font-bold text-xs text-white truncate">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-neutral-500 truncate">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[11px] font-bold uppercase tracking-wider text-neutral-400 hover:text-pink-400 transition">
                            Thoát
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ================= MAIN ================= -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0 w-full">

            <!-- Top bar -->
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-pink-100 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-neutral-900 border border-neutral-900 hover:bg-neutral-900 hover:text-white transition" aria-label="Mở menu">
                        Menu
                    </button>
                    <div class="flex items-center gap-3 text-sm font-extrabold text-neutral-900">
                        <span class="w-1 h-5 bg-pink-600"></span>
                        <span>{{ $breadcrumb ?? $panelLabel }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3">
                    @if(!empty($topPendingBadges))
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($topPendingBadges as $pBadge)
                                <a href="{{ $pBadge['url'] }}"
                                   class="px-2.5 py-1.5 {{ $pBadge['class'] ?? 'bg-pink-600' }} text-white text-xs font-bold hover:opacity-90 transition flex items-center gap-1.5 shadow-2xs shrink-0 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse shrink-0"></span>
                                    <span class="whitespace-nowrap">{{ $pBadge['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @elseif(($topPending ?? 0) > 0)
                        <a href="{{ $topPendingUrl ?? '#' }}"
                           class="px-3 py-1.5 bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 transition flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse shrink-0"></span>
                            <span class="whitespace-nowrap">{{ $topPending }} mục chờ duyệt</span>
                        </a>
                    @endif
                    <x-notification-bell :dark="false" />
                    <a href="{{ route('home') }}" class="hidden sm:inline text-xs font-bold uppercase tracking-wider text-neutral-500 hover:text-pink-600 transition">
                        Trang chủ
                    </a>
                </div>
            </header>

            <!-- Page header slot -->
            @isset($header)
                <div class="bg-white border-b border-pink-100 px-4 sm:px-6 lg:px-6 xl:px-8 py-5 sm:py-6 relative w-full">
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-pink-600"></span>
                    {{ $header }}
                </div>
            @endisset

            <!-- Content -->
            <main class="panel-main flex-1 p-4 sm:p-6 lg:p-6 xl:p-8 space-y-6 w-full">
                <!-- Floating Centered Toast Notifications -->
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

                                <div class="absolute bottom-0 left-0 right-0 h-1 bg-zinc-800/80">
                                    <div class="h-full bg-gradient-to-r from-rose-500 to-pink-500 transition-all duration-75 ease-linear"
                                         :style="`width: ${percent}%`"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="mt-auto px-6 py-4 border-t border-pink-100 bg-white text-xs text-neutral-400 flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>&copy; {{ date('Y') }} <strong class="text-neutral-800">Ctrl C+V</strong> &mdash; Đồ án 1</div>
                <div class="font-semibold uppercase tracking-widest">{{ $panelLabel }}</div>
            </footer>
        </div>
    </div>

    <!-- Trợ lý AI Chatbox -->
    <x-ai-chatbox />

    @stack('scripts')
</body>
</html>
