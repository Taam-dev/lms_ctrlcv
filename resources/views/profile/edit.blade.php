<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">
                    Hồ sơ cá nhân
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Quản lý thông tin tài khoản, ảnh đại diện và bảo mật
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-pink-50 border border-pink-100 text-xs font-bold text-pink-700">
                Vai trò: 
                @if(Auth::user()->role === 'admin')
                    <span class="text-pink-600">Quản trị viên</span>
                @elseif(Auth::user()->role === 'teacher')
                    <span class="text-pink-600">Giảng viên</span>
                @else
                    <span class="text-pink-600">Học viên</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-3xl">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-3xl">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white shadow-sm border border-slate-200/80 rounded-3xl">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
