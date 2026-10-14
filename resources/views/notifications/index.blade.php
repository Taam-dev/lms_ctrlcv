<x-app-layout>
    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 mb-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-neutral-900 text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Hộp thư thông báo
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Cập nhật các hoạt động về khóa học, bài kiểm tra, đánh giá và báo cáo của bạn.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    @if(auth()->user()->unreadNotificationsCount() > 0)
                        <form method="POST" action="{{ route('notifications.markAllRead') }}">
                            @csrf
                            <button type="submit" 
                                    class="px-4 py-2 rounded-xl bg-pink-50 hover:bg-pink-100 text-pink-700 font-bold text-xs transition border border-pink-200 cursor-pointer">
                                Đánh dấu đã đọc tất cả
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Notifications List -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                @if($notifications->isEmpty())
                    <div class="py-16 px-4 text-center space-y-3">
                        <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-800">
                            Chưa có thông báo nào
                        </h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                            Hiện tại bạn chưa nhận được thông báo nào. Mọi tin tức mới sẽ được cập nhật trực tiếp tại đây.
                        </p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($notifications as $notification)
                            @php
                                $isUnread = ! $notification->isRead();
                            @endphp
                            <a href="{{ route('notifications.go', $notification->id) }}"
                               class="flex items-start gap-4 p-4 sm:p-5 transition hover:bg-slate-50/90 {{ $isUnread ? 'bg-pink-50/40' : 'bg-white' }}">
                                <!-- Icon badge -->
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200/60 shadow-2xs">
                                    @if($notification->type === 'quiz_created')
                                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    @elseif($notification->type === 'course_approved' || $notification->type === 'quiz_approved')
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif($notification->type === 'course_reviewed')
                                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @elseif($notification->type === 'user_reported')
                                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    @endif
                                </div>

                                <!-- Body -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="text-sm font-bold text-slate-900 truncate">
                                            {{ $notification->title }}
                                        </h3>
                                        <div class="flex items-center gap-2 shrink-0">
                                            @if($isUnread)
                                                <span class="w-2.5 h-2.5 rounded-full bg-pink-600"></span>
                                            @endif
                                            <span class="text-xs text-slate-400 font-medium">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                        {{ $notification->message }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if($notifications->hasPages())
                        <div class="p-4 border-t border-slate-100 bg-slate-50">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
