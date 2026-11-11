<x-app-layout>
    <x-slot name="title">Phòng Chờ Giảng Viên - PIN: {{ $room->pin_code }}</x-slot>

    <div class="py-8 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)]" x-data="{
        pin: '{{ $room->pin_code }}',
        participants: {{ json_encode($room->participants->map(fn($p) => ['id' => $p->id, 'nickname' => $p->nickname, 'avatar' => $p->user?->avatar_url])) }},
        count: {{ $room->participants->count() }},
        copied: false,
        pollInterval: null,
        copyPin() {
            navigator.clipboard.writeText(this.pin);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },
        copyLink() {
            const url = window.location.origin + '/minigame?pin=' + this.pin;
            navigator.clipboard.writeText(url);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        },
        async poll() {
            try {
                const res = await fetch('{{ route('minigame.rooms.poll-lobby', $room->pin_code) }}');
                if (res.ok) {
                    const data = await res.json();
                    this.participants = data.participants;
                    this.count = data.count;
                    if (data.status === 'playing') {
                        window.location.href = '{{ route('minigame.rooms.host.screen', $room->pin_code) }}';
                    }
                }
            } catch (e) {
                console.error('Polling error', e);
            }
        },
        init() {
            this.pollInterval = setInterval(() => this.poll(), 2000);
        }
    }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-center">

            <!-- PIN Banner Header -->
            <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-8 sm:p-10 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-64 h-64 bg-pink-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-4 relative z-10">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-zinc-900/90 text-zinc-300 border border-zinc-700/80">
                        <svg class="w-4 h-4 text-zinc-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        <span>Hướng dẫn học viên truy cập: <strong class="text-white">{{ url('/minigame') }}</strong></span>
                    </div>

                    <p class="text-xs uppercase tracking-[0.25em] text-zinc-400 font-bold">MÃ PIN PHÒNG CHƠI</p>

                    <!-- GIANT 6-DIGIT PIN -->
                    <div class="flex items-center justify-center gap-4 flex-wrap">
                        <div class="text-6xl sm:text-8xl font-black font-mono tracking-[0.25em] text-white select-all drop-shadow-[0_0_25px_rgba(255,255,255,0.18)]">
                            {{ $room->pin_code }}
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <button type="button"
                                @click="copyPin()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white border border-zinc-700/80 transition">
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-1.5 text-white">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Đã sao chép!</span>
                                </span>
                            </template>
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span>Sao chép mã PIN</span>
                                </span>
                            </template>
                        </button>
                        <button type="button"
                                @click="copyLink()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white border border-zinc-700/80 transition">
                            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span>Sao chép link tham gia</span>
                        </button>
                    </div>

                    <div class="pt-4 flex items-center justify-center gap-5 text-xs text-zinc-400 flex-wrap border-t border-zinc-800/80 mt-2">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Bộ đề: <strong class="text-zinc-100 font-semibold">{{ $room->quiz->title }}</strong>
                        </span>
                        <span class="text-zinc-600">•</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Thời gian: <strong class="text-zinc-100 font-semibold">{{ $room->duration_minutes }} phút</strong>
                        </span>
                        <span class="text-zinc-600">•</span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tổng số: <strong class="text-zinc-100 font-semibold">{{ $room->quiz->questions()->count() }} câu</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Nút Bắt đầu trận đấu & Đếm người chơi -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 bg-[#141417] rounded-2xl border border-zinc-800 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-zinc-900 border border-zinc-700/80 text-zinc-200 flex items-center justify-center shadow-inner">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="text-left">
                        <div class="text-lg font-black text-white">
                            <span x-text="count"></span> Học viên đã vào phòng
                        </div>
                        <p class="text-xs text-zinc-400">Danh sách tự động cập nhật mỗi 2 giây</p>
                    </div>
                </div>

                <form action="{{ route('minigame.rooms.start', $room->pin_code) }}" method="POST">
                    @csrf
                    <button type="submit"
                            :disabled="count === 0"
                            :class="count === 0 ? 'opacity-40 cursor-not-allowed bg-zinc-800 text-zinc-500 border border-zinc-700' : 'bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 active:scale-95 border border-pink-400/40'"
                            class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl font-bold uppercase tracking-wider text-sm transition-all">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.84 1.76m0 0L4.5 21.5l1.75-5.25m3.5-4.5A6.002 6.002 0 0114.37 8.41"/></svg>
                        <span>Bắt Đầu Trận Đấu Ngay</span>
                    </button>
                </form>
            </div>

            <!-- Danh sách học viên trong phòng (Live Grid) -->
            <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-6 sm:p-8 space-y-4 shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-300 text-left flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-zinc-200"></span>
                    </span>
                    <span>Học Viên Sẵn Sàng (<span x-text="count"></span>)</span>
                </h3>

                <div x-show="count === 0" class="py-12 text-center text-zinc-400 text-xs">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400">
                        <svg class="w-5 h-5 text-zinc-300 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    Đang đợi học viên nhập mã PIN để vào phòng...
                </div>

                <div x-show="count > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                    <template x-for="p in participants" :key="p.id">
                        <div class="bg-[#18181b] border border-zinc-800 hover:border-zinc-700 rounded-2xl p-3 flex items-center gap-2.5 shadow-sm transition">
                            <template x-if="p.avatar">
                                <img :src="p.avatar" class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-700">
                            </template>
                            <template x-if="!p.avatar">
                                <div class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-200 font-bold text-xs flex items-center justify-center shrink-0">
                                    <span x-text="p.nickname.substring(0, 1).toUpperCase()"></span>
                                </div>
                            </template>
                            <span class="text-xs font-semibold text-zinc-200 truncate text-left" x-text="p.nickname"></span>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
