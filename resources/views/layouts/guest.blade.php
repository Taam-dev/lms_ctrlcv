<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MiniLMS') }} - Đăng nhập / Đăng ký</title>
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

        <!-- Fonts (Plus Jakarta Sans) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="tiltController()" 
          @mousemove.window="onMouseMove($event)" 
          @mouseleave.window="onMouseLeave()"
          class="font-sans antialiased bg-neutral-950 text-neutral-200 min-h-full flex flex-col justify-between selection:bg-pink-600 selection:text-white relative overflow-x-hidden">

        <!-- Main Auth Area -->
        <main class="relative z-10 flex-1 flex flex-col items-center justify-center py-8 sm:py-12 px-4">
            <!-- Central wrapper maintaining precise sizing and alignment -->
            <div class="relative w-full {{ $maxWidth ?? 'max-w-[420px]' }} flex flex-col items-center">
                
                <!-- 3D Tilting Background Layer (Tấm slab gradient và Logo dính chặt vào nhau, thu gọn chiều cao phía trên) -->
                <div :style="transformStyle"
                     class="absolute -inset-x-4 sm:-inset-x-8 -top-7 sm:-top-8 -bottom-5 sm:-bottom-6 rounded-[32px] transition-transform duration-200 ease-out will-change-transform pointer-events-none z-0">
                    
                    <!-- Vệt sáng lan tỏa mờ phía sau slab -->
                    <div :style="glowStyle"
                         class="absolute -inset-6 rounded-full bg-pink-600/15 blur-[70px] transition-transform duration-500 ease-out pointer-events-none"></div>

                    <!-- Viền sáng chuyển sắc cho tấm slab -->
                    <div class="absolute inset-0 rounded-[32px] bg-gradient-to-tr from-pink-500/35 via-rose-500/15 to-transparent p-px shadow-2xl shadow-pink-950/20">
                        <div class="w-full h-full rounded-[31px] bg-neutral-950/80 backdrop-blur-2xl"></div>
                    </div>

                    <!-- Lớp gradient mesh bên trong tấm slab -->
                    <div class="absolute inset-0 rounded-[32px] overflow-hidden pointer-events-none">
                        <div class="absolute inset-0 bg-gradient-to-br from-pink-600/20 via-pink-900/10 to-transparent"></div>
                        <div class="absolute -top-12 -right-12 w-48 h-48 rounded-full bg-pink-500/20 blur-3xl"></div>
                        <div class="absolute -bottom-12 -left-12 w-48 h-48 rounded-full bg-rose-600/20 blur-3xl"></div>

                        <!-- Họa tiết chấm lưới mờ tạo chiều sâu -->
                        <div class="absolute inset-0 opacity-[0.06]" 
                             style="background-image: radial-gradient(rgba(244, 114, 182, 0.9) 1px, transparent 1px); background-size: 24px 24px;"></div>
                    </div>

                    <!-- Logo dính liền vào tấm nền slab, vị trí tinh gọn ngay sát phía trên form card -->
                    <div class="absolute top-2 sm:top-2.5 inset-x-0 flex items-center justify-center pointer-events-auto z-10">
                        <a href="{{ route('home') }}" class="inline-block transition-opacity hover:opacity-90 cursor-pointer" title="Về trang chủ">
                            <x-application-logo :dark="true" />
                        </a>
                    </div>
                </div>

                <!-- Form đăng nhập / đăng ký: Độc lập phía trước, cố định 1 chỗ không nghiêng -->
                <div class="relative z-10 w-full mt-10 sm:mt-11">
                    <div class="bg-neutral-900/85 backdrop-blur-xl border border-neutral-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/80">
                        {{ $slot }}
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 py-6 text-center text-xs text-neutral-600">
            <span>&copy; {{ date('Y') }} <strong class="text-neutral-500 font-medium">Ctrl C+V</strong></span>
        </footer>

        <script>
            function tiltController() {
                return {
                    x: 0,
                    y: 0,
                    onMouseMove(e) {
                        const w = window.innerWidth;
                        const h = window.innerHeight;
                        // Tọa độ chuẩn hóa từ -1 đến 1 (tâm màn hình là 0)
                        this.x = (e.clientX - w / 2) / (w / 2);
                        this.y = (e.clientY - h / 2) / (h / 2);
                    },
                    onMouseLeave() {
                        this.x = 0;
                        this.y = 0;
                    },
                    get transformStyle() {
                        const maxDeg = 12;
                        const maxTranslate = 18;
                        const rotX = (-this.y * maxDeg).toFixed(2);
                        const rotY = (this.x * maxDeg).toFixed(2);
                        const transX = (this.x * maxTranslate).toFixed(2);
                        const transY = (this.y * maxTranslate).toFixed(2);
                        return `transform: perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg) translate3d(${transX}px, ${transY}px, -15px);`;
                    },
                    get glowStyle() {
                        const transX = (this.x * 30).toFixed(2);
                        const transY = (this.y * 30).toFixed(2);
                        return `transform: translate3d(${transX}px, ${transY}px, 0);`;
                    }
                };
            }
        </script>
    </body>
</html>
