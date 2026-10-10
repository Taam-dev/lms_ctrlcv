<x-guest-layout max-width="max-w-[800px]">
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

        <form method="POST" action="{{ route('register') }}" class="w-full">
            @csrf

            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Tạo tài khoản mới
                </h1>
            </div>

            <!-- Form Grid: 2 Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Cột 1: Thông tin cá nhân -->
                <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-neutral-800/80">
                        <div class="w-8 h-8 rounded-xl bg-pink-600/10 border border-pink-500/30 flex items-center justify-center text-pink-500 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-neutral-200 uppercase tracking-wider">
                            Thông tin cá nhân
                        </h2>
                    </div>

                    <!-- Họ và tên -->
                    <div>
                        <label for="name" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                            Họ và tên
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Nhập họ và tên của bạn"
                            class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150"
                        />
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-rose-400" />
                    </div>

                    <!-- Email -->
                    <div x-data="{
                        email: '{{ old('email', '') }}',
                        emailError: '',
                        isChecking: false,
                        checkEmail() {
                            const val = (this.email || '').trim().toLowerCase();
                            if (!val || !val.includes('@') || !val.includes('.')) {
                                this.emailError = '';
                                return;
                            }
                            this.isChecking = true;
                            fetch('{{ route('check-email') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ email: val })
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.isChecking = false;
                                if (data.exists) {
                                    this.emailError = 'Email đã được sử dụng';
                                } else {
                                    this.emailError = '';
                                }
                            })
                            .catch(() => {
                                this.isChecking = false;
                            });
                        }
                    }" x-init="if (email) checkEmail()">
                        <div class="flex items-center justify-between mb-2">
                            <label for="email" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider">
                                Địa chỉ Email
                            </label>
                            <span x-show="isChecking" class="text-[11px] text-neutral-500 animate-pulse">
                                Đang kiểm tra...
                            </span>
                        </div>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            x-model="email"
                            @input.debounce.350ms="checkEmail()"
                            @blur="checkEmail()"
                            required
                            autocomplete="username"
                            placeholder="email@example.com"
                            class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150"
                            :class="{ 'border-rose-500/70 ring-1 ring-rose-500/30': emailError }"
                        />
                        <template x-if="emailError">
                            <p class="mt-1.5 text-xs text-rose-400 font-medium flex items-center gap-1.5" x-text="emailError"></p>
                        </template>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
                    </div>
                </div>

                <!-- Cột 2: Bảo mật tài khoản -->
                <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b border-neutral-800/80">
                        <div class="w-8 h-8 rounded-xl bg-pink-600/10 border border-pink-500/30 flex items-center justify-center text-pink-500 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-neutral-200 uppercase tracking-wider">
                            Bảo mật tài khoản
                        </h2>
                    </div>

                    <!-- Mật khẩu -->
                    <div>
                        <label for="password" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                            Mật khẩu
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <input
                                id="password"
                                :type="show ? 'text' : 'password'"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
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

                    <!-- Xác nhận mật khẩu -->
                    <div>
                        <label for="password_confirmation" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                            Xác nhận mật khẩu
                        </label>
                        <div class="relative" x-data="{ show: false }">
                            <input
                                id="password_confirmation"
                                :type="show ? 'text' : 'password'"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
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
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-rose-400" />
                    </div>
                </div>
            </div>

            <!-- Submit Button & Login Link -->
            <div class="mt-8 flex flex-col items-center gap-4">
                <button
                    type="submit"
                    class="w-full sm:max-w-sm min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] hover:-translate-y-0.5 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Xác nhận đăng ký</span>
                    <span>&rarr;</span>
                </button>

                <div class="text-xs sm:text-sm text-neutral-400 text-center">
                    Bạn đã có tài khoản?
                    <a
                        href="{{ route('login') }}"
                        class="font-bold text-pink-400 hover:text-pink-300 hover:underline ms-1 inline-flex items-center gap-1"
                    >
                        <span>Đăng nhập tại đây</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>