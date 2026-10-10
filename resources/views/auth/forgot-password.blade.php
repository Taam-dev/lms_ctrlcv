<x-guest-layout>
    <div class="w-full">
        <!-- Header row: Nút trang chủ bên trái ngang hàng với tiêu đề Quên mật khẩu -->
        <div class="relative flex items-center justify-center mb-6 min-h-[36px]">
            <a href="{{ route('home') }}" 
               class="absolute left-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-neutral-950/60 hover:bg-neutral-950 border border-neutral-800 hover:border-neutral-700 text-xs font-medium text-neutral-400 hover:text-white transition duration-150 group">
                <span class="transition-transform group-hover:-translate-x-0.5">&larr;</span>
                <span>Trang chủ</span>
            </a>

            <h1 class="text-xl font-bold text-white tracking-tight">
                Quên mật khẩu
            </h1>
        </div>

        <p class="text-center -mt-2 mb-5 text-xs text-neutral-400">
            Nhập email của bạn để nhận liên kết đặt lại mật khẩu.
        </p>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-5 p-3 rounded-xl bg-neutral-950 border border-emerald-500/30 text-xs font-medium text-emerald-400 flex items-center gap-2.5">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-medium text-neutral-300 mb-1.5">
                    Email
                </label>
                <input id="email" 
                       class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       placeholder="email@example.com" 
                       required 
                       autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-400" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-pink-600 hover:bg-pink-500 active:bg-pink-700 text-white font-semibold text-sm rounded-xl transition duration-150 cursor-pointer">
                    Gửi liên kết đặt lại mật khẩu
                </button>
            </div>

            <div class="text-center pt-2">
                <a class="text-xs text-neutral-400 hover:text-pink-400 transition-colors" href="{{ route('login') }}">
                    &larr; Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
