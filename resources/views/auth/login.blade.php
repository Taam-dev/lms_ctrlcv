<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-2xl bg-neutral-950 border border-emerald-500/40 text-sm font-medium text-emerald-400 flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="w-full">
            @csrf

            <!-- Header -->
            <div class="text-center mb-7">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-950 border border-neutral-800 text-neutral-400 text-xs font-medium mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                    <span>Hệ thống học tập Ctrl C+V</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Đăng nhập tài khoản
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-neutral-400">
                    Chào mừng bạn quay trở lại! Nhập thông tin để tiếp tục học tập.
                </p>
            </div>

            <!-- Card: Form Inputs -->
            <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5 sm:p-6 space-y-4">
                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                        Địa chỉ Email
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
                        class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider">
                            Mật khẩu
                        </label>
                        @if (Route::has('password.request'))
                            <a
                                href="{{ route('password.request') }}"
                                class="text-xs font-semibold text-pink-400 hover:text-pink-300 hover:underline transition-colors"
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
                            class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 pr-12 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150"
                        />
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-pink-400 focus:outline-none transition-colors"
                            :aria-label="show ? 'Ẩn mật khẩu' : 'Hiển thị mật khẩu'"
                        >
                            <svg :class="{ 'hidden': show }" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg :class="{ 'hidden': !show }" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 1.274-4.057 5.064-7 9.542-7 1.053 0 2.062.18 3 .512M7.5 7.5l9 9M10.125 10.125a3 3 0 114.25 4.25" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-400" />
                </div>

                <!-- Remember Me -->
                <div class="pt-1 flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="w-4 h-4 rounded-md border-neutral-700 bg-neutral-900 text-pink-600 focus:ring-pink-500/20 focus:ring-offset-0 transition"
                        >
                        <span class="ms-2.5 text-xs sm:text-sm text-neutral-300 font-medium">Ghi nhớ đăng nhập</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button & Register Link -->
            <div class="mt-7 space-y-4">
                <button
                    type="submit"
                    class="w-full min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] hover:-translate-y-0.5 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Đăng nhập ngay</span>
                    <span>&rarr;</span>
                </button>

                <div class="text-xs sm:text-sm text-neutral-400 text-center">
                    Chưa có tài khoản?
                    <a
                        href="{{ route('register') }}"
                        class="font-bold text-pink-400 hover:text-pink-300 hover:underline ms-1 inline-flex items-center gap-1"
                    >
                        <span>Đăng ký miễn phí</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
