<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800">Quên mật khẩu?</h2>
        <p class="text-sm text-slate-500 mt-1">
            Đừng lo lắng! Hãy nhập email của bạn để nhận liên kết đặt lại mật khẩu mới.
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                {{ session('status') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block font-semibold text-sm text-slate-700 mb-1.5">
                Địa chỉ Email <span class="text-rose-500">*</span>
            </label>
            <input id="email" 
                   class="block w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition duration-150" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   placeholder="vidu@gmail.com" 
                   required 
                   autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/25 transition duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            Gửi liên kết đặt lại mật khẩu
        </button>

        <div class="text-center pt-2 border-t border-slate-100">
            <a class="text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline" href="{{ route('login') }}">
                &larr; Quay lại đăng nhập
            </a>
        </div>
    </form>
</x-guest-layout>
