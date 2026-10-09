<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <div class="text-center mb-7">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-950 border border-neutral-800 text-neutral-400 text-xs font-medium mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                <span>Khôi phục tài khoản</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                Quên mật khẩu?
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-neutral-400">
                Nhập email của bạn để nhận liên kết đặt lại mật khẩu mới.
            </p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-5 p-4 rounded-xl bg-neutral-950 border border-emerald-500/40 text-sm font-medium text-emerald-400 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5">
                <label for="email" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                    Địa chỉ Email
                </label>
                <input id="email" 
                       class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="email@example.com" 
                       required 
                       autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
            </div>

            <button type="submit" class="w-full min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2">
                Gửi liên kết đặt lại mật khẩu &rarr;
            </button>

            <div class="text-center pt-2">
                <a class="text-xs sm:text-sm font-bold text-pink-400 hover:text-pink-300 hover:underline" href="{{ route('login') }}">
                    &larr; Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
