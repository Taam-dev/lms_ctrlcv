<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-white">Xác nhận mật khẩu</h1>
            <p class="text-xs text-neutral-400 mt-1">
                Đây là khu vực bảo mật. Vui lòng xác nhận mật khẩu trước khi tiếp tục.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            <!-- Password -->
            <div class="bg-neutral-950/60 border border-neutral-800 rounded-2xl p-5">
                <label for="password" class="block font-bold text-neutral-300 text-xs uppercase tracking-wider mb-2">
                    Mật khẩu
                </label>
                <input id="password" class="block w-full rounded-xl border border-neutral-800 bg-neutral-900/90 px-4 py-3 text-sm text-white placeholder-neutral-500 focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 transition duration-150"
                       type="password"
                       name="password"
                       required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-400" />
            </div>

            <button type="submit" class="w-full min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center">
                Xác nhận
            </button>
        </form>
    </div>
</x-guest-layout>
