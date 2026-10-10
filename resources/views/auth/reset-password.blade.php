<x-guest-layout>
    <div class="w-full">
        <!-- Header row: Nút trang chủ bên trái ngang hàng với tiêu đề Đặt lại mật khẩu -->
        <div class="relative flex items-center justify-center mb-6 min-h-[36px]">
            <a href="{{ route('home') }}" 
               class="absolute left-0 inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-neutral-950/60 hover:bg-neutral-950 border border-neutral-800 hover:border-neutral-700 text-xs font-medium text-neutral-400 hover:text-white transition duration-150 group">
                <span class="transition-transform group-hover:-translate-x-0.5">&larr;</span>
                <span>Trang chủ</span>
            </a>

            <h1 class="text-xl font-bold text-white tracking-tight">
                Đặt lại mật khẩu
            </h1>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-medium text-neutral-300 mb-1.5">
                    Email
                </label>
                <input id="email" 
                       class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150" 
                       type="email" 
                       name="email" 
                       value="{{ old('email', $request->email) }}" 
                       required 
                       autofocus 
                       autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-400" />
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-medium text-neutral-300 mb-1.5">
                    Mật khẩu mới
                </label>
                <input id="password" 
                       class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150" 
                       type="password" 
                       name="password" 
                       required 
                       autocomplete="new-password" 
                       placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-400" />
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-neutral-300 mb-1.5">
                    Xác nhận mật khẩu mới
                </label>
                <input id="password_confirmation" 
                       class="block w-full rounded-xl border border-neutral-800 bg-neutral-950 px-3.5 py-2.5 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-1 focus:ring-pink-500 transition duration-150" 
                       type="password" 
                       name="password_confirmation" 
                       required 
                       autocomplete="new-password" 
                       placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-rose-400" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-pink-600 hover:bg-pink-500 active:bg-pink-700 text-white font-semibold text-sm rounded-xl transition duration-150 cursor-pointer">
                    Xác nhận đổi mật khẩu
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
