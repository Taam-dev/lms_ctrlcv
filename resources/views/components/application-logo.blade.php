@props(['dark' => false])

<div class="flex items-center gap-2.5">
    <img src="{{ asset('images/logo.png') }}" alt="Ctrl C+V đồ án 1" class="h-10 w-10 object-contain rounded-lg {{ $dark ? 'bg-white p-0.5' : '' }}">
    <div class="flex flex-col">
        <span class="text-lg font-black tracking-tight leading-none {{ $dark ? 'text-white' : 'text-slate-900' }}">
            Ctrl C+V
        </span>
        <span class="text-[11px] font-bold text-pink-600 tracking-wider mt-0.5">
            đồ án 1
        </span>
    </div>
</div>
