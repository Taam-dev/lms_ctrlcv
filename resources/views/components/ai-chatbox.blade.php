<div x-data="lmsAiChatbox()"
     x-cloak
     @keydown.escape.window="if (isOpen) isOpen = false"
     class="relative z-50">

    <!-- ================= FLOATING TOGGLE BUTTON ================= -->
    <div class="fixed bottom-6 right-6 z-50 flex items-center gap-2">
        <!-- Floating Greeting Pill (Khi chatbox đang đóng) -->
        <div x-show="!isOpen && showGreetingBadge"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             @click="toggleChat()"
             class="hidden sm:flex items-center gap-2 px-3.5 py-2 bg-white/95 backdrop-blur-sm border border-pink-200 rounded-full shadow-lg shadow-pink-900/10 text-xs font-bold text-slate-800 hover:text-pink-600 hover:border-pink-400 transition cursor-pointer select-none">
            <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse"></span>
            <span>Cần trợ giúp học tập? 💬</span>
            <button @click.stop="showGreetingBadge = false"
                    class="ml-1 text-slate-400 hover:text-slate-600 text-xs leading-none"
                    title="Đóng gợi ý">&times;</button>
        </div>

        <!-- Nút Tròn Mở/Đóng Chatbox -->
        <button type="button"
                @click="toggleChat()"
                id="btn-ai-chat-toggle"
                class="relative flex items-center justify-center w-14 h-14 sm:w-15 sm:h-15 rounded-full bg-gradient-to-tr from-pink-600 via-rose-500 to-pink-500 hover:from-pink-500 hover:to-rose-400 text-white shadow-xl shadow-pink-600/35 hover:shadow-2xl hover:shadow-pink-600/45 hover:scale-105 active:scale-95 transition-all duration-300 border-2 border-white/30 cursor-pointer select-none group"
                :class="{ 'rotate-90': isOpen }"
                :aria-expanded="isOpen.toString()"
                aria-label="Trợ lý AI Học tập Ctrl C+V">

            <!-- Vòng hào quang phát sáng nhẹ -->
            <span class="absolute inset-0 rounded-full bg-pink-400 opacity-30 group-hover:opacity-60 blur-md transition-opacity"></span>

            <!-- Icon khi đang Đóng: Sparkle / AI Bot Icon -->
            <span x-show="!isOpen" class="relative flex items-center justify-center">
                <svg class="w-7 h-7 transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <!-- Badge chấm tròn unread / online -->
                <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white"></span>
                </span>
            </span>

            <!-- Icon khi đang Mở: Dấu X đóng -->
            <span x-show="isOpen" class="relative flex items-center justify-center">
                <svg class="w-6 h-6 transform group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
        </button>
    </div>

    <!-- ================= CHAT WINDOW CONTAINER ================= -->
    <div x-show="isOpen"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed bottom-22 sm:bottom-24 right-3 sm:right-6 z-50 flex flex-col bg-white rounded-3xl border border-pink-200/90 shadow-2xl shadow-pink-950/20 overflow-hidden transition-all duration-300"
         :class="isExpanded
            ? 'w-[calc(100vw-1.5rem)] sm:w-[580px] h-[calc(100vh-7rem)] sm:h-[720px] max-h-[92vh]'
            : 'w-[calc(100vw-1.5rem)] sm:w-[410px] h-[580px] max-h-[85vh]'"
         role="dialog"
         aria-labelledby="ai-chatbox-title">

        <!-- HEADER BANNER -->
        <div class="bg-gradient-to-r from-neutral-950 via-slate-900 to-neutral-950 text-white px-4 sm:px-5 py-3.5 flex items-center justify-between border-b border-neutral-800 shrink-0">
            <!-- Left: Avatar + Title -->
            <div class="flex items-center gap-3">
                <div class="relative w-9 h-9 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-500 flex items-center justify-center text-white shadow-md shadow-pink-600/30 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <!-- Active dot -->
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-neutral-950"
                          title="Trợ lý đang trực tuyến"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="ai-chatbox-title" class="text-xs sm:text-sm font-extrabold tracking-tight text-white flex items-center gap-1.5">
                            <span>Ctrl C+V AI</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-black uppercase tracking-wider bg-pink-500 text-white">PRO</span>
                        </h3>
                    </div>
                    <p class="text-[11px] text-neutral-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span x-text="statusSubtitle">Tra cứu dữ liệu LMS & Tư vấn 24/7</span>
                    </p>
                </div>
            </div>

            <!-- Right: Actions (Expand, Clear, Close) -->
            <div class="flex items-center gap-1 text-neutral-400">
                <!-- Nút Phóng to / Thu nhỏ -->
                <button type="button"
                        @click="toggleExpand()"
                        class="p-1.5 rounded-xl hover:bg-neutral-800 hover:text-white transition cursor-pointer"
                        :title="isExpanded ? 'Thu nhỏ giao diện' : 'Mở rộng giao diện'">
                    <!-- Icon Expand -->
                    <svg x-show="!isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <!-- Icon Compress -->
                    <svg x-show="isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m11-4l-5 5m5-5h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 4l-5-5m5 5h-4m4 0v-4" />
                    </svg>
                </button>

                <!-- Nút Xóa lịch sử trò chuyện -->
                <button type="button"
                        @click="confirmClearChat()"
                        class="p-1.5 rounded-xl hover:bg-neutral-800 hover:text-rose-400 transition cursor-pointer"
                        title="Xóa lịch sử chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>

                <!-- Nút Đóng -->
                <button type="button"
                        @click="toggleChat()"
                        class="p-1.5 rounded-xl hover:bg-neutral-800 hover:text-white transition cursor-pointer"
                        title="Đóng cửa sổ chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- SUBTLE CONTEXT STATUS BAR -->
        <div class="px-4 py-1.5 bg-pink-50/70 border-b border-pink-100 flex items-center justify-between text-[11px] text-slate-600 shrink-0">
            <div class="flex items-center gap-1.5 truncate">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                <span class="font-semibold text-slate-700">Dữ liệu thời gian thực:</span>
                <span class="text-pink-600 font-extrabold" x-text="coursesCountText">Đang tải...</span>
            </div>
            <div class="shrink-0 text-[10px] text-slate-400">
                <span>Zero-Hallucination</span>
            </div>
        </div>

        <!-- ================= CHAT MESSAGE STREAM ================= -->
        <div id="ai-chatbox-messages"
             x-ref="messageContainer"
             class="flex-grow overflow-y-auto p-4 sm:p-5 space-y-4 bg-[#fdf2f8]/40 scroll-smooth">

            <!-- Loop Messages -->
            <template x-for="(msg, index) in messages" :key="msg.id || index">
                <div class="space-y-2">
                    <!-- TIN NHẮN TỪ NGƯỜI DÙNG -->
                    <template x-if="msg.role === 'user'">
                        <div class="flex items-start justify-end gap-2.5">
                            <div class="max-w-[85%] bg-gradient-to-r from-pink-600 to-rose-600 text-white rounded-2xl rounded-tr-xs p-3.5 shadow-sm text-xs sm:text-sm leading-relaxed">
                                <p class="whitespace-pre-wrap break-words" x-text="msg.content"></p>
                                <span class="block text-[10px] text-pink-200 text-right mt-1" x-text="msg.time"></span>
                            </div>
                        </div>
                    </template>

                    <!-- TIN NHẮN TỪ TRỢ LÝ AI -->
                    <template x-if="msg.role === 'assistant'">
                        <div class="flex items-start gap-2.5">
                            <!-- Bot Avatar -->
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>

                            <div class="max-w-[88%] space-y-2">
                                <!-- Bubble Content -->
                                <div class="bg-white border border-pink-100/90 rounded-2xl rounded-tl-xs p-4 shadow-xs text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2 prose-sm">
                                    <!-- Render Markdown an toàn -->
                                    <div class="chat-markdown-content break-words" x-html="renderMarkdown(msg.content)"></div>

                                    <!-- Thẻ hiển thị các khóa học đề xuất trực quan (nếu có) -->
                                    <template x-if="msg.relevant_courses && msg.relevant_courses.length > 0">
                                        <div class="pt-3 mt-3 border-t border-slate-100 space-y-2">
                                            <p class="text-[11px] font-extrabold uppercase tracking-wider text-pink-600 flex items-center gap-1">
                                                <span>📌 Khóa học liên quan:</span>
                                            </p>
                                            <div class="grid grid-cols-1 gap-2">
                                                <template x-for="c in msg.relevant_courses" :key="c.id">
                                                    <a :href="c.url"
                                                       class="group/card flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-pink-50/70 border border-slate-200/80 hover:border-pink-300 transition duration-200 text-left">
                                                        <div class="min-w-0 pr-2">
                                                            <h5 class="text-xs font-bold text-slate-900 group-hover/card:text-pink-600 transition truncate" x-text="c.title"></h5>
                                                            <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                                                <span x-text="c.teacher"></span>
                                                                <span>&bull;</span>
                                                                <span class="font-bold text-slate-700" x-text="c.formatted_price"></span>
                                                            </div>
                                                        </div>
                                                        <span class="shrink-0 px-2.5 py-1 rounded-lg bg-pink-600 text-white text-[10px] font-black group-hover/card:bg-pink-700 transition flex items-center gap-1 shadow-2xs">
                                                            <span>Xem</span>
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                        </span>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Bottom metadata tag -->
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-50 text-[10px] text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <template x-if="msg.source === 'local_ai'">
                                                <span class="text-emerald-600 font-bold flex items-center gap-0.5">
                                                    <span>⚡ Local AI</span>
                                                </span>
                                            </template>
                                            <template x-if="msg.source === 'smart_fallback' || msg.source === 'safety_fallback'">
                                                <span class="text-pink-600 font-bold flex items-center gap-0.5">
                                                    <span>🧠 LMS Intelligence</span>
                                                </span>
                                            </template>
                                        </span>
                                        <span x-text="msg.time"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- TYPING INDICATOR -->
            <div x-show="isLoading"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex items-start gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs animate-pulse">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>
                <div class="bg-white border border-pink-100/90 rounded-2xl rounded-tl-xs px-4 py-3 shadow-xs flex items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span class="text-xs text-slate-500 font-medium">AI đang tra cứu dữ liệu & soạn câu trả lời...</span>
                </div>
            </div>
        </div>

        <!-- ================= QUICK-ASK SUGGESTION CHIPS ================= -->
        <div class="bg-white px-3 sm:px-4 py-2 border-t border-slate-100 shrink-0">
            <div class="flex items-center gap-1.5 overflow-x-auto sidebar-scroll py-1">
                <span class="text-[11px] font-bold text-slate-400 shrink-0 mr-1">Gợi ý:</span>
                <template x-for="chip in suggestedChips" :key="chip">
                    <button type="button"
                            @click="sendChip(chip)"
                            :disabled="isLoading"
                            class="px-2.5 py-1 rounded-full bg-pink-50/70 hover:bg-pink-600 text-slate-700 hover:text-white border border-pink-200/80 hover:border-pink-600 text-xs font-semibold shadow-2xs transition-all duration-200 whitespace-nowrap shrink-0 cursor-pointer disabled:opacity-50">
                        <span x-text="chip"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- ================= INPUT COMPONENT ================= -->
        <div class="bg-white p-3 sm:p-4 border-t border-slate-100 shrink-0">
            <form @submit.prevent="sendMessage()" class="relative flex items-center">
                <input type="text"
                       x-ref="chatInput"
                       x-model="inputMessage"
                       :disabled="isLoading"
                       placeholder="Hỏi về khóa học, lộ trình học tập, bài giảng..."
                       maxlength="2000"
                       class="w-full pl-4 pr-12 py-2.5 text-xs sm:text-sm border border-slate-200 focus:border-pink-600 focus:ring-1 focus:ring-pink-500 rounded-2xl bg-slate-50/60 focus:bg-white text-slate-800 placeholder-slate-400 transition shadow-2xs disabled:bg-slate-100">

                <!-- Nút Gửi -->
                <button type="submit"
                        :disabled="isLoading || !inputMessage.trim()"
                        class="absolute right-1.5 p-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white disabled:opacity-40 disabled:hover:bg-pink-600 transition shadow-xs cursor-pointer disabled:cursor-not-allowed"
                        title="Gửi câu hỏi">
                    <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                    </svg>
                </button>
            </form>

            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400 px-1">
                <span>Nhấn <kbd class="px-1 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono">Enter</kbd> để gửi</span>
                <span class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Hệ thống học tập Ctrl C+V</span>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- ================= ALPINE.JS CHATBOX HANDLER ================= -->
