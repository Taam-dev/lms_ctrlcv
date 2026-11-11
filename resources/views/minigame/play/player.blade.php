<x-app-layout>
    <x-slot name="title">Đang Thi Đấu - {{ $room->quiz->title }}</x-slot>

    @php
        $rawQuestions = $questions->map(function($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => $q->options ?? [],
                'time_limit' => $q->time_limit,
            ];
        })->shuffle()->values();
    @endphp

    <div class="py-6 bg-[#09090b] text-zinc-100 min-h-[calc(100vh-4rem)] select-none" x-data="{
        pin: '{{ $room->pin_code }}',
        csrfToken: '{{ csrf_token() }}',
        gold: {{ $participant->gold }},
        rank: 1,
        totalPlayers: 1,
        remainingSeconds: {{ $room->remainingSeconds() }},
        formattedTime: '{{ sprintf('%02d:%02d', floor($room->remainingSeconds() / 60), $room->remainingSeconds() % 60) }}',

        // Questions pool
        allQuestions: {{ json_encode($rawQuestions) }},
        currentIndex: 0,
        currentQuestion: null,

        // Đếm ngược thời gian cho từng câu hỏi
        questionTimeTotal: 20,
        questionTimeLeft: 20,
        questionTimer: null,
        isTimeout: false,

        // State machine: 'answering' | 'submitting' | 'wrong' | 'chests' | 'chest_opened'
        state: 'answering',
        selectedOption: null,

        // Wrong feedback
        penaltySeconds: 3,
        wrongTimer: null,
        correctText: '',

        // Chest result
        chestResult: null,
        openedChestIndex: null,

        // Audio controls
        soundEnabled: true,
        audioCtx: null,

        pollInterval: null,
        timerInterval: null,

        playSound(type) {
            if (!this.soundEnabled) return;
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                if (!this.audioCtx) {
                    this.audioCtx = new AudioCtx();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const ctx = this.audioCtx;

                if (type === 'correct') {
                    [523.25, 659.25, 783.99].forEach((freq, i) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.08);
                        gain.gain.setValueAtTime(0.12, ctx.currentTime + i * 0.08);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.08 + 0.25);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + i * 0.08);
                        osc.stop(ctx.currentTime + i * 0.08 + 0.25);
                    });
                } else if (type === 'wrong') {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(190, ctx.currentTime);
                    osc.frequency.linearRampToValueAtTime(110, ctx.currentTime + 0.28);
                    gain.gain.setValueAtTime(0.16, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.28);
                } else if (type === 'chest_open') {
                    [440, 554.37, 659.25, 880].forEach((freq, i) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.07);
                        gain.gain.setValueAtTime(0.14, ctx.currentTime + i * 0.07);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.07 + 0.32);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + i * 0.07);
                        osc.stop(ctx.currentTime + i * 0.07 + 0.32);
                    });
                }
            } catch (e) {
                // AudioContext not allowed before user interaction
            }
        },

        // Bộ đếm thời gian cho câu hỏi hiện tại
        startQuestionTimer() {
            this.stopQuestionTimer();
            this.isTimeout = false;
            this.questionTimeTotal = parseInt(this.currentQuestion?.time_limit) || 20;
            this.questionTimeLeft = this.questionTimeTotal;

            this.questionTimer = setInterval(() => {
                if (this.state !== 'answering') {
                    this.stopQuestionTimer();
                    return;
                }

                if (this.questionTimeLeft > 0) {
                    this.questionTimeLeft--;
                } else {
                    this.stopQuestionTimer();
                    this.handleQuestionTimeout();
                }
            }, 1000);
        },

        stopQuestionTimer() {
            if (this.questionTimer) {
                clearInterval(this.questionTimer);
                this.questionTimer = null;
            }
        },

        handleQuestionTimeout() {
            if (this.state !== 'answering') return;
            this.isTimeout = true;
            this.submitAnswer('TIMEOUT');
        },

        loadNextQuestion() {
            if (this.allQuestions.length === 0) return;
            this.stopQuestionTimer();
            if (this.wrongTimer) {
                clearInterval(this.wrongTimer);
                this.wrongTimer = null;
            }
            this.currentIndex = (this.currentIndex + 1) % this.allQuestions.length;
            this.currentQuestion = this.allQuestions[this.currentIndex];
            this.state = 'answering';
            this.selectedOption = null;
            this.chestResult = null;
            this.openedChestIndex = null;
            this.isTimeout = false;

            this.startQuestionTimer();
        },

        async submitAnswer(key) {
            if (this.state !== 'answering') return;
            this.stopQuestionTimer();
            this.state = 'submitting';
            this.selectedOption = key;

            try {
                const res = await fetch('{{ route('minigame.rooms.answer', $room->pin_code) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: this.currentQuestion.id,
                        answer: key
                    })
                });

                const data = await res.json();

                if (data.finished) {
                    window.location.href = data.redirect;
                    return;
                }

                if (data.is_correct) {
                    // Trả lời đúng -> Âm thanh chúc mừng + chuyển sang chọn rương
                    this.playSound('correct');
                    this.state = 'chests';
                } else {
                    // Trả lời sai -> Âm thanh báo lỗi + phạt chờ
                    this.playSound('wrong');
                    this.state = 'wrong';
                    this.correctText = data.correct_text;
                    this.penaltySeconds = data.penalty_seconds || 3;

                    this.wrongTimer = setInterval(() => {
                        this.penaltySeconds--;
                        if (this.penaltySeconds <= 0) {
                            clearInterval(this.wrongTimer);
                            this.wrongTimer = null;
                            this.loadNextQuestion();
                        }
                    }, 1000);
                }
            } catch (e) {
                console.error('Answer error', e);
                this.state = 'answering';
            }
        },

        skipWrongWait() {
            if (this.wrongTimer) {
                clearInterval(this.wrongTimer);
                this.wrongTimer = null;
            }
            this.loadNextQuestion();
        },

        async pickChest(index) {
            if (this.state !== 'chests') return;
            this.openedChestIndex = index;

            try {
                const res = await fetch('{{ route('minigame.rooms.open-chest', $room->pin_code) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        chest_index: index
                    })
                });

                const data = await res.json();

                if (data.finished) {
                    window.location.href = data.redirect;
                    return;
                }

                if (data.success && data.result) {
                    this.playSound('chest_open');
                    this.chestResult = data.result;
                    this.gold = data.result.new_gold;
                    this.state = 'chest_opened';
                }
            } catch (e) {
                console.error('Chest error', e);
                this.loadNextQuestion();
            }
        },

        async poll() {
            try {
                const res = await fetch('{{ route('minigame.rooms.poll-player', $room->pin_code) }}');
                if (res.ok) {
                    const data = await res.json();
                    this.gold = data.gold;
                    this.rank = data.rank;
                    this.totalPlayers = data.total_players;
                    this.remainingSeconds = data.remaining_seconds;
                    this.formattedTime = data.formatted_time;

                    if (data.is_finished) {
                        window.location.href = '{{ route('minigame.rooms.podium', $room->pin_code) }}';
                    }
                }
            } catch (e) {
                console.error('Player poll error', e);
            }
        },

        init() {
            if (this.allQuestions.length > 0) {
                this.currentQuestion = this.allQuestions[0];
                this.startQuestionTimer();
            }
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

            // Blooket Rapid-Play Keyboard Listeners (1, 2, 3, 4 / A, B, C, D / Space / Enter)
            window.addEventListener('keydown', (e) => {
                if (['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) return;

                const upper = e.key.toUpperCase();

                // 1. Giai đoạn trả lời câu hỏi
                if (this.state === 'answering') {
                    const mapNumbers = { '1': 'A', '2': 'B', '3': 'C', '4': 'D' };
                    let picked = null;
                    if (mapNumbers[e.key]) {
                        picked = mapNumbers[e.key];
                    } else if (['A', 'B', 'C', 'D'].includes(upper)) {
                        picked = upper;
                    }
                    if (picked && this.currentQuestion?.options && this.currentQuestion.options[picked] !== undefined) {
                        e.preventDefault();
                        this.submitAnswer(picked);
                    }
                }
                // 2. Giai đoạn chọn 1 trong 3 rương
                else if (this.state === 'chests') {
                    if (['1', '2', '3'].includes(e.key)) {
                        e.preventDefault();
                        this.pickChest(parseInt(e.key));
                    }
                }
                // 3. Giai đoạn xem kết quả rương hoặc phạt sai -> bấm Space/Enter để tiếp tục
                else if (this.state === 'chest_opened') {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        this.loadNextQuestion();
                    }
                } else if (this.state === 'wrong') {
                    if (e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        this.skipWrongWait();
                    }
                }
            });
        }
    }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ============================================================== -->
            <!-- TOP GAME HUD (Vàng, Xếp Hạng, Đồng hồ đếm ngược, Âm thanh)    -->
            <!-- ============================================================== -->
            <div class="flex items-center justify-between p-4 rounded-2xl bg-[#141417] border border-zinc-800 shadow-xl">
                <!-- Người chơi & Hạng -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-pink-600 text-white font-black flex items-center justify-center text-sm shadow-md shadow-pink-600/30">
                        {{ strtoupper(substr($participant->nickname, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-white text-xs sm:text-sm">{{ $participant->nickname }}</div>
                        <div class="text-[11px] text-zinc-400">
                            Hạng <strong class="text-white">#<span x-text="rank"></span></strong> / <span x-text="totalPlayers"></span>
                        </div>
                    </div>
                </div>

                <!-- Kho Vàng Của Học Viên -->
                <div class="flex items-center gap-3 px-4 py-2 rounded-xl bg-zinc-900 border border-zinc-800 shadow-inner">
                    <svg class="w-5 h-5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-right">
                        <div class="text-[10px] font-bold uppercase text-zinc-400 tracking-wider">Kho Vàng</div>
                        <div class="text-lg sm:text-xl font-black font-mono text-white tracking-wide" x-text="new Intl.NumberFormat().format(gold)"></div>
                    </div>
                </div>

                <!-- Đồng Hồ Đếm Ngược & Nút Mute Âm Thanh -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-zinc-900 border border-zinc-800">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-mono font-bold text-white text-xs sm:text-sm" x-text="formattedTime"></span>
                    </div>

                    <button type="button"
                            @click="soundEnabled = !soundEnabled"
                            class="p-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white transition"
                            :title="soundEnabled ? 'Tắt âm thanh' : 'Bật âm thanh'">
                        <template x-if="soundEnabled">
                            <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        </template>
                        <template x-if="!soundEnabled">
                            <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                        </template>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TRẠNG THÁI 1: HIỂN THỊ CÂU HỎI TRẮC NGHIỆM    -->
            <!-- ============================================== -->
            <div x-show="state === 'answering' || state === 'submitting'" class="space-y-6">

                <!-- Thẻ Câu Hỏi Kèm Đồng Hồ & Thanh Thời Gian Co Dần -->
                <div class="bg-[#141417] rounded-3xl border border-zinc-800 p-6 sm:p-8 text-center shadow-2xl relative min-h-[180px] flex flex-col justify-between overflow-hidden space-y-2">
                    <!-- Thanh thời gian câu hỏi co dần từ 100% về 0% -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-zinc-800/80 overflow-hidden">
                        <div class="h-full transition-all duration-1000 ease-linear rounded-full"
                             :class="{
                                 'bg-gradient-to-r from-emerald-400 to-teal-400': questionTimeLeft > (questionTimeTotal * 0.4),
                                 'bg-gradient-to-r from-amber-400 to-yellow-400': questionTimeLeft <= (questionTimeTotal * 0.4) && questionTimeLeft > (questionTimeTotal * 0.2),
                                 'bg-gradient-to-r from-rose-500 to-red-600 animate-pulse': questionTimeLeft <= (questionTimeTotal * 0.2)
                             }"
                             :style="'width: ' + Math.max(0, (questionTimeLeft / questionTimeTotal) * 100) + '%'">
                        </div>
                    </div>

                    <!-- Header thẻ câu hỏi: Số thứ tự câu & Đồng hồ số của câu -->
                    <div class="flex items-center justify-between w-full pt-1">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-900 border border-zinc-700/80 text-zinc-400">
                            Câu <span x-text="(currentIndex + 1) + '/' + allQuestions.length"></span>
                        </span>
                        
                        <!-- Đồng hồ đếm ngược từng câu hỏi (Nhấp nháy đỏ khi <= 5s) -->
                        <div class="flex items-center space-x-1.5 px-3 py-1 rounded-full font-mono font-black text-xs sm:text-sm border shadow-md transition"
                             :class="{
                                 'bg-zinc-900 border-zinc-700 text-emerald-400': questionTimeLeft > 5,
                                 'bg-rose-950/90 border-rose-500 text-rose-300 animate-bounce ring-2 ring-rose-500/50': questionTimeLeft <= 5
                             }">
                            <span>⏱️</span>
                            <span x-text="questionTimeLeft + 's'"></span>
                        </div>
                    </div>

                    <h2 class="text-lg sm:text-2xl font-black text-white leading-relaxed my-auto py-2" x-text="currentQuestion?.question"></h2>
                </div>

                <!-- 4 Thẻ Lựa Chọn Trả Lời (Hỗ trợ phím tắt 1, 2, 3, 4 hoặc A, B, C, D) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <template x-for="(text, key, idx) in currentQuestion?.options" :key="key">
                        <button type="button"
                                @click="submitAnswer(key)"
                                :disabled="state === 'submitting'"
                                :class="{
                                    'border-pink-500 bg-pink-500/10 scale-95': selectedOption === key,
                                    'border-zinc-800 bg-[#141417] hover:border-pink-500/80 hover:bg-[#18181b]': selectedOption !== key
                                }"
                                class="p-5 rounded-2xl text-left border-2 shadow-lg transition-all transform active:scale-95 flex items-center justify-between gap-4 group">
                            <div class="flex items-center gap-3.5">
                                <span class="w-10 h-10 rounded-xl bg-zinc-900 border border-zinc-700/80 flex items-center justify-center font-black text-base text-white group-hover:border-pink-500 transition shrink-0"
                                      x-text="key"></span>
                                <span class="text-sm sm:text-base font-bold text-zinc-100 leading-snug" x-text="text"></span>
                            </div>
                            <!-- Phím tắt nhanh -->
                            <span class="hidden sm:inline-block px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-900 text-zinc-400 border border-zinc-700 group-hover:text-pink-400 transition shrink-0"
                                  x-text="'[' + (key === 'A' ? '1' : (key === 'B' ? '2' : (key === 'C' ? '3' : '4'))) + ']'"></span>
                        </button>
                    </template>
                </div>

                <div class="text-center text-[11px] text-zinc-400">
                    Mẹo: Dùng bàn phím ấn nhanh số <strong class="text-white">1, 2, 3, 4</strong> hoặc phím <strong class="text-white">A, B, C, D</strong> để trả lời tốc độ cao!
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TRẠNG THÁI 2: TRẢ LỜI SAI & PHẠT CHỜ 3 GIÂY  -->
            <!-- ============================================== -->
            <div x-show="state === 'wrong'" x-cloak class="bg-[#141417] rounded-3xl border border-rose-500/40 p-8 sm:p-12 text-center space-y-6 shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/30 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10">
                    <svg class="w-8 h-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-2xl sm:text-3xl font-black text-rose-300" x-text="isTimeout ? '⏰ Hết Thời Gian Trả Lời!' : 'Rất Tiếc, Chưa Chính Xác!'"></h3>
                    <p class="text-sm text-zinc-300">
                        Đáp án đúng là: <strong class="text-white font-bold underline underline-offset-4 decoration-pink-500" x-text="correctText"></strong>
                    </p>
                </div>

                <!-- Đồng Hồ Phạt 3 Giây & Nút Bỏ Qua -->
                <div class="p-4 rounded-2xl bg-zinc-900 border border-zinc-800 max-w-sm mx-auto space-y-3">
                    <div class="text-xs uppercase font-bold tracking-wider text-rose-400">Hình Phạt Chờ</div>
                    <div class="text-4xl font-black font-mono text-white" x-text="penaltySeconds + 's'"></div>
                    <button type="button"
                            @click="skipWrongWait()"
                            class="px-4 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider text-zinc-300 hover:text-white bg-zinc-800 hover:bg-zinc-700 transition border border-zinc-700">
                        Bỏ qua chờ [Space / Enter] &rarr;
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TRẠNG THÁI 3: TRẢ LỜI ĐÚNG -> CHỌN 1 TRONG 3 RƯƠNG -->
            <!-- ============================================== -->
            <div x-show="state === 'chests'" x-cloak class="space-y-6 text-center">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-pink-500/10 text-pink-300 border border-pink-500/30">
                        <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Trả Lời Chính Xác!</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-black text-white">Hãy Chọn 1 Rương Kho Báu!</h2>
                    <p class="text-xs sm:text-sm text-zinc-400">Chứa Vàng Khủng, Nhân x2/x3, Cướp Vàng, Hoán Đổi Hoặc Bẫy Gai!</p>
                </div>

                <!-- 3 Rương Kho Báu Monochrome Tinh Tế Chuẩn Blooket -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-2">
                    <template x-for="chestNo in [1, 2, 3]" :key="chestNo">
                        <button type="button"
                                @click="pickChest(chestNo)"
                                class="bg-[#141417] hover:bg-[#18181b] border-2 border-zinc-800 hover:border-pink-500 rounded-3xl p-8 space-y-4 shadow-xl hover:shadow-pink-500/10 hover:scale-105 active:scale-95 transition-all group flex flex-col items-center">
                            <!-- SVG Chest Icon -->
                            <div class="w-20 h-20 rounded-2xl bg-zinc-900 border border-zinc-700/80 flex items-center justify-center group-hover:scale-110 group-hover:border-pink-500 transition-all duration-300 shadow-inner">
                                <svg class="w-10 h-10 text-zinc-300 group-hover:text-pink-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div class="font-black text-base uppercase tracking-wider text-white" x-text="'Rương Số ' + chestNo"></div>
                            <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-[11px] font-mono font-bold text-zinc-400 group-hover:text-white transition"
                                  x-text="'Phím [' + chestNo + ']'"></span>
                        </button>
                    </template>
                </div>

                <div class="text-center text-[11px] text-zinc-400">
                    Bấm chuột hoặc ấn phím số <strong class="text-white">[1]</strong>, <strong class="text-white">[2]</strong>, <strong class="text-white">[3]</strong> trên bàn phím để mở rương!
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TRẠNG THÁI 4: KẾT QUẢ MỞ RƯƠNG (CHEST OPENED) -->
            <!-- ============================================== -->
            <div x-show="state === 'chest_opened'" x-cloak class="bg-[#141417] rounded-3xl border-2 border-zinc-700 p-8 sm:p-12 text-center space-y-6 shadow-2xl relative overflow-hidden">

                <!-- Dynamic Monochrome SVG Icon Theo Kết Quả Rương -->
                <div class="w-24 h-24 rounded-3xl bg-zinc-900 border border-zinc-700 flex items-center justify-center mx-auto shadow-xl transition-transform hover:scale-110">
                    <!-- Icon Vàng (Gold) -->
                    <template x-if="chestResult?.icon === 'gold'">
                        <svg class="w-12 h-12 text-pink-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>

                    <!-- Icon Nhân Bội Số (Multiplier) -->
                    <template x-if="chestResult?.icon === 'multiplier'">
                        <svg class="w-12 h-12 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </template>

                    <!-- Icon Cướp Vàng (Steal) -->
                    <template x-if="chestResult?.icon === 'steal'">
                        <svg class="w-12 h-12 text-rose-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>

                    <!-- Icon Hoán Đổi Vàng (Swap) -->
                    <template x-if="chestResult?.icon === 'swap'">
                        <svg class="w-12 h-12 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </template>

                    <!-- Icon Bẫy Gai (Trap) -->
                    <template x-if="chestResult?.icon === 'trap'">
                        <svg class="w-12 h-12 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>

                    <!-- Icon Rương Rỗng (Nothing) -->
                    <template x-if="chestResult?.icon === 'nothing'">
                        <svg class="w-12 h-12 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </template>
                </div>

                <div class="space-y-2">
                    <h3 class="text-3xl sm:text-4xl font-black text-white" x-text="chestResult?.title"></h3>
                    <p class="text-sm text-zinc-300 max-w-md mx-auto" x-text="chestResult?.description"></p>
                </div>

                <div class="inline-flex items-center gap-2.5 px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800">
                    <svg class="w-5 h-5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs text-zinc-400 uppercase font-bold">Tổng Vàng Hiện Có:</span>
                    <strong class="text-xl font-black font-mono text-white" x-text="new Intl.NumberFormat().format(gold)"></strong>
                </div>

                <div class="pt-4 flex flex-col items-center gap-2">
                    <button type="button"
                            @click="loadNextQuestion()"
                            class="px-8 py-3.5 rounded-2xl font-black uppercase tracking-wider text-xs sm:text-sm bg-pink-600 hover:bg-pink-500 text-white shadow-xl shadow-pink-600/30 active:scale-95 transition-all">
                        Tiếp Tục Câu Tiếp Theo &rarr;
                    </button>
                    <span class="text-[11px] text-zinc-500">
                        Hoặc bấm phím <strong class="text-zinc-300">[Space]</strong> / <strong class="text-zinc-300">[Enter]</strong> trên bàn phím
                    </span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

