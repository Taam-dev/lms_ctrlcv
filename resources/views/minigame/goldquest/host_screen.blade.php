<x-app-layout>
    <x-slot name="title">Màn Hình Trực Tiếp - {{ $room->quiz->title }}</x-slot>

    <div class="min-h-screen bg-slate-950 text-white select-none py-8 px-4 sm:px-6 lg:px-8" x-data="{
        pin: '{{ $room->pin_code }}',
        remainingSeconds: {{ $room->remainingSeconds() }},
        formattedTime: '{{ sprintf('%02d:%02d', floor($room->remainingSeconds() / 60), $room->remainingSeconds() % 60) }}',
        leaderboard: [],
        activities: [],
        timerInterval: null,
        pollInterval: null,

        blookEmojis: {
            dragon: '🐲',
            wizard: '🧙‍♂️',
            robot: '🤖',
            unicorn: '🦄',
            pirate: '🏴‍☠️',
            alien: '👽',
            tiger: '🐯',
            ninja: '🥷',
            fox: '🦊',
            bear: '🐻'
        },

        init() {
            // Khởi động bộ đếm ngược
            this.timerInterval = setInterval(() => {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                    const m = Math.floor(this.remainingSeconds / 60);
                    const s = this.remainingSeconds % 60;
                    this.formattedTime = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                } else {
                    clearInterval(this.timerInterval);
                    window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                }
            }, 1000);

            // Lắng nghe Socket
            if (window.Echo) {
                window.Echo.channel(`gold-quest.${this.pin}`)
                    .listen('.LeaderboardUpdated', (e) => {
                        this.leaderboard = e.topPlayers;
                    })
                    .listen('.PlayerTargeted', (e) => {
                        let text = '';
                        if (e.actionType === 'STEAL') {
                            text = `🔥 ${e.actorNickname} vừa cướp ${e.amount} vàng từ đối thủ!`;
                        } else if (e.actionType === 'SWAP') {
                            text = `🔄 ${e.actorNickname} vừa tráo đổi toàn bộ số vàng với đối thủ!`;
                        }
                        this.activities.unshift({
                            id: Date.now(),
                            action_type: e.actionType.toLowerCase(),
                            message: text,
                            time: new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                        });
                        if (this.activities.length > 8) this.activities.pop();
                    })
                    .listen('.GameEnded', () => {
                        window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                    });
            }

            // Polling dự phòng mỗi 1.5s
            this.fetchHostData();
            this.pollInterval = setInterval(() => this.fetchHostData(), 1500);
        },

        async fetchHostData() {
            try {
                const res = await fetch(`{{ route('goldquest.rooms.poll-host', $room->pin_code) }}`);
                const data = await res.json();
                if (data.is_finished) {
                    window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                    return;
                }
                if (data.leaderboard && data.leaderboard.length > 0) {
                    this.leaderboard = data.leaderboard;
                }
                if (data.activities) {
                    this.activities = data.activities;
                }
            } catch (e) {}
        }
    }">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Top Bar: Tiêu đề + Đồng hồ đếm ngược + Nút kết thúc sớm -->
            <div class="bg-slate-900/90 border-2 border-amber-500/40 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4 backdrop-blur-xl">
                <div>
                    <span class="text-xs uppercase font-black text-amber-400 tracking-widest">TRẬN ĐẤU ĐANG DIỄN RA • PIN: <span class="text-amber-200 font-mono">{{ $room->pin_code }}</span></span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white">{{ $room->quiz->title }}</h1>
                </div>

                <!-- Đồng hồ đếm ngược Projector Style -->
                <div class="flex items-center space-x-3 bg-slate-950/80 border-2 border-amber-400 px-6 py-3 rounded-2xl shadow-inner shadow-amber-500/20">
                    <span class="text-3xl animate-pulse">⏱️</span>
                    <span class="text-4xl sm:text-5xl font-black font-mono tracking-widest text-amber-300" 
                          :class="remainingSeconds <= 30 ? 'text-rose-400 animate-ping' : 'text-amber-300'"
                          x-text="formattedTime">
                    </span>
                </div>

                <!-- Nút Kết thúc sớm -->
                <form action="{{ route('goldquest.rooms.end', $room->pin_code) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Bạn có chắc muốn kết thúc trận đấu ngay bây giờ?');"
                            class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-black text-sm uppercase tracking-wider rounded-2xl shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer">
                        ⏹️ Kết Thúc Trận Đấu
                    </button>
                </form>
            </div>

            <!-- Layout 2 cột: Bảng xếp hạng Top 10 & Thanh nhật ký sự kiện Activity Ticker -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cột Bảng xếp hạng (Chiếm 2 phần) -->
                <div class="lg:col-span-2 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center space-x-3">
                            <span class="text-3xl">🏆</span>
                            <h2 class="text-2xl font-black text-white uppercase tracking-wider">Bảng Dẫn Đầu Top 10</h2>
                        </div>
                        <span class="text-xs font-bold text-slate-400">Tự động cập nhật trực tiếp</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(p, idx) in leaderboard" :key="p.player_id">
                            <div class="flex items-center justify-between p-4 rounded-2xl border transition-all duration-300 shadow-md"
                                 :class="{
                                     'bg-gradient-to-r from-amber-500/20 to-slate-800 border-amber-400/80 shadow-amber-500/20 scale-[1.02]': p.rank === 1,
                                     'bg-gradient-to-r from-slate-400/20 to-slate-800 border-slate-400/60': p.rank === 2,
                                     'bg-gradient-to-r from-amber-700/20 to-slate-800 border-amber-700/60': p.rank === 3,
                                     'bg-slate-800/60 border-slate-800 hover:border-slate-700': p.rank > 3
                                 }">
                                <div class="flex items-center space-x-4">
                                    <!-- Huy hiệu thứ hạng -->
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg"
                                         :class="{
                                             'bg-amber-400 text-slate-950 shadow-lg shadow-amber-400/50': p.rank === 1,
                                             'bg-slate-300 text-slate-950': p.rank === 2,
                                             'bg-amber-700 text-white': p.rank === 3,
                                             'bg-slate-800 text-slate-400 border border-slate-700': p.rank > 3
                                         }">
                                        <span x-text="p.rank === 1 ? '🥇' : (p.rank === 2 ? '🥈' : (p.rank === 3 ? '🥉' : '#' + p.rank))"></span>
                                    </div>

                                    <!-- Linh vật Avatar & Tên -->
                                    <div class="text-3xl" x-text="blookEmojis[p.blook] || '👾'"></div>
                                    <div class="font-black text-lg text-white" x-text="p.nickname"></div>
                                </div>

                                <!-- Số Vàng -->
                                <div class="flex items-center space-x-2">
                                    <span class="text-xl">💰</span>
                                    <span class="text-2xl font-black text-amber-300 font-mono" x-text="Number(p.gold).toLocaleString()"></span>
                                    <span class="text-xs uppercase font-bold text-slate-400">Vàng</span>
                                </div>
                            </div>
                        </template>

                        <div x-show="leaderboard.length === 0" class="text-center py-12 text-slate-500 font-bold">
                            Đang thu thập dữ liệu thi đấu...
                        </div>
                    </div>
                </div>

                <!-- Cột Hoạt động Sự kiện (Activity Ticker) -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl flex flex-col">
                    <div class="flex items-center space-x-3 pb-4 border-b border-slate-800">
                        <span class="text-2xl">⚡</span>
                        <h2 class="text-xl font-black text-white uppercase tracking-wider">Nhật Ký Cướp Vàng</h2>
                    </div>

                    <div class="mt-4 space-y-3 flex-grow overflow-y-auto max-h-[500px]">
                        <template x-for="act in activities" :key="act.id">
                            <div class="p-3.5 rounded-2xl border text-sm transition"
                                 :class="{
                                     'bg-rose-950/40 border-rose-800/60 text-rose-200': act.action_type === 'steal',
                                     'bg-indigo-950/40 border-indigo-800/60 text-indigo-200': act.action_type === 'swap',
                                     'bg-amber-950/40 border-amber-800/60 text-amber-200': act.action_type === 'gold' || act.action_type === 'multiplier',
                                     'bg-slate-800/40 border-slate-700/60 text-slate-300': act.action_type === 'nothing' || act.action_type === 'trap'
                                 }">
                                <div class="flex items-center justify-between text-xs opacity-70 mb-1">
                                    <span class="font-bold uppercase" x-text="act.action_type"></span>
                                    <span x-text="act.time"></span>
                                </div>
                                <div class="font-semibold leading-relaxed" x-text="act.message"></div>
                            </div>
                        </template>

                        <div x-show="activities.length === 0" class="text-center py-12 text-slate-500 font-bold">
                            Chưa có sự kiện cướp vàng nào diễn ra.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

