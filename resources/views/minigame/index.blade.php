<x-app-layout>
    <x-slot name="title">Minigame Gold Quest - Đấu Trí Mở Rương | Ctrl C+V</x-slot>

    @push('styles')
        <style>
            /* 100% Dark Theme System for Minigame */
            .minigame-page {
                background-color: #09090b !important;
                color: #f4f4f5 !important;
                min-height: calc(100vh - 4.0625rem);
                background-image: 
                    radial-gradient(circle at 50% 0%, rgba(236, 72, 153, 0.08) 0%, transparent 60%),
                    radial-gradient(circle at 90% 80%, rgba(244, 63, 94, 0.04) 0%, transparent 50%),
                    linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
                background-size: 100% 100%, 100% 100%, 32px 32px, 32px 32px;
            }

            /* Surface Cards */
            .mg-card-primary {
                background-color: #141417 !important;
                border: 1px solid rgba(255, 255, 255, 0.09) !important;
            }

            .mg-card-secondary {
                background-color: #18181b !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
            }

            .mg-card-inner {
                background-color: #0d0d11 !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
            }

            /* Typography High-Contrast Overrides */
            .mg-text-white {
                color: #ffffff !important;
            }

            .mg-text-subtitle {
                color: #a1a1aa !important;
            }

            .mg-text-muted {
                color: #71717a !important;
            }

            .mg-text-pink {
                color: #ec4899 !important;
            }

            /* PIN Input & Form Controls - Overrides @tailwindcss/forms defaults */
            .mg-pin-input {
                background-color: #0d0d11 !important;
                color: #ffffff !important;
                border: 1.5px solid rgba(255, 255, 255, 0.15) !important;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
                letter-spacing: 0.25em !important;
                text-align: center !important;
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.6) !important;
            }

            .mg-pin-input:focus {
                border-color: #ec4899 !important;
                box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.35), inset 0 2px 4px rgba(0, 0, 0, 0.6) !important;
                outline: none !important;
            }

            .mg-pin-input::placeholder {
                color: #3f3f46 !important;
                letter-spacing: 0.25em !important;
            }

            .minigame-page input[type="text"]:not(.mg-pin-input),
            .minigame-page input[type="file"],
            .minigame-page select,
            .minigame-page textarea {
                background-color: #0d0d11 !important;
                color: #ffffff !important;
                border: 1px solid rgba(255, 255, 255, 0.12) !important;
            }

            .minigame-page input[type="text"]:not(.mg-pin-input):focus,
            .minigame-page select:focus,
            .minigame-page textarea:focus {
                border-color: #ec4899 !important;
                box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.35) !important;
                outline: none !important;
            }

            .minigame-page input[type="text"]:not(.mg-pin-input)::placeholder,
            .minigame-page textarea::placeholder {
                color: #52525b !important;
            }

            /* Shake animation for invalid PIN */
            @keyframes pin-shake {
                0%, 100% { transform: translateX(0); }
                15%, 55%, 85% { transform: translateX(-6px); }
                35%, 70% { transform: translateX(6px); }
            }
            .animate-shake {
                animation: pin-shake 0.4s ease-in-out;
            }
        </style>
    @endpush

    <div class="minigame-page py-5 sm:py-7 lg:py-8"
         x-data="{
             pin: '{{ request('pin', '') }}',
             nickname: '{{ Auth::check() ? Auth::user()->name : '' }}',
             isShaking: false,
             activeTab: '{{ (Auth::check() && Auth::user()->canManageMinigames()) ? 'hub' : 'play' }}',
             selectedBlook: 'king',
             hostModalOpen: false,
             jsonModalOpen: false,
             selectedDuration: '5',
             searchQuery: '',

             triggerShake() {
                 this.isShaking = true;
                 setTimeout(() => { this.isShaking = false; }, 450);
             },

             validatePin(event) {
                 const clean = this.pin.replace(/\D/g, '').trim();
                 if (clean.length < 6) {
                     event.preventDefault();
                     this.triggerShake();
                     const input = document.getElementById('pin_code');
                     if (input) {
                         input.focus();
                     }
                 }
             },

             generateFunNickname() {
                 const titles = ['CaoThuCode', 'VuaMoRuong', 'ThanTocDev', 'HocVienPro', 'ChienThanLMS', 'TrumDauTri', 'CoderMaster', 'QuanQuanVang'];
                 const num = Math.floor(100 + Math.random() * 900);
                 const pick = titles[Math.floor(Math.random() * titles.length)];
                 this.nickname = pick + num;
             }
         }">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ============================================================== -->
            <!-- TOP BAR: ROLE-BASED CONTROLS (GIẢNG VIÊN / ADMIN)              -->
            <!-- ============================================================== -->
            @if(Auth::check() && Auth::user()->canManageMinigames())
                <div class="mg-card-primary rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl shadow-black/40">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl mg-card-inner flex items-center justify-center text-white shrink-0 shadow-inner">
                            <svg class="w-5 h-5 text-zinc-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-pink-500/20 text-pink-400 border border-pink-500/30">
                                    {{ Auth::user()->role_label }}
                                </span>
                                <h2 class="font-extrabold mg-text-white text-base sm:text-lg">Khu Vực Quản Lý Giảng Viên</h2>
                            </div>
                            <p class="text-xs mg-text-subtitle font-normal mt-0.5">Chuyển đổi linh hoạt giữa giao diện Tham gia và Quản lý phòng minigame</p>
                        </div>
                    </div>

                    <!-- Segmented View Mode Tabs -->
                    <div class="flex items-center p-1.5 mg-card-inner rounded-xl shrink-0 w-full md:w-auto">
                        <button type="button"
                                @click="activeTab = 'play'"
                                :class="activeTab === 'play' ? 'bg-pink-600 text-white shadow-md shadow-pink-600/30' : 'text-zinc-300 hover:text-white hover:bg-white/5'"
                                class="flex-1 md:flex-initial flex items-center justify-center gap-2 py-2 px-4 rounded-lg text-xs font-bold transition">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span>Vào Chơi (Mã PIN)</span>
                        </button>

                        <button type="button"
                                @click="activeTab = 'hub'"
                                :class="activeTab === 'hub' ? 'bg-pink-600 text-white shadow-md shadow-pink-600/30' : 'text-zinc-300 hover:text-white hover:bg-white/5'"
                                class="flex-1 md:flex-initial flex items-center justify-center gap-2 py-2 px-4 rounded-lg text-xs font-bold transition">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Quản Lý Phòng Chơi ({{ $quizzes->count() }})</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- ============================================================== -->
            <!-- 1. HERO SECTION: PLAYFUL & SLEEK ARENA                         -->
            <!-- ============================================================== -->
            <div class="relative overflow-hidden rounded-2xl mg-card-primary p-6 sm:p-7 lg:p-9 shadow-2xl shadow-black/50">

                <!-- Ambient Glow Accents -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-pink-600/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-rose-600/5 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

                    <!-- Hero Left: Gamified Title & Persona Selector -->
                    <div class="lg:col-span-7 space-y-4 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-pink-500/15 border border-pink-500/30 text-pink-400 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse"></span>
                            <span>Chế độ Gold Quest &bull; Đấu Trí Mở Rương</span>
                        </div>

                        <div class="space-y-2.5">
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black mg-text-white leading-tight tracking-tight">
                                Đại Chiến <span class="mg-text-pink">Mở Rương</span><br class="hidden sm:inline">
                                Cướp Vàng <span class="mg-text-white">Đỉnh Cao</span>
                            </h1>
                            <p class="text-sm sm:text-base mg-text-subtitle font-normal max-w-xl leading-relaxed">
                                Trả lời trắc nghiệm theo tốc độ cá nhân, mở rương may mắn nhân bội số vàng khủng, tung chiêu hoán đổi vị trí ngoạn mục để chiếm lĩnh <strong class="mg-text-white font-bold">Top 1 Vinh Quang</strong>!
                            </p>
                        </div>

                        <!-- Mascot (Blook) Character Selector -->
                        <div class="p-3.5 mg-card-inner rounded-xl inline-block w-full sm:w-auto text-left">
                            <div class="flex items-center justify-between sm:justify-start gap-3 mb-2.5">
                                <span class="text-xs font-bold uppercase tracking-wider mg-text-subtitle">Chọn Linh Vật (Blook):</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-pink-500/15 text-pink-400 border border-pink-500/30"
                                      x-text="selectedBlook === 'king' ? 'Vua Vàng' : (selectedBlook === 'pirate' ? 'Hải Tặc' : (selectedBlook === 'wizard' ? 'Phù Thủy' : (selectedBlook === 'astro' ? 'Phi Hành Gia' : 'Cáo Tinh Nghịch')))">
                                </span>
                            </div>

                            <div class="flex items-center gap-2 justify-center sm:justify-start">
                                <!-- Blook 1: King -->
                                <button type="button"
                                        @click="selectedBlook = 'king'"
                                        :class="selectedBlook === 'king' ? 'border-pink-500 bg-pink-500/20 text-white ring-2 ring-pink-500/40 shadow-[0_0_12px_rgba(236,72,153,0.35)]' : 'border-white/10 bg-[#141417] text-zinc-400 hover:text-white hover:border-white/30'"
                                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                                        title="King Blook">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l4 6 5-7 5 7 4-6v10H3V8z" />
                                    </svg>
                                </button>

                                <!-- Blook 2: Pirate -->
                                <button type="button"
                                        @click="selectedBlook = 'pirate'"
                                        :class="selectedBlook === 'pirate' ? 'border-pink-500 bg-pink-500/20 text-white ring-2 ring-pink-500/40 shadow-[0_0_12px_rgba(236,72,153,0.35)]' : 'border-white/10 bg-[#141417] text-zinc-400 hover:text-white hover:border-white/30'"
                                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                                        title="Pirate Blook">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a5 5 0 00-5 5c0 2.21 1.43 4.09 3.42 4.75A7.002 7.002 0 004 18v1a1 1 0 001 1h14a1 1 0 001-1v-1a7.002 7.002 0 00-6.42-6.25C15.57 11.09 17 9.21 17 7a5 5 0 00-5-5zM9 7a3 3 0 116 0 3 3 0 01-6 0z" />
                                    </svg>
                                </button>

                                <!-- Blook 3: Wizard -->
                                <button type="button"
                                        @click="selectedBlook = 'wizard'"
                                        :class="selectedBlook === 'wizard' ? 'border-pink-500 bg-pink-500/20 text-white ring-2 ring-pink-500/40 shadow-[0_0_12px_rgba(236,72,153,0.35)]' : 'border-white/10 bg-[#141417] text-zinc-400 hover:text-white hover:border-white/30'"
                                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                                        title="Wizard Blook">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </button>

                                <!-- Blook 4: Astro -->
                                <button type="button"
                                        @click="selectedBlook = 'astro'"
                                        :class="selectedBlook === 'astro' ? 'border-pink-500 bg-pink-500/20 text-white ring-2 ring-pink-500/40 shadow-[0_0_12px_rgba(236,72,153,0.35)]' : 'border-white/10 bg-[#141417] text-zinc-400 hover:text-white hover:border-white/30'"
                                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                                        title="Astro Blook">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </button>

                                <!-- Blook 5: Fox -->
                                <button type="button"
                                        @click="selectedBlook = 'fox'"
                                        :class="selectedBlook === 'fox' ? 'border-pink-500 bg-pink-500/20 text-white ring-2 ring-pink-500/40 shadow-[0_0_12px_rgba(236,72,153,0.35)]' : 'border-white/10 bg-[#141417] text-zinc-400 hover:text-white hover:border-white/30'"
                                        class="w-10 h-10 rounded-xl border flex items-center justify-center transition"
                                        title="Fox Blook">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Right: CENTERPIECE PIN ENTRY CARD -->
                    <div class="lg:col-span-5 w-full">
                        <div class="mg-card-primary rounded-2xl p-5 sm:p-6 relative transition-all shadow-2xl shadow-black/60"
                             :class="{ 'animate-shake': isShaking }">

                            <!-- Top Header -->
                            <div class="flex items-center justify-between pb-3.5 border-b border-white/10 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl mg-card-inner flex items-center justify-center text-white shrink-0">
                                        <svg class="w-5 h-5 text-zinc-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold mg-text-white text-base leading-tight">Tham Gia Trận Đấu</h3>
                                        <p class="text-xs mg-text-subtitle">Nhập mã PIN 6 số do Giảng viên cung cấp</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    LIVE
                                </span>
                            </div>

                            <!-- Join Form -->
                            <form action="{{ route('minigame.join') }}"
                                  method="POST"
                                  class="space-y-3.5"
                                  @submit="validatePin($event)">
                                @csrf

                                <!-- PIN Code Input Field with Monospace -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="pin_code" class="block text-xs font-bold uppercase tracking-wider mg-text-white">
                                            Mã PIN Phòng (6 số) <span class="mg-text-pink">*</span>
                                        </label>
                                        <span class="text-[11px] font-mono font-bold mg-text-pink" x-text="pin.length + '/6 ký tự'"></span>
                                    </div>

                                    <div class="relative">
                                        <input type="text"
                                               id="pin_code"
                                               name="pin_code"
                                               maxlength="6"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               autocomplete="off"
                                               placeholder="000000"
                                               required
                                               x-model="pin"
                                               @input="pin = pin.replace(/\D/g, '').slice(0, 6)"
                                               class="mg-pin-input w-full rounded-xl text-3xl sm:text-4xl font-black py-3 transition">
                                    </div>

                                    <!-- 6 Digit Slot Helper Bars -->
                                    <div class="grid grid-cols-6 gap-1.5 mt-2 select-none">
                                        <template x-for="i in 6" :key="i">
                                            <div class="h-1 rounded-full transition-all duration-150"
                                                 :class="pin.length >= i ? 'bg-pink-500 shadow-[0_0_8px_#ec4899]' : 'bg-zinc-800'">
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Player Identity: Guest Nickname vs Logged-in User -->
                                @guest
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <label for="nickname" class="block text-xs font-bold uppercase tracking-wider mg-text-white">
                                                Biệt danh của bạn
                                            </label>
                                            <button type="button"
                                                    @click="generateFunNickname()"
                                                    class="text-[11px] font-bold mg-text-pink hover:underline flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <span>Tên ngẫu nhiên</span>
                                            </button>
                                        </div>

                                        <div class="relative flex items-center">
                                            <div class="absolute left-3 w-7 h-7 rounded-lg mg-card-inner flex items-center justify-center text-zinc-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <input type="text"
                                                   id="nickname"
                                                   name="nickname"
                                                   maxlength="30"
                                                   placeholder="VD: CaoThuLMS"
                                                   x-model="nickname"
                                                   class="w-full text-sm font-semibold pl-12 pr-4 py-2.5 rounded-xl transition">
                                        </div>
                                    </div>
                                @endguest

                                @auth
                                    <div class="p-2.5 rounded-xl mg-card-inner flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if(Auth::user()->avatar_url)
                                                <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 border border-white/20" />
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-pink-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="leading-tight">
                                                <div class="text-[10px] font-bold uppercase tracking-wider mg-text-subtitle">Tài khoản tham gia</div>
                                                <div class="text-xs font-bold mg-text-white truncate max-w-[170px] sm:max-w-[200px]">{{ Auth::user()->name }}</div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            Sẵn Sàng
                                        </span>
                                    </div>
                                @endauth

                                <!-- Action Button: VÀO PHÒNG CHỜ -->
                                <button type="submit"
                                        class="w-full py-3 px-5 rounded-xl font-black text-xs sm:text-sm tracking-wider uppercase text-white bg-gradient-to-r from-pink-600 via-rose-600 to-pink-600 hover:from-pink-500 hover:to-rose-500 active:scale-[0.98] transition shadow-lg shadow-pink-600/30 flex items-center justify-center gap-2 mt-2 cursor-pointer border-0">
                                    <span>VÀO PHÒNG CHỜ</span>
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 2. TEACHER / HOST STUDIO COMMAND CARDS                         -->
            <!-- ============================================================== -->
            @if(Auth::check() && Auth::user()->canManageMinigames())
                <div x-show="activeTab === 'hub'" x-transition class="space-y-6">

                    <!-- Quick Action Command Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                        <!-- Action Card 1: Create Question Set -->
                        <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 shadow-md hover:border-white/20">
                            <div class="space-y-3">
                                <div class="w-12 h-12 rounded-xl mg-card-inner flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-lg mg-text-white">Tạo Bộ Câu Hỏi Mới</h3>
                                <p class="text-xs mg-text-subtitle font-normal leading-relaxed">
                                    Thiết kế ngân hàng câu hỏi đa dạng qua 3 phương thức: Tự động hóa bằng AI, tải lên tệp JSON hoặc biên soạn thủ công.
                                </p>
                            </div>
                            <div class="pt-5">
                                <a href="{{ route('minigame.quizzes.create') }}"
                                   class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-pink-600 hover:bg-pink-500 transition shadow-sm">
                                    <span>Tạo Bộ Đề Mới</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Action Card 2: Host Room (Targeted launch) -->
                        <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 shadow-md hover:border-white/20">
                            <div class="space-y-3">
                                <div class="w-12 h-12 rounded-xl mg-card-inner flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-lg mg-text-white">Mở Phòng Cho Lớp Chơi</h3>
                                <p class="text-xs mg-text-subtitle font-normal leading-relaxed">
                                    Khởi tạo phòng đấu trực tiếp, tùy chỉnh thời gian (3-15 phút) và chiếu màn hình thi đấu cho cả lớp cùng tranh tài.
                                </p>
                            </div>
                            <div class="pt-5">
                                <button type="button"
                                        @click="hostModalOpen = true"
                                        class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-zinc-200 mg-card-secondary hover:bg-zinc-800 transition">
                                    <span>Cài Đặt & Mở Phòng</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Action Card 3: Quick JSON Import -->
                        <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 shadow-md hover:border-white/20">
                            <div class="space-y-3">
                                <div class="w-12 h-12 rounded-xl mg-card-inner flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-lg mg-text-white">Import Nhanh File JSON</h3>
                                <p class="text-xs mg-text-subtitle font-normal leading-relaxed">
                                    Kéo thả file JSON định dạng chuẩn để nhập ngay hàng chục câu hỏi trong tích tắc mà không tốn công gõ lại.
                                </p>
                            </div>
                            <div class="pt-5 flex items-center gap-2">
                                <button type="button"
                                        @click="jsonModalOpen = true"
                                        class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-zinc-200 mg-card-secondary hover:bg-zinc-800 transition">
                                    <span>Tải Lên JSON</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                </button>
                                <a href="{{ route('minigame.sample-json') }}"
                                   class="p-2.5 rounded-xl mg-card-inner text-zinc-400 hover:text-white transition text-sm flex items-center justify-center shrink-0"
                                   title="Tải file mẫu JSON">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================== -->
                    <!-- 3. QUESTION SETS MANAGEMENT & CARD GRID                        -->
                    <!-- ============================================================== -->
                    <div class="mg-card-primary rounded-2xl p-5 sm:p-6 space-y-6 shadow-xl">

                        <!-- Filter and Search Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl mg-card-inner flex items-center justify-center text-white">
                                    <svg class="w-5 h-5 text-zinc-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold mg-text-white text-lg">Danh Sách Bộ Câu Hỏi ({{ $quizzes->count() }})</h3>
                                    <p class="text-xs mg-text-subtitle">Chọn một bộ câu hỏi để bắt đầu mở phòng hoặc chỉnh sửa nội dung</p>
                                </div>
                            </div>

                            @if($quizzes->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-3">
                                    <!-- Search input -->
                                    <div class="relative w-full sm:w-64">
                                        <input type="text"
                                               x-model="searchQuery"
                                               placeholder="Tìm kiếm bộ câu hỏi..."
                                               class="w-full text-xs rounded-xl py-2.5 pl-9 pr-3 transition">
                                        <span class="absolute left-3 top-2.5 text-zinc-400 text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Active Quiz Grid vs Empty State -->
                        @if($quizzes->isEmpty())
                            <div class="p-8 sm:p-12 text-center rounded-2xl mg-card-inner border-dashed space-y-5">
                                <div class="w-16 h-16 rounded-2xl mg-card-secondary flex items-center justify-center text-zinc-300 mx-auto">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>

                                <div class="max-w-md mx-auto space-y-1.5">
                                    <h4 class="font-bold text-xl mg-text-white">Chưa Có Bộ Câu Hỏi Nào</h4>
                                    <p class="text-xs sm:text-sm mg-text-subtitle font-normal leading-relaxed">
                                        Kho báu đề thi hiện đang trống. Hãy tạo ngay bộ đề đầu tiên hoặc kéo thả file JSON để bắt đầu mở phòng thi đấu!
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                                    <a href="{{ route('minigame.quizzes.create') }}"
                                       class="py-2.5 px-5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-pink-600 hover:bg-pink-500 transition shadow-sm">
                                        <span>Tạo Bộ Đề Đầu Tiên</span>
                                    </a>
                                    <button type="button"
                                            @click="jsonModalOpen = true"
                                            class="py-2.5 px-5 rounded-xl font-bold text-xs uppercase tracking-wider text-zinc-200 mg-card-secondary hover:bg-zinc-800 transition">
                                        <span>Kéo Thả JSON Đề Thi</span>
                                    </button>
                                </div>

                                <!-- Quick In-Place JSON Upload Dropzone -->
                                <div class="max-w-lg mx-auto pt-6 border-t border-white/10">
                                    <form action="{{ route('minigame.quizzes.store') }}"
                                          method="POST"
                                          enctype="multipart/form-data"
                                          class="p-4 sm:p-5 rounded-xl mg-card-secondary text-left space-y-3">
                                        @csrf
                                        <input type="hidden" name="creation_mode" value="import_json">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider mg-text-white">Tải Lên Nhanh Bộ Đề (.json)</span>
                                            <a href="{{ route('minigame.sample-json') }}" class="text-[11px] font-bold mg-text-pink hover:underline">
                                                Tải file mẫu
                                            </a>
                                        </div>

                                        <input type="text"
                                               name="title"
                                               placeholder="Tên bộ đề (VD: Trắc nghiệm Nhập môn Web)"
                                               required
                                               class="w-full text-xs rounded-xl py-2 px-3">

                                        <input type="hidden" name="difficulty" value="medium">

                                        <div class="flex items-center gap-2">
                                            <input type="file"
                                                   name="json_file"
                                                   accept=".json,application/json,text/plain"
                                                   required
                                                   class="block w-full text-xs text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-zinc-800 file:text-zinc-200 hover:file:bg-zinc-700 cursor-pointer">
                                            <button type="submit"
                                                    class="py-2 px-4 rounded-xl text-xs font-bold text-white bg-pink-600 hover:bg-pink-500 transition whitespace-nowrap">
                                                Upload
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @else
                            <!-- ACTIVE STATE: CARDS GRID -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($quizzes as $quiz)
                                    <div class="mg-card-secondary rounded-xl p-5 flex flex-col justify-between transition-all duration-200 shadow-sm hover:border-white/20"
                                         x-show="'{{ strtolower($quiz->title) }}'.includes(searchQuery.toLowerCase()) || searchQuery === ''">

                                        <!-- Card Top Badges & Meta -->
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[11px] font-bold font-mono bg-pink-500/10 text-pink-400 border border-pink-500/20">
                                                    {{ $quiz->questions_count }} câu hỏi
                                                </span>
                                                <span class="text-[10px] text-zinc-500 font-mono">Blooket Gold Quest</span>
                                            </div>

                                            <div>
                                                <h4 class="font-bold text-base mg-text-white leading-snug line-clamp-2" title="{{ $quiz->title }}">
                                                    {{ $quiz->title }}
                                                </h4>
                                                @if($quiz->description)
                                                    <p class="text-xs mg-text-subtitle line-clamp-2 mt-2 leading-relaxed">
                                                        {{ $quiz->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Card Bottom Actions -->
                                        <div class="pt-4 mt-4 border-t border-white/10 space-y-2.5">
                                            <!-- One-Click Room Creation (5 mins) -->
                                            <form action="{{ route('minigame.rooms.create') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                                                <input type="hidden" name="duration_minutes" value="5">
                                                <button type="submit"
                                                        {{ $quiz->questions_count === 0 ? 'disabled' : '' }}
                                                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-pink-600 hover:bg-pink-500 transition shadow-sm {{ $quiz->questions_count === 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                                    <span>{{ $quiz->questions_count === 0 ? 'Chưa Có Câu Hỏi' : 'Mở Phòng Ngay (5p)' }}</span>
                                                </button>
                                            </form>

                                            <!-- Sub-actions: Custom launch, Edit, Delete -->
                                            <div class="flex items-center justify-between gap-2 pt-1">
                                                <button type="button"
                                                        @click="selectedQuizId = '{{ $quiz->id }}'; hostModalOpen = true;"
                                                        class="text-xs font-bold mg-text-pink hover:underline flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                                    </svg>
                                                    <span>Tùy chọn phút</span>
                                                </button>

                                                <div class="flex items-center gap-1.5">
                                                    <a href="{{ route('minigame.quizzes.edit', $quiz) }}"
                                                       class="w-8 h-8 rounded-lg mg-card-inner text-zinc-300 hover:text-white flex items-center justify-center transition"
                                                       title="Chỉnh sửa bộ đề">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </a>

                                                    <form action="{{ route('minigame.quizzes.destroy', $quiz) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa bộ câu hỏi này không?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400 hover:bg-rose-500/20 flex items-center justify-center transition"
                                                                title="Xóa bộ đề">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>

                    <!-- ============================================================== -->
                    <!-- RECENT HOSTED ROOMS CONSOLE                                    -->
                    <!-- ============================================================== -->
                    @if($hostedRooms->isNotEmpty())
                        <div class="mg-card-primary rounded-2xl p-5 sm:p-6 space-y-4 shadow-xl">
                            <div class="flex items-center justify-between pb-3 border-b border-white/10">
                                <div class="flex items-center gap-2.5">
                                    <svg class="w-5 h-5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="font-bold mg-text-white text-base sm:text-lg">Phòng Đấu Gần Đây Của Bạn</h3>
                                </div>
                                <span class="text-xs mg-text-subtitle">{{ $hostedRooms->count() }} phòng gần nhất</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($hostedRooms as $room)
                                    <div class="p-4 rounded-xl mg-card-secondary space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider mg-text-subtitle">MÃ PIN:</span>
                                            <span class="font-mono text-lg font-bold mg-text-white tracking-wider mg-card-inner px-2.5 py-0.5 rounded-lg">
                                                {{ $room->pin_code }}
                                            </span>
                                        </div>

                                        <div>
                                            <div class="font-bold mg-text-white text-sm truncate">{{ $room->quiz->title }}</div>
                                            <div class="text-xs mg-text-subtitle mt-0.5">
                                                {{ $room->participants->count() }} người chơi &bull; {{ $room->duration_minutes }} phút
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-white/10 flex items-center justify-between">
                                            @if($room->status === 'waiting')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-white/10 text-zinc-300 border border-white/15">
                                                    Đang Chờ
                                                </span>
                                                <a href="{{ route('minigame.rooms.host', $room->pin_code) }}"
                                                   class="text-xs font-bold mg-text-pink hover:underline">
                                                    Vào Lobby &rarr;
                                                </a>
                                            @elseif($room->status === 'playing')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 animate-pulse">
                                                    Đang Đấu
                                                </span>
                                                <a href="{{ route('minigame.rooms.host.screen', $room->pin_code) }}"
                                                   class="text-xs font-bold text-emerald-400 hover:underline">
                                                    Chiếu Live &rarr;
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-white/5 text-zinc-500 border border-white/5">
                                                    Kết Thúc
                                                </span>
                                                <a href="{{ route('minigame.rooms.podium', $room->pin_code) }}"
                                                   class="text-xs font-bold text-zinc-400 hover:text-zinc-300 hover:underline">
                                                    Bục Vinh Danh &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endif

            <!-- ============================================================== -->
            <!-- 4. UPGRADED "GOLD QUEST RULES" MINI ROADMAP                    -->
            <!-- ============================================================== -->
            <div class="pt-4 space-y-6">
                <div class="text-center space-y-2 max-w-2xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-pink-500/15 text-pink-400 border border-pink-500/30 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Luật Chơi Chuẩn Blooket</span>
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-black mg-text-white">
                        Luật Chơi Chế Độ <span class="mg-text-pink">Gold Quest</span> Cực Cuốn
                    </h3>
                    <p class="text-xs sm:text-sm mg-text-subtitle font-normal">
                        Hành trình 4 chặng đua đầy kịch tính từ lúc gõ bàn phím đến giây phút nâng cúp vinh quang!
                    </p>
                </div>

                <!-- 4 Steps Interactive Mini Roadmap -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                    <!-- Step 1: Solo Answering -->
                    <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 group shadow-md hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-zinc-300 border border-white/15">
                                    CHẶNG 1
                                </span>
                                <span class="text-xs text-zinc-500 font-mono font-bold">01</span>
                            </div>

                            <div class="w-12 h-12 mx-auto rounded-xl mg-card-inner flex items-center justify-center text-white group-hover:border-pink-500/40 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div class="text-center space-y-1.5">
                                <h4 class="font-bold mg-text-white text-base">Trả Lời Độc Lập</h4>
                                <p class="text-xs mg-text-subtitle leading-relaxed">
                                    Mỗi học viên trả lời câu hỏi theo tốc độ riêng. Trả lời đúng nhận lượt mở rương; trả lời sai bị phạt đóng băng 3 giây để ôn tập đáp án đúng.
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-white/10 text-center">
                            <span class="text-[11px] font-bold mg-text-pink uppercase tracking-wider">
                                Tự Chủ Tốc Độ &bull; Không Chờ Đợi
                            </span>
                        </div>
                    </div>

                    <!-- Step 2: Treasure Chests -->
                    <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 group shadow-md hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-zinc-300 border border-white/15">
                                    CHẶNG 2
                                </span>
                                <span class="text-xs text-zinc-500 font-mono font-bold">02</span>
                            </div>

                            <div class="w-12 h-12 mx-auto rounded-xl mg-card-inner flex items-center justify-center text-white group-hover:border-pink-500/40 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>

                            <div class="text-center space-y-1.5">
                                <h4 class="font-bold mg-text-white text-base">Mở Rương Kho Báu</h4>
                                <p class="text-xs mg-text-subtitle leading-relaxed">
                                    Trả lời đúng sẽ mở ra 3 rương thần bí chứa vàng ngẫu nhiên (+50 đến +300 vàng) hoặc x2, x3 số vàng hiện có... hoặc rương rỗng bất ngờ!
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-white/10 text-center">
                            <span class="text-[11px] font-bold mg-text-pink uppercase tracking-wider">
                                Vận May Rương Vàng &bull; Nhân Bội Số
                            </span>
                        </div>
                    </div>

                    <!-- Step 3: Gold Steal & Swap -->
                    <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 group shadow-md hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-zinc-300 border border-white/15">
                                    CHẶNG 3
                                </span>
                                <span class="text-xs text-zinc-500 font-mono font-bold">03</span>
                            </div>

                            <div class="w-12 h-12 mx-auto rounded-xl mg-card-inner flex items-center justify-center text-white group-hover:border-pink-500/40 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>

                            <div class="text-center space-y-1.5">
                                <h4 class="font-bold mg-text-white text-base">Cướp & Hoán Đổi Vàng</h4>
                                <p class="text-xs mg-text-subtitle leading-relaxed">
                                    Cướp 20% - 50% vàng từ đối thủ hoặc hoán đổi toàn bộ vàng với người chơi bất kỳ để lật ngược thế cờ chỉ trong 1 giây!
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-white/10 text-center">
                            <span class="text-[11px] font-bold mg-text-pink uppercase tracking-wider">
                                Lật Kèo Ngoạn Mục &bull; Đột Phá
                            </span>
                        </div>
                    </div>

                    <!-- Step 4: Victory Podium -->
                    <div class="mg-card-primary rounded-2xl p-6 flex flex-col justify-between transition-all duration-200 group shadow-md hover:border-white/20">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-zinc-300 border border-white/15">
                                    CHẶNG 4
                                </span>
                                <span class="text-xs text-zinc-500 font-mono font-bold">04</span>
                            </div>

                            <div class="w-12 h-12 mx-auto rounded-xl mg-card-inner flex items-center justify-center text-white group-hover:border-pink-500/40 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>

                            <div class="text-center space-y-1.5">
                                <h4 class="font-bold mg-text-white text-base">Bục Vinh Quang</h4>
                                <p class="text-xs mg-text-subtitle leading-relaxed">
                                    Khi hết giờ thi đấu, 3 người chơi có số vàng cao nhất sẽ bước lên bục vinh danh Top 1, 2, 3 cùng bảng tổng kết thành tích toàn lớp.
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-3 border-t border-white/10 text-center">
                            <span class="text-[11px] font-bold mg-text-pink uppercase tracking-wider">
                                Tôn Vinh Nhà Vô Địch &bull; Master
                            </span>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- MODAL: HOST ROOM (TẠO PHÒNG CHƠI CHUYÊN NGHIỆP)                -->
        <!-- ============================================================== -->
        @if(Auth::check() && Auth::user()->canManageMinigames())
            <div id="modal-host-room"
                 x-show="hostModalOpen"
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">

                <div class="mg-card-primary rounded-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 shadow-2xl"
                     @click.away="hostModalOpen = false">

                    <div class="flex items-center justify-between pb-3.5 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg mg-card-inner flex items-center justify-center text-white">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-bold mg-text-white text-lg">Cài Đặt Phòng Chơi Mới</h3>
                        </div>
                        <button type="button"
                                @click="hostModalOpen = false"
                                class="w-8 h-8 rounded-lg mg-card-inner text-zinc-400 hover:text-white inline-flex items-center justify-center p-0 leading-none transition shrink-0"
                                title="Đóng">
                            <svg class="w-4 h-4 block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('minigame.rooms.create') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- Select Quiz -->
                        <div>
                            <label for="modal_quiz_id" class="block text-xs font-bold uppercase tracking-wider mg-text-white mb-2">
                                1. Chọn Bộ Câu Hỏi Thi Đấu
                            </label>
                            <select name="quiz_id"
                                    id="modal_quiz_id"
                                    x-model="selectedQuizId"
                                    required
                                    class="w-full text-xs sm:text-sm rounded-xl py-2.5 px-3">
                                @foreach($quizzes as $q)
                                    <option value="{{ $q->id }}" {{ $q->questions_count === 0 ? 'disabled' : '' }}>
                                        {{ $q->title }} ({{ $q->questions_count }} câu hỏi) {{ $q->questions_count === 0 ? '- [Chưa có câu hỏi]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Match Duration Pills & Custom Input -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold uppercase tracking-wider mg-text-white">
                                    2. Thời Gian Trận Đấu
                                </label>
                                <span class="text-xs font-mono font-bold text-pink-400" x-text="selectedDuration + ' phút'"></span>
                            </div>
                            <input type="hidden" name="duration_minutes" :value="selectedDuration">

                            <div class="grid grid-cols-5 gap-2">
                                <button type="button"
                                        @click="selectedDuration = '3'"
                                        :class="selectedDuration == 3 ? 'bg-pink-600 text-white font-bold' : 'mg-card-inner text-zinc-400 hover:text-white'"
                                        class="py-2 rounded-xl text-xs transition">
                                    3p
                                </button>
                                <button type="button"
                                        @click="selectedDuration = '5'"
                                        :class="selectedDuration == 5 ? 'bg-pink-600 text-white font-bold' : 'mg-card-inner text-zinc-400 hover:text-white'"
                                        class="py-2 rounded-xl text-xs transition">
                                    5p
                                </button>
                                <button type="button"
                                        @click="selectedDuration = '7'"
                                        :class="selectedDuration == 7 ? 'bg-pink-600 text-white font-bold' : 'mg-card-inner text-zinc-400 hover:text-white'"
                                        class="py-2 rounded-xl text-xs transition">
                                    7p
                                </button>
                                <button type="button"
                                        @click="selectedDuration = '10'"
                                        :class="selectedDuration == 10 ? 'bg-pink-600 text-white font-bold' : 'mg-card-inner text-zinc-400 hover:text-white'"
                                        class="py-2 rounded-xl text-xs transition">
                                    10p
                                </button>
                                <button type="button"
                                        @click="selectedDuration = '15'"
                                        :class="selectedDuration == 15 ? 'bg-pink-600 text-white font-bold' : 'mg-card-inner text-zinc-400 hover:text-white'"
                                        class="py-2 rounded-xl text-xs transition">
                                    15p
                                </button>
                            </div>

                            <!-- Tùy chỉnh tự nhập số phút tùy thích -->
                            <div class="mt-2.5 p-2.5 rounded-xl mg-card-inner flex items-center justify-between gap-3 border border-zinc-800 text-xs">
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <svg class="w-4 h-4 text-pink-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Hoặc tự nhập thời gian:</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="number"
                                           x-model.number="selectedDuration"
                                           min="1"
                                           max="60"
                                           placeholder="5"
                                           class="w-16 py-1 px-2 rounded-lg bg-zinc-900 border border-zinc-700 text-white font-mono text-center text-xs font-bold focus:outline-none focus:border-pink-500">
                                    <span class="text-xs text-zinc-300 font-bold">phút</span>
                                </div>
                            </div>

                            <p class="text-[11px] mg-text-subtitle mt-2 font-normal">
                                Khuyến nghị: <span class="mg-text-pink font-semibold">5 phút</span> là thời lượng tiêu chuẩn lý tưởng trong giờ học!
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="pt-3 border-t border-white/10 flex items-center justify-end gap-3">
                            <button type="button"
                                    @click="hostModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-zinc-400 hover:text-white transition">
                                Hủy Bỏ
                            </button>
                            <button type="submit"
                                    class="py-2.5 px-5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-pink-600 hover:bg-pink-500 transition shadow-md shadow-pink-600/20">
                                <span>Tạo Phòng & Sinh PIN</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif

        <!-- ============================================================== -->
        <!-- MODAL: QUICK JSON IMPORT                                       -->
        <!-- ============================================================== -->
        @if(Auth::check() && Auth::user()->canManageMinigames())
            <div id="modal-json-import"
                 x-show="jsonModalOpen"
                 x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">

                <div class="mg-card-primary rounded-2xl max-w-lg w-full p-6 sm:p-7 space-y-5 shadow-2xl"
                     @click.away="jsonModalOpen = false">

                    <div class="flex items-center justify-between pb-3.5 border-b border-white/10">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg mg-card-inner flex items-center justify-center text-white">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <h3 class="font-bold mg-text-white text-lg">Import Bộ Đề Từ File JSON</h3>
                        </div>
                        <button type="button"
                                @click="jsonModalOpen = false"
                                class="w-8 h-8 rounded-lg mg-card-inner text-zinc-400 hover:text-white inline-flex items-center justify-center p-0 leading-none transition shrink-0"
                                title="Đóng">
                            <svg class="w-4 h-4 block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('minigame.quizzes.store') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="space-y-4">
                        @csrf
                        <input type="hidden" name="creation_mode" value="import_json">

                        <div>
                            <label for="modal_json_title" class="block text-xs font-bold uppercase tracking-wider mg-text-white mb-1.5">
                                Tên Bộ Câu Hỏi <span class="mg-text-pink">*</span>
                            </label>
                            <input type="text"
                                   id="modal_json_title"
                                   name="title"
                                   placeholder="VD: Kiểm tra kiến thức PHP / Laravel"
                                   required
                                   class="w-full text-xs sm:text-sm rounded-xl py-2.5 px-3">
                        </div>

                        <input type="hidden" name="difficulty" value="medium">

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider mg-text-white">
                                    Tệp JSON (.json) <span class="mg-text-pink">*</span>
                                </label>
                                <a href="{{ route('minigame.sample-json') }}"
                                   class="text-[11px] font-bold mg-text-pink hover:underline">
                                    Tải file mẫu
                                </a>
                            </div>

                            <input type="file"
                                   name="json_file"
                                   accept=".json,application/json,text/plain"
                                   required
                                   class="block w-full text-xs text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-zinc-800 file:text-zinc-200 hover:file:bg-zinc-700 cursor-pointer p-2 rounded-xl">
                        </div>

                        <div class="pt-3 border-t border-white/10 flex items-center justify-end gap-3">
                            <button type="button"
                                    @click="jsonModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-zinc-400 hover:text-white transition">
                                Hủy Bỏ
                            </button>
                            <button type="submit"
                                    class="py-2.5 px-5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-pink-600 hover:bg-pink-500 transition shadow-md shadow-pink-600/20">
                                <span>Tải Lên & Khởi Tạo</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif

    </div>
</x-app-layout>
