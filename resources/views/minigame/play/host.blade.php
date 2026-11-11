<x-app-layout>
    <x-slot name="title">Màn Hình Host Giảng Viên - {{ $room->quiz->title }}</x-slot>

    <div class="py-6 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)]" x-data="{
        pin: '{{ $room->pin_code }}',
        remainingSeconds: {{ $room->remainingSeconds() }},
        formattedTime: '{{ sprintf('%02d:%02d', floor($room->remainingSeconds() / 60), $room->remainingSeconds() % 60) }}',
        leaderboard: [],
        activities: [],
        pollInterval: null,
        timerInterval: null,
        async poll() {
            try {
                const res = await fetch('{{ route('minigame.rooms.poll-host', $room->pin_code) }}');
                if (res.ok) {
                    const data = await res.json();
                    this.remainingSeconds = data.remaining_seconds;
                    this.formattedTime = data.formatted_time;
                    this.leaderboard = data.leaderboard;
                    this.activities = data.activities;
                    if (data.is_finished) {
                        window.location.href = '{{ route('minigame.rooms.podium', $room->pin_code) }}';
                    }
                }
            } catch (e) {
                console.error('Host polling error', e);
            }
        },
        init() {
            this.poll();
            this.pollInterval = setInterval(() => this.poll(), 2000);
            this.timerInterval = setInterval(() => {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                    const m = Math.floor(this.remainingSeconds / 60);
                    const s = this.remainingSeconds % 60;
                    this.formattedTime = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                } else {
                    window.location.href = '{{ route('minigame.rooms.podium', $room->pin_code) }}';
                }
            }, 1000);
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Top Dashboard Bar -->
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 p-5 rounded-2xl bg-[#141417] border border-zinc-800 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-zinc-900 border border-zinc-700/80 flex items-center justify-center text-white shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-zinc-200"></span>
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-zinc-800 text-zinc-300 border border-zinc-700">
                                Đang trực tiếp
                            </span>
                            <h1 class="text-lg font-bold text-white">{{ $room->quiz->title }}</h1>
                        </div>
                        <p class="text-xs text-zinc-400 mt-0.5">PIN: <strong class="text-white tracking-wider font-mono font-bold">{{ $room->pin_code }}</strong> • Tổng số học viên: <strong class="text-white" x-text="leaderboard.length"></strong></p>
                    </div>
                </div>

                <!-- ĐỒNG HỒ ĐẾM NGƯỢC KHỔ LỚN -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-700/80 shadow-inner">
                        <svg class="w-6 h-6 text-zinc-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Thời gian còn lại</div>
                            <div class="text-2xl sm:text-3xl font-black font-mono text-white tracking-wider" x-text="formattedTime"></div>
                        </div>
                    </div>

                    <!-- Nút kết thúc trận đấu sớm -->
                    <form action="{{ route('minigame.rooms.end', $room->pin_code) }}"
                          method="POST"
                          onsubmit="return confirm('Bạn có chắc chắn muốn kết thúc trận đấu ngay bây giờ?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white border border-zinc-700 transition">
                            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            <span>Kết thúc sớm</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Grid 2 Cột: Bảng xếp hạng Top & Dòng sự kiện trực tiếp -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Cột Trái & Giữa: BẢNG XẾP HẠNG THỜI GIAN THỰC (LEADERBOARD) -->
                <div class="lg:col-span-2 bg-[#141417] rounded-3xl border border-zinc-800 p-6 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Bảng Xếp Hạng Vàng (Live Leaderboard)</h2>
                        </div>
                        <span class="text-xs text-zinc-400">Cập nhật mỗi 2s</span>
                    </div>

                    <div x-show="leaderboard.length === 0" class="py-12 text-center text-zinc-400 text-xs">
                        Đang đồng bộ dữ liệu người chơi...
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="(p, index) in leaderboard" :key="p.id">
                            <div :class="{
                                    'bg-zinc-900 border-zinc-700/80 shadow-md': index === 0,
                                    'bg-[#18181b] border-zinc-800': index > 0
                                 }"
                                 class="flex items-center justify-between p-3.5 rounded-2xl border transition-all duration-300">

                                <div class="flex items-center gap-3">
                                    <!-- Hạng -->
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs shrink-0"
                                         :class="{
                                            'bg-white text-zinc-950': index === 0,
                                            'bg-zinc-300 text-zinc-950': index === 1,
                                            'bg-zinc-600 text-white': index === 2,
                                            'bg-zinc-800 text-zinc-400': index > 2
                                         }">
                                        <span x-text="index + 1"></span>
                                    </div>

                                    <!-- Avatar / Initial -->
                                    <template x-if="p.avatar">
                                        <img :src="p.avatar" class="w-9 h-9 rounded-full object-cover shrink-0 border border-zinc-700">
                                    </template>
                                    <template x-if="!p.avatar">
                                        <div class="w-9 h-9 rounded-full bg-zinc-800 border border-zinc-700 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                            <span x-text="p.nickname.substring(0, 1).toUpperCase()"></span>
                                        </div>
                                    </template>

                                    <!-- Nickname & đúng -->
                                    <div>
                                        <div class="font-bold text-white text-sm" x-text="p.nickname"></div>
                                        <div class="text-[11px] text-zinc-400">Đúng: <strong class="text-zinc-200" x-text="p.correct_count"></strong> câu</div>
                                    </div>
                                </div>

                                <!-- Số Vàng -->
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800">
                                    <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-base font-bold text-white font-mono" x-text="new Intl.NumberFormat().format(p.gold)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cột Phải: DÒNG SỰ KIỆN TRỰC TIẾP (LIVE ACTIVITY TICKER) -->
                <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-6 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Nhật Ký Sự Kiện</h2>
                        </div>
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-zinc-200"></span>
                        </span>
                    </div>

                    <div x-show="activities.length === 0" class="py-12 text-center text-zinc-400 text-xs">
                        Chưa có sự kiện nào được ghi nhận...
                    </div>

                    <div class="space-y-2.5 max-h-[500px] overflow-y-auto pr-1">
                        <template x-for="act in activities" :key="act.id">
                            <div class="p-3 rounded-xl bg-[#18181b] border border-zinc-800 space-y-1 text-xs">
                                <div class="flex items-center justify-between text-[10px] text-zinc-500">
                                    <span class="font-bold uppercase tracking-wider text-zinc-300"
                                          x-text="act.action_type"></span>
                                    <span x-text="act.time"></span>
                                </div>
                                <p class="text-zinc-300 font-medium" x-text="act.message"></p>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
