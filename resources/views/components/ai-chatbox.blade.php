@php
    if (defined('CTRLCV_AI_CHATBOX_MOUNTED')) {
        return;
    }
    define('CTRLCV_AI_CHATBOX_MOUNTED', true);
@endphp

<div x-data="lmsAiChatbox()"
     x-cloak
     @keydown.escape.window="if (isOpen) isOpen = false"
     class="relative z-50">

    <!-- ================= FLOATING TOGGLE BUTTON ================= -->
    <div class="fixed bottom-6 right-6 z-[9999] flex items-center gap-2">
        <!-- Floating Greeting Pill (Khi chatbox đang đóng) -->
        <div x-show="!isOpen && showGreetingBadge"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             @click="toggleChat()"
             class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 bg-neutral-900 text-white border border-neutral-700 rounded-full shadow-lg text-xs font-semibold select-none cursor-pointer hover:bg-neutral-800 transition">
            <svg class="w-3.5 h-3.5 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>Trợ lý AI</span>
            <button @click.stop="showGreetingBadge = false"
                    class="ml-1 text-neutral-400 hover:text-white text-xs leading-none"
                    title="Đóng">&times;</button>
        </div>

        <!-- Nút Tròn Mở/Đóng Chatbox Với Team Logo -->
        <button type="button"
                @click="toggleChat()"
                id="btn-ai-chat-toggle"
                class="relative flex items-center justify-center w-14 h-14 rounded-full bg-neutral-900 hover:bg-neutral-800 text-white shadow-xl shadow-neutral-950/30 hover:scale-105 active:scale-95 transition-all duration-200 border border-neutral-700 cursor-pointer select-none group"
                :class="{ 'rotate-90': isOpen }"
                :aria-expanded="isOpen.toString()"
                aria-label="Trợ lý AI Ctrl C+V">

            <!-- Icon khi đang Đóng: Team Logo với viền và chấm trạng thái -->
            <span x-show="!isOpen" class="relative flex items-center justify-center w-9 h-9">
                <img src="{{ asset('logo.png') }}"
                     alt="Ctrl C+V AI"
                     class="w-full h-full object-contain rounded-full group-hover:scale-105 transition-transform duration-200">
                <!-- Chấm trạng thái nhỏ gọn -->
                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400 border border-neutral-900"></span>
                </span>
            </span>

            <!-- Icon khi đang Mở: Dấu X đóng -->
            <span x-show="isOpen" class="relative flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
         class="fixed z-[9999] flex flex-col bg-white rounded-2xl border border-slate-300 shadow-2xl shadow-neutral-950/20 overflow-hidden transition-all duration-300"
         :class="isExpanded
            ? 'top-4 sm:top-6 bottom-20 sm:bottom-24 right-3 sm:right-6 w-[calc(100vw-1.5rem)] sm:w-[580px] max-w-[95vw]'
            : 'bottom-20 sm:bottom-24 right-3 sm:right-6 w-[calc(100vw-1.5rem)] sm:w-[400px] max-w-[95vw] h-[520px] max-h-[calc(100vh-7.5rem)]'"
         role="dialog"
         aria-labelledby="ai-chatbox-title">

        <!-- HEADER BANNER (Team Logo + Sleek Dark) -->
        <div class="bg-neutral-900 text-white px-4 py-3 flex items-center justify-between border-b border-neutral-800 shrink-0">
            <!-- Left: Team Logo Avatar + Title -->
            <div class="flex items-center gap-2.5">
                <div class="relative w-8 h-8 rounded-lg bg-neutral-800 p-0.5 border border-neutral-700 flex items-center justify-center shrink-0 overflow-hidden">
                    <img src="{{ asset('logo.png') }}" alt="Ctrl C+V AI" class="w-full h-full object-contain rounded">
                    <!-- Active status dot -->
                    <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-400 border border-neutral-900"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <h3 id="ai-chatbox-title" class="text-xs sm:text-sm font-bold tracking-tight text-white">
                            Ctrl C+V AI
                        </h3>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider bg-neutral-800 border border-neutral-700 text-neutral-300">
                            Assistant
                        </span>
                    </div>
                    <p class="text-[11px] text-neutral-400" x-text="statusSubtitle">Tra cứu thông tin LMS</p>
                </div>
            </div>

            <!-- Right: Actions (Expand, Clear, Close) -->
            <div class="flex items-center gap-1 text-neutral-400">
                <!-- Nút Phóng to / Thu nhỏ -->
                <button type="button"
                        @click="toggleExpand()"
                        class="p-1.5 rounded-lg hover:bg-neutral-800 hover:text-white transition cursor-pointer"
                        :title="isExpanded ? 'Thu nhỏ' : 'Mở rộng'">
                    <svg x-show="!isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                    <svg x-show="isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m11-4l-5 5m5-5h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 4l-5-5m5 5h-4m4 0v-4" />
                    </svg>
                </button>

                <!-- Nút Xóa lịch sử trò chuyện -->
                <button type="button"
                        @click="confirmClearChat()"
                        class="p-1.5 rounded-lg hover:bg-neutral-800 hover:text-white transition cursor-pointer"
                        title="Xóa lịch sử chat">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>

                <!-- Nút Đóng -->
                <button type="button"
                        @click="toggleChat()"
                        class="p-1.5 rounded-lg hover:bg-neutral-800 hover:text-white transition cursor-pointer"
                        title="Đóng">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- STATUS BAR -->
        <div class="px-4 py-1.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-[11px] text-slate-600 shrink-0">
            <div class="flex items-center gap-1.5 truncate">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                <span>Hệ thống:</span>
                <span class="font-bold text-slate-800" x-text="coursesCountText">Đang tải...</span>
            </div>
            <div class="shrink-0 text-[10px] text-slate-400 uppercase tracking-wider font-medium">
                Dữ liệu thực tế
            </div>
        </div>

        <!-- ================= CHAT MESSAGE STREAM ================= -->
        <div id="ai-chatbox-messages"
             x-ref="messageContainer"
             class="flex-1 min-h-0 overflow-y-auto p-4 pb-6 space-y-3.5 bg-slate-50/50 scroll-smooth">

            <!-- Loop Messages -->
            <template x-for="(msg, index) in messages" :key="msg.id || index">
                <div class="space-y-1.5">
                    <!-- TIN NHẮN TỪ NGƯỜI DÙNG (Dark Monochrome Bubble) -->
                    <template x-if="msg.role === 'user'">
                        <div class="flex items-start justify-end">
                            <div class="max-w-[85%] bg-neutral-900 text-white rounded-xl rounded-tr-xs px-3.5 py-2.5 text-xs sm:text-sm leading-relaxed shadow-xs">
                                <p class="whitespace-pre-wrap break-words" x-text="msg.content"></p>
                                <span class="block text-[10px] text-neutral-400 text-right mt-1" x-text="msg.time"></span>
                            </div>
                        </div>
                    </template>

                    <!-- TIN NHẮN TỪ TRỢ LÝ AI (Clean White Card + Team Logo Avatar) -->
                    <template x-if="msg.role === 'assistant'">
                        <div class="flex items-start gap-2">
                            <!-- Team Logo Bot Avatar -->
                            <div class="w-7 h-7 rounded-lg bg-neutral-900 p-0.5 border border-neutral-700 flex items-center justify-center shrink-0 mt-0.5 overflow-hidden">
                                <img src="{{ asset('logo.png') }}" alt="Ctrl C+V AI" class="w-full h-full object-contain rounded">
                            </div>

                            <div class="max-w-[90%] space-y-1.5">
                                <!-- Bubble Content -->
                                <div class="bg-white border border-slate-200 rounded-xl rounded-tl-xs p-3.5 shadow-2xs text-xs sm:text-sm text-slate-800 leading-relaxed">
                                    <!-- Render Markdown an toàn, không emoji -->
                                    <div class="chat-markdown-content break-words" x-html="renderMarkdown(msg.content)"></div>

                                    <!-- Thẻ hiển thị khóa học liên quan (nếu có) -->
                                    <template x-if="msg.relevant_courses && msg.relevant_courses.length > 0">
                                        <div class="pt-2.5 mt-2.5 border-t border-slate-100 space-y-1.5">
                                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                                Khóa học liên quan:
                                            </p>
                                            <div class="space-y-1.5">
                                                <template x-for="c in msg.relevant_courses" :key="c.id">
                                                    <a :href="c.url"
                                                       class="group/card flex items-center justify-between p-2 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 transition text-left">
                                                        <div class="min-w-0 pr-2">
                                                            <div class="text-xs font-bold text-slate-900 group-hover/card:text-pink-600 transition truncate" x-text="c.title"></div>
                                                            <div class="text-[11px] text-slate-500 mt-0.5">
                                                                <span x-text="c.teacher"></span>
                                                                <span>&bull;</span>
                                                                <span class="font-semibold text-slate-700" x-text="c.formatted_price"></span>
                                                            </div>
                                                        </div>
                                                        <span class="shrink-0 px-2 py-0.5 rounded bg-neutral-900 text-white text-[10px] font-bold group-hover/card:bg-black transition flex items-center gap-1">
                                                            <span>Xem</span>
                                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                        </span>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Bottom metadata tag -->
                                    <div class="flex items-center justify-between pt-1.5 mt-2 border-t border-slate-100 text-[10px] text-slate-400">
                                        <span class="font-medium text-slate-500" x-text="msg.source === 'local_ai' ? 'Local AI' : 'LMS Data'"></span>
                                        <span x-text="msg.time"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- TYPING INDICATOR (Team Logo + Spinner) -->
            <div x-show="isLoading"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex items-start gap-2">
                <div class="w-7 h-7 rounded-lg bg-neutral-900 p-0.5 border border-neutral-700 flex items-center justify-center shrink-0 overflow-hidden">
                    <img src="{{ asset('logo.png') }}" alt="Ctrl C+V AI" class="w-full h-full object-contain rounded opacity-80">
                </div>
                <div class="bg-white border border-slate-200 rounded-xl rounded-tl-xs px-3.5 py-2.5 shadow-2xs flex items-center gap-2">
                    <div class="flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600 animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span class="text-xs text-slate-500">Đang chuẩn bị câu trả lời...</span>
                </div>
            </div>
        </div>

        <!-- ================= SUGGESTION CHIPS (Monochrome clean buttons with generous margin) ================= -->
        <div class="bg-white px-3 py-2.5 border-t border-slate-200 shrink-0">
            <div class="flex items-center gap-1.5 overflow-x-auto sidebar-scroll py-0.5">
                <template x-for="chip in suggestedChips" :key="chip">
                    <button type="button"
                            @click="sendChip(chip)"
                            :disabled="isLoading || isSending"
                            class="px-2.5 py-1 rounded-md bg-slate-100 hover:bg-neutral-900 text-slate-700 hover:text-white border border-slate-200 hover:border-neutral-900 text-xs font-medium transition whitespace-nowrap shrink-0 cursor-pointer disabled:opacity-50">
                        <span x-text="chip"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- ================= INPUT COMPONENT ================= -->
        <div class="bg-white p-3 border-t border-slate-200 shrink-0">
            <form @submit.prevent="sendMessage()" class="relative flex items-center">
                <input type="text"
                       x-ref="chatInput"
                       x-model="inputMessage"
                       :disabled="isLoading || isSending"
                       placeholder="Nhập câu hỏi về khóa học, lộ trình..."
                       maxlength="2000"
                       class="w-full pl-3 pr-10 py-2 text-xs sm:text-sm border border-slate-200 focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 rounded-xl bg-slate-50/70 focus:bg-white text-slate-800 placeholder-slate-400 transition disabled:bg-slate-100">

                <!-- Nút Gửi (Monochrome with strict disable on sending) -->
                <button type="submit"
                        :disabled="isLoading || isSending || !inputMessage.trim()"
                        class="absolute right-1.5 p-1.5 rounded-lg bg-neutral-900 hover:bg-black text-white disabled:opacity-30 transition cursor-pointer disabled:cursor-not-allowed"
                        title="Gửi câu hỏi">
                    <svg class="w-3.5 h-3.5 transform rotate-90 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19V5m0 0l-7 7m7-7l7 7" />
                    </svg>
                </button>
            </form>

            <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400 px-1">
                <span>Nhấn <kbd class="px-1 py-0.2 rounded bg-slate-100 border border-slate-200 text-slate-600 font-mono">Enter</kbd> để gửi</span>
                <span>Ctrl C+V LMS</span>
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
            isSending: false,
            statusSubtitle: 'Tra cứu thông tin LMS',
            coursesCountText: '...',
            suggestedChips: [
                'Tất cả khóa học',
                'Lộ trình học',
                'Khóa học của tôi',
                'Khóa học miễn phí',
            ],
            messages: [],
            storageKey: 'ctrlcv_lms_ai_chat_v3',

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
                            // Filter consecutive identical duplicates
                            const deduplicated = [];
                            for (const m of parsed) {
                                const prev = deduplicated[deduplicated.length - 1];
                                if (!prev || prev.role !== m.role || prev.content !== m.content) {
                                    deduplicated.push(m);
                                }
                            }
                            this.messages = deduplicated;
                            return;
                        }
                    }
                } catch (e) {
                    console.error('Error loading chat history:', e);
                }

                // Initial Welcome Greeting (Gọn gàng, dễ hiểu, không emoji)
                this.messages = [
                    {
                        id: 'welcome_' + Date.now(),
                        role: 'assistant',
                        content: "**Xin chào!** Tôi là Trợ lý AI của **Ctrl C+V**.\n\nTôi có thể hỗ trợ bạn:\n- Tra cứu thông tin khóa học và học phí\n- Đề xuất lộ trình học tập phù hợp\n- Theo dõi tiến độ học tập cá nhân\n- Hướng dẫn làm bài thi trắc nghiệm\n\nBạn cần tìm khóa học hoặc thông tin gì?",
                        source: 'smart_fallback',
                        time: this.currentTime(),
                        relevant_courses: []
                    }
                ];
                this.saveMessages();
            },

            saveMessages() {
                try {
                    const trimmed = this.messages.slice(-40);
                    localStorage.setItem(this.storageKey, JSON.stringify(trimmed));
                } catch (e) {
                    console.error('Error saving chat history:', e);
                }
            },

            confirmClearChat() {
                if (confirm('Xóa toàn bộ lịch sử trò chuyện này?')) {
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
                if (this.isLoading || this.isSending) return;
                this.inputMessage = chipText;
                this.sendMessage();
            },

            async sendMessage() {
                const text = this.inputMessage.trim();
                // Strict check: if empty or already in flight, exit immediately
                if (!text || this.isLoading || this.isSending) return;

                // Anti-duplicate consecutive user message check
                const lastMsg = this.messages[this.messages.length - 1];
                if (lastMsg && lastMsg.role === 'user' && lastMsg.content.trim() === text) {
                    return;
                }

                // Lock request state
                this.isSending = true;
                this.isLoading = true;

                const userMsg = {
                    id: 'usr_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                    role: 'user',
                    content: text,
                    time: this.currentTime()
                };

                this.messages.push(userMsg);
                this.inputMessage = '';
                this.saveMessages();
                this.$nextTick(() => this.scrollToBottom());

                // Prepare deduplicated history payload for backend
                const historyForApi = [];
                for (const m of this.messages.slice(-8)) {
                    if (m.role === 'user' || m.role === 'assistant') {
                        const lastInPayload = historyForApi[historyForApi.length - 1];
                        if (!lastInPayload || lastInPayload.role !== m.role || lastInPayload.content !== m.content) {
                            historyForApi.push({
                                role: m.role,
                                content: m.content
                            });
                        }
                    }
                }

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
                    const replyText = data.message || 'Xin lỗi, chưa thể xử lý câu trả lời lúc này.';

                    // Anti-duplicate consecutive assistant response check
                    const currentLastMsg = this.messages[this.messages.length - 1];
                    if (!currentLastMsg || currentLastMsg.role !== 'assistant' || currentLastMsg.content.trim() !== replyText.trim()) {
                        const assistantMsg = {
                            id: 'ast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4),
                            role: 'assistant',
                            content: replyText,
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
                    }
                } catch (error) {
                    console.error('Chat error:', error);
                    const errorMsgContent = "Tạm thời không kết nối được máy chủ. Bạn có thể xem danh mục tại [Tất cả khóa học]({{ route('courses.index') }}).";
                    const currentLastMsg = this.messages[this.messages.length - 1];
                    if (!currentLastMsg || currentLastMsg.content !== errorMsgContent) {
                        this.messages.push({
                            id: 'err_' + Date.now(),
                            role: 'assistant',
                            content: errorMsgContent,
                            source: 'safety_fallback',
                            time: this.currentTime(),
                            relevant_courses: []
                        });
                        this.saveMessages();
                    }
                } finally {
                    this.isLoading = false;
                    this.isSending = false;
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
                            this.statusSubtitle = 'LMS Intelligence';
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
             * Phân tích cú pháp Markdown sang HTML an toàn, gọn gàng
             */
            renderMarkdown(raw) {
                if (!raw) return '';

                let safe = raw
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');

                // Code blocks
                safe = safe.replace(/```([\s\S]*?)```/g, '<pre class="my-2 p-2.5 rounded-lg bg-neutral-900 text-slate-100 font-mono text-[11px] overflow-x-auto leading-relaxed"><code>$1</code></pre>');

                // Inline code
                safe = safe.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-xs font-mono font-medium border border-slate-200">$1</code>');

                // Headers
                safe = safe.replace(/^### (.*$)/gim, '<h4 class="font-bold text-slate-900 text-xs sm:text-sm mt-2.5 mb-1">$1</h4>');
                safe = safe.replace(/^## (.*$)/gim, '<h3 class="font-bold text-slate-900 text-sm mt-3 mb-1.5">$1</h3>');

                // Bold & Italic
                safe = safe.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>');
                safe = safe.replace(/\*([^*]+)\*/g, '<em class="text-slate-700">$1</em>');

                // Links
                safe = safe.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+|\/[^\s\)]+)\)/g, (match, text, url) => {
                    return `<a href="${url}" class="inline-flex items-center gap-1 text-slate-900 hover:text-pink-600 font-bold underline underline-offset-2 decoration-slate-300 hover:decoration-pink-600 transition">${text}<svg class="w-3 h-3 inline-block shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></a>`;
                });

                // Bullet Lists (Monochrome dot)
                safe = safe.replace(/^[\*\-] (.*$)/gim, '<div class="flex items-start gap-2 my-1"><span class="text-slate-400 font-bold shrink-0 mt-0.5">&bull;</span><span>$1</span></div>');

                // Numbered Lists
                safe = safe.replace(/^(\d+)\. (.*$)/gim, '<div class="flex items-start gap-2 my-1"><span class="font-bold text-slate-800 shrink-0">$1.</span><span>$2</span></div>');

                // Line breaks
                safe = safe.replace(/\n\n/g, '<div class="h-2"></div>');
                safe = safe.replace(/\n/g, '<br>');

                return safe;
            }
        };
    }
</script>
