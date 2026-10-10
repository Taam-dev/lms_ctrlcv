<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo có giảng viên để gán khóa học
        $teachers = User::where('role', 'teacher')->get();

        if ($teachers->isEmpty()) {
            $teacher = User::factory()->create([
                'name' => 'Giảng viên Chuẩn',
                'email' => 'teacher@example.com',
                'role' => 'teacher',
            ]);
            $teachers = collect([$teacher]);
        }

        $teacherIds = $teachers->pluck('id')->toArray();

        $coursesData = [
            [
                'title' => 'Lập trình Python từ Zero đến Hero',
                'description' => 'Khóa học lập trình Python toàn diện dành cho người mới bắt đầu đến nâng cao. Học cú pháp, cấu trúc dữ liệu, OOP, tự động hóa tác vụ và phân tích dữ liệu.',
                'thumbnail' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Cài đặt Python & Thiết lập môi trường VS Code',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                    ],
                    [
                        'title' => 'Cú pháp cơ bản, Biến & Kiểu dữ liệu trong Python',
                        'content_type' => 'text',
                        'content' => "Trong bài học này, chúng ta sẽ tìm hiểu các khái niệm nền tảng của Python:\n\n1. Khai báo biến: `name = 'Python'`, `age = 30`\n2. Kiểu dữ liệu số học: `int`, `float`, `complex`\n3. Kiểu dữ liệu chuỗi (String) và các phương thức xử lý chuỗi: `.upper()`, `.lower()`, `.strip()`\n4. Kiểu boolean: `True`, `False`\n\nVí dụ mã nguồn:\n```python\n# In lời chào ra màn hình\nmessage = 'Chào mừng bạn đến với khóa học Python!'\nprint(message)\n\nx = 10\ny = 20\nprint(f'Tổng của {x} và {y} là {x + y}')\n```\n\nHãy thực hành gõ code trực tiếp trên trình soạn thảo của bạn để ghi nhớ tốt nhất!",
                    ],
                    [
                        'title' => 'Cấu trúc điều khiển: If-Else & Vòng lặp For, While',
                        'content_type' => 'text',
                        'content' => "Cấu trúc điều khiển giúp chương trình của bạn đưa ra quyết định logic linh hoạt:\n\n- Câu lệnh rẽ nhánh `if`, `elif`, `else`\n- Vòng lặp `for` duyệt qua danh sách (list), từ điển (dict)\n- Vòng lặp `while` lặp theo điều kiện\n- Các từ khóa điều khiển: `break`, `continue`, `pass`\n\n```python\nfor i in range(1, 11):\n    if i % 2 == 0:\n        print(f'{i} là số chẵn')\n    else:\n        print(f'{i} là số lẻ')\n```",
                    ],
                    [
                        'title' => 'Hàm (Functions) và Xử lý ngoại lệ trong Python',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=9Os0o3wzS_I',
                    ],
                    [
                        'title' => 'Lập trình Hướng đối tượng (OOP) với Python',
                        'content_type' => 'text',
                        'content' => "Lập trình hướng đối tượng là phương pháp lập trình phổ biến giúp quản lý dự án lớn:\n\n1. Lớp (Class) và Đối tượng (Object)\n2. Phương thức khởi tạo `__init__` và con trỏ `self`\n3. 4 tính chất OOP: Kế thừa (Inheritance), Đóng gói (Encapsulation), Đa hình (Polymorphism), Trừu tượng (Abstraction)\n\n```python\nclass Student:\n    def __init__(self, name, score):\n        self.name = name\n        self.score = score\n        \n    def get_grade(self):\n        return 'Đạt' if self.score >= 5.0 else 'Trượt'\n\ns1 = Student('Nguyễn Văn A', 8.5)\nprint(f'{s1.name}: {s1.get_grade()}')\n```",
                    ],
                ],
            ],
            [
                'title' => 'ReactJS & Redux Toolkit Thực Chiến',
                'description' => 'Học ReactJS từ nền tảng component, props, state đến React Hooks chuyên sâu và quản lý trạng thái tập trung với Redux Toolkit qua các dự án thực tế.',
                'thumbnail' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Khởi tạo dự án React với Vite & Cấu trúc thư mục',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=SqcY0GlETgk',
                    ],
                    [
                        'title' => 'Thành phần (Components), Props và State cơ bản',
                        'content_type' => 'text',
                        'content' => "Tìm hiểu các khối xây dựng cơ bản của ReactJS:\n\n- Function Component và cú pháp JSX\n- Truyền dữ liệu giữa các component với Props\n- Quản lý trạng thái nội bộ với Hook `useState`\n\n```jsx\nimport { useState } from 'react';\n\nfunction Counter({ initialCount = 0 }) {\n  const [count, setCount] = useState(initialCount);\n\n  return (\n    <div>\n      <p>Số lượt bấm: {count}</p>\n      <button onClick={() => setCount(count + 1)}>Tăng</button>\n    </div>\n  );\n}\n```",
                    ],
                    [
                        'title' => 'Làm chủ React Hooks: useState, useEffect, useRef, useMemo',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=O6P86uwfdR0',
                    ],
                    [
                        'title' => 'Quản lý State toàn cục với Redux Toolkit',
                        'content_type' => 'text',
                        'content' => "Redux Toolkit là cách tiêu chuẩn để viết logic Redux hiện đại:\n\n- `configureStore`: Tạo Redux Store tập trung\n- `createSlice`: Kết hợp reducers và actions trong cùng một file\n- Hooks `useSelector` và `useDispatch` để tương tác với store\n\n```javascript\nimport { createSlice, configureStore } from '@reduxjs/toolkit';\n\nconst counterSlice = createSlice({\n  name: 'counter',\n  initialState: { value: 0 },\n  reducers: {\n    increment: (state) => { state.value += 1; },\n    decrement: (state) => { state.value -= 1; },\n  },\n});\n```",
                    ],
                    [
                        'title' => 'Xây dựng Ứng dụng Giỏ hàng (Shopping Cart) Hoàn Chỉnh',
                        'content_type' => 'text',
                        'content' => 'Dự án thực hành cuối khóa: Tích hợp đầy đủ các kiến thức đã học vào ứng dụng giỏ hàng gồm hiển thị danh sách sản phẩm, thêm vào giỏ, tăng giảm số lượng, tính tổng tiền và lưu vào LocalStorage.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình Web Backend với Node.js & Express',
                'description' => 'Khóa học xây dựng API Backend chuẩn RESTful hiệu năng cao với Node.js, Express framework, kết nối MongoDB và xác thực người dùng an toàn.',
                'thumbnail' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Tổng quan về Node.js Runtime & Kiến trúc Event Loop',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=TlB_eWDSMt4',
                    ],
                    [
                        'title' => 'Xây dựng RESTful API đầu tiên với Express.js',
                        'content_type' => 'text',
                        'content' => "Cài đặt Express và cấu hình các route cơ bản:\n\n```javascript\nconst express = require('express');\nconst app = express();\n\napp.use(express.json());\n\napp.get('/api/users', (req, res) => {\n  res.json({ success: true, data: [] });\n});\n\napp.listen(3000, () => console.log('Server chạy tại port 3000'));\n```",
                    ],
                    [
                        'title' => 'Kết nối Cơ sở dữ liệu MongoDB với Mongoose',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=DZBGEExL2eE',
                    ],
                    [
                        'title' => 'Xác thực người dùng với JWT (JSON Web Token) & Bcrypt',
                        'content_type' => 'text',
                        'content' => "Bảo mật tài khoản với mã hóa mật khẩu Bcrypt và phát hành mã thông báo JWT:\n\n1. Băm mật khẩu khi đăng ký người dùng bằng `bcrypt.hash()`\n2. So sánh mật khẩu khi đăng nhập bằng `bcrypt.compare()`\n3. Ký mã truy cập bằng `jwt.sign({ userId }, secretKey, { expiresIn: '7d' })`\n4. Viết middleware xác thực bảo vệ các route riêng tư.",
                    ],
                    [
                        'title' => 'Viết Middleware & Xử lý lỗi tập trung trong Express',
                        'content_type' => 'text',
                        'content' => 'Xây dựng hệ thống middleware kiểm tra quyền hạn, logging request và middleware bắt lỗi tập trung (`error handler`) để trả về thông báo lỗi chuẩn JSON.',
                    ],
                ],
            ],
            [
                'title' => 'Xây Dựng Ứng Dụng Hiện Đại với Next.js 14 & Tailwind CSS',
                'description' => 'Khám phá sức mạnh của Next.js 14 App Router, React Server Components (RSC), Server Actions và xây dựng giao diện tuyệt đẹp với Tailwind CSS.',
                'thumbnail' => 'https://images.unsplash.com/photo-1517180102446-f3ece451e9d8?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Làm quen với Next.js 14 & App Router mới nhất',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=843nec-IvW0',
                    ],
                    [
                        'title' => 'Server Components vs Client Components trong Next.js',
                        'content_type' => 'text',
                        'content' => "Sự khác biệt cốt lõi trong kiến trúc Next.js App Router:\n\n- Mặc định tất cả components trong thư mục `app/` là Server Components\n- Chỉ sử dụng chỉ thị `'use client'` khi cần tương tác người dùng (`onClick`, `onChange`, `useState`, `useEffect`)\n- Tối ưu hóa bundle tải về trình duyệt giúp website load nhanh gấp nhiều lần.",
                    ],
                    [
                        'title' => 'Tối ưu hóa UI với Tailwind CSS & Server Actions',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=d_IFKP1Ofq0',
                    ],
                    [
                        'title' => 'Tối ưu hóa SEO, Metadata và OpenGraph Images',
                        'content_type' => 'text',
                        'content' => 'Kỹ thuật SEO chuẩn quốc tế với hàm `generateMetadata` của Next.js 14, thẻ canonical, sitemap động và sinh ảnh thumbnail xem trước tự động khi chia sẻ Facebook/Zalo.',
                    ],
                    [
                        'title' => 'Deploy ứng dụng Next.js lên Vercel & Tối ưu hóa bộ nhớ đệm',
                        'content_type' => 'text',
                        'content' => 'Các bước đưa ứng dụng lên production trên nền tảng Vercel, cấu hình biến môi trường và sử dụng ISR (Incremental Static Regeneration) để tăng tốc độ website.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình Laravel 11 Chuyên Sâu từ A đến Z',
                'description' => 'Khóa học làm chủ PHP Framework hàng đầu thế giới. Tìm hiểu kiến trúc MVC, Eloquent ORM, Authentication, Authorization, APIs và tối ưu hóa hiệu năng.',
                'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Cài đặt Laravel 11, Cấu trúc thư mục mới & Artisan CLI',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=MYyJ4PuL4pY',
                    ],
                    [
                        'title' => 'Routing, Controllers & Blade Templating Engine',
                        'content_type' => 'text',
                        'content' => "Nắm vững cơ chế định tuyến routes, controllers dạng Resource và viết giao diện với Blade:\n\n```php\nRoute::get('/courses', [CourseController::class, 'index'])->name('courses.index');\n```\n\nTận dụng tính năng Blade Components để tái sử dụng layout và thẻ UI dễ dàng.",
                    ],
                    [
                        'title' => 'Eloquent ORM, Relationships & Query Builder nâng cao',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE',
                    ],
                    [
                        'title' => 'Middleware, Form Requests & Xác thực dữ liệu',
                        'content_type' => 'text',
                        'content' => 'Tách biệt logic kiểm tra dữ liệu bằng Form Request và bảo vệ tuyến đường bằng Middleware tùy chỉnh, kiểm tra vai trò người dùng (Roles & Permissions).',
                    ],
                    [
                        'title' => 'Xây dựng RESTful API với API Resources & Laravel Sanctum',
                        'content_type' => 'text',
                        'content' => 'Biến Laravel thành backend mạnh mẽ phục vụ ứng dụng di động hoặc React/Vue frontend với API Resources và token xác thực bảo mật Sanctum.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình Java & Spring Boot Xây Dựng Microservices',
                'description' => 'Khóa học Java backend chuyên nghiệp từ Spring Core, Spring Boot, Spring Data JPA, bảo mật với Spring Security đến thiết kế hệ thống Microservices.',
                'thumbnail' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Giới thiệu hệ sinh thái Java & Khởi tạo dự án Spring Boot',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=9SGDpanrc8U',
                    ],
                    [
                        'title' => 'Dependency Injection & Inversion of Control trong Spring',
                        'content_type' => 'text',
                        'content' => "Khái niệm cốt lõi làm nên sức mạnh của Spring Framework:\n\n- `@Component`, `@Service`, `@Repository`\n- Cơ chế tiêm phụ thuộc với `@Autowired` và Constructor Injection\n- Quản lý vòng đời của Bean trong Spring Application Context.",
                    ],
                    [
                        'title' => 'Xây dựng CRUD API với Spring Data JPA & Hibernate',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=vtPkZShrvXQ',
                    ],
                    [
                        'title' => 'Bảo mật ứng dụng với Spring Security 6 & OAuth2',
                        'content_type' => 'text',
                        'content' => 'Bảo vệ các endpoint với filter chain của Spring Security, tích hợp đăng nhập bảo mật qua JWT và OAuth2 Google/GitHub.',
                    ],
                    [
                        'title' => 'Triển khai kiến trúc Microservices với Spring Cloud',
                        'content_type' => 'text',
                        'content' => 'Tách nhỏ hệ thống thành các service độc lập, sử dụng Eureka Server để đăng ký service và Spring Cloud Gateway làm cổng giao tiếp tập trung.',
                    ],
                ],
            ],
            [
                'title' => 'Cơ Sở Dữ Liệu SQL & Thiết Kế Hệ Thống Cơ Sở Dữ Liệu',
                'description' => 'Học viết truy vấn SQL từ cơ bản đến phức tạp, chuẩn hóa dữ liệu, tối ưu hóa chỉ mục Index và thiết kế cơ sở dữ liệu quan hệ cho các hệ thống lớn.',
                'thumbnail' => 'https://images.unsplash.com/photo-1544383835-bda2bc66a55d?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Tổng quan về Hệ quản trị CSDL quan hệ RDBMS & MySQL',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=7S_tz1z_5bA',
                    ],
                    [
                        'title' => 'Truy vấn dữ liệu cơ bản: SELECT, WHERE, ORDER BY, GROUP BY',
                        'content_type' => 'text',
                        'content' => "Học các câu lệnh truy vấn dữ liệu nền tảng:\n\n```sql\nSELECT category_id, COUNT(*) AS total_courses\nFROM courses\nWHERE status = 'approved'\nGROUP BY category_id\nHAVING total_courses > 5\nORDER BY total_courses DESC;\n```",
                    ],
                    [
                        'title' => 'Kỹ thuật kết hợp bảng nâng cao: INNER, LEFT, RIGHT JOIN',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=2HVMiPPuPIM',
                    ],
                    [
                        'title' => 'Chuẩn hóa dữ liệu 1NF, 2NF, 3NF & Thiết kế lược đồ ERD',
                        'content_type' => 'text',
                        'content' => 'Quy trình thiết kế cơ sở dữ liệu không bị dư thừa thông tin, tránh bất thường khi thêm/sửa/xóa và mô hình hóa quan hệ 1-1, 1-N, N-N.',
                    ],
                    [
                        'title' => 'Tối ưu hóa truy vấn với Indexing & Phân tích EXPLAIN',
                        'content_type' => 'text',
                        'content' => 'Sử dụng B-Tree Index, Composite Index và lệnh `EXPLAIN` để phát hiện truy vấn chạy chậm (slow queries) và tối ưu hóa tốc độ hệ thống.',
                    ],
                ],
            ],
            [
                'title' => 'Cấu Trúc Dữ Liệu và Giải Thuật Cho Lập Trình Viên',
                'description' => 'Rèn luyện tư duy thuật toán sắc bén, chuẩn bị cho các kỳ phỏng vấn lập trình tại các tập đoàn công nghệ lớn: Big O, Array, LinkedList, Tree, Graph, Dynamic Programming.',
                'thumbnail' => 'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Đánh giá độ phức tạp thuật toán: Big O Notation',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=kS_JQF_xupc',
                    ],
                    [
                        'title' => 'Danh sách liên kết (Linked List), Ngăn xếp (Stack) & Hàng đợi (Queue)',
                        'content_type' => 'text',
                        'content' => "Tìm hiểu các cấu trúc dữ liệu tuyến tính quan trọng:\n\n- Singly Linked List vs Doubly Linked List\n- Nguyên lý LIFO của Stack và ứng dụng quay lui (Backtracking)\n- Nguyên lý FIFO của Queue và ứng dụng trong hàng đợi xử lý tác vụ (Jobs/Queue).",
                    ],
                    [
                        'title' => 'Các thuật toán tìm kiếm và sắp xếp phổ biến (QuickSort, MergeSort)',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=kgBjXUE_Nwc',
                    ],
                    [
                        'title' => 'Cây nhị phân tìm kiếm (Binary Search Tree) và Đồ thị (Graph)',
                        'content_type' => 'text',
                        'content' => 'Cấu trúc dữ liệu phi tuyến tính: Duyệt cây tiền thứ tự, trung thứ tự, hậu thứ tự. Thuật toán tìm kiếm theo chiều sâu (DFS) và theo chiều rộng (BFS) trên đồ thị.',
                    ],
                    [
                        'title' => 'Kỹ thuật Quy hoạch động (Dynamic Programming) thực chiến',
                        'content_type' => 'text',
                        'content' => 'Bẻ nhỏ bài toán phức tạp thành các bài toán con gối nhau: Bài toán dãy con tăng dài nhất, bài toán cái ba lô (Knapsack problem) và kỹ thuật ghi nhớ Memoization.',
                    ],
                ],
            ],
            [
                'title' => 'DevOps Cơ Bản: Docker, CI/CD và Kubernetes',
                'description' => 'Tìm hiểu văn hóa DevOps, đóng gói ứng dụng với Docker Container, xây dựng luồng tự động hóa CI/CD với GitHub Actions và điều phối cụm Kubernetes.',
                'thumbnail' => 'https://images.unsplash.com/photo-1607799279861-4dd421887fb3?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Giới thiệu về Container hóa và Cài đặt Docker Desktop',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=3c-iBn73dDE',
                    ],
                    [
                        'title' => 'Viết Dockerfile tối ưu & Quản lý Container với Docker Compose',
                        'content_type' => 'text',
                        'content' => 'Thực hành viết tệp Dockerfile đa tầng (Multi-stage build) và khởi chạy đồng thời ứng dụng web, cơ sở dữ liệu MySQL, Redis bằng tệp `docker-compose.yml`.',
                    ],
                    [
                        'title' => 'Xây dựng Pipeline CI/CD tự động hóa với GitHub Actions',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=R8_veQiYBjI',
                    ],
                    [
                        'title' => 'Làm quen với Kubernetes: Pods, Services, Deployments',
                        'content_type' => 'text',
                        'content' => 'Kiến trúc Kubernetes và các đối tượng tài nguyên cơ bản giúp hệ thống tự động mở rộng (Auto-scaling) và tự phục hồi khi có sự cố.',
                    ],
                    [
                        'title' => 'Giám sát hệ thống với Prometheus và Grafana',
                        'content_type' => 'text',
                        'content' => 'Thiết lập bảng theo dõi chỉ số tài nguyên CPU, RAM, lưu lượng mạng và thiết lập cảnh báo khi hệ thống gặp lỗi.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình Di Động Đa Nền Tảng với Flutter & Dart',
                'description' => 'Học lập trình ứng dụng di động cho cả iOS và Android chỉ với một bộ mã nguồn duy nhất bằng framework Flutter của Google.',
                'thumbnail' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Khởi động với Flutter SDK & Ngôn ngữ lập trình Dart',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=1ukSR1GRtMU',
                    ],
                    [
                        'title' => 'Xây dựng giao diện UI với Widget Tree: Stateless vs Stateful',
                        'content_type' => 'text',
                        'content' => 'Tất cả mọi thứ trong Flutter đều là Widget. Khám phá Container, Row, Column, ListView và cách cập nhật giao diện linh hoạt.',
                    ],
                    [
                        'title' => 'Quản lý trạng thái ứng dụng với Provider và Bloc',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=d_m5cSmepPI',
                    ],
                    [
                        'title' => 'Gọi REST API & Lưu trữ dữ liệu cục bộ với SharedPreferences',
                        'content_type' => 'text',
                        'content' => 'Sử dụng package `http` để gọi API lấy dữ liệu JSON, parse model và lưu trữ trạng thái đăng nhập trên thiết bị.',
                    ],
                    [
                        'title' => 'Đóng gói và phát hành ứng dụng lên Google Play & App Store',
                        'content_type' => 'text',
                        'content' => 'Cấu hình icon ứng dụng, ký chứng chỉ số (Keystore) và chuẩn bị các tài liệu để đưa ứng dụng lên các kho ứng dụng lớn.',
                    ],
                ],
            ],
            [
                'title' => 'Trí Tuệ Nhân Tạo & Học Máy (Machine Learning) Cơ Bản',
                'description' => 'Nhập môn AI và Machine Learning thực hành bằng Python. Khám phá các thuật toán học có giám sát, không giám sát và mạng nơ-ron cơ bản.',
                'thumbnail' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Nhập môn Trí tuệ nhân tạo (AI) và Học máy (Machine Learning)',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=ukzFI9rgwfU',
                    ],
                    [
                        'title' => 'Tiền xử lý dữ liệu với thư viện NumPy và Pandas',
                        'content_type' => 'text',
                        'content' => 'Thao tác trên mảng đa chiều NumPy và phân tích tập dữ liệu dạng bảng với Pandas Dataframe, làm sạch dữ liệu thiếu (missing data) và chuẩn hóa dữ liệu.',
                    ],
                    [
                        'title' => 'Thuật toán Học có giám sát: Hồi quy tuyến tính & Logistic Regression',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=i_LwzRVP7bg',
                    ],
                    [
                        'title' => 'Xây dựng mô hình phân loại dữ liệu với Scikit-Learn',
                        'content_type' => 'text',
                        'content' => 'Thực hành huấn luyện mô hình dự đoán giá nhà hoặc phân loại email spam, đánh giá độ chính xác qua ma trận Confusion Matrix.',
                    ],
                    [
                        'title' => 'Nhập môn Mạng nơ-ron nhân tạo (Neural Networks) & Deep Learning',
                        'content_type' => 'text',
                        'content' => 'Tổng quan về Perceptron, hàm kích hoạt (Activation Functions) và cấu trúc mạng nơ-ron nhiều tầng cơ bản.',
                    ],
                ],
            ],
            [
                'title' => 'Bảo Mật Web & An Toàn Thông Tin (Cybersecurity Essentials)',
                'description' => 'Học cách nhận biết các lỗ hổng bảo mật web phổ biến (OWASP Top 10), kỹ thuật tấn công và biện pháp phòng vệ kiên cố cho website.',
                'thumbnail' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Tổng quan về An toàn thông tin và Mô hình OWASP Top 10',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=inWWhr5tnEA',
                    ],
                    [
                        'title' => 'Phòng chống tấn công SQL Injection và Cross-Site Scripting (XSS)',
                        'content_type' => 'text',
                        'content' => 'Phân tích nguyên nhân xảy ra lỗi chèn mã độc SQL/JS vào website và cách thức khắc phục bằng Prepared Statements và Content Security Policy.',
                    ],
                    [
                        'title' => 'Cơ chế CSRF, CORS và Cách thức bảo vệ Session/Cookies',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=4b_B863FqZc',
                    ],
                    [
                        'title' => 'Mã hóa đối xứng, bất đối xứng & Giao thức HTTPS / SSL / TLS',
                        'content_type' => 'text',
                        'content' => 'Nguyên lý mã hóa AES, RSA, chứng chỉ số SSL và cách dữ liệu được bảo vệ an toàn trên đường truyền Internet.',
                    ],
                    [
                        'title' => 'Kiểm thử xâm nhập (Penetration Testing) cơ bản với Burp Suite',
                        'content_type' => 'text',
                        'content' => 'Sử dụng công cụ proxy Burp Suite để chặn bắt gói tin HTTP, phân tích tham số và đánh giá mức độ an toàn của hệ thống.',
                    ],
                ],
            ],
            [
                'title' => 'Thiết Kế Giao Diện UI/UX Chuyên Nghiệp với Figma',
                'description' => 'Khóa học thiết kế trải nghiệm người dùng (UX) và giao diện sản phẩm (UI) với Figma: Từ wireframe, design system đến prototype sống động.',
                'thumbnail' => 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Nguyên lý thị giác và Quy trình thiết kế sản phẩm số',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=FTFaQWZBqQ8',
                    ],
                    [
                        'title' => 'Làm chủ công cụ Figma: Frames, Shapes, Typography & Grids',
                        'content_type' => 'text',
                        'content' => 'Thiết lập hệ thống lưới 8pt chuẩn, lựa chọn font chữ hài hòa và quy chuẩn phân cấp thị giác (Visual Hierarchy).',
                    ],
                    [
                        'title' => 'Thiết kế Auto Layout & Responsive Design trong Figma',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=NrKX46DzkGQ',
                    ],
                    [
                        'title' => 'Xây dựng Design System, Components & Variants chuẩn chỉnh',
                        'content_type' => 'text',
                        'content' => 'Cách tạo bộ linh kiện UI tái sử dụng (Buttons, Form Inputs, Modals) với tính năng Variants và Variables mới nhất của Figma.',
                    ],
                    [
                        'title' => 'Tạo Prototype tương tác động và Bàn giao thiết kế cho Developer',
                        'content_type' => 'text',
                        'content' => 'Tạo luồng chuyển động mượt mà với Smart Animate và sử dụng Dev Mode để bàn giao thông số CSS/Kích thước chuẩn cho đội ngũ lập trình.',
                    ],
                ],
            ],
            [
                'title' => 'Quản Lý Phiên Bản Mã Nguồn Chuyên Nghiệp với Git & GitHub',
                'description' => 'Khóa học bắt buộc cho mọi lập trình viên. Nắm vững lệnh Git, phân nhánh, giải quyết xung đột mã nguồn và làm việc nhóm hiệu quả trên GitHub.',
                'thumbnail' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Git là gì? Khởi tạo kho chứa và Các lệnh cơ bản git add, commit, status',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=RGOj5yH7evk',
                    ],
                    [
                        'title' => 'Làm việc với Nhánh (Branches), Merge và Xử lý xung đột (Merge Conflicts)',
                        'content_type' => 'text',
                        'content' => 'Hiểu rõ cách phân nhánh tính năng (`feature branches`), cách gộp nhánh an toàn và giải quyết xung đột code trực tiếp trên VS Code.',
                    ],
                    [
                        'title' => 'Kết nối với GitHub: git remote, push, pull và fetch',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=tRZGeaHPoaw',
                    ],
                    [
                        'title' => 'Quy trình làm việc nhóm chuyên nghiệp: Fork, Pull Request & Code Review',
                        'content_type' => 'text',
                        'content' => 'Tiêu chuẩn đóng góp mã nguồn trong các công ty công nghệ và dự án mã nguồn mở với Pull Request và thảo luận cải tiến code.',
                    ],
                    [
                        'title' => 'Các mẹo cứu mã nguồn với git stash, rebase, cherry-pick và reflog',
                        'content_type' => 'text',
                        'content' => 'Các kỹ thuật nâng cao giúp khôi phục các commit bị mất, dọn dẹp lịch sử commit gọn gàng trước khi merge vào nhánh chính.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình Web Hiện Đại với Vue.js 3 & Pinia',
                'description' => 'Khóa học Vue 3 toàn diện sử dụng Composition API, Vite, Vue Router 4 và hệ thống quản lý trạng thái hiện đại Pinia.',
                'thumbnail' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Nhập môn Vue 3: Composition API & Khởi tạo dự án với Vite',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=YrxBCBibVo0',
                    ],
                    [
                        'title' => 'Reactivity trong Vue 3: ref, reactive, computed và watch',
                        'content_type' => 'text',
                        'content' => 'Hiểu rõ cơ chế phản ứng dữ liệu (Reactivity System) của Vue 3 được xây dựng trên JavaScript Proxy hiện đại.',
                    ],
                    [
                        'title' => 'Xây dựng Single Page Application với Vue Router 4',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=qZXt1Aom3Cs',
                    ],
                    [
                        'title' => 'Quản lý trạng thái tập trung với Pinia Store',
                        'content_type' => 'text',
                        'content' => 'Thay thế Vuex bằng Pinia với cú pháp gọn nhẹ, hỗ trợ TypeScript hoàn hảo và phân chia module trực quan.',
                    ],
                    [
                        'title' => 'Tích hợp UI Component Library và Xây dựng trang Dashboard hoàn chỉnh',
                        'content_type' => 'text',
                        'content' => 'Xây dựng giao diện bảng điều khiển quản trị hoàn chỉnh với biểu đồ thống kê, bảng dữ liệu lọc tìm kiếm và phân trang.',
                    ],
                ],
            ],
            [
                'title' => 'Lập Trình C++ Hiện Đại & Tư Duy Giải Quyết Vấn Đề',
                'description' => 'Khóa học lập trình C++ từ căn bản đến nâng cao (C++11/14/17/20): Con trỏ, bộ nhớ động, OOP, STL và kỹ thuật lập trình thi đấu.',
                'thumbnail' => 'https://images.unsplash.com/photo-1534972195531-a756b1126f24?w=800&auto=format&fit=crop&q=80',
                'lessons' => [
                    [
                        'title' => 'Cài đặt Compiler GCC/Clang & Cú pháp C++ căn bản',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=vLnPwxZdW4Y',
                    ],
                    [
                        'title' => 'Con trỏ (Pointers), Tham chiếu (References) và Quản lý bộ nhớ động',
                        'content_type' => 'text',
                        'content' => 'Hiểu sâu sắc về địa chỉ ô nhớ trong RAM, toán tử `*` và `&`, cấp phát bộ nhớ động với `new` và giải phóng bộ nhớ với `delete` để tránh rò rỉ bộ nhớ (Memory Leak).',
                    ],
                    [
                        'title' => 'Lập trình Hướng đối tượng C++: Classes, Encapsulation, Inheritance, Polymorphism',
                        'content_type' => 'video',
                        'content' => 'https://www.youtube.com/watch?v=wN0x9eZLix4',
                    ],
                    [
                        'title' => 'Sử dụng thư viện chuẩn C++ Standard Template Library (STL)',
                        'content_type' => 'text',
                        'content' => 'Làm chủ các cấu trúc dữ liệu container trong STL: `std::vector`, `std::map`, `std::unordered_map`, `std::set`, `std::priority_queue` để giải quyết bài toán nhanh chóng.',
                    ],
                    [
                        'title' => 'Con trỏ thông minh (Smart Pointers) trong C++ Hiện Đại',
                        'content_type' => 'text',
                        'content' => 'Nguyên lý RAII và sử dụng `std::unique_ptr`, `std::shared_ptr`, `std::weak_ptr` để tự động quản lý tài nguyên an toàn.',
                    ],
                ],
            ],
        ];

        foreach ($coursesData as $index => $cData) {
            $assignedTeacherId = $teacherIds[$index % count($teacherIds)];

            $course = Course::firstOrCreate(
                ['title' => $cData['title']],
                [
                    'teacher_id' => $assignedTeacherId,
                    'slug' => Str::slug($cData['title']),
                    'description' => $cData['description'],
                    'thumbnail' => $cData['thumbnail'],
                    'status' => 'approved',
                ]
            );

            // Đảm bảo status luôn là approved
            if ($course->status !== 'approved') {
                $course->status = 'approved';
                $course->save();
            }

            // Tạo các bài giảng cho khóa học nếu chưa có
            foreach ($cData['lessons'] as $lIndex => $lessonData) {
                Lesson::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'title' => $lessonData['title'],
                    ],
                    [
                        'slug' => Str::slug($lessonData['title']),
                        'content_type' => $lessonData['content_type'],
                        'content' => $lessonData['content'],
                        'order_number' => $lIndex + 1,
                    ]
                );
            }
        }
    }
}
