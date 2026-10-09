<section>
    <header>
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            
            Đổi mật khẩu
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Đảm bảo tài khoản của bạn sử dụng mật khẩu dài và ngẫu nhiên để tăng tính bảo mật.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block font-medium text-sm text-slate-700 mb-1">
                Mật khẩu hiện tại <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_current_password" 
                   name="current_password" 
                   type="password" 
                   class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" 
                   autocomplete="current-password" 
                   placeholder="Nhập mật khẩu hiện tại" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block font-medium text-sm text-slate-700 mb-1">
                Mật khẩu mới <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_password" 
                   name="password" 
                   type="password" 
                   class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" 
                   autocomplete="new-password" 
                   placeholder="Tối thiểu 8 ký tự" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block font-medium text-sm text-slate-700 mb-1">
                Xác nhận mật khẩu mới <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_password_confirmation" 
                   name="password_confirmation" 
                   type="password" 
                   class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition duration-150" 
                   autocomplete="new-password" 
                   placeholder="Nhập lại mật khẩu mới" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-pink-600 hover:bg-pink-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-pink-500/30 transition duration-150 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:ring-offset-2">
                
                Lưu thay đổi
            </button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 3000)"
                     class="flex items-center gap-1.5 text-sm font-semibold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                    
                    Đã đổi mật khẩu thành công!
                </div>
            @endif
        </div>
    </form>
</section>
