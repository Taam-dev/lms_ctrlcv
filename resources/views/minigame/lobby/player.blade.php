<x-app-layout>
    <x-slot name="title">Phòng Chờ Trận Đấu - PIN: {{ $room->pin_code }}</x-slot>

    <div class="py-12 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)] flex items-center justify-center" x-data="{
        pin: '{{ $room->pin_code }}',
        count: 1,
        pollInterval: null,
        async poll() {
            try {
                const res = await fetch('{{ route('minigame.rooms.poll-lobby', $room->pin_code) }}');
                if (res.ok) {
                    const data = await res.json();
                    this.count = data.count;
                    if (data.status === 'playing') {
                        window.location.href = '{{ route('minigame.rooms.play', $room->pin_code) }}';
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
        <div class="max-w-xl w-full mx-4 text-center space-y-6">

            <!-- Card Trạng thái chờ -->
            <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-8 sm:p-10 shadow-2xl space-y-6 relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-pink-500/5 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-zinc-900 border border-zinc-700/80 flex items-center justify-center shadow-xl shadow-black/40">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0-6h2m-6 0h2m-8 6h.01M6 15h.01M18 15h.01M18 12h.01M9 12h.01M12 12h.01M6 8a4 4 0 00-4 4v4a4 4 0 004 4h12a4 4 0 004-4v-4a4 4 0 00-4-4H6z"/></svg>
                    </div>

                    <div class="space-y-1">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-zinc-800 text-zinc-300 border border-zinc-700">
                            Phòng #{{ $room->pin_code }}
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-black text-white pt-2">Bạn Đang Ở Trong Phòng Chờ!</h1>
                        <p class="text-xs text-zinc-400">
                            Chủ phòng: <strong class="text-white">{{ $room->host->name }}</strong> • Đề: <strong class="text-zinc-200">{{ $room->quiz->title }}</strong>
                        </p>
                    </div>

                    <!-- Thông tin người chơi -->
                    <div class="p-4 rounded-2xl bg-[#18181b] border border-zinc-800 flex items-center justify-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-zinc-800 border border-zinc-700 text-white font-black text-sm flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($participant->nickname, 0, 1)) }}
                        </div>
                        <div class="text-left">
                            <div class="text-xs text-zinc-400">Biệt danh của bạn:</div>
                            <div class="text-base font-black text-white">{{ $participant->nickname }}</div>
                        </div>
                    </div>

                    <!-- Spinner Chờ Host Bắt Đầu -->
                    <div class="pt-4 flex flex-col items-center gap-3">
                        <div class="flex items-center gap-2 text-sm font-bold text-zinc-300">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Đang đợi Giảng viên bắt đầu trận đấu...</span>
                        </div>
                        <p class="text-xs text-zinc-500">Màn hình sẽ tự động chuyển sang chế độ chơi ngay khi trận đấu bắt đầu!</p>
                    </div>
                </div>
            </div>

            <!-- Mẹo chơi nhanh -->
            <div class="p-4 rounded-2xl bg-[#141417] border border-zinc-800 text-xs text-zinc-400 text-left space-y-2">
                <div class="font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-zinc-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>Mẹo thi đấu Gold Quest:</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-zinc-400">
                    <li>Trả lời thật nhanh và chính xác để được mở rương liên tục.</li>
                    <li>Rương chứa vàng, x2, x3 số vàng và cả cơ hội cướp/đổi vàng ngoạn mục!</li>
                    <li>Chú ý né bẫy gai vì nó sẽ trừ bớt 20% số vàng của bạn.</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>
