<x-app-layout>
    <x-slot name="title">Đại Chiến Cướp Vàng - {{ $room->quiz->title }}</x-slot>

    @push('styles')
    <style>
        @keyframes screen-shake-hard {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: translate(-14px, -8px) rotate(-3deg); }
            20%, 40%, 60%, 80% { transform: translate(14px, 8px) rotate(3deg); }
        }

        .shake-victim {
            animation: screen-shake-hard 0.65s cubic-bezier(.36,.07,.19,.97) both;
        }

        .victim-flash {
            background-color: rgba(225, 29, 72, 0.4) !important;
            box-shadow: inset 0 0 100px rgba(225, 29, 72, 0.8) !important;
        }

        /* 3D Chest perspective */
        .perspective-1000 {
            perspective: 1000px;
        }

        .chest-card {
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        }

        .chest-card:hover {
            transform: translateY(-16px) scale(1.05) rotateZ(-1deg);
        }
    </style>
    @endpush

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

    <div id="game-root" class="min-h-[calc(100vh-4rem)] bg-slate-950 text-white select-none py-6 px-4 relative overflow-hidden" x-data="{
        pin: '{{ $room->pin_code }}',
        playerId: '{{ $playerId }}',
        nickname: '{{ $nickname }}',
        blook: '{{ $blook }}',
        csrfToken: '{{ csrf_token() }}',

        // Điểm số & Thứ hạng
        gold: 0,
        rank: 1,
        remainingSeconds: {{ $room->remainingSeconds() }},
        formattedTime: '{{ sprintf('%02d:%02d', floor($room->remainingSeconds() / 60), $room->remainingSeconds() % 60) }}',

        // Pool câu hỏi (Client Loop)
        questions: @js($rawQuestions),
        currentIndex: 0,
        currentQuestion: null,

        // Đếm ngược thời gian cho từng câu hỏi
        questionTimeTotal: 20,
        questionTimeLeft: 20,
        questionTimer: null,
        isTimeout: false,

        // State machine: 'answering' | 'submitting' | 'wrong' | 'chests' | 'chest_opened' | 'choose_target'
        state: 'answering',
        selectedOption: null,

        // Phạt khi trả lời sai
        penaltySeconds: 2,
        wrongTimer: null,
        correctAnswerText: '',

        // Mở rương & Token
        chestToken: null,
        openedChestIndex: null,
        chestResult: null,

        // Chế độ Cướp / Tráo (Steal / Swap)
        targetActionType: '',
        availableTargets: [],
        targetCountdown: 15,
        targetTimer: null,

        // Toasts
        toasts: [],

        // Web Audio Synthesizer
        audioCtx: null,

        init() {
            if (this.questions.length > 0) {
                this.currentQuestion = this.questions[0];
                this.startQuestionTimer();
            }

            // Bộ đếm lùi thời gian
            setInterval(() => {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                    const m = Math.floor(this.remainingSeconds / 60);
                    const s = this.remainingSeconds % 60;
                    this.formattedTime = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                } else {
                    window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                }
            }, 1000);

            // Khởi tạo Laravel Echo Socket Listener
            this.initSocketListener();

            // Polling đồng bộ cá nhân mỗi 2s
            setInterval(() => this.pollPersonalState(), 2000);
        },

        initSocketListener() {
            if (window.Echo) {
                window.Echo.channel(`gold-quest.${this.pin}`)
                    // Lắng nghe bị CƯỚP hoặc TRÁO ĐỔI (Screen Shake)
                    .listen('.PlayerTargeted', (e) => {
                        if (e.targetPlayerId === this.playerId) {
                            this.triggerVictimShake(e);
                        }
                    })
                    // Cập nhật Leaderboard định kỳ
                    .listen('.LeaderboardUpdated', (e) => {
                        const me = e.topPlayers.find(p => p.player_id === this.playerId);
                        if (me) {
                            this.rank = me.rank;
                            this.animateGoldTo(me.gold);
                        }
                    })
                    // Kết thúc trận đấu
                    .listen('.GameEnded', () => {
                        window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                    });
            }
        },

        // Kích hoạt rung lắc màn hình và còi báo động khi bị cướp
        triggerVictimShake(e) {
            const root = document.getElementById('game-root');
            root.classList.add('shake-victim', 'victim-flash');
            this.playSynthesizedSound('alarm');

            if (e.actionType === 'STEAL') {
                this.addToast(`💥 ÔI KHÔNG! ${e.actorNickname} vừa cướp ${e.amount} vàng của bạn!`, 'error');
                this.gold = Math.max(0, this.gold - e.amount);
            } else if (e.actionType === 'SWAP') {
                this.addToast(`🔄 BÙM! ${e.actorNickname} vừa tráo đổi toàn bộ vàng với bạn!`, 'warning');
                this.pollPersonalState();
            }

            setTimeout(() => {
                root.classList.remove('shake-victim', 'victim-flash');
            }, 700);
        },

        async pollPersonalState() {
            try {
                const res = await fetch(`{{ route('goldquest.rooms.poll-player', $room->pin_code) }}?player_id=${this.playerId}`);
                const data = await res.json();
                if (data.is_finished) {
                    window.location.href = `{{ route('minigame.rooms.podium', $room->pin_code) }}`;
                    return;
                }
                this.rank = data.rank;
                this.animateGoldTo(data.gold);
            } catch (e) {}
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

        async handleQuestionTimeout() {
            if (this.state !== 'answering') return;
            this.isTimeout = true;
            this.submitAnswer('TIMEOUT');
        },

        // Chuyển sang câu hỏi kế tiếp
        nextQuestion() {
            this.stopQuestionTimer();
            if (this.wrongTimer) {
                clearInterval(this.wrongTimer);
                this.wrongTimer = null;
            }
            if (this.targetTimer) {
                clearInterval(this.targetTimer);
                this.targetTimer = null;
            }

            this.currentIndex = (this.currentIndex + 1) % this.questions.length;
            this.currentQuestion = this.questions[this.currentIndex];
            this.state = 'answering';
            this.selectedOption = null;
            this.chestToken = null;
            this.openedChestIndex = null;
            this.chestResult = null;
            this.isTimeout = false;

            this.startQuestionTimer();
        },

        // Nộp câu trả lời
        async submitAnswer(key) {
            if (this.state !== 'answering') return;
            this.stopQuestionTimer();
            this.state = 'submitting';
            this.selectedOption = key;

            try {
                const res = await fetch(`{{ route('goldquest.rooms.answer', $room->pin_code) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        player_id: this.playerId,
                        question_id: this.currentQuestion.id,
                        answer: key
                    })
                });

                const data = await res.json();

                if (data.is_correct) {
                    this.playSynthesizedSound('correct');
                    this.chestToken = data.chest_token;
                    this.state = 'chests';
                } else {
                    this.playSynthesizedSound('wrong');
                    this.state = 'wrong';
                    this.correctAnswerText = data.correct_text;
                    this.penaltySeconds = data.freeze_seconds || 2;

                    this.wrongTimer = setInterval(() => {
                        this.penaltySeconds--;
                        if (this.penaltySeconds <= 0) {
                            clearInterval(this.wrongTimer);
                            this.wrongTimer = null;
                            this.nextQuestion();
                        }
                    }, 1000);
                }
            } catch (e) {
                console.error(e);
                this.state = 'answering';
            }
        },

        // Mở rương vàng
        async pickChest(index) {
            if (this.state !== 'chests' || !this.chestToken) return;
            this.openedChestIndex = index;
            this.playSynthesizedSound('chest');

            try {
                const res = await fetch(`{{ route('goldquest.rooms.open-chest', $room->pin_code) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        player_id: this.playerId,
                        chest_token: this.chestToken
                    })
                });

                const data = await res.json();
                this.chestToken = null; // Tiêu thụ token

                if (data.outcome === 'CHOOSE_TARGET') {
                    // Mở màn hình chọn nạn nhân để cướp / tráo
                    this.targetActionType = data.action_type;
                    this.availableTargets = data.targets || [];
                    this.targetCountdown = data.expires_in || 15;
                    this.state = 'choose_target';

                    this.targetTimer = setInterval(() => {
                        this.targetCountdown--;
                        if (this.targetCountdown <= 0) {
                            clearInterval(this.targetTimer);
                            this.targetTimer = null;
                            this.nextQuestion();
                        }
                    }, 1000);
                } else {
                    this.chestResult = data;
                    if (data.total_gold !== undefined) {
                        this.animateGoldTo(data.total_gold);
                    }
                    this.state = 'chest_opened';
                    setTimeout(() => {
                        if (this.state === 'chest_opened') {
                            this.nextQuestion();
                        }
                    }, 1800);
                }
            } catch (e) {
                console.error(e);
                this.nextQuestion();
            }
        },

        // Chọn mục tiêu cướp hoặc tráo đổi
        async executeActionOnTarget(victimId) {
            if (this.targetTimer) {
                clearInterval(this.targetTimer);
                this.targetTimer = null;
            }

            try {
                const res = await fetch(`{{ route('goldquest.rooms.execute-action', $room->pin_code) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        player_id: this.playerId,
                        victim_id: victimId
                    })
                });

                const data = await res.json();
                if (data.success) {
                    if (data.action === 'STEAL') {
                        this.addToast(`🎉 Cướp thành công ${data.result.stolen_amount} vàng!`, 'success');
                        this.animateGoldTo(data.result.thief_gold);
                    } else if (data.action === 'SWAP') {
                        this.addToast(`🔄 Tráo đổi vàng thành công!`, 'success');
                        this.animateGoldTo(data.result.my_new_gold);
                    }
                }
            } catch (e) {
                console.error(e);
            }

            setTimeout(() => this.nextQuestion(), 800);
        },

        // Hiệu ứng nhảy số vàng mượt
        animateGoldTo(newGold) {
            const start = this.gold;
            const diff = newGold - start;
            if (diff === 0) return;

            const duration = 400;
            const startTime = performance.now();

            const step = (now) => {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                this.gold = Math.floor(start + diff * progress);
                if (progress < 1) requestAnimationFrame(step);
                else this.gold = newGold;
            };

            requestAnimationFrame(step);
        },

        // Web Audio Synthesizer (Không lo thiếu file mp3)
        playSynthesizedSound(type) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                if (!this.audioCtx) this.audioCtx = new AudioContext();
                if (this.audioCtx.state === 'suspended') this.audioCtx.resume();

                const ctx = this.audioCtx;
                if (type === 'correct') {
                    [523.25, 659.25, 783.99, 1046.5].forEach((freq, i) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.08);
                        gain.gain.setValueAtTime(0.12, ctx.currentTime + i * 0.08);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.08 + 0.3);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + i * 0.08);
                        osc.stop(ctx.currentTime + i * 0.08 + 0.3);
                    });
                } else if (type === 'wrong') {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(150, ctx.currentTime);
                    gain.gain.setValueAtTime(0.2, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.35);
                } else if (type === 'chest') {
                    [440, 554, 659, 880].forEach((freq, i) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(freq, ctx.currentTime + i * 0.06);
                        gain.gain.setValueAtTime(0.15, ctx.currentTime + i * 0.06);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + i * 0.06 + 0.25);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + i * 0.06);
                        osc.stop(ctx.currentTime + i * 0.06 + 0.25);
                    });
                } else if (type === 'alarm') {
                    for (let j = 0; j < 3; j++) {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'square';
                        osc.frequency.setValueAtTime(800, ctx.currentTime + j * 0.15);
                        osc.frequency.linearRampToValueAtTime(400, ctx.currentTime + j * 0.15 + 0.12);
                        gain.gain.setValueAtTime(0.18, ctx.currentTime + j * 0.15);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + j * 0.15 + 0.12);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(ctx.currentTime + j * 0.15);
                        osc.stop(ctx.currentTime + j * 0.15 + 0.12);
                    }
                }
            } catch (err) {}
        },

        addToast(message, type = 'info') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3500);
        }
    }">
        <!-- Floating Notifications / Toasts -->
        <div class="fixed top-20 right-6 z-50 space-y-2 max-w-sm pointer-events-none">
            <template x-for="t in toasts" :key="t.id">
                <div class="px-5 py-3 rounded-2xl shadow-2xl text-sm font-black border backdrop-blur-xl animate-bounce"
                     :class="{
                         'bg-rose-900/90 border-rose-500 text-rose-100': t.type === 'error',
                         'bg-emerald-900/90 border-emerald-500 text-emerald-100': t.type === 'success',
                         'bg-amber-900/90 border-amber-500 text-amber-100': t.type === 'warning',
                         'bg-slate-900/90 border-slate-600 text-slate-100': t.type === 'info'
                     }"
                     x-text="t.message">
                </div>
            </template>
        </div>

        <!-- Header Thanh Trạng Thái Cá Nhân (Top Bar) -->
        <div class="max-w-4xl mx-auto flex items-center justify-between bg-slate-900/90 border border-slate-800 rounded-3xl px-6 py-3 shadow-xl mb-6 backdrop-blur">
            <!-- Vàng cá nhân -->
            <div class="flex items-center space-x-3">
                <span class="text-3xl">💰</span>
                <div>
                    <div class="text-2xl sm:text-3xl font-black text-amber-300 font-mono tracking-tight" x-text="gold.toLocaleString()"></div>
                    <div class="text-[10px] uppercase font-bold text-slate-400">Vàng Của Bạn</div>
                </div>
            </div>

            <!-- Thứ hạng cá nhân -->
            <div class="flex items-center space-x-3 bg-slate-950/80 border border-slate-800 px-5 py-2 rounded-2xl">
                <span class="text-xs uppercase font-bold text-slate-400">Hạng:</span>
                <span class="text-2xl font-black text-emerald-400 font-mono" x-text="'#' + rank"></span>
            </div>

            <!-- Đồng hồ -->
            <div class="flex items-center space-x-2 text-slate-300 font-mono font-bold text-lg">
                <span>⏱️</span>
                <span x-text="formattedTime"></span>
            </div>
        </div>

        <!-- KHUNG GAME CHÍNH (STATE MACHINE) -->
        <div class="max-w-4xl mx-auto">
            <!-- 1. MÀN HÌNH TRẢ LỜI CÂU HỎI (answering & submitting) -->
            <div x-show="state === 'answering' || state === 'submitting'" class="space-y-6">
                <!-- Nội dung câu hỏi kèm Đồng hồ đếm ngược & Thanh thời gian co dần -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 border-2 border-slate-800 rounded-3xl p-6 sm:p-8 text-center shadow-2xl relative min-h-[180px] flex flex-col justify-between overflow-hidden">
                    <!-- Thanh thời gian câu hỏi co dần từ 100% về 0% -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-slate-800/80 overflow-hidden">
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
                    <div class="flex items-center justify-between w-full pt-1 mb-3">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-amber-300 bg-amber-500/15 border border-amber-500/30 px-3 py-1 rounded-full">
                            Câu <span x-text="(currentIndex + 1) + '/' + questions.length"></span>
                        </span>
                        
                        <!-- Đồng hồ đếm ngược từng câu hỏi (Nhấp nháy đỏ khi <= 5s) -->
                        <div class="flex items-center space-x-1.5 px-3.5 py-1 rounded-full font-mono font-black text-sm border shadow-md transition"
                             :class="{
                                 'bg-slate-800/90 border-slate-700 text-emerald-400': questionTimeLeft > 5,
                                 'bg-rose-950/90 border-rose-500 text-rose-300 animate-bounce ring-2 ring-rose-500/50': questionTimeLeft <= 5
                             }">
                            <span>⏱️</span>
                            <span x-text="questionTimeLeft + 's'"></span>
                        </div>
                    </div>

                    <h2 class="text-xl sm:text-3xl font-black text-white leading-relaxed my-auto py-2" 
                        x-text="currentQuestion?.question || 'Đang tải câu hỏi...'"></h2>
                </div>

                <!-- 4 Thẻ đáp án Blooket Style (Đỏ, Xanh lam, Xanh lục, Vàng hổ phách) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Option A (Đỏ) -->
                    <button @click="submitAnswer('A')"
                            :disabled="state === 'submitting'"
                            type="button"
                            class="p-6 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 active:scale-98 text-white rounded-3xl shadow-xl flex items-center space-x-4 border-2 border-rose-400/50 cursor-pointer transition transform hover:-translate-y-1">
                        <span class="w-12 h-12 rounded-2xl bg-black/20 flex items-center justify-center text-xl font-black shrink-0">A</span>
                        <span class="text-left font-bold text-lg flex-grow" x-text="currentQuestion?.options?.A || 'A'"></span>
                    </button>

                    <!-- Option B (Xanh lam) -->
                    <button @click="submitAnswer('B')"
                            :disabled="state === 'submitting'"
                            type="button"
                            class="p-6 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 active:scale-98 text-white rounded-3xl shadow-xl flex items-center space-x-4 border-2 border-blue-400/50 cursor-pointer transition transform hover:-translate-y-1">
                        <span class="w-12 h-12 rounded-2xl bg-black/20 flex items-center justify-center text-xl font-black shrink-0">B</span>
                        <span class="text-left font-bold text-lg flex-grow" x-text="currentQuestion?.options?.B || 'B'"></span>
                    </button>

                    <!-- Option C (Xanh lục) -->
                    <button @click="submitAnswer('C')"
                            :disabled="state === 'submitting'"
                            type="button"
                            class="p-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-98 text-white rounded-3xl shadow-xl flex items-center space-x-4 border-2 border-emerald-400/50 cursor-pointer transition transform hover:-translate-y-1">
                        <span class="w-12 h-12 rounded-2xl bg-black/20 flex items-center justify-center text-xl font-black shrink-0">C</span>
                        <span class="text-left font-bold text-lg flex-grow" x-text="currentQuestion?.options?.C || 'C'"></span>
                    </button>

                    <!-- Option D (Vàng Hổ Phách) -->
                    <button @click="submitAnswer('D')"
                            :disabled="state === 'submitting'"
                            type="button"
                            class="p-6 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 active:scale-98 text-white rounded-3xl shadow-xl flex items-center space-x-4 border-2 border-amber-400/50 cursor-pointer transition transform hover:-translate-y-1">
                        <span class="w-12 h-12 rounded-2xl bg-black/20 flex items-center justify-center text-xl font-black shrink-0">D</span>
                        <span class="text-left font-bold text-lg flex-grow" x-text="currentQuestion?.options?.D || 'D'"></span>
                    </button>
                </div>
            </div>

            <!-- 2. MÀN HÌNH BÁO SAI & PHẠT CHỜ (wrong) -->
            <div x-show="state === 'wrong'" class="bg-rose-950/80 border-2 border-rose-500 rounded-3xl p-10 text-center shadow-2xl space-y-6">
                <div class="text-6xl animate-bounce" x-text="isTimeout ? '⏰' : '❌'"></div>
                <h3 class="text-3xl sm:text-4xl font-black text-rose-300 uppercase tracking-wide"
                    x-text="isTimeout ? 'HẾT THỜI GIAN TRẢ LỜI!' : 'CHƯA CHÍNH XÁC!'">
                </h3>
                <div class="bg-rose-900/60 border border-rose-700/60 p-4 rounded-2xl max-w-lg mx-auto">
                    <span class="text-xs font-bold text-rose-300 uppercase">Đáp án đúng là:</span>
                    <p class="text-xl font-black text-white mt-1" x-text="correctAnswerText"></p>
                </div>
                <div class="inline-flex items-center space-x-2 text-rose-200 font-bold text-lg bg-black/40 px-6 py-2.5 rounded-full">
                    <span>Đang đóng băng:</span>
                    <span class="text-2xl font-black font-mono text-rose-400" x-text="penaltySeconds + 's'"></span>
                </div>
            </div>

            <!-- 3. MÀN HÌNH CHỌN 1 TRONG 3 RƯƠNG KHO BÁU (chests) -->
            <div x-show="state === 'chests'" class="text-center space-y-8">
                <div class="inline-block bg-gradient-to-r from-amber-500/20 via-yellow-500/20 to-amber-500/20 border border-amber-400/50 px-6 py-2 rounded-full">
                    <span class="text-amber-300 font-black text-sm uppercase tracking-wider">CHÍNH XÁC! CHỌN 1 CHIẾC RƯƠNG BÍ ẨN</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 perspective-1000">
                    <!-- Rương 1 -->
                    <div @click="pickChest(1)" class="chest-card bg-gradient-to-b from-amber-600 via-amber-700 to-amber-900 border-4 border-amber-400 rounded-3xl p-8 shadow-2xl cursor-pointer flex flex-col items-center justify-center h-64 relative overflow-hidden group">
                        <div class="text-7xl group-hover:scale-125 transition-transform duration-300">📦</div>
                        <span class="mt-4 font-black text-amber-200 text-lg tracking-widest uppercase">RƯƠNG SỐ 1</span>
                    </div>

                    <!-- Rương 2 -->
                    <div @click="pickChest(2)" class="chest-card bg-gradient-to-b from-amber-600 via-amber-700 to-amber-900 border-4 border-amber-400 rounded-3xl p-8 shadow-2xl cursor-pointer flex flex-col items-center justify-center h-64 relative overflow-hidden group">
                        <div class="text-7xl group-hover:scale-125 transition-transform duration-300">📦</div>
                        <span class="mt-4 font-black text-amber-200 text-lg tracking-widest uppercase">RƯƠNG SỐ 2</span>
                    </div>

                    <!-- Rương 3 -->
                    <div @click="pickChest(3)" class="chest-card bg-gradient-to-b from-amber-600 via-amber-700 to-amber-900 border-4 border-amber-400 rounded-3xl p-8 shadow-2xl cursor-pointer flex flex-col items-center justify-center h-64 relative overflow-hidden group">
                        <div class="text-7xl group-hover:scale-125 transition-transform duration-300">📦</div>
                        <span class="mt-4 font-black text-amber-200 text-lg tracking-widest uppercase">RƯƠNG SỐ 3</span>
                    </div>
                </div>
            </div>

            <!-- 4. MÀN HÌNH HIỂN THỊ KẾT QUẢ MỞ RƯƠNG (chest_opened) -->
            <div x-show="state === 'chest_opened'" class="bg-gradient-to-b from-slate-900 to-slate-950 border-4 border-amber-400 rounded-3xl p-10 text-center shadow-2xl space-y-4">
                <div class="text-7xl animate-bounce" x-text="chestResult?.loot?.icon || '🎁'"></div>
                <h3 class="text-3xl sm:text-5xl font-black text-amber-300" x-text="chestResult?.loot?.label"></h3>
                <p class="text-slate-400 text-sm font-bold">Tự động nạp câu hỏi tiếp theo sau giây lát...</p>
            </div>

            <!-- 5. MÀN HÌNH CHỌN NẠN NHÂN ĐỂ CƯỚP HOẶC TRÁO ĐỔI (choose_target) -->
            <div x-show="state === 'choose_target'" class="bg-slate-900/95 border-2 border-amber-400 rounded-3xl p-8 shadow-2xl space-y-6">
                <div class="text-center space-y-2">
                    <h3 class="text-3xl font-black uppercase text-amber-300"
                        x-text="targetActionType === 'STEAL' ? '🥷 CHỌN MỤC TIÊU ĐỂ CƯỚP VÀNG' : '🔄 CHỌN MỤC TIÊU ĐỂ TRÁO ĐỔI VÀNG'">
                    </h3>
                    <div class="inline-flex items-center space-x-2 text-rose-400 font-mono font-bold text-sm bg-black/40 px-4 py-1.5 rounded-full">
                        <span>Hết hạn sau:</span>
                        <span class="text-lg font-black" x-text="targetCountdown + 's'"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-80 overflow-y-auto p-2">
                    <template x-for="t in availableTargets" :key="t.player_id">
                        <div @click="executeActionOnTarget(t.player_id)"
                             class="bg-slate-800 hover:bg-amber-600/30 border border-slate-700 hover:border-amber-400 p-4 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition transform hover:scale-105 shadow-md">
                            <div class="text-4xl">👾</div>
                            <span class="font-bold text-white text-base mt-2 truncate max-w-full" x-text="t.nickname"></span>
                            <span class="text-amber-300 font-mono font-black text-sm mt-1" x-text="Number(t.gold).toLocaleString() + ' Vàng'"></span>
                        </div>
                    </template>

                    <div x-show="availableTargets.length === 0" class="col-span-full text-center py-8 text-slate-400 font-bold">
                        Chưa có đối thủ nào có sẵn để nhắm tới!
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

