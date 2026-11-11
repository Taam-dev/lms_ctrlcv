<x-app-layout>
    <x-slot name="title">Bục Vinh Danh Minigame - {{ $room->quiz->title }}</x-slot>

    <div class="py-10 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 text-center">

            <!-- Tiêu đề chúc mừng -->
            <div class="space-y-3">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-zinc-800 text-zinc-200 border border-zinc-700">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>Trận Đấu Đã Kết Thúc!</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white">
                    Bục Vinh Danh <span class="text-zinc-300">Nhà Vô Địch</span>
                </h1>
                <p class="text-xs sm:text-sm text-zinc-400">
                    Bộ đề: <strong class="text-white">{{ $room->quiz->title }}</strong> • Chủ phòng: <strong class="text-zinc-200">{{ $room->host->name }}</strong>
                </p>
            </div>

            <!-- BỤC VINH QUANG TOP 1, 2, 3 (PODIUM PILLARS) -->
            <div class="pt-8 pb-4 flex items-end justify-center gap-2 sm:gap-6 max-w-2xl mx-auto min-h-[360px]">

                <!-- HẠNG 2 (BẠC - BÊN TRÁI) -->
                <div class="flex-1 flex flex-col items-center">
                    @if($top2)
                        <div class="mb-3 flex flex-col items-center space-y-1">
                            <span class="text-xs font-black uppercase tracking-wider text-zinc-400">Hạng 2</span>
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-zinc-400 shadow-xl overflow-hidden bg-zinc-800 flex items-center justify-center font-black text-lg">
                                @if($top2->user?->avatar_url)
                                    <img src="{{ $top2->user->avatar_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-white">{{ strtoupper(substr($top2->nickname, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="font-bold text-xs sm:text-sm text-white truncate max-w-[110px]">{{ $top2->nickname }}</div>
                            <div class="px-2.5 py-0.5 rounded-lg bg-zinc-900 border border-zinc-700 text-xs font-mono font-bold text-zinc-200 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ number_format($top2->gold) }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="w-full h-36 sm:h-44 rounded-t-2xl bg-[#141417] border-t-2 border-zinc-400 flex items-center justify-center shadow-2xl">
                        <span class="text-3xl sm:text-4xl font-black text-zinc-300">2</span>
                    </div>
                </div>

                <!-- HẠNG 1 (VÀNG - Ở GIỮA, CAO NHẤT) -->
                <div class="flex-1 flex flex-col items-center">
                    @if($top1)
                        <div class="mb-3 flex flex-col items-center space-y-1">
                            <span class="text-xs font-black uppercase tracking-wider text-pink-400">Vô Địch</span>
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-2 border-pink-500 shadow-2xl shadow-pink-600/30 overflow-hidden bg-zinc-800 flex items-center justify-center font-black text-2xl text-white">
                                @if($top1->user?->avatar_url)
                                    <img src="{{ $top1->user->avatar_url }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ strtoupper(substr($top1->nickname, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="font-bold text-sm sm:text-base text-white truncate max-w-[130px]">{{ $top1->nickname }}</div>
                            <div class="px-3 py-1 rounded-xl bg-zinc-900 border border-zinc-700 text-sm font-mono font-bold text-white flex items-center gap-1 shadow-lg">
                                <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ number_format($top1->gold) }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="w-full h-52 sm:h-64 rounded-t-2xl bg-[#18181b] border-t-2 border-pink-500 flex items-center justify-center shadow-2xl">
                        <span class="text-5xl sm:text-6xl font-black text-white">1</span>
                    </div>
                </div>

                <!-- HẠNG 3 (ĐỒNG - BÊN PHẢI) -->
                <div class="flex-1 flex flex-col items-center">
                    @if($top3)
                        <div class="mb-3 flex flex-col items-center space-y-1">
                            <span class="text-xs font-black uppercase tracking-wider text-zinc-400">Hạng 3</span>
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full border-2 border-zinc-600 shadow-xl overflow-hidden bg-zinc-800 flex items-center justify-center font-black text-lg">
                                @if($top3->user?->avatar_url)
                                    <img src="{{ $top3->user->avatar_url }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-white">{{ strtoupper(substr($top3->nickname, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="font-bold text-xs sm:text-sm text-white truncate max-w-[110px]">{{ $top3->nickname }}</div>
                            <div class="px-2.5 py-0.5 rounded-lg bg-zinc-900 border border-zinc-700 text-xs font-mono font-bold text-zinc-200 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ number_format($top3->gold) }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="w-full h-28 sm:h-32 rounded-t-2xl bg-[#141417] border-t-2 border-zinc-600 flex items-center justify-center shadow-2xl">
                        <span class="text-2xl sm:text-3xl font-black text-zinc-400">3</span>
                    </div>
                </div>

            </div>

            <!-- BẢNG TỔNG KẾT ĐIỂM CHI TIẾT (FULL SCOREBOARD) -->
            <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-6 space-y-4 shadow-xl text-left">
                <h3 class="font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Bảng Điểm Toàn Bộ Người Chơi ({{ $rankings->count() }})</span>
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="text-zinc-400 uppercase tracking-wider border-b border-zinc-800 font-bold">
                            <tr>
                                <th class="py-3 px-4">Hạng</th>
                                <th class="py-3 px-4">Người chơi</th>
                                <th class="py-3 px-4 text-center">Số câu đúng</th>
                                <th class="py-3 px-4 text-center">Số câu sai</th>
                                <th class="py-3 px-4 text-right">Tổng Vàng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach($rankings as $idx => $player)
                                <tr class="hover:bg-zinc-800/30 transition">
                                    <td class="py-3 px-4 font-bold">
                                        @if($idx === 0)
                                            <span class="text-white font-black">#1</span>
                                        @elseif($idx === 1)
                                            <span class="text-zinc-300 font-bold">#2</span>
                                        @elseif($idx === 2)
                                            <span class="text-zinc-400 font-bold">#3</span>
                                        @else
                                            <span class="text-zinc-500">#{{ $idx + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            @if($player->user?->avatar_url)
                                                <img src="{{ $player->user->avatar_url }}" class="w-7 h-7 rounded-full object-cover border border-zinc-700">
                                            @else
                                                <div class="w-7 h-7 rounded-full bg-zinc-800 border border-zinc-700 text-white font-bold text-[11px] flex items-center justify-center">
                                                    {{ strtoupper(substr($player->nickname, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span class="font-bold text-white">{{ $player->nickname }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-zinc-200">
                                        {{ $player->correct_count }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-zinc-400">
                                        {{ $player->wrong_count }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold font-mono text-sm text-white">
                                        {{ number_format($player->gold) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Các nút hành động điều hướng -->
            <div class="pt-4 flex items-center justify-center gap-4 flex-wrap">
                <a href="{{ route('minigame.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs bg-zinc-900 hover:bg-zinc-800 text-zinc-200 hover:text-white border border-zinc-700/80 transition">
                    <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0-6h2m-6 0h2m-8 6h.01M6 15h.01M18 15h.01M18 12h.01M9 12h.01M12 12h.01M6 8a4 4 0 00-4 4v4a4 4 0 004 4h12a4 4 0 004-4v-4a4 4 0 00-4-4H6z"/></svg>
                    <span>Về Sảnh Chính Minigame</span>
                </a>

                @if(Auth::check() && Auth::user()->canManageMinigames())
                    <form action="{{ route('minigame.rooms.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="quiz_id" value="{{ $room->quiz_id }}">
                        <input type="hidden" name="duration_minutes" value="{{ $room->duration_minutes }}">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 transition">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Mở Lại Trận Mới Với Bộ Đề Này</span>
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
