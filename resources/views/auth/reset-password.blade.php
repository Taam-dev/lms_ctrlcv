<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <div class="text-center mb-7">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-neutral-950 border border-neutral-800 text-neutral-400 text-xs font-medium mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                <span>Bảo mật tài khoản</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                Đặt lại mật khẩu mới
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-neutral-400">
                Nhập email và mật khẩu mới cho tài khoản của bạn.
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5 space-y-4">
                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                        Địa chỉ Email
                    </label>
                    <input id="email" 
                           class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150" 
                           type="email" 
                           name="email" 
                           value="{{ old('email', $request->email) }}" 
                           required 
                           autofocus 
                           autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-400" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                        Mật khẩu mới
                    </label>
                    <input id="password" 
                           class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Tối thiểu 8 ký tự" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-400" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                        Xác nhận mật khẩu mới
                    </label>
                    <input id="password_confirmation" 
                           class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150" 
                           type="password" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password" 
                           placeholder="Nhập lại mật khẩu mới" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-rose-400" />
                </div>
            </div>

            <button type="submit" class="w-full min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2">
                <span>Xác nhận đổi mật khẩu</span>
                <span>&rarr;</span>
            </button>

            <div class="text-center pt-2">
                <a class="text-xs sm:text-sm font-bold text-pink-400 hover:text-pink-300 hover:underline inline-flex items-center gap-1.5" href="{{ route('login') }}">
                    &larr; Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
