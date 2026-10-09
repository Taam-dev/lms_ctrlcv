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
</head>
<body class="font-sans antialiased bg-[#fdf2f8] text-slate-800 min-h-full" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">

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
            <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-7">
                @foreach($navGroups as $group)
                    <div>
                        <div class="px-3 mb-2 text-[10px] font-extrabold uppercase tracking-[0.18em] text-neutral-500">
                            {{ $group['title'] }}
                        </div>
                        <div class="space-y-0.5">
                            @foreach($group['items'] as $item)
                                @php $isActive = $item['active'] ?? false; @endphp
                                <a href="{{ $item['href'] }}"
                                   class="flex items-center justify-between px-3 py-2.5 text-[13px] font-semibold border-l-2 transition-colors duration-150 {{ $isActive ? 'border-pink-500 bg-white/[0.07] text-white' : 'border-transparent text-neutral-400 hover:text-white hover:bg-white/[0.04] hover:border-pink-500/50' }}">
                                    <span>{{ $item['label'] }}</span>
                                    @if(isset($item['badge']) && $item['badge'] !== null)
                                        <span class="min-w-[1.5rem] text-center px-1.5 py-0.5 text-[10px] font-black {{ ($item['badgeWarn'] ?? false) ? 'bg-pink-600 text-white' : 'bg-white/10 text-neutral-400' }}">
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
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">

            <!-- Top bar -->
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-pink-100 px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-neutral-900 border border-neutral-900 hover:bg-neutral-900 hover:text-white transition" aria-label="Mở menu">
                        Menu
                    </button>
                    <div class="flex items-center gap-3 text-sm font-extrabold text-neutral-900">
                        <span class="w-1 h-5 bg-pink-600"></span>
                        <span>{{ $breadcrumb ?? $panelLabel }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if(($topPending ?? 0) > 0)
                        <a href="{{ $topPendingUrl ?? '#' }}"
                           class="px-3 py-1.5 bg-pink-600 text-white text-xs font-bold hover:bg-pink-700 transition">
                            {{ $topPending }} mục chờ duyệt
                        </a>
                    @endif
                    <a href="{{ route('home') }}" class="hidden sm:inline text-xs font-bold uppercase tracking-wider text-neutral-500 hover:text-pink-600 transition">
                        Trang chủ
                    </a>
                </div>
            </header>

            <!-- Page header slot -->
            @isset($header)
                <div class="bg-white border-b border-pink-100 px-4 sm:px-6 lg:px-6 xl:px-8 py-5 sm:py-6 relative">
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-pink-600"></span>
                    {{ $header }}
                </div>
            @endisset

            <!-- Content -->
            <main class="panel-main flex-1 p-4 sm:p-6 lg:p-6 xl:p-8 space-y-6">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show"
                         class="flex items-center justify-between p-4 bg-white border-l-4 border-emerald-500 shadow-sm text-emerald-800 text-sm font-semibold">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 px-2 text-lg leading-none" aria-label="Đóng thông báo">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         class="flex items-center justify-between p-4 bg-white border-l-4 border-rose-500 shadow-sm text-rose-800 text-sm font-semibold">
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="text-rose-700 hover:text-rose-900 px-2 text-lg leading-none" aria-label="Đóng thông báo">&times;</button>
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
    @stack('scripts')
</body>
</html>
