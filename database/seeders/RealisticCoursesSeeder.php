<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\MinigameQuestion;
use App\Models\MinigameQuiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealisticCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tạo danh sách giảng viên uy tín với chuyên môn đa dạng
        $teachersData = [
            [
                'name' => 'ThS. Nguyễn Văn Hải',
                'email' => 'hai.nguyen@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Trần Đức Nam',
                'email' => 'nam.tran@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Lê Hoàng Yến',
                'email' => 'yen.le@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Phạm Quang Huy',
                'email' => 'huy.pham@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Đỗ Minh Trí',
                'email' => 'tri.do@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Vũ Minh Quân',
                'email' => 'quan.vu@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Hoàng Thu Trang',
                'email' => 'trang.hoang@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&fit=crop&q=80',
            ],
            [
                'name' => 'Nguyễn Thanh Tùng',
                'email' => 'tung.nguyen@lms.edu.vn',
                'avatar' => 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=200&fit=crop&q=80',
            ],
        ];

        $teachers = [];
        foreach ($teachersData as $tData) {
            $teachers[$tData['name']] = User::firstOrCreate(
                ['email' => $tData['email']],
                [
                    'name' => $tData['name'],
                    'password' => bcrypt('password'),
                    'role' => 'teacher',
                    'avatar' => $tData['avatar'],
                ]
            );
        }

        // Lấy thêm các giảng viên sẵn có trong hệ thống (mrbit, mrpip...)
        $existingTeachers = User::where('role', 'teacher')->get();
        foreach ($existingTeachers as $et) {
            if (! isset($teachers[$et->name])) {
                $teachers[$et->name] = $et;
            }
        }

        $getTeacherId = function (string $preferredName) use ($teachers) {
            if (isset($teachers[$preferredName])) {
                return $teachers[$preferredName]->id;
            }

            return collect($teachers)->random()->id;
        };

        // Danh sách học viên mẫu để tạo đánh giá & lượt đăng ký thực tế
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $students[] = User::firstOrCreate(
                ['email' => "student_review_{$i}@lms.edu.vn"],
                [
                    'name' => "Học viên Nguyễn Văn {$i}",
                    'password' => bcrypt('password'),
                    'role' => 'student',
                ]
            );
        }

        // 2. Danh sách 30 khóa học thực tế, đa dạng chủ đề và giá tiền
        $courses = [
            // --- NHÓM 1: FRONTEND & MODERN WEB ---
            [
                'title' => 'React 19 & Next.js 15: Xây Dựng Nền Tảng Thương Mại Điện Tử',
                'teacher' => 'Trần Đức Nam',
                'price' => 990000,
                'thumbnail' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học thực chiến xây dựng ứng dụng E-commerce fullstack với React 19 Server Components, Next.js 15 App Router, thanh toán trực tuyến Stripe & VNPAY, tối ưu SEO và Server Actions.',
                'lessons' => [
                    ['title' => 'Tổng quan kiến trúc Next.js 15 App Router & Server Components', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=wm5gMKuwSYk'],
                    ['title' => 'Server Actions & Xử lý biểu mẫu không cần API Route trung gian', 'type' => 'text', 'content' => "Trong Next.js 15, Server Actions cho phép bạn gọi trực tiếp hàm trên server từ form phía client mà không cần viết REST API trung gian.\n\n```tsx\n// app/actions/cart.ts\n'use server';\n\nexport async function addToCart(productId: string) {\n  await db.cart.create({ data: { productId } });\n  revalidatePath('/cart');\n}\n```\n\nĐiều này giúp code tinh gọn, bảo mật cao và giảm thiểu đáng kể boilerplate code."],
                    ['title' => 'Tích hợp Giỏ hàng & Thanh toán tự động VNPAY / Momo', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=k3Vfj-e1Ma4'],
                    ['title' => 'Tối ưu Core Web Vitals & Caching chiến lược ISR (Incremental Static Regeneration)', 'type' => 'text', 'content' => 'Tối ưu điểm số Google PageSpeed đạt 95+ điểm với chiến lược Caching cấp độ CDN, nén ảnh tự động bằng next/image và ISR tự động build lại trang sản phẩm theo thời gian thực.'],
                ],
            ],
            [
                'title' => 'Vue 3 & Nuxt 3: Kiến Trúc Ứng Dụng Doanh Nghiệp Toàn Diện',
                'teacher' => 'Nguyễn Thanh Tùng',
                'price' => 699000,
                'thumbnail' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
                'description' => 'Học Vue 3 Composition API, Nuxt 3 SSR, quản lý trạng thái tập trung với Pinia và xây dựng Dashboard quản trị đa chi nhánh phục vụ hàng chục ngàn người dùng.',
                'lessons' => [
                    ['title' => 'Kiến trúc Composition API và Script Setup trong Vue 3', 'type' => 'text', 'content' => 'Composition API đem lại khả năng tái sử dụng logic (Composables) cực kỳ mạch lạc tương tự React Hooks nhưng không gặp lỗi re-render thừa.'],
                    ['title' => 'Khởi tạo dự án Nuxt 3 với Nitro Engine & File-based Routing', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=dCxSsr5xuL8'],
                    ['title' => 'Quản lý trạng thái phân tán với Pinia Store & Plugin lưu trữ', 'type' => 'text', 'content' => 'Sử dụng Pinia thay thế hoàn toàn Vuex cổ điển, hỗ trợ TypeScript tự động hoàn thành mã nguồn cực kỳ chính xác.'],
                    ['title' => 'Xây dựng Bảng điều khiển phân quyền (Role-based Dashboard) chuyên nghiệp', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=GBdw3b_fB_8'],
                ],
            ],
            [
                'title' => 'TypeScript Thực Chiến: Design Patterns & Clean Code',
                'teacher' => 'Nguyễn Thanh Tùng',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1516116211227-bbc03a7d4576?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học miễn phí giúp bạn làm chủ Type System, Generics nâng cao, Utility Types và các mẫu thiết kế hướng đối tượng (GoF Design Patterns) trong JavaScript/TypeScript hiện đại.',
                'lessons' => [
                    ['title' => 'Tư duy Type-Driven Development và lợi ích của Static Typing', 'type' => 'text', 'content' => 'Tại sao các tập đoàn công nghệ hàng đầu chuyển toàn bộ codebase JS sang TypeScript? Cách setup tsconfig.json chuẩn Strict Mode.'],
                    ['title' => 'Generics nâng cao, Conditional Types & Mapped Types', 'type' => 'text', 'content' => 'Tự tay viết các helper types phức tạp: `DeepReadonly<T>`, `NonNullable<T>`, `PickByValue<T, ValueType>`.'],
                    ['title' => 'Áp dụng Singleton, Factory và Observer Pattern trong dự án thực tế', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=tv-_1er1mWI'],
                ],
            ],
            [
                'title' => 'Tailwind CSS 4 & Framer Motion: Thiết Kế Giao Diện Tương Tác Đỉnh Cao',
                'teacher' => 'Lê Hoàng Yến',
                'price' => 299000,
                'thumbnail' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
                'description' => 'Học cách biến các bản thiết kế tĩnh thành giao diện web sống động với Tailwind CSS thế hệ mới, hoạt ảnh vi mô (Micro-interactions) và hiệu ứng chuyển cảnh mượt mà 60fps.',
                'lessons' => [
                    ['title' => 'Khám phá kiến trúc Engine siêu tốc trong Tailwind CSS v4', 'type' => 'text', 'content' => 'Cấu hình CSS-first không cần file tailwind.config.js cồng kềnh, tối ưu thời gian build dưới 100ms.'],
                    ['title' => 'Hoạt ảnh Scroll Trigger và Parallax với Framer Motion', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=znbCa4Rr054'],
                    ['title' => 'Thiết kế Dark Mode tự động & Accessible Glassmorphism UI', 'type' => 'text', 'content' => 'Xây dựng bảng phối màu tương phản đạt chuẩn WCAG AA cho mọi thiết bị di động và máy tính bàn.'],
                ],
            ],
            [
                'title' => 'Lập Trình Frontend Nâng Cao: Tối Ưu Hiệu Năng Web & Web Vitals',
                'teacher' => 'Nguyễn Thanh Tùng',
                'price' => 499000,
                'thumbnail' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&auto=format&fit=crop&q=80',
                'description' => 'Chuyên sâu về tối ưu LCP, INP, CLS, phân tích Bundle Size bằng Webpack/Vite, chiến lược Lazy Loading, Service Workers và kiến trúc Offline-first cho trang web.',
                'lessons' => [
                    ['title' => 'Hiểu sâu về Critical Rendering Path của trình duyệt', 'type' => 'text', 'content' => 'DOM, CSSOM, Render Tree, Layout, Paint và Composite. Tránh tình trạng Layout Thrashing và Reflow nặng.'],
                    ['title' => 'Kỹ thuật Code Splitting & Tối ưu hóa Bundle Size', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=0fONene3OIA'],
                    ['title' => 'Chinh phục chỉ số INP (Interaction to Next Paint) thay thế FID', 'type' => 'text', 'content' => 'Sử dụng requestIdleCallback và Web Workers để giải phóng Main Thread khi xử lý tác vụ nặng.'],
                ],
            ],

            // --- NHÓM 2: BACKEND & HỆ THỐNG PHÂN TÁN ---
            [
                'title' => 'Laravel 11 & Inertia.js: Xây Dựng Ứng Dụng SaaS Đa Doanh Nghiệp',
                'teacher' => 'mrbit',
                'price' => 1290000,
                'thumbnail' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=800&auto=format&fit=crop&q=80',
                'description' => 'Xây dựng giải pháp SaaS Multi-tenancy hoàn chỉnh: Phân tách dữ liệu nhiều công ty, tích hợp gói đăng ký định kỳ (Subscription), hóa đơn tự động và realtime với Laravel Reverb.',
                'lessons' => [
                    ['title' => 'Kiến trúc Multi-tenant: Phân tách theo Database vs Single-DB Tenant Column', 'type' => 'text', 'content' => 'Phân tích ưu nhược điểm giữa Multi-database Tenancy và Scoped Tenancy trong Laravel 11.'],
                    ['title' => 'Kết nối Vue.js không cần viết API với Inertia.js v2', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=0h3cW_LfZp4'],
                    ['title' => 'Quản lý hàng đợi khổng lồ với Redis Queue & Laravel Horizon', 'type' => 'text', 'content' => 'Xử lý hàng triệu email thông báo và background jobs mỗi ngày mà không làm chậm server chính.'],
                    ['title' => 'Webhooks, Báo cáo Doanh thu & Tự động gia hạn gói cước', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=VqCgcpAypFQ'],
                ],
            ],
            [
                'title' => 'Node.js & NestJS: Kiến Trúc Microservices & Event-Driven với RabbitMQ',
                'teacher' => 'Trần Đức Nam',
                'price' => 1190000,
                'thumbnail' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=800&auto=format&fit=crop&q=80',
                'description' => 'Học tư duy kiến trúc hướng dịch vụ nhỏ (Microservices) chuẩn doanh nghiệp với NestJS Framework, Message Broker RabbitMQ, CQRS Pattern và gRPC giao tiếp siêu tốc giữa các service.',
                'lessons' => [
                    ['title' => 'Tổng quan NestJS: Dependency Injection, Module, Controllers & Services', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=GHTA143_b-s'],
                    ['title' => 'Thiết lập Message Broker RabbitMQ cho kiến trúc Event-Driven', 'type' => 'text', 'content' => 'Cơ chế Exchange, Queue, Routing Key và Dead Letter Exchange (DLX) để xử lý message thất bại.'],
                    ['title' => 'Giao tiếp liên dịch vụ siêu tốc độ qua giao thức nhị phân gRPC', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=Yw4rkaTc0f8'],
                    ['title' => 'Theo dõi phân tán (Distributed Tracing) với OpenTelemetry & Jaeger', 'type' => 'text', 'content' => 'Truy vết một yêu cầu người dùng đi qua 5 microservices để phát hiện nút thắt cổ chai trong 1 giây.'],
                ],
            ],
            [
                'title' => 'Golang (Go) Thực Chiến: Xây Dựng High-Performance RESTful API',
                'teacher' => 'mrpip',
                'price' => 799000,
                'thumbnail' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=800&auto=format&fit=crop&q=80',
                'description' => 'Làm chủ ngôn ngữ Go của Google: Goroutines, Channels, bộ nhớ Concurrency an toàn và framework Gin/Fiber để xây dựng API xử lý 50.000 requests/giây với lượng RAM cực thấp.',
                'lessons' => [
                    ['title' => 'Cú pháp cơ bản, Struct, Interfaces & Con trỏ (Pointers) trong Go', 'type' => 'text', 'content' => 'Nắm vững cách quản lý bộ nhớ không có class kế thừa, thay vào đó sử dụng Composition và Interface ẩn.'],
                    ['title' => 'Goroutines & Channels: Sức mạnh xử lý song song siêu luồng', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=yyUHQIec83I'],
                    ['title' => 'Xây dựng RESTful API với Gin Web Framework & GORM kết nối PostgreSQL', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=bj77B59nkTk'],
                    ['title' => 'Unit Test, Benchmark kiểm thử tốc độ & Profiling với pprof', 'type' => 'text', 'content' => 'Cách tối ưu CPU và RAM của ứng dụng Go trước khi đóng gói thành Docker image chỉ nặng 15MB.'],
                ],
            ],
            [
                'title' => 'Spring Boot 3 & Apache Kafka: Hệ Thống Xử Lý Giao Dịch Tài Chính',
                'teacher' => 'Trần Đức Nam',
                'price' => 1590000,
                'thumbnail' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học chuyên sâu dành cho lập trình viên Backend Java muốn làm việc tại các ngân hàng, Fintech và tập đoàn công nghệ lớn: Spring Boot 3, Java 21 Virtual Threads và Kafka Streaming.',
                'lessons' => [
                    ['title' => 'Tận dụng Java 21 Virtual Threads trong Spring Boot 3.2+', 'type' => 'text', 'content' => 'Hiểu về Project Loom, cơ chế thread nhẹ giải quyết bài toán hàng trăm nghìn kết nối đồng thời mà không nghẽn OS thread.'],
                    ['title' => 'Thiết lập cụm Apache Kafka: Producer, Consumer, Partitions & Offsets', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=Ch5VhJzaoaI'],
                    ['title' => 'Xử lý tính nhất quán dữ liệu phân tán với Saga Pattern & Outbox Pattern', 'type' => 'text', 'content' => 'Giải quyết bài toán trừ tiền ví và cộng tiền tài khoản khi các microservices nằm trên database riêng biệt.'],
                    ['title' => 'Bảo mật chuẩn ngân hàng với Spring Security 6 & OAuth2 Resource Server', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=da4G_GZ2Xm0'],
                ],
            ],
            [
                'title' => 'ASP.NET Core 8 & Clean Architecture cho Doanh Nghiệp',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 899000,
                'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80',
                'description' => 'Áp dụng Clean Architecture, CQRS với MediatR, Entity Framework Core 8 và Minimal APIs trong C# 12 để tạo nên hệ thống phần mềm doanh nghiệp dễ bảo trì, dễ mở rộng và kiểm thử.',
                'lessons' => [
                    ['title' => '4 tầng kiến trúc Clean Architecture: Domain, Application, Infrastructure, WebUI', 'type' => 'text', 'content' => 'Nguyên tắc Dependency Inversion: Tầng trung tâm Domain không phụ thuộc vào bất kỳ thư viện ngoài nào.'],
                    ['title' => 'Tách biệt đọc và ghi (CQRS) với thư viện MediatR trong .NET 8', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=3bxAmyoxL2g'],
                    ['title' => 'Entity Framework Core 8: Migration, Tối ưu hóa truy vấn LINQ & Compiled Queries', 'type' => 'text', 'content' => 'Cách giảm thiểu lỗi N+1 query và tối ưu thời gian phản hồi API xuống dưới 20ms.'],
                ],
            ],
            [
                'title' => 'Python FastAPI: Xây Dựng API Xử Lý Dữ Liệu Tốc Độ Cao',
                'teacher' => 'Phạm Quang Huy',
                'price' => 399000,
                'thumbnail' => 'https://images.unsplash.com/photo-1526379095098-d400fd0bf935?w=800&auto=format&fit=crop&q=80',
                'description' => 'FastAPI là framework Python hiện đại số 1 cho các ứng dụng AI và Machine Learning. Học Async/Await, xác thực kiểu dữ liệu tự động với Pydantic và sinh tài liệu Swagger UI.',
                'lessons' => [
                    ['title' => 'Tại sao FastAPI nhanh ngang ngửa Go và Node.js? Kiến trúc ASGI & Starlette', 'type' => 'text', 'content' => 'So sánh sự khác biệt cơ bản giữa WSGI truyền thống (Django/Flask) và ASGI bất đồng bộ.'],
                    ['title' => 'Validation dữ liệu đầu vào siêu chuẩn với Pydantic v2', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=kCgGjBG1m4A'],
                    ['title' => 'Đóng gói Model Machine Learning thành REST API sẵn sàng phục vụ Production', 'type' => 'text', 'content' => 'Tải model Scikit-learn / PyTorch vào bộ nhớ và xử lý hàng trăm request inference mỗi giây.'],
                ],
            ],
            [
                'title' => 'Xây Dựng Kiến Trúc Web API An Toàn & Chuẩn RESTful',
                'teacher' => 'mrbit',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học nền tảng miễn phí giúp bạn hiểu thấu đáo về HTTP Status Codes, RESTful Constraints, xác thực JWT, bảo vệ API chống Rate Limit và Brute Force attacks.',
                'lessons' => [
                    ['title' => 'Nguyên lý thiết kế Endpoint chuẩn REST: Danh từ, Số nhiều và HTTP Methods', 'type' => 'text', 'content' => 'Sử dụng đúng GET, POST, PUT, PATCH, DELETE và các mã trạng thái 200, 201, 204, 400, 401, 403, 404, 422, 500.'],
                    ['title' => 'Cơ chế xác thực Access Token & Refresh Token an toàn', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=7Q17ubqL29k'],
                    ['title' => 'Bảo vệ API với Throttling / Rate Limiting và API Key', 'type' => 'text', 'content' => 'Cấu hình thuật toán Token Bucket và Leaky Bucket để ngăn chặn tấn công DoS.'],
                ],
            ],

            // --- NHÓM 3: LẬP TRÌNH DI ĐỘNG (MOBILE APPS) ---
            [
                'title' => 'Flutter & Firebase: Xây Dựng Ứng Dụng Giao Đồ Ăn Đa Nền Tảng',
                'teacher' => 'Đỗ Minh Trí',
                'price' => 850000,
                'thumbnail' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&auto=format&fit=crop&q=80',
                'description' => 'Lập trình ứng dụng Food Delivery chạy mượt mà trên cả iOS và Android bằng Flutter 3, Bloc Pattern, bản đồ Google Maps theo dõi tài xế thời gian thực và thông báo đẩy Push Notifications.',
                'lessons' => [
                    ['title' => 'Thiết lập kiến trúc dự án Flutter với BLoC State Management', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=laFnGSt413k'],
                    ['title' => 'Tích hợp Google Maps API vẽ lộ trình và Marker tài xế di chuyển realtime', 'type' => 'text', 'content' => 'Sử dụng WebSocket hoặc Firebase Firestore snapshot để cập nhật tọa độ GPS mượt mà 60fps.'],
                    ['title' => 'Cài đặt Firebase Cloud Messaging (FCM) gửi thông báo đơn hàng tức thì', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=k0d_6S_e0Q8'],
                ],
            ],
            [
                'title' => 'React Native & Expo: Lập Trình Ứng Dụng Mạng Xã Hội iOS & Android',
                'teacher' => 'Đỗ Minh Trí',
                'price' => 750000,
                'thumbnail' => 'https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?w=800&auto=format&fit=crop&q=80',
                'description' => 'Học phát triển app di động bằng JavaScript/TypeScript với Expo SDK mới nhất, cuộn video ngắn dạng TikTok, camera chụp ảnh, chat nhóm Socket.io và xuất bản lên App Store & Google Play.',
                'lessons' => [
                    ['title' => 'Khởi tạo ứng dụng với Expo Router và cấu trúc thư mục tối ưu', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=bPpLwXj5t0k'],
                    ['title' => 'Tối ưu FlatList cuộn mượt mà hàng nghìn bài viết kèm ảnh và video', 'type' => 'text', 'content' => 'Kỹ thuật ảo hóa danh sách (Windowing), nén ảnh thumbnail và hủy bộ nhớ đệm video khi cuộn qua.'],
                    ['title' => 'Xây dựng tính năng nhắn tin thời gian thực với Socket.IO Client', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=djvHj4WwL0Y'],
                ],
            ],
            [
                'title' => 'Lập Trình iOS Nền Tảng với Swift & SwiftUI',
                'teacher' => 'Đỗ Minh Trí',
                'price' => 1350000,
                'thumbnail' => 'https://images.unsplash.com/photo-1563206767-5b18f218e8de?w=800&auto=format&fit=crop&q=80',
                'description' => 'Dành cho lập trình viên muốn đi sâu vào hệ sinh thái Apple Native: Ngôn ngữ Swift hiện đại, SwiftUI Declarative, Swift Data lưu trữ cục bộ, WidgetKit và tích hợp Apple Pay.',
                'lessons' => [
                    ['title' => 'Ngôn ngữ Swift cơ bản: Optionals, Closures, Structs vs Classes', 'type' => 'text', 'content' => 'Nắm chắc triết lý Value Type trong Swift để tránh rò rỉ bộ nhớ (Memory Leaks / Retain Cycles).'],
                    ['title' => 'Xây dựng giao diện Declarative thời thượng với SwiftUI', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=b1oC7sLIgpI'],
                    ['title' => 'Lưu trữ dữ liệu offline hiện đại với SwiftData & CloudKit Sync', 'type' => 'text', 'content' => 'Đồng bộ dữ liệu học tập giữa iPhone, iPad và Mac của người dùng thông qua tài khoản iCloud.'],
                ],
            ],

            // --- NHÓM 4: DATABASE, DATA SCIENCE & TRÍ TUỆ NHÂN TẠO (AI) ---
            [
                'title' => 'Tối Ưu Hóa PostgreSQL & MySQL Chuyên Sâu: Indexing & Query Tuning',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 650000,
                'thumbnail' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&auto=format&fit=crop&q=80',
                'description' => 'Giải quyết tận gốc các câu truy vấn chậm chạp làm treo database: Đọc hiểu EXPLAIN ANALYZE, thiết kế B-Tree, GIN, GiST Index, Partitioning bảng dữ liệu 50 triệu dòng và connection pooling.',
                'lessons' => [
                    ['title' => 'Đọc hiểu kế hoạch thực thi EXPLAIN và EXPLAIN ANALYZE', 'type' => 'text', 'content' => 'Phân biệt Seq Scan, Index Scan, Index Only Scan và Bitmap Heap Scan để biết tại sao query lại chậm.'],
                    ['title' => 'Kỹ thuật đánh Composite Index theo nguyên tắc Left-Most Prefix', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=HubezKbFL7E'],
                    ['title' => 'Partitioning bảng dữ liệu lớn (Table Partitioning by Range/List) trong PostgreSQL', 'type' => 'text', 'content' => 'Chia nhỏ bảng lịch sử đơn hàng 100 triệu dòng theo từng tháng để tăng tốc truy vấn gấp 20 lần.'],
                ],
            ],
            [
                'title' => 'NoSQL Thực Chiến với MongoDB, Redis Caching & ElasticSearch',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 590000,
                'thumbnail' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&auto=format&fit=crop&q=80',
                'description' => 'Nắm vững kiến trúc Polyglot Persistence: Khi nào dùng NoSQL thay vì SQL? Thiết kế Document trong MongoDB, triển khai bộ đệm In-Memory Redis và tìm kiếm Full-Text siêu nhanh với ElasticSearch.',
                'lessons' => [
                    ['title' => 'Mô hình hóa dữ liệu Document NoSQL: Embed vs Reference trong MongoDB', 'type' => 'text', 'content' => 'Cách tư duy theo access pattern thay vì chuẩn hóa 3NF như cơ sở dữ liệu quan hệ.'],
                    ['title' => 'Redis Caching nâng cao: String, Hash, Set, Sorted Set và cơ chế TTL', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=jgpVdJB2sKQ'],
                    ['title' => 'Tích hợp ElasticSearch: Phân tích cú pháp tiếng Việt và tìm kiếm mờ Fuzzy Search', 'type' => 'text', 'content' => 'Tự động gợi ý từ khóa (Autocomplete) và tìm kiếm sản phẩm chính xác kể cả khi người dùng gõ sai chính tả.'],
                ],
            ],
            [
                'title' => 'Phân Tích Dữ Liệu (Data Analysis) với Python, Pandas & Power BI',
                'teacher' => 'Phạm Quang Huy',
                'price' => 490000,
                'thumbnail' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=800&auto=format&fit=crop&q=80',
                'description' => 'Biến dữ liệu thô thành thông tin giá trị hỗ trợ quyết định kinh doanh: Thu thập dữ liệu (Web Scraping), làm sạch dữ liệu với Pandas, trực quan hóa biểu đồ và dựng Dashboard Power BI chuyên nghiệp.',
                'lessons' => [
                    ['title' => 'Thu thập dữ liệu web tự động với BeautifulSoup và Requests', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=XVv6mJpFOb0'],
                    ['title' => 'Kỹ thuật làm sạch và xử lý dữ liệu thiếu (Missing Data) với Pandas & NumPy', 'type' => 'text', 'content' => 'Xử lý các dòng null, loại bỏ ngoại lai (Outliers), chuẩn hóa kiểu ngày tháng và pivot table.'],
                    ['title' => 'Xây dựng Báo cáo Dashboard chỉ số KPI tự động trên Power BI', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=TmhQCQr_8dQ'],
                ],
            ],
            [
                'title' => 'Kỹ Thuật Prompt Engineering & Tích Hợp LLM (OpenAI / Claude API)',
                'teacher' => 'Phạm Quang Huy',
                'price' => 199000,
                'thumbnail' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học thực chiến giúp bạn khai thác tối đa sức mạnh của các mô hình ngôn ngữ lớn: Viết System Prompt chuẩn mực, kỹ thuật Few-shot, Chain-of-Thought, Function Calling và Structured Output JSON.',
                'lessons' => [
                    ['title' => 'Nguyên tắc vàng của Prompt Engineering: Bối cảnh, Vai trò, Ràng buộc & Định dạng', 'type' => 'text', 'content' => 'Cách ngăn chặn AI trả lời lan man, ảo giác (hallucination) và ép trả về cấu trúc JSON nghiêm ngặt.'],
                    ['title' => 'Kỹ thuật Chain-of-Thought (CoT) và ReAct: Dạy AI cách tư duy từng bước', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=jC4v5AS4RIM'],
                    ['title' => 'Tích hợp Function Calling / Tool Use gọi API ngoài tự động', 'type' => 'text', 'content' => 'Cho phép AI tự động tra cứu cơ sở dữ liệu thời gian thực hoặc gọi webhook gửi email cho học viên.'],
                ],
            ],
            [
                'title' => 'Ứng Dụng Trí Tuệ Nhân Tạo RAG với LangChain & Vector Database',
                'teacher' => 'Phạm Quang Huy',
                'price' => 1490000,
                'thumbnail' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800&auto=format&fit=crop&q=80',
                'description' => 'Xây dựng trợ lý AI thông minh có khả năng đọc hiểu tài liệu nội bộ của công ty (PDF, Word, Confluence): Embedding models, Vector Database (Qdrant/ChromaDB), Semantic Search và Hybrid Search.',
                'lessons' => [
                    ['title' => 'Nguyên lý hoạt động của kiến trúc RAG (Retrieval-Augmented Generation)', 'type' => 'text', 'content' => 'Khắc phục nhược điểm giới hạn token và dữ liệu lỗi thời của mô hình AI nguyên bản.'],
                    ['title' => 'Chiến lược cắt nhỏ văn bản (Chunking Strategies) & Vector Embeddings', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=tcqEUSNCn8I'],
                    ['title' => 'Cài đặt Vector Database và tìm kiếm tương đồng (Cosine Similarity)', 'type' => 'text', 'content' => 'Lưu trữ hàng triệu vector đặc trưng và truy vấn tìm đoạn văn bản liên quan trong 5 mili giây.'],
                ],
            ],
            [
                'title' => 'Học Máy (Machine Learning) Thực Hành: Từ Nền Tảng Đến Triển Khai Model',
                'teacher' => 'Phạm Quang Huy',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học miễn phí trang bị toàn bộ toán học và giải thuật Machine Learning cốt lõi: Hồi quy tuyến tính, Phân loại Logistic, Cây quyết định (Decision Trees), Random Forest và Đánh giá Model.',
                'lessons' => [
                    ['title' => 'Hồi quy tuyến tính (Linear Regression) & Hàm mất mát (Loss Function)', 'type' => 'text', 'content' => 'Thuật toán Gradient Descent tìm điểm tối ưu và tránh hiện tượng Overfitting.'],
                    ['title' => 'Phân loại dữ liệu với Logistic Regression & Ma trận nhầm lẫn (Confusion Matrix)', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=yIYKR4sgzI8'],
                    ['title' => 'Đánh giá mô hình chuẩn khoa học: Precision, Recall, F1-Score & ROC-AUC', 'type' => 'text', 'content' => 'Tại sao độ chính xác (Accuracy) 99% lại có thể là một mô hình dự báo gian lận hoàn toàn vô dụng?'],
                ],
            ],

            // --- NHÓM 5: DEVOPS, CLOUD & AN NINH MẠNG ---
            [
                'title' => 'DevOps Toàn Diện: Docker, Kubernetes & Triển Khai Production',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 1890000,
                'thumbnail' => 'https://images.unsplash.com/photo-1607799279861-4dd421887fb3?w=800&auto=format&fit=crop&q=80',
                'description' => 'Lộ trình từ Developer thành kỹ sư DevOps thực thụ: Viết Dockerfile tối ưu đa tầng (Multi-stage build), quản lý Pods, Deployments, Services, Ingress trong cụm Kubernetes (K8s) và tự động scale ứng dụng.',
                'lessons' => [
                    ['title' => 'Đóng gói ứng dụng với Docker Multi-Stage Build siêu nhỏ gọn', 'type' => 'text', 'content' => 'Cách giảm kích thước image từ 1.2GB xuống còn dưới 80MB và quét lỗ hổng bảo mật với Trivy.'],
                    ['title' => 'Kiến trúc cụm Kubernetes: Control Plane, Worker Nodes & Kubelet', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=X48VuDVv0do'],
                    ['title' => 'Cấu hình Tự động co giãn theo tải (Horizontal Pod Autoscaler - HPA)', 'type' => 'text', 'content' => 'Tự động mở rộng từ 2 pods lên 50 pods khi lượng truy cập đột biến và thu hẹp khi đêm về.'],
                    ['title' => 'Triển khai Ingress NGINX & Chứng chỉ SSL Let\'s Encrypt tự động cấp phát', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=7XDeI5fyj3w'],
                ],
            ],
            [
                'title' => 'Tự Động Hóa CI/CD Pipeline với GitHub Actions & Docker',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 350000,
                'thumbnail' => 'https://images.unsplash.com/photo-1618401471353-b98aedd04e11?w=800&auto=format&fit=crop&q=80',
                'description' => 'Tạm biệt việc deploy thủ công bằng FTP hay kéo code bằng SSH: Thiết lập đường ống CI/CD tự động chạy kiểm thử, build image, đẩy lên Docker Registry và deploy lên máy chủ chỉ sau 1 lệnh Git push.',
                'lessons' => [
                    ['title' => 'Cú pháp Workflow YAML trong GitHub Actions: Triggers, Jobs & Steps', 'type' => 'text', 'content' => 'Tự động kích hoạt khi có Pull Request được merge vào nhánh main.'],
                    ['title' => 'Tự động chạy Unit Test, Linting và Static Code Analysis với SonarQube', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=mFFXuXjVgkU'],
                    ['title' => 'Zero-Downtime Deployment lên VPS qua SSH và Docker Compose', 'type' => 'text', 'content' => 'Cách cập nhật phiên bản mới mà người dùng đang truy cập web hoàn toàn không bị ngắt quãng 1 giây nào.'],
                ],
            ],
            [
                'title' => 'Thực Hành Luyện Thi AWS Certified Solutions Architect Associate',
                'teacher' => 'ThS. Nguyễn Văn Hải',
                'price' => 2490000,
                'thumbnail' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học chứng chỉ quốc tế số 1 ngành Cloud Computing: Thiết kế hạ tầng chịu tải cao (High Availability), VPC mạng riêng ảo, EC2, S3, RDS Multi-AZ, Load Balancer, Route 53 và Serverless Lambda.',
                'lessons' => [
                    ['title' => 'Kiến trúc Mạng riêng ảo AWS VPC: Subnets, Route Tables, Internet Gateway & NAT', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=g2JOHLHh4rI'],
                    ['title' => 'Lưu trữ đối tượng S3: Lifecycle Policies, Versioning và Phân quyền IAM Bucket Policy', 'type' => 'text', 'content' => 'Cách tiết kiệm 70% chi phí lưu trữ ảnh và video bằng cách chuyển tự động sang S3 Glacier.'],
                    ['title' => 'Cơ sở dữ liệu đám mây RDS Aurora Serverless & Tự động sao lưu Failover', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=a9__D53WsUs'],
                    ['title' => 'Giải đề thi mẫu thực tế với 200 câu hỏi sát đề thi thật 2026', 'type' => 'text', 'content' => 'Phân tích mẹo bắt từ khóa trong câu hỏi tình huống để chọn đúng đáp án ngay trong 30 giây.'],
                ],
            ],
            [
                'title' => 'An Toàn Thông Tin & Phòng Chống Tấn Công Web (OWASP Top 10)',
                'teacher' => 'Vũ Minh Quân',
                'price' => 890000,
                'thumbnail' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800&auto=format&fit=crop&q=80',
                'description' => 'Học cách hacker tấn công để bảo vệ trang web của bạn: Thực hành khai thác và vá lỗi SQL Injection, XSS, CSRF, IDOR, Server-Side Request Forgery (SSRF) và Broken Authentication.',
                'lessons' => [
                    ['title' => 'Vén màn top 10 lỗ hổng nguy hiểm nhất theo tổ chức bảo mật OWASP', 'type' => 'text', 'content' => 'Tại sao các hệ thống lớn vẫn bị rò rỉ dữ liệu qua những lỗ hổng kiểm soát truy cập phân quyền IDOR.'],
                    ['title' => 'Mô phỏng tấn công SQL Injection và kỹ thuật phòng thủ với Prepared Statements', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=ciNHn38EyRc'],
                    ['title' => 'Hiểu về Cross-Site Scripting (XSS) và thiết lập Content Security Policy (CSP)', 'type' => 'text', 'content' => 'Cách kẻ gian đánh cắp Session Cookie của người dùng qua mã JavaScript độc hại và cách phòng ngừa triệt để.'],
                ],
            ],
            [
                'title' => 'Kỹ Thuật Kiểm Thử Xâm Nhập (Penetration Testing) Thực Hành',
                'teacher' => 'Vũ Minh Quân',
                'price' => 1200000,
                'thumbnail' => 'https://images.unsplash.com/photo-1563986768494-4dee2763ff3f?w=800&auto=format&fit=crop&q=80',
                'description' => 'Trang bị kỹ năng của một Chuyên viên Kiểm thử Bảo mật (White-hat Hacker): Sử dụng Kali Linux, Nmap quét cổng mạng, Burp Suite bắt chặn gói tin HTTP và viết báo cáo đánh giá an toàn thông tin.',
                'lessons' => [
                    ['title' => 'Quy trình chuẩn mực của một cuộc Penetration Testing chuyên nghiệp', 'type' => 'text', 'content' => 'Thu thập thông tin mục tiêu (Reconnaissance), Quét dò tìm điểm yếu (Scanning), Khai thác (Exploitation) và Báo cáo.'],
                    ['title' => 'Làm chủ công cụ Burp Suite bắt chặn và sửa đổi gói tin HTTP Proxy', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=Fj_9b8kIe2g'],
                    ['title' => 'Thực hành khai thác trên môi trường Lab mô phỏng có kiểm soát (TryHackMe / HTB)', 'type' => 'text', 'content' => 'Giải quyết các thử thách chiếm quyền root (Privilege Escalation) từ xa.'],
                ],
            ],
            [
                'title' => 'Quản Trị Hệ Điều Hành Linux & Lập Trình Shell Script Cho SysAdmin',
                'teacher' => 'Vũ Minh Quân',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học miễn phí giúp bạn làm chủ dòng lệnh Linux (Ubuntu/Debian/CentOS): Quản lý tiến trình, phân quyền file (chmod, chown), viết kịch bản Bash tự động hóa sao lưu và bảo mật SSH Server.',
                'lessons' => [
                    ['title' => 'Các lệnh Linux thiết yếu: Di chuyển, tìm kiếm file và xử lý văn bản (grep, awk, sed)', 'type' => 'text', 'content' => 'Cách lọc log server dung lượng 10GB tìm IP đang tấn công mạng chỉ bằng 1 dòng lệnh.'],
                    ['title' => 'Cấu hình bảo mật máy chủ SSH với SSH Key & Vô hiệu hóa Root Login', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=SrhskRk6K54'],
                    ['title' => 'Viết kịch bản Bash Shell Script tự động backup Database hàng ngày', 'type' => 'text', 'content' => 'Lên lịch tác vụ định kỳ với Cron Job và gửi thông báo trực tiếp qua Telegram Bot khi có lỗi.'],
                ],
            ],

            // --- NHÓM 6: UI/UX DESIGN, QUẢN LÝ DỰ ÁN & KỸ NĂNG NGHỀ ---
            [
                'title' => 'Thiết Kế UI/UX Chuyên Nghiệp với Figma: Từ Wireframe Đến Design System',
                'teacher' => 'Lê Hoàng Yến',
                'price' => 690000,
                'thumbnail' => 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học thiết kế sản phẩm số thực chiến: Quy trình nghiên cứu người dùng (User Research), thiết kế Wireframe, làm chủ Auto Layout, Variants, Variables và xây dựng Design System đồng bộ với Devs.',
                'lessons' => [
                    ['title' => 'Nền tảng UI/UX: Typography, Hệ thống lưới Grid 8pt và Phối màu khoa học', 'type' => 'text', 'content' => 'Cách chọn cỡ chữ, khoảng cách padding đồng bộ để bản thiết kế trông chuyên nghiệp chuẩn quốc tế.'],
                    ['title' => 'Làm chủ Auto Layout & Variables trong Figma thế hệ mới', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=FTFaQWZBqQ8'],
                    ['title' => 'Xây dựng Design System toàn diện cho doanh nghiệp (Components & Tokens)', 'type' => 'text', 'content' => 'Đồng bộ Design Tokens với lập trình viên Frontend qua Style Dictionary và GitHub.'],
                ],
            ],
            [
                'title' => 'Quản Lý Dự Án Phần Mềm với Agile, Scrum & Jira',
                'teacher' => 'Hoàng Thu Trang',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học miễn phí giúp bạn hiểu cách vận hành của một đội ngũ công nghệ hiện đại: Các vai trò Product Owner, Scrum Master, Developer, tổ chức Sprint Planning, Daily Scrum, Retrospective trên Jira.',
                'lessons' => [
                    ['title' => 'Triết lý Agile Manifesto và 4 giá trị cốt lõi trong phát triển phần mềm', 'type' => 'text', 'content' => 'Tại sao mô hình Thác nước (Waterfall) thất bại trong thời đại biến động và cách Agile giúp sản phẩm thích ứng nhanh.'],
                    ['title' => 'Khung làm việc Scrum: Sprint, Backlog Grooming và Story Points estimation', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=9TycLR0TqFA'],
                    ['title' => 'Thực hành quản trị dự án thực tế trên phần mềm Jira & Confluence', 'type' => 'text', 'content' => 'Tạo Kanban board, theo dõi Burn-down chart và phân bổ khối lượng công việc cho các thành viên trong team.'],
                ],
            ],
            [
                'title' => 'Bí Quyết Phỏng Vấn Thuật Toán & System Design Vào Big Tech',
                'teacher' => 'mrbit',
                'price' => 0, // Miễn phí
                'thumbnail' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&auto=format&fit=crop&q=80',
                'description' => 'Khóa học miễn phí tổng hợp kinh nghiệm phỏng vấn lập trình viên: Kỹ thuật giải LeetCode bằng Two Pointers, Sliding Window, Dynamic Programming và giải quyết bài toán thiết kế hệ thống triệu người dùng.',
                'lessons' => [
                    ['title' => 'Tư duy phân tích độ phức tạp thời gian Big-O và không gian bộ nhớ', 'type' => 'text', 'content' => 'Cách nhận biết khi nào một bài toán có thể tối ưu từ O(N^2) xuống O(N log N) hoặc O(N).'],
                    ['title' => 'Bí kíp thiết kế hệ thống: Bài toán Rút gọn liên kết URL (TinyURL System Design)', 'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=fMZMm_0ZhK4'],
                    ['title' => 'Kỹ năng giao tiếp và tư duy phản biện khi trao đổi với nhà tuyển dụng', 'type' => 'text', 'content' => 'Làm gì khi gặp câu hỏi thuật toán khó mà bạn chưa từng giải qua trong phòng phỏng vấn?'],
                ],
            ],
        ];

        // 3. Tiến hành chèn các khóa học vào Database
        $sampleComments = [
            'Khóa học tuyệt vời, kiến thức thực chiến chứ không lý thuyết suông!',
            'Giảng viên giải thích rất dễ hiểu, code ví dụ rõ ràng và chạy được ngay.',
            'Nội dung rất sát với yêu cầu thực tế khi đi làm tại các công ty công nghệ.',
            'Bài giảng chất lượng cao, rất đáng để đầu tư thời gian học tập.',
            'Cảm ơn giảng viên và đội ngũ hỗ trợ đã nhiệt tình giải đáp thắc mắc!',
            'Các bài tập thực hành rất cuốn hút, làm xong hiểu sâu bản chất vấn đề.',
        ];

        foreach ($courses as $c) {
            $teacherId = $getTeacherId($c['teacher']);
            $baseSlug = Str::slug($c['title']);

            $course = Course::firstOrCreate(
                ['slug' => $baseSlug],
                [
                    'teacher_id' => $teacherId,
                    'title' => $c['title'],
                    'description' => $c['description'],
                    'price' => $c['price'],
                    'status' => 'approved',
                    'thumbnail' => $c['thumbnail'],
                ]
            );

            // Cập nhật thông tin nếu đã tồn tại
            $course->update([
                'teacher_id' => $teacherId,
                'title' => $c['title'],
                'description' => $c['description'],
                'price' => $c['price'],
                'status' => 'approved',
                'thumbnail' => $c['thumbnail'],
            ]);

            // Tạo các bài học cho khóa
            foreach ($c['lessons'] as $idx => $lData) {
                Lesson::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'order_number' => $idx + 1,
                    ],
                    [
                        'title' => $lData['title'],
                        'slug' => Str::slug($lData['title']).'-'.($course->id).'-'.($idx + 1),
                        'content_type' => $lData['type'],
                        'content' => $lData['content'],
                    ]
                );
            }

            // Tạo đăng ký & đánh giá thực tế cho khóa học
            foreach (collect($students)->random(rand(2, 4)) as $student) {
                Enrollment::firstOrCreate([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ]);

                CourseReview::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'rating' => fake()->randomElement([5, 5, 5, 4]),
                        'comment' => fake()->randomElement($sampleComments),
                    ]
                );
            }
        }

        // 4. Khởi tạo 4 bộ đề Minigame Gold Quest công khai chất lượng cao
        $defaultHostUser = User::where('role', 'teacher')->first() ?? User::first();

        $minigameQuizzesData = [
            [
                'title' => 'Đại Chiến Kiến Thức Lập Trình Web & Frameworks',
                'description' => 'Bộ câu hỏi trắc nghiệm kiểm tra hiểu biết toàn diện về PHP, Laravel, JavaScript, HTML5/CSS3 và RESTful API.',
                'difficulty' => 'medium',
                'questions' => [
                    ['q' => 'Trong mô hình MVC của Laravel, Controller đóng vai trò gì?', 'opts' => ['A' => 'Điều hướng luồng xử lý và kết nối Model với View', 'B' => 'Trực tiếp vẽ giao diện HTML cho người dùng', 'C' => 'Chỉ quản lý các bảng trong cơ sở dữ liệu', 'D' => 'Thay thế cho Web Server Nginx'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Mã phản hồi HTTP Status Code nào biểu thị "Yêu cầu thành công" (OK)?', 'opts' => ['A' => '200', 'B' => '404', 'C' => '500', 'D' => '301'], 'ans' => 'A', 'time' => 10],
                    ['q' => 'Công cụ quản lý gói thư viện phụ thuộc chính thức của ngôn ngữ PHP là gì?', 'opts' => ['A' => 'npm', 'B' => 'Composer', 'C' => 'Pip', 'D' => 'Maven'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Trong JavaScript, toán tử === khác với == ở điểm cốt lõi nào?', 'opts' => ['A' => 'So sánh cả giá trị và kiểu dữ liệu không ép kiểu', 'B' => 'Tự động ép kiểu trước khi so sánh', 'C' => 'Chỉ dùng được cho kiểu chuỗi string', 'D' => 'Luôn luôn trả về giá trị false'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Directive nào trong Blade template của Laravel dùng để chống tấn công CSRF?', 'opts' => ['A' => '@auth', 'B' => '@csrf', 'C' => '@secure', 'D' => '@token'], 'ans' => 'B', 'time' => 10],
                    ['q' => 'Phương thức HTTP nào được dùng phổ biến để cập nhật MỘT PHẦN dữ liệu bản ghi?', 'opts' => ['A' => 'GET', 'B' => 'POST', 'C' => 'PATCH', 'D' => 'DELETE'], 'ans' => 'C', 'time' => 15],
                    ['q' => 'ORM mặc định được tích hợp sẵn trong Laravel có tên gọi là gì?', 'opts' => ['A' => 'Doctrine', 'B' => 'Eloquent', 'C' => 'Hibernate', 'D' => 'Prisma'], 'ans' => 'B', 'time' => 10],
                    ['q' => 'Khái niệm "Cache" trong kiến trúc hệ thống web nhằm mục đích chính là gì?', 'opts' => ['A' => 'Lưu tạm dữ liệu để truy xuất siêu tốc giảm tải DB', 'B' => 'Quét mã độc', 'C' => 'Nén hình ảnh', 'D' => 'Mã hóa mật khẩu'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Lệnh Artisan nào dùng để chạy các migration cập nhật cấu trúc database?', 'opts' => ['A' => 'php artisan db:run', 'B' => 'php artisan migrate', 'C' => 'php artisan schema:sync', 'D' => 'php artisan make:migration'], 'ans' => 'B', 'time' => 10],
                    ['q' => 'Trong thể thức Blooket Gold Quest, mỗi lần trả lời đúng bạn được chọn bao nhiêu rương?', 'opts' => ['A' => '1 trong 2 rương', 'B' => '1 trong 3 rương', 'C' => '1 trong 4 rương', 'D' => '1 trong 5 rương'], 'ans' => 'B', 'time' => 10],
                ],
            ],
            [
                'title' => 'Thử Thách Tư Duy Thuật Toán & Cấu Trúc Dữ Liệu',
                'description' => 'Kiểm tra phản xạ logic về mảng, ngăn xếp Stack, hàng đợi Queue, cây nhị phân và độ phức tạp Big-O.',
                'difficulty' => 'hard',
                'questions' => [
                    ['q' => 'Cấu trúc dữ liệu Ngăn xếp (Stack) hoạt động theo nguyên lý nào sau đây?', 'opts' => ['A' => 'FIFO (Vào trước ra trước)', 'B' => 'LIFO (Vào sau ra trước)', 'C' => 'Ngẫu nhiên không thứ tự', 'D' => 'Ưu tiên giá trị lớn nhất'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Độ phức tạp thời gian trung bình của thuật toán Tìm kiếm Nhị phân (Binary Search) là gì?', 'opts' => ['A' => 'O(1)', 'B' => 'O(N)', 'C' => 'O(log N)', 'D' => 'O(N^2)'], 'ans' => 'C', 'time' => 15],
                    ['q' => 'Cấu trúc dữ liệu Hàng đợi (Queue) hoạt động theo nguyên lý chuẩn nào?', 'opts' => ['A' => 'FIFO (Vào trước ra trước)', 'B' => 'LIFO (Vào sau ra trước)', 'C' => 'Chỉ vào không ra', 'D' => 'Xếp theo bảng chữ cái'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Thuật toán sắp xếp Quick Sort trong trường hợp trung bình có độ phức tạp thời gian là bao nhiêu?', 'opts' => ['A' => 'O(N)', 'B' => 'O(N log N)', 'C' => 'O(N^2)', 'D' => 'O(2^N)'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Hệ đếm nhị phân (Binary) chỉ sử dụng duy nhất hai chữ số nào sau đây?', 'opts' => ['A' => '0 và 1', 'B' => '1 và 2', 'C' => '0 và 9', 'D' => 'A và B'], 'ans' => 'A', 'time' => 10],
                    ['q' => 'Trong đồ thị, thuật toán Dijkstra dùng để tìm điều gì?', 'opts' => ['A' => 'Đường đi ngắn nhất từ một đỉnh nguồn', 'B' => 'Số lượng cạnh chẵn', 'C' => 'Chu trình Euler', 'D' => 'Tô màu đồ thị'], 'ans' => 'A', 'time' => 15],
                    ['q' => '1 Gigabyte (GB) tiêu chuẩn tương đương với bao nhiêu Megabyte (MB)?', 'opts' => ['A' => '512 MB', 'B' => '1000 MB', 'C' => '1024 MB', 'D' => '2048 MB'], 'ans' => 'C', 'time' => 10],
                    ['q' => 'Bộ nhớ đệm L1, L2, L3 nằm trực tiếp bên trong linh kiện phần cứng nào?', 'opts' => ['A' => 'RAM', 'B' => 'Bộ vi xử lý CPU', 'C' => 'Ổ cứng SSD', 'D' => 'Bo mạch chủ'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Cấu trúc dữ liệu Hash Table (Bảng băm) có tốc độ truy xuất lý tưởng trung bình là bao nhiêu?', 'opts' => ['A' => 'O(1)', 'B' => 'O(log N)', 'C' => 'O(N)', 'D' => 'O(N log N)'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Hành động "Swap" trong chế độ Gold Quest có tác dụng gì?', 'opts' => ['A' => 'Đổi toàn bộ số vàng và thứ hạng với 1 người chơi khác', 'B' => 'Nhân đôi số vàng hiện có', 'C' => 'Cướp 50% vàng đối thủ', 'D' => 'Xóa điểm của người dẫn đầu'], 'ans' => 'A', 'time' => 10],
                ],
            ],
            [
                'title' => 'Chinh Phục Kiến Thức Khoa Học Tự Nhiên & Công Nghệ 4.0',
                'description' => 'Bộ câu hỏi đố vui tổng hợp về vũ trụ, khám phá khoa học, lịch sử phát minh và xu hướng AI thời đại mới.',
                'difficulty' => 'easy',
                'questions' => [
                    ['q' => 'Hành tinh nào nằm ở vị trí gần Mặt Trời nhất trong Hệ Mặt Trời?', 'opts' => ['A' => 'Sao Kim', 'B' => 'Sao Thủy', 'C' => 'Sao Hỏa', 'D' => 'Trái Đất'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Khí nào chiếm tỷ lệ phần trăm lớn nhất trong khí quyển Trái Đất?', 'opts' => ['A' => 'Oxy (O2)', 'B' => 'Nito (N2)', 'C' => 'Cacbonic (CO2)', 'D' => 'Hydro (H2)'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Ai là nhà phát minh ra bóng đèn dây tóc thương mại ứng dụng thực tế?', 'opts' => ['A' => 'Nikola Tesla', 'B' => 'Thomas Edison', 'C' => 'Alexander Bell', 'D' => 'Isaac Newton'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Đại dương nào có diện tích và độ sâu lớn nhất trên Trái Đất?', 'opts' => ['A' => 'Đại Tây Dương', 'B' => 'Thái Bình Dương', 'C' => 'Ấn Độ Dương', 'D' => 'Bắc Băng Dương'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Loài động vật có vú nào to lớn nhất trong lịch sử Trái Đất?', 'opts' => ['A' => 'Voi châu Phi', 'B' => 'Cá voi xanh', 'C' => 'Khủng long cổ dài', 'D' => 'Hà mã'], 'ans' => 'B', 'time' => 15],
                    ['q' => 'Công nghệ Blockchain là nền tảng cốt lõi của lĩnh vực nổi bật nào?', 'opts' => ['A' => 'Tiền mã hóa (Cryptocurrency)', 'B' => 'Nén âm thanh MP3', 'C' => 'Thiết kế card màn hình', 'D' => 'Phần mềm diệt virus'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Mạng Internet toàn cầu được bắt nguồn từ dự án mạng quân sự nào của Mỹ?', 'opts' => ['A' => 'ARPANET', 'B' => 'DARPA-NET', 'C' => 'INTERNET-1', 'D' => 'US-NET'], 'ans' => 'A', 'time' => 15],
                    ['q' => 'Từ viết tắt "AI" trong lĩnh vực khoa học máy tính có nghĩa là gì?', 'opts' => ['A' => 'Artificial Intelligence (Trí tuệ nhân tạo)', 'B' => 'Automated Interface', 'C' => 'Advanced Internet', 'D' => 'Algorithm Integration'], 'ans' => 'A', 'time' => 10],
                    ['q' => 'Kim tự tháp Giza vĩ đại nổi tiếng thế giới nằm ở quốc gia nào?', 'opts' => ['A' => 'Hy Lạp', 'B' => 'Ai Cập', 'C' => 'Ý', 'D' => 'Mexico'], 'ans' => 'B', 'time' => 10],
                    ['q' => 'Khi mở rương trúng ô "Cướp vàng" (Steal), người chơi được phép cướp bao nhiêu phần trăm vàng của đối thủ?', 'opts' => ['A' => '10% đến 30%', 'B' => '50%', 'C' => '100%', 'D' => 'Chỉ 5%'], 'ans' => 'A', 'time' => 10],
                ],
            ],
        ];

        foreach ($minigameQuizzesData as $mq) {
            $quiz = MinigameQuiz::firstOrCreate(
                [
                    'title' => $mq['title'],
                ],
                [
                    'user_id' => $defaultHostUser?->id,
                    'description' => $mq['description'],
                    'difficulty' => $mq['difficulty'],
                    'is_public' => true,
                ]
            );

            // Xóa câu hỏi cũ nếu đã có để cập nhật chuẩn xác
            $quiz->questions()->delete();

            foreach ($mq['questions'] as $qIdx => $questionItem) {
                MinigameQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionItem['q'],
                    'options' => $questionItem['opts'],
                    'correct_answer' => $questionItem['ans'],
                    'time_limit' => $questionItem['time'],
                    'order_number' => $qIdx + 1,
                ]);
            }
        }
    }
}
