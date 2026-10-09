<footer class="bg-neutral-950 text-neutral-400 mt-auto border-t border-white/10">
    <div class="h-0.5 bg-pink-600"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            <!-- Brand -->
            <div class="space-y-4">
                <x-application-logo :dark="true" />
                <p class="text-sm leading-relaxed text-neutral-400 max-w-sm">
                    Hệ thống học tập trực tuyến do nhóm Ctrl C+V xây dựng dành cho Giảng viên và Học viên.
                </p>

                <!-- Kênh mạng xã hội (Facebook, Messenger, Zalo) -->
                <div class="pt-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-neutral-300 block mb-3">
                        Kết nối mạng xã hội
                    </span>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <!-- Facebook -->
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-neutral-900 border border-neutral-800 hover:border-[#1877F2]/60 hover:bg-[#1877F2]/10 text-neutral-300 hover:text-[#1877F2] transition-all duration-200 group text-xs font-semibold shadow-sm hover:-translate-y-0.5"
                           title="Theo dõi trên Facebook">
                            <svg class="w-4 h-4 text-[#1877F2] shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span>Facebook</span>
                        </a>

                        <!-- Messenger -->
                        <a href="https://m.me" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-neutral-900 border border-neutral-800 hover:border-[#0084FF]/60 hover:bg-[#0084FF]/10 text-neutral-300 hover:text-[#0084FF] transition-all duration-200 group text-xs font-semibold shadow-sm hover:-translate-y-0.5"
                           title="Nhắn tin qua Messenger">
                            <svg class="w-4 h-4 text-[#0084FF] shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.082.3 2.23.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0zm1.192 14.963l-3.056-3.26-5.963 3.26 6.559-6.963 3.13 3.26 5.89-3.26-6.56 6.963z"/>
                            </svg>
                            <span>Messenger</span>
                        </a>

                        <!-- Zalo -->
                        <a href="https://zalo.me" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-neutral-900 border border-neutral-800 hover:border-[#0068FF]/60 hover:bg-[#0068FF]/10 text-neutral-300 hover:text-[#0068FF] transition-all duration-200 group text-xs font-semibold shadow-sm hover:-translate-y-0.5"
                           title="Hỗ trợ qua Zalo">
                            <span class="w-4 h-4 rounded-full bg-[#0068FF] text-white font-black text-[9px] flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">Z</span>
                            <span>Zalo</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick links -->
            <div>
                <h4 class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-white mb-4 pb-2 border-b-2 border-pink-600 inline-block">
                    Điều hướng
                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-pink-400 transition">Trang chủ</a></li>
                    <li><a href="{{ route('courses.index') }}" class="hover:text-pink-400 transition">Tất cả khóa học</a></li>
                    @auth
                        <li><a href="{{ route('student.quizzes.index') }}" class="hover:text-pink-400 transition">Bài kiểm tra trắc nghiệm</a></li>
                        @if(auth()->user()->role === 'teacher')
                            <li><a href="{{ route('teacher.dashboard') }}" class="hover:text-pink-400 transition">Bảng điều khiển giảng viên</a></li>
                        @elseif(auth()->user()->role === 'admin')
                            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-pink-400 transition">Trang quản trị</a></li>
                        @endif
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-pink-400 transition">Hồ sơ cá nhân</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-pink-400 transition">Đăng nhập</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-pink-400 transition">Đăng ký tài khoản</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-white mb-4 pb-2 border-b-2 border-pink-600 inline-block">
                    Liên hệ
                </h4>
                <ul class="space-y-3 text-sm">
                    <li>Tòa nhà JVPE, Lô 20, Đường số 2, Công viên phần mềm Quang Trung, P. Trung Mỹ Tây, TP. HCM</li>
                    <li>Hotline: <strong class="text-white">0912 429 944</strong></li>
                    <li>Email: <a href="mailto:tuyensinh@stc.edu.vn" class="text-pink-400 hover:underline">tuyensinh@stc.edu.vn</a></li>
                    <li>Thời gian: Thứ 2 - Thứ 7 (07:00 - 19:00)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 text-xs py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <p>
                &copy; {{ date('Y') }} <strong class="text-white font-semibold">MiniLMS</strong>. Bản quyền thuộc về Đồ án 1 - Hệ thống Quản lý Học tập Trực tuyến của nhóm Ctrl C+V.
            </p>
        </div>
    </div>
</footer>
