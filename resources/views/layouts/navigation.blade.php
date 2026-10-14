<nav x-data="{ open: false }" class="bg-neutral-950 sticky top-0 z-50 border-b border-white/10">
    <!-- Solid accent line -->
    <div class="h-0.5 bg-pink-600"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo :dark="true" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ms-10 sm:flex items-stretch gap-1">
                    @php
                        $navLink = fn (bool $active) => 'inline-flex items-center px-4 text-xs font-extrabold uppercase tracking-[0.14em] border-b-2 transition-colors duration-150 '
                            . ($active ? 'text-white border-pink-500' : 'text-neutral-400 border-transparent hover:text-white hover:border-pink-500/60');
                    @endphp
                    <a href="{{ route('home') }}" class="{{ $navLink(request()->routeIs('home')) }}">Trang chủ</a>
                    <a href="{{ route('courses.index') }}" class="{{ $navLink(request()->routeIs('courses.index') || request()->routeIs('student.courses.*') || request()->routeIs('student.lessons.*')) }}">Khóa học</a>
                    @auth
                        <a href="{{ route('student.quizzes.index') }}" class="{{ $navLink(request()->routeIs('student.quizzes.*')) }}">Bài kiểm tra</a>
                    @endauth
                </div>
            </div>

            <!-- Auth -->
            <div class="hidden sm:flex sm:items-center sm:gap-2.5 sm:ms-6">
                @auth
                    <!-- Icon chuông thông báo trắng đen (kế bên và bên trái profile) -->
                    <x-notification-bell :dark="true" />

                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-3 px-3 py-1.5 border border-white/15 hover:border-pink-500 text-left focus:outline-none transition duration-150">
                                @if(Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0" />
                                @else
                                    <div class="w-8 h-8 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-xs shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="leading-tight">
                                    <div class="font-bold text-white text-xs">{{ Auth::user()->name }}</div>
                                    <div class="text-[10px] font-extrabold uppercase tracking-wider text-pink-500">
                                        @if(Auth::user()->isAdmin())
                                            Admin
                                        @elseif(Auth::user()->isTeacher() || Auth::user()->isInstructor())
                                            Giảng viên
                                        @else
                                            Học viên
                                        @endif
                                    </div>
                                </div>
                                <span class="text-neutral-500 text-xs">&#9662;</span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 text-xs text-slate-500 border-b border-slate-100 text-center truncate">
                                Đang đăng nhập: <strong class="text-slate-800">{{ Auth::user()->email }}</strong>
                            </div>

                            <!-- Option Ví tiền với icon trắng đen & số tiền căn giữa cân đối -->
                            <x-dropdown-link :href="route('wallet.deposit')" class="!flex items-center justify-center gap-3 text-slate-800 hover:bg-slate-50 transition border-b border-slate-100 py-2.5 text-center">
                                <div class="inline-flex items-center gap-2 shrink-0 whitespace-nowrap">
                                    <svg class="w-4 h-4 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    <span class="font-bold text-slate-900 whitespace-nowrap">Ví&nbsp;tiền</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-black bg-neutral-900 text-white whitespace-nowrap shrink-0">
                                    {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }} đ
                                </span>
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.edit')" class="!text-center font-medium">
                                {{ __('Hồ sơ cá nhân') }}
                            </x-dropdown-link>

                            @if(Auth::user()->isTeacher() || Auth::user()->isInstructor())
                                <x-dropdown-link :href="route('instructor.dashboard')" class="!text-center text-pink-700 hover:text-pink-800 hover:bg-pink-50 font-bold">
                                    {{ __('GV') }}
                                </x-dropdown-link>
                            @endif

                            @if(Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('admin.dashboard')" class="!text-center text-pink-700 hover:text-pink-800 hover:bg-pink-50 font-bold">
                                    {{ __('ADMIN') }}
                                </x-dropdown-link>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="!text-center text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-medium">
                                    {{ __('Đăng xuất') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-xs font-extrabold uppercase tracking-[0.14em] text-neutral-300 hover:text-white px-3 py-2 transition">
                            Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="text-xs font-extrabold uppercase tracking-[0.14em] bg-pink-600 hover:bg-pink-500 text-white px-5 py-2.5 transition">
                            Đăng ký
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="px-3 py-1.5 text-xs font-extrabold uppercase tracking-wider text-white border border-white/20 hover:border-pink-500 focus:outline-none transition" aria-label="Mở menu">
                    <span x-show="!open">Menu</span>
                    <span x-show="open" style="display: none;">Đóng</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-white/10 bg-neutral-950 px-4 pt-2 pb-4 space-y-1">
        <a href="{{ route('home') }}" class="block px-3 py-2.5 text-sm font-bold border-l-2 {{ request()->routeIs('home') ? 'text-white border-pink-500 bg-white/5' : 'text-neutral-400 border-transparent' }}">
            Trang chủ
        </a>
        <a href="{{ route('courses.index') }}" class="block px-3 py-2.5 text-sm font-bold border-l-2 {{ request()->routeIs('courses.index') ? 'text-white border-pink-500 bg-white/5' : 'text-neutral-400 border-transparent' }}">
            Khóa học
        </a>

        @auth
            <a href="{{ route('student.quizzes.index') }}" class="block px-3 py-2.5 text-sm font-bold border-l-2 {{ request()->routeIs('student.quizzes.*') ? 'text-white border-pink-500 bg-white/5' : 'text-neutral-400 border-transparent' }}">
                Bài kiểm tra
            </a>

            <div class="pt-3 mt-2 border-t border-white/10">
                <div class="px-3 flex items-center gap-3">
                    @if(Auth::user()->avatar_url)
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover shrink-0" />
                    @else
                        <div class="w-10 h-10 rounded-full bg-pink-600 text-white flex items-center justify-center font-black text-base shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="font-bold text-white leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-neutral-500">{{ Auth::user()->email }} ({{ Auth::user()->role }})</div>
                    </div>
                </div>

                <div class="mt-2 space-y-0.5">
                    <a href="{{ route('wallet.deposit') }}" class="flex items-center justify-between px-3 py-2 text-sm text-neutral-200 hover:text-white hover:bg-white/5 rounded-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="font-bold">Ví tiền</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-black bg-neutral-900 text-white border border-neutral-700">
                            {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }} đ
                        </span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-between px-3 py-2 text-sm text-neutral-200 hover:text-white hover:bg-white/5 rounded-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span class="font-bold">Thông báo</span>
                        </div>
                        @if(Auth::user()->unreadNotificationsCount() > 0)
                            <span class="px-2 py-0.5 rounded text-xs font-black bg-pink-600 text-white">
                                {{ Auth::user()->unreadNotificationsCount() }} mới
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm text-neutral-300 hover:text-white">
                        Hồ sơ cá nhân
                    </a>
                    @if(Auth::user()->isTeacher() || Auth::user()->isInstructor())
                        <a href="{{ route('instructor.dashboard') }}" class="block px-3 py-2 text-sm text-pink-400 font-bold">
                            GV
                        </a>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm text-pink-400 font-bold">
                            ADMIN
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-rose-400 font-medium">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-3 mt-2 border-t border-white/10 grid grid-cols-2 gap-2">
                <a href="{{ route('login') }}" class="block text-center py-2.5 text-xs font-extrabold uppercase tracking-wider text-white border border-white/20">
                    Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="block text-center py-2.5 text-xs font-extrabold uppercase tracking-wider text-white bg-pink-600">
                    Đăng ký
                </a>
            </div>
        @endauth
    </div>
</nav>