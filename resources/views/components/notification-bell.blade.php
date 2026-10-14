@props(['dark' => true])

@php
    $navUser = Auth::user();
    $notifications = $navUser ? $navUser->appNotifications()->take(10)->get() : collect();
    $unreadCount = $navUser ? $navUser->unreadNotificationsCount() : 0;
@endphp

<div x-data="{ 
    open: false, 
    unreadCount: {{ (int) $unreadCount }},
    markAllRead() {
        if (this.unreadCount <= 0) return;
        fetch('{{ route('notifications.markAllRead') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(() => {
            this.unreadCount = 0;
            $el.querySelectorAll('.notif-unread-item').forEach(el => {
                el.classList.remove('bg-pink-50/50', 'notif-unread-item');
                el.classList.add('bg-white');
            });
            $el.querySelectorAll('.notif-dot').forEach(el => el.remove());
        }).catch(err => console.error(err));
    },
    deleteNotification(id, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        fetch('/notifications/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                const item = document.getElementById('bell-notif-' + id);
                if (item) {
                    if (item.classList.contains('notif-unread-item') && this.unreadCount > 0) {
                        this.unreadCount--;
                    }
                    item.remove();
                }
            }
        }).catch(err => console.error(err));
    }
}" 
     @click.outside="open = false" 
     @keydown.escape.window="open = false"
     class="relative">

    <!-- Nút Chuông Thông Báo (Trắng đen tối giản, tinh tế) -->
    <button type="button" 
            @click="open = !open" 
            class="relative inline-flex items-center justify-center w-10 h-10 transition duration-150 focus:outline-none cursor-pointer {{ $dark ? 'border border-white/15 hover:border-pink-500 text-neutral-300 hover:text-white bg-transparent' : 'border border-slate-200 hover:border-pink-500 text-slate-700 hover:text-slate-900 bg-white' }}"
            title="Thông báo"
            aria-label="Thông báo">
        <!-- SVG Chuông trắng đen -->
        <svg class="w-5 h-5 {{ $dark ? 'text-white' : 'text-slate-800' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Badge chấm đỏ/hồng báo số lượng chưa đọc -->
        <span x-show="unreadCount > 0" 
              x-cloak
              class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-pink-600 text-white text-[10px] font-black rounded-full flex items-center justify-center {{ $dark ? 'ring-2 ring-neutral-950' : 'ring-2 ring-white' }} shadow-xs"
              x-text="unreadCount > 9 ? '9+' : unreadCount">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
    </button>

    <!-- Dropdown Menu Thông Báo (Gọn gàng, tinh tế, hiện đại) -->
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-white text-slate-800 shadow-2xl border border-slate-200/90 z-50 overflow-hidden"
         style="display: none;">

        <!-- Header thông báo -->
        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xs uppercase font-black tracking-wider text-slate-900">Thông báo</span>
                <span x-show="unreadCount > 0" 
                      x-cloak
                      class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-full bg-pink-100 text-pink-700" 
                      x-text="unreadCount + ' mới'">
                    {{ $unreadCount }} mới
                </span>
            </div>
            <button type="button" 
                    x-show="unreadCount > 0" 
                    x-cloak
                    @click="markAllRead()"
                    class="text-[11px] font-bold text-pink-600 hover:text-pink-700 transition cursor-pointer">
                Đã đọc hết
            </button>
        </div>

        <!-- Danh sách thông báo (Scroll gọn gàng) -->
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
            @forelse($notifications as $notification)
                @php
                    $isUnread = ! $notification->isRead();
                @endphp
                <div id="bell-notif-{{ $notification->id }}"
                     class="group relative flex items-start justify-between gap-2 p-3 transition hover:bg-slate-50/80 {{ $isUnread ? 'bg-pink-50/40 notif-unread-item' : 'bg-white' }}">
                    <a href="{{ route('notifications.go', $notification->id) }}"
                       class="flex items-start gap-3 flex-1 min-w-0 text-left">
                        <!-- Icon biểu thị loại thông báo -->
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 mt-0.5 border border-slate-200/60 shadow-2xs">
                            @if($notification->type === 'quiz_created')
                                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            @elseif($notification->type === 'course_approved' || $notification->type === 'quiz_approved')
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($notification->type === 'course_pending' || $notification->type === 'quiz_pending')
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @elseif($notification->type === 'course_reviewed')
                                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @elseif($notification->type === 'user_reported')
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            @endif
                        </div>

                        <!-- Nội dung -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="text-xs font-bold text-slate-900 truncate group-hover:text-pink-600 transition">
                                    {{ $notification->title }}
                                </h4>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if($isUnread)
                                        <span class="w-2 h-2 rounded-full bg-pink-600 notif-dot"></span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed mt-0.5">
                                {{ $notification->message }}
                            </p>
                        </div>
                    </a>

                    <!-- Nút X xóa nhanh từ dropdown -->
                    <button type="button"
                            @click="deleteNotification({{ $notification->id }}, $event)"
                            class="shrink-0 w-6 h-6 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition border border-transparent hover:border-rose-100 cursor-pointer opacity-70 hover:opacity-100"
                            title="Xóa thông báo này"
                            aria-label="Xóa thông báo">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @empty
                <div class="py-8 px-4 text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Chưa có thông báo nào</p>
                    <p class="text-[11px] text-slate-400 max-w-[220px] mx-auto leading-relaxed">
                        Bạn sẽ nhận được thông báo về bài kiểm tra, khóa học và các hoạt động tại đây.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
            <a href="{{ route('notifications.index') }}" 
               class="text-xs font-bold text-slate-700 hover:text-pink-600 transition inline-flex items-center gap-1">
                <span>Xem tất cả thông báo</span>
                <span class="text-slate-400">&rarr;</span>
            </a>
        </div>
    </div>
</div>
