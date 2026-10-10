<x-guest-layout>
    <div class="w-full">
        <!-- Header row: Nút trang chủ bên trái ngang hàng với tiêu đề Đăng nhập -->
        <div class="relative flex items-center justify-center mb-6 min-h-[36px]">
            <a href="{{ route('home') }}" 
               class="absolute left-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-neutral-950/60 hover:bg-neutral-950 border border-neutral-800 hover:border-neutral-700 text-xs font-medium text-neutral-400 hover:text-white transition duration-150 group">
                <span class="transition-transform group-hover:-translate-x-0.5">&larr;</span>
                <span>Trang chủ</span>
            </a>

            <h1 class="text-xl font-bold text-white tracking-tight">
                Đăng nhập
            </h1>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-neutral-950 border border-emerald-500/30 text-xs font-medium text-emerald-400 flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-medium text-neutral-300 mb-1.5">
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="email@example.com"
                    class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-400" />
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-medium text-neutral-300">
                        Mật khẩu
                    </label>
                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="text-xs text-neutral-400 hover:text-pink-400 transition-colors"
                        >
                            Quên mật khẩu?
                        </a>
                    @endif
                </div>
                <div class="relative" x-data="{ show: false }">
                    <input
                        id="password"
                        :type="show ? 'text' : 'password'"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 pr-10 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-neutral-300 focus:outline-none transition-colors"
                        :aria-label="show ? 'Ẩn mật khẩu' : 'Hiển thị mật khẩu'"
                    >
                        <svg :class="{ 'hidden': show }" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg :class="{ 'hidden': !show }" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.053 0 2.062.18 3 .512M7.5 7.5l9 9M10.125 10.125a3 3 0 114.25 4.25" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-400" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="w-4 h-4 rounded border-neutral-700 bg-neutral-950 text-pink-600 focus:ring-pink-500/20 focus:ring-offset-0 transition"
                    >
                    <span class="ms-2 text-xs text-neutral-400">Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-2.5 px-4 bg-pink-600 hover:bg-pink-500 active:bg-pink-700 text-white font-semibold text-sm rounded-xl transition duration-150 cursor-pointer"
                >
                    Đăng nhập
                </button>
            </div>

            <!-- Switch to Register -->
            <div class="text-xs text-neutral-400 text-center pt-2">
                Chưa có tài khoản?
                <a
                    href="{{ route('register') }}"
                    class="text-pink-400 hover:text-pink-300 font-medium ms-1"
                >
                    Đăng ký
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