<script>
    function lmsAiChatbox() {
        return {
            isOpen: false,
            isExpanded: false,
            showGreetingBadge: true,
            inputMessage: '',
            isLoading: false,
            statusSubtitle: 'Tra cứu dữ liệu LMS & Tư vấn 24/7',
            coursesCountText: '...',
            suggestedChips: [
                '📚 Các khóa học hiện có',
                '🎯 Tư vấn lộ trình học',
                '📖 Khóa học của tôi',
                '🚀 Khóa học miễn phí',
            ],
            messages: [],
            storageKey: 'ctrlcv_lms_ai_chat_v2',

            init() {
                this.loadMessages();
                this.fetchAiStatus();
            },

            loadMessages() {
                try {
                    const saved = localStorage.getItem(this.storageKey);
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            this.messages = parsed;
                            return;
                        }
                    }
                } catch (e) {
                    console.error('Error loading chat history:', e);
                }

                // Initial Welcome Greeting
                this.messages = [
                    {
                        id: 'welcome_' + Date.now(),
                        role: 'assistant',
                        content: "👋 Xin chào bạn! Mình là **Trợ lý AI Học tập** của nền tảng **Ctrl C+V**.\n\nMình có thể giúp bạn:\n- 📚 **Khám phá các khóa học** lập trình & kỹ năng phù hợp.\n- 🎯 **Tư vấn lộ trình học tập** từng bước cho người mới.\n- 📖 **Theo dõi tiến độ học tập** và gợi ý bài học tiếp theo.\n- 📝 **Tìm kiếm bài thi trắc nghiệm (Quizzes)** rèn luyện kiến thức.\n\nBạn có thể nhấn vào các gợi ý bên dưới hoặc đặt câu hỏi bất kỳ nhé!",
                        source: 'smart_fallback',
                        time: this.currentTime(),
                        relevant_courses: []
                    }
                ];
                this.saveMessages();
            },

            saveMessages() {
                try {
                    // Giữ tối đa 40 tin nhắn trong localStorage để tiết kiệm dung lượng
                    const trimmed = this.messages.slice(-40);
                    localStorage.setItem(this.storageKey, JSON.stringify(trimmed));
                } catch (e) {
                    console.error('Error saving chat history:', e);
                }
            },

            confirmClearChat() {
                if (confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử trò chuyện này?')) {
                    localStorage.removeItem(this.storageKey);
                    this.messages = [];
                    this.loadMessages();
                }
            },

            toggleChat() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.showGreetingBadge = false;
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (this.$refs.chatInput) {
                            this.$refs.chatInput.focus();
                        }
                    });
                }
            },

            toggleExpand() {
                this.isExpanded = !this.isExpanded;
                this.$nextTick(() => this.scrollToBottom());
            },

            sendChip(chipText) {
                this.inputMessage = chipText;
                this.sendMessage();
            },

            async sendMessage() {
                const text = this.inputMessage.trim();
                if (!text || this.isLoading) return;

                // Thêm tin nhắn của user
                const userMsg = {
                    id: 'usr_' + Date.now(),
                    role: 'user',
                    content: text,
                    time: this.currentTime()
                };

                this.messages.push(userMsg);
                this.inputMessage = '';
                this.isLoading = true;
                this.saveMessages();
                this.$nextTick(() => this.scrollToBottom());

                // Chuẩn bị payload lịch sử gửi lên server
                const historyForApi = this.messages
                    .filter(m => m.role === 'user' || m.role === 'assistant')
                    .slice(-8)
                    .map(m => ({
                        role: m.role,
                        content: m.content
                    }));

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    const response = await fetch('{{ route("ai.chat") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({
                            message: text,
                            history: historyForApi
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }

                    const data = await response.json();

                    const assistantMsg = {
                        id: 'ast_' + Date.now(),
                        role: 'assistant',
                        content: data.message || 'Xin lỗi, mình chưa thể xử lý câu trả lời lúc này.',
                        source: data.source || 'smart_fallback',
                        model: data.model || '',
                        time: this.currentTime(),
                        relevant_courses: data.relevant_courses || []
                    };

                    this.messages.push(assistantMsg);

                    if (Array.isArray(data.suggested_chips) && data.suggested_chips.length > 0) {
                        this.suggestedChips = data.suggested_chips;
                    }

                    this.saveMessages();
                } catch (error) {
                    console.error('Chat error:', error);
                    // Thông báo fallback thân thiện, không bao giờ để crash
                    this.messages.push({
                        id: 'err_' + Date.now(),
                        role: 'assistant',
                        content: "⚠️ Đã xảy ra gián đoạn kết nối ngắn. Bạn có thể xem danh mục khóa học tại [Tất cả khóa học]({{ route('courses.index') }}) hoặc thử lại sau nhé!",
                        source: 'safety_fallback',
                        time: this.currentTime(),
                        relevant_courses: []
                    });
                    this.saveMessages();
                } finally {
                    this.isLoading = false;
                    this.$nextTick(() => {
                        this.scrollToBottom();
                        if (this.$refs.chatInput) {
                            this.$refs.chatInput.focus();
                        }
                    });
                }
            },

            async fetchAiStatus() {
                try {
                    const res = await fetch('{{ route("ai.status") }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.catalog_summary) {
                            this.coursesCountText = `${data.catalog_summary.total_courses} khóa học đã duyệt`;
                        }
                        if (data.ai_health && data.ai_health.online) {
                            this.statusSubtitle = `Local AI (${data.model}) sẵn sàng`;
                        } else {
                            this.statusSubtitle = `LMS Intelligence & Tra cứu dữ liệu thực`;
                        }
                    }
                } catch (e) {
                    this.coursesCountText = 'Thư viện trực tuyến';
                }
            },

            scrollToBottom() {
                const el = this.$refs.messageContainer;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            },

            currentTime() {
                const d = new Date();
                return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            },

            /**
             * Phân tích cú pháp Markdown sang HTML an toàn
             */
            renderMarkdown(raw) {
                if (!raw) return '';

                // 1. Escape HTML căn bản chống XSS
                let safe = raw
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                // 2. Code blocks (```code```)
                safe = safe.replace(/```([\s\S]*?)```/g, '<pre class="my-2 p-2.5 rounded-xl bg-slate-900 text-slate-100 font-mono text-[11px] overflow-x-auto leading-relaxed"><code>$1</code></pre>');

                // 3. Inline code (`code`)
                safe = safe.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 text-xs font-mono font-bold border border-pink-100">$1</code>');

                // 4. Headers: ### Header
                safe = safe.replace(/^### (.*$)/gim, '<h4 class="font-black text-slate-900 text-xs sm:text-sm mt-3 mb-1 flex items-center gap-1.5 text-pink-700"><span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>$1</h4>');
                safe = safe.replace(/^## (.*$)/gim, '<h3 class="font-black text-slate-900 text-sm mt-3 mb-1.5 text-pink-700">$1</h3>');

                // 5. Bold & Italic
                safe = safe.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-extrabold text-slate-900">$1</strong>');
                safe = safe.replace(/\*([^*]+)\*/g, '<em class="text-slate-700">$1</em>');

                // 6. Actionable Markdown Links [Text](URL)
                safe = safe.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+|\/[^\s\)]+)\)/g, (match, text, url) => {
                    return `<a href="${url}" class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-700 font-extrabold underline underline-offset-2 decoration-pink-300 hover:decoration-pink-600 transition">${text} <svg class="w-3 h-3 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>`;
                });

                // 7. Bullet Lists (- Item or * Item)
                safe = safe.replace(/^[\*\-] (.*$)/gim, '<div class="flex items-start gap-2 my-1"><span class="text-pink-500 font-bold shrink-0 mt-0.5">&bull;</span><span>$1</span></div>');

                // 8. Numbered Lists (1. Item)
                safe = safe.replace(/^(\d+)\. (.*$)/gim, '<div class="flex items-start gap-2 my-1"><span class="font-bold text-pink-600 shrink-0">$1.</span><span>$2</span></div>');

                // 9. Line breaks (\n)
                safe = safe.replace(/\n\n/g, '<div class="h-2"></div>');
                safe = safe.replace(/\n/g, '<br>');

                return safe;
            }
        };
    }
</script>
