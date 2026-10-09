<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-6 p-4 rounded-2xl bg-pink-50/80 border border-pink-200 text-sm font-medium text-pink-800 flex items-center gap-3">
                <svg class="w-5 h-5 text-pink-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="w-full">
            @csrf

            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-pink-600 tracking-tight">
                    Đăng nhập
                </h2>
                <p class="mt-2 text-sm text-gray-500">
                    Chào mừng bạn quay trở lại với hệ thống học tập
                </p>
            </div>

            <!-- Card: Thông tin đăng nhập -->
            <div class="bg-white p-5 rounded-2xl border border-pink-100 shadow-sm">
                <div class="flex items-center mb-5 border-b border-pink-50 pb-3">
                    <div class="w-9 h-9 rounded-xl bg-pink-100 flex items-center justify-center text-pink-600 mr-3 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-800 whitespace-nowrap">
                        Thông tin đăng nhập
                    </h3>
                </div>

                <div class="space-y-4">
                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block font-bold text-gray-600 text-xs uppercase tracking-wider mb-1.5">
                            Địa chỉ email
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
                            class="block w-full rounded-xl border-gray-200 bg-gray-50/60 px-4 py-3 text-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-500/30 transition duration-150"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block font-bold text-gray-600 text-xs uppercase tracking-wider">
                                Mật khẩu
                            </label>
                            @if (Route::has('password.request'))
                                <a
                                    href="{{ route('password.request') }}"
                                    class="text-xs font-semibold text-pink-600 hover:text-pink-700 hover:underline transition-colors"
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
                                class="block w-full rounded-xl border-gray-200 bg-gray-50/60 px-4 py-3 pr-12 text-sm focus:border-pink-500 focus:ring-2 focus:ring-pink-500/30 transition duration-150"
                            />
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-pink-600 focus:outline-none transition-colors"
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
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
                    </div>

                    <!-- Remember Me -->
                    <div class="pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="w-4 h-4 rounded-md border-gray-300 text-pink-600 shadow-xs focus:ring-pink-500 focus:ring-offset-0 transition"
                            >
                            <span class="ms-2 text-sm text-gray-600 font-medium">Ghi nhớ đăng nhập</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Button & Register Link -->
            <div class="mt-8 flex flex-col items-center">
                <button
                    type="submit"
                    class="w-full max-w-sm min-h-[56px] px-6 py-3.5 bg-pink-600 hover:bg-pink-700 active:scale-[0.98] hover:-translate-y-0.5 text-white font-bold text-base rounded-xl shadow-lg shadow-pink-200/80 transition-all duration-200 text-center cursor-pointer flex items-center justify-center"
                >
                    Đăng nhập
                </button>

                <div class="mt-3 text-sm text-gray-500 text-center">
                    Chưa có tài khoản?
                    <a
                        href="{{ route('register') }}"
                        class="font-bold text-pink-600 hover:text-pink-700 hover:underline decoration-2 underline-offset-4 ms-1"
                    >
                        Đăng ký ngay
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
