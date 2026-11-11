<x-app-layout>
    <x-slot name="title">Sảnh Chờ Trận Đấu - {{ $room->quiz->title }}</x-slot>

    <div class="min-h-screen bg-slate-950 text-white select-none py-10 px-4 sm:px-6 lg:px-8" x-data="{
        pin: '{{ $room->pin_code }}',
        nickname: '{{ $playerNickname ?? Auth::user()?->name ?? 'Người chơi' }}',
        selectedBlook: '{{ $playerBlook ?? 'dragon' }}',
        blooks: [
            { id: 'dragon', emoji: '🐲', name: 'Rồng Lửa' },
            { id: 'wizard', emoji: '🧙‍♂️', name: 'Phù Thủy' },
            { id: 'robot', emoji: '🤖', name: 'Người Máy' },
            { id: 'unicorn', emoji: '🦄', name: 'Kỳ Lân' },
            { id: 'pirate', emoji: '🏴‍☠️', name: 'Hải Tặc' },
            { id: 'alien', emoji: '👽', name: 'Người Ngoài HT' },
            { id: 'tiger', emoji: '🐯', name: 'Cọp Chúa' },
            { id: 'ninja', emoji: '🥷', name: 'Ninja' },
            { id: 'fox', emoji: '🦊', name: 'Cáo Tuyết' },
            { id: 'bear', emoji: '🐻', name: 'Gấu Nâu' }
        ],
        init() {
            // Lắng nghe socket GameStarted
            if (window.Echo) {
                window.Echo.channel(`gold-quest.${this.pin}`)
                    .listen('.GameStarted', () => {
                        window.location.href = `{{ route('goldquest.play', $room->pin_code) }}`;
                    });
            }

            // Polling dự phòng mỗi 1.5s
            setInterval(() => this.checkRoomStatus(), 1500);
        },
        async checkRoomStatus() {
            try {
                const res = await fetch(`{{ route('goldquest.rooms.poll-player', $room->pin_code) }}`);
                const data = await res.json();
                if (data.status === 'playing') {
                    window.location.href = `{{ route('goldquest.play', $room->pin_code) }}`;
                }
            } catch (e) {}
        }
    }">
        <div class="max-w-2xl mx-auto space-y-8">
            <!-- Thẻ chào mừng & Thông tin phòng -->
            <div class="bg-gradient-to-br from-amber-500/20 via-slate-900 to-amber-500/10 border-2 border-amber-500/40 rounded-3xl p-8 text-center shadow-2xl relative">
                <span class="text-xs uppercase tracking-widest font-black text-amber-400">BẠN ĐÃ VÀO PHÒNG CHỜ</span>
                <h1 class="text-3xl font-black text-white mt-1">{{ $room->quiz->title }}</h1>
                <div class="mt-3 inline-flex items-center space-x-2 bg-slate-800/80 px-4 py-1.5 rounded-full border border-slate-700">
                    <span class="text-slate-400 text-sm font-semibold">Mã PIN:</span>
                    <span class="text-amber-300 font-mono font-black text-lg" x-text="pin"></span>
                </div>

                <div class="mt-8 flex flex-col items-center">
                    <div class="w-24 h-24 bg-slate-800 border-4 border-amber-400 rounded-3xl flex items-center justify-center text-5xl shadow-xl shadow-amber-500/20 transform hover:rotate-6 transition">
                        <span x-text="blooks.find(b => b.id === selectedBlook)?.emoji || '🐲'"></span>
                    </div>
                    <span class="mt-3 text-2xl font-black text-white" x-text="nickname"></span>
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-wider" x-text="blooks.find(b => b.id === selectedBlook)?.name"></span>
                </div>
            </div>

            <!-- Chọn Blook (Avatar) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-wider mb-4 text-center">
                    Chọn Linh Vật (Blook) Đại Diện Của Bạn
                </h3>

                <div class="grid grid-cols-5 gap-3">
                    <template x-for="b in blooks" :key="b.id">
                        <button @click="selectedBlook = b.id"
                                type="button"
                                :class="selectedBlook === b.id ? 'border-amber-400 bg-amber-500/20 scale-105 shadow-amber-400/30' : 'border-slate-800 bg-slate-800/60 hover:border-slate-600'"
                                class="p-3 border-2 rounded-2xl flex flex-col items-center justify-center transition transform shadow-md cursor-pointer">
                            <span class="text-3xl" x-text="b.emoji"></span>
                            <span class="text-[10px] font-bold text-slate-300 mt-1 truncate max-w-full" x-text="b.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Hướng dẫn & Trạng thái chờ -->
            <div class="text-center py-6">
                <div class="inline-flex items-center space-x-3 text-amber-300 bg-amber-500/10 px-6 py-3 rounded-2xl border border-amber-500/30 animate-pulse">
                    <span class="text-xl">⏳</span>
                    <span class="font-bold text-sm">Chờ Giảng viên bấm Bắt đầu trận đấu...</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

