<x-guest-layout max-width="max-w-[480px]">
    <div class="w-full">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-white">Xác thực địa chỉ Email</h1>
            <p class="text-xs text-neutral-400 mt-2 leading-relaxed">
                Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, vui lòng xác thực địa chỉ email bằng cách nhấp vào liên kết chúng tôi vừa gửi qua hòm thư của bạn.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-5 p-4 rounded-xl bg-neutral-950 border border-emerald-500/40 text-xs font-medium text-emerald-400">
                Một liên kết xác thực mới vừa được gửi tới email bạn đã cung cấp khi đăng ký.
            </div>
        @endif

        <div class="mt-6 flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full min-h-[50px] px-6 py-3.5 bg-pink-600 hover:bg-pink-500 active:scale-[0.99] text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-pink-600/30 transition-all duration-200 cursor-pointer flex items-center justify-center">
                    Gửi lại email xác thực
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="text-xs font-semibold text-neutral-400 hover:text-pink-400 transition underline">
                    Đăng xuất
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
