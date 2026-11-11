<x-app-layout>
    <x-slot name="title">Sảnh Chờ Host - {{ $room->quiz->title }}</x-slot>

    <div class="min-h-screen bg-slate-950 text-white select-none py-10 px-4 sm:px-6 lg:px-8" x-data="{
        pin: '{{ $room->pin_code }}',
        players: @js($room->participants->map(fn($p) => [
            'player_id' => $p->session_id ?? (string)$p->id,
            'nickname' => $p->nickname,
            'blook' => $p->blook ?? 'dragon'
        ])),
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
            // Lắng nghe socket nếu có Echo
            if (window.Echo) {
                window.Echo.channel(`gold-quest.${this.pin}`)
                    .listen('.RoomJoined', (e) => {
                        if (!this.players.find(p => p.player_id === e.player.player_id)) {
                            this.players.push(e.player);
                        }
                    });
            }

            // Polling dự phòng mỗi 2s
            setInterval(() => this.fetchLobby(), 2000);
        },
        async fetchLobby() {
            try {
                const res = await fetch(`{{ route('goldquest.rooms.poll-host', $room->pin_code) }}`);
                const data = await res.json();
                if (data.status === 'playing') {
                    window.location.href = `{{ route('goldquest.host.screen', $room->pin_code) }}`;
                }
            } catch (e) {}
        }
    }">
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header Thông tin phòng & PIN 6 số -->
            <div class="bg-gradient-to-r from-amber-600/30 via-slate-900 to-amber-600/30 border-2 border-amber-500/50 rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden backdrop-blur-xl">
                <div class="absolute -right-10 -bottom-10 text-9xl opacity-10">👑</div>
                <div class="text-xs uppercase tracking-widest font-black text-amber-400 mb-2">ĐẠI CHIẾN CƯỚP VÀNG • GOLD QUEST</div>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight drop-shadow-md">
                    {{ $room->quiz->title }}
                </h1>

                <!-- PIN Code nổi bật -->
                <div class="mt-6 flex flex-col items-center justify-center">
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">Học viên truy cập và nhập mã PIN:</span>
                    <div class="mt-2 inline-flex items-center space-x-3 bg-amber-500/10 border-2 border-amber-400 px-8 py-4 rounded-3xl shadow-inner shadow-amber-500/20">
                        <span class="text-5xl sm:text-7xl font-black tracking-widest text-amber-300 font-mono" x-text="pin"></span>
                    </div>
                </div>

                <!-- Nút Bắt đầu trận đấu -->
                <div class="mt-8 flex justify-center items-center space-x-4">
                    <form action="{{ route('goldquest.rooms.start', $room->pin_code) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="px-10 py-5 bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-400 hover:to-yellow-300 text-slate-950 text-2xl font-black rounded-2xl shadow-xl transform transition hover:-translate-y-1 active:translate-y-0 hover:shadow-amber-400/40 cursor-pointer">
                            🚀 BẮT ĐẦU TRẬN ĐẤU (<span x-text="players.length"></span> NGƯỜI CHƠI)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Danh sách người chơi tham gia Realtime -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-xl">
                <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                    <div class="flex items-center space-x-3">
                        <span class="text-2xl">👥</span>
                        <h2 class="text-xl font-black text-white uppercase tracking-wider">Danh Sách Tham Gia</h2>
                    </div>
                    <span class="px-4 py-1.5 bg-amber-500/20 text-amber-400 font-black rounded-full text-sm border border-amber-500/30">
                        <span x-text="players.length"></span> chiến binh
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 min-h-[200px]">
                    <template x-for="player in players" :key="player.player_id">
                        <div class="bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 rounded-2xl p-4 flex flex-col items-center justify-center space-y-2 transform transition hover:scale-105 shadow-md">
                            <div class="text-4xl" x-text="blookEmojis[player.blook] || '👾'"></div>
                            <span class="text-sm font-bold text-white truncate max-w-full" x-text="player.nickname"></span>
                        </div>
                    </template>

                    <div x-show="players.length === 0" class="col-span-full flex flex-col items-center justify-center py-12 text-slate-500">
                        <span class="text-5xl animate-bounce mb-3">⏳</span>
                        <p class="font-bold text-lg">Đang chờ người chơi nhập mã PIN...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

