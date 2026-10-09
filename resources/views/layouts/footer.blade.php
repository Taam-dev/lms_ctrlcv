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
