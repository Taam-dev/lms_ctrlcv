<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách giảng viên / admin
        $instructors = User::whereIn('role', ['teacher', 'admin'])->get();

        if ($instructors->isEmpty()) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@lms.edu.vn'],
                [
                    'name' => 'Quản trị viên',
                    'password' => bcrypt('password'),
                    'role' => 'admin',
                ]
            );
            $instructors = collect([$admin]);
        }

        $sampleDocs = [
            [
                'title' => 'Giáo trình Lập trình Web Fullstack với Laravel 11 & Vue 3',
                'category' => 'Lập trình Web',
                'description' => "Bộ giáo trình toàn diện hướng dẫn xây dựng ứng dụng web hiện đại từ căn bản đến nâng cao.\n- Kiến trúc MVC, Service Layer, Repository Pattern\n- Xác thực đa tầng Sanctum/Breeze, RBAC\n- RESTful API & WebSocket tương tác thời gian thực\n- Vue 3 Composition API & TailwindCSS.",
                'cover_image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'view_count' => 1420,
                'download_count' => 482,
                'files' => [
                    [
                        'name' => 'Giao-trinh-Laravel-11-Vue-Fullstack-v2.pdf',
                        'type' => 'pdf',
                        'size' => 12450000,
                        'downloads' => 294,
                        'dummy_content' => 'Giao trinh Lap trinh Web Fullstack Laravel 11 & Vue 3 - Ctrl C+V LMS.',
                    ],
                    [
                        'name' => 'Slide-Bai-Giang-Chuong-1-den-10.pptx',
                        'type' => 'pptx',
                        'size' => 18600000,
                        'downloads' => 128,
                        'dummy_content' => 'Slide bai giang tong hop cac chuong Laravel 11.',
                    ],
                    [
                        'name' => 'SourceCode-Fullstack-Project-Demo.zip',
                        'type' => 'zip',
                        'size' => 24800000,
                        'downloads' => 60,
                        'dummy_content' => 'Source code du an mau Fullstack Laravel + Vue 3.',
                    ],
                ],
            ],
            [
                'title' => 'Đề cương & Ngân hàng câu hỏi ôn thi Cơ sở dữ liệu nâng cao',
                'category' => 'Đề thi & Đề cương ôn tập',
                'description' => "Đề cương ôn tập chi tiết môn Cơ sở dữ liệu và CSDL nâng cao:\n- Tối ưu hóa truy vấn SQL (Query Optimization)\n- Cơ chế Indexing (B-Tree, Hash, GiST), Partitioning\n- Giao dịch ACID và các mức cô lập (Isolation Levels)\n- Ngân hàng 150 câu hỏi trắc nghiệm và 10 bài toán thiết kế thực tế.",
                'cover_image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'view_count' => 980,
                'download_count' => 315,
                'files' => [
                    [
                        'name' => 'De-cuong-on-tap-CSDL-SQL-Indexing.pdf',
                        'type' => 'pdf',
                        'size' => 4500000,
                        'downloads' => 195,
                        'dummy_content' => 'De cuong on tap CSDL nang cao - Ctrl C+V.',
                    ],
                    [
                        'name' => 'Bo-de-thi-thu-kem-dap-an-CSDL.docx',
                        'type' => 'docx',
                        'size' => 2100000,
                        'downloads' => 120,
                        'dummy_content' => 'Bo de thi thu mon CSDL kem loi giai chi tiet.',
                    ],
                ],
            ],
            [
                'title' => 'Slide tổng hợp Kiến trúc Vi dịch vụ (Microservices Architecture) & Docker',
                'category' => 'DevOps & Điện toán đám mây',
                'description' => "Tài liệu đào tạo chuyên sâu về kiến trúc hệ thống phân tán:\n- API Gateway, Service Discovery, Circuit Breaker\n- Message Broker với RabbitMQ / Apache Kafka\n- Containerization với Docker & Docker Compose\n- CI/CD tự động hóa kiểm thử và triển khai.",
                'cover_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'view_count' => 870,
                'download_count' => 264,
                'files' => [
                    [
                        'name' => 'Slide-Microservices-Event-Driven.pptx',
                        'type' => 'pptx',
                        'size' => 15800000,
                        'downloads' => 154,
                        'dummy_content' => 'Slide kien truc Microservices.',
                    ],
                    [
                        'name' => 'Docker-Compose-CheatSheet-and-Templates.pdf',
                        'type' => 'pdf',
                        'size' => 3200000,
                        'downloads' => 110,
                        'dummy_content' => 'Cheat sheet Docker & Docker compose.',
                    ],
                ],
            ],
            [
                'title' => 'Giáo trình Nhập môn Trí tuệ nhân tạo (AI) & Machine Learning cơ bản',
                'category' => 'Trí tuệ nhân tạo (AI)',
                'description' => "Cung cấp nền tảng toán học và lập trình thực hành cho ngành AI:\n- Đại số tuyến tính, Xác suất thống kê ứng dụng\n- Thuật toán hồi quy, phân lớp (Linear/Logistic Regression, Decision Trees)\n- Mạng nơ-ron nhân tạo (ANN) và Deep Learning với PyTorch\n- Xử lý ngôn ngữ tự nhiên (NLP) căn bản.",
                'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => false,
                'view_count' => 1230,
                'download_count' => 410,
                'files' => [
                    [
                        'name' => 'Giao-trinh-AI-Machine-Learning-Python.pdf',
                        'type' => 'pdf',
                        'size' => 16400000,
                        'downloads' => 270,
                        'dummy_content' => 'Giao trinh AI & Machine Learning.',
                    ],
                    [
                        'name' => 'Jupyter-Notebook-Bai-Tap-Thuc-Hanh.zip',
                        'type' => 'zip',
                        'size' => 8900000,
                        'downloads' => 140,
                        'dummy_content' => 'Tap hop file jupyter notebook bai tap thuc hanh AI.',
                    ],
                ],
            ],
            [
                'title' => 'Cẩm nang Bảo mật ứng dụng Web & Phòng chống tấn công OWASP Top 10',
                'category' => 'An toàn thông tin & Bảo mật',
                'description' => "Hướng dẫn bảo vệ hệ thống trước các lỗ hổng phổ biến nhất:\n- SQL Injection, XSS (Cross-Site Scripting), CSRF\n- Broken Object Level Authorization (BOLA/IDOR)\n- Mã hóa dữ liệu nhạy cảm, lưu trữ an toàn mật khẩu (Bcrypt, Argon2)\n- Checklist rà soát bảo mật mã nguồn trước khi deploy.",
                'cover_image' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => false,
                'view_count' => 760,
                'download_count' => 230,
                'files' => [
                    [
                        'name' => 'Cam-nang-Bao-mat-Web-OWASP-Top10.pdf',
                        'type' => 'pdf',
                        'size' => 5400000,
                        'downloads' => 145,
                        'dummy_content' => 'Cam nang phong chong OWASP Top 10.',
                    ],
                    [
                        'name' => 'Slide-Bao-mat-Authentication-Authorization.pptx',
                        'type' => 'pptx',
                        'size' => 9800000,
                        'downloads' => 85,
                        'dummy_content' => 'Slide bao mat xac thuc & phan quyen.',
                    ],
                ],
            ],
            [
                'title' => 'Tài liệu Cấu trúc Dữ liệu & Giải thuật nâng cao (Data Structures & Algorithms)',
                'category' => 'Khoa học máy tính',
                'description' => "Tổng hợp các kỹ thuật thuật toán trọng tâm dành cho sinh viên CNTT và kỹ sư phỏng vấn:\n- Độ phức tạp thời gian Big-O\n- Cây nhị phân tìm kiếm, Red-Black Tree, Trie, Heap, Graph\n- Quy hoạch động (Dynamic Programming), Backtracking\n- Hướng dẫn giải 100 bài toán LeetCode Medium/Hard.",
                'cover_image' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => false,
                'view_count' => 1150,
                'download_count' => 380,
                'files' => [
                    [
                        'name' => 'Giao-trinh-CTDL-va-Giai-thuat-Chuan.pdf',
                        'type' => 'pdf',
                        'size' => 11200000,
                        'downloads' => 240,
                        'dummy_content' => 'Giao trinh CTDL & Giai thuat nang cao.',
                    ],
                    [
                        'name' => 'Tong-hop-100-bai-toan-Leetcode-loi-giai.docx',
                        'type' => 'docx',
                        'size' => 3400000,
                        'downloads' => 140,
                        'dummy_content' => '100 bai toan thuat toan kinh dien.',
                    ],
                ],
            ],
            [
                'title' => 'Source Code Mẫu: Hệ thống Thương mại điện tử E-Commerce Đa kênh',
                'category' => 'Source Code & Đồ án mẫu',
                'description' => "Đồ án mẫu hoàn chỉnh thích hợp làm đồ án tốt nghiệp hoặc tham khảo cấu trúc dự án doanh nghiệp:\n- Giỏ hàng đa sản phẩm, quản lý kho tồn, mã giảm giá voucher\n- Cổng thanh toán trực tuyến (VNPay, MoMo, VietQR)\n- Quản trị viên Dashboard thống kê doanh thu và báo cáo PDF\n- Hệ thống thông báo đơn hàng realtime.",
                'cover_image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'view_count' => 2100,
                'download_count' => 740,
                'files' => [
                    [
                        'name' => 'SourceCode-Ecommerce-Full-Laravel-Tailwind.zip',
                        'type' => 'zip',
                        'size' => 38500000,
                        'downloads' => 450,
                        'dummy_content' => 'Source code Ecommerce Fullstack Laravel.',
                    ],
                    [
                        'name' => 'Bao-cao-Do-an-He-thong-Thuong-mai-dien-tu.docx',
                        'type' => 'docx',
                        'size' => 4600000,
                        'downloads' => 180,
                        'dummy_content' => 'Bao cao thuyet minh do an E-commerce.',
                    ],
                    [
                        'name' => 'So-do-Database-ERD-va-API-Documentation.pdf',
                        'type' => 'pdf',
                        'size' => 2800000,
                        'downloads' => 110,
                        'dummy_content' => 'So do thiet ke CSDL ERD & API Docs.',
                    ],
                ],
            ],
            [
                'title' => 'Giáo trình Lập trình Di động Đa nền tảng với Flutter & Dart (2026)',
                'category' => 'Lập trình di động',
                'description' => "Khóa tài liệu thực chiến phát triển ứng dụng di động iOS & Android bằng 1 codebase duy nhất:\n- Widget tree, Layout căn bản và Custom UI\n- State Management (Bloc Pattern & Riverpod)\n- Tích hợp REST API, Offline Cache với Hive / SQLite\n- Đóng gói ứng dụng và phát hành lên Google Play Store & Apple App Store.",
                'cover_image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1000&auto=format&fit=crop&q=80',
                'is_featured' => false,
                'view_count' => 840,
                'download_count' => 290,
                'files' => [
                    [
                        'name' => 'Giao-trinh-Flutter-Dart-Cap-nhat-2026.pdf',
                        'type' => 'pdf',
                        'size' => 14300000,
                        'downloads' => 190,
                        'dummy_content' => 'Giao trinh Flutter Dart 2026.',
                    ],
                    [
                        'name' => 'Slide-Bai-Giang-State-Management-Bloc.pptx',
                        'type' => 'pptx',
                        'size' => 11200000,
                        'downloads' => 100,
                        'dummy_content' => 'Slide quan ly trang thai Flutter Bloc.',
                    ],
                ],
            ],
        ];

        foreach ($sampleDocs as $index => $docData) {
            $author = $instructors->get($index % $instructors->count());

            // Tạo Document
            $slug = Str::slug($docData['title']);
            $existing = Document::where('slug', $slug)->first();
            if ($existing) {
                continue;
            }

            $document = Document::create([
                'user_id' => $author->id,
                'title' => $docData['title'],
                'slug' => $slug,
                'category' => $docData['category'],
                'description' => $docData['description'],
                'cover_image' => $docData['cover_image'],
                'view_count' => $docData['view_count'],
                'download_count' => $docData['download_count'],
                'is_featured' => $docData['is_featured'],
            ]);

            // Tạo các file đính kèm thực tế trên disk
            foreach ($docData['files'] as $fileData) {
                $dirPath = 'documents/'.$document->id;
                $storedFileName = Str::random(20).'.'.$fileData['type'];
                $storedPath = $dirPath.'/'.$storedFileName;

                // Ghi nội dung mẫu vào storage disk public
                Storage::disk('public')->put($storedPath, $fileData['dummy_content']);

                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_name' => $fileData['name'],
                    'file_path' => $storedPath,
                    'file_size' => $fileData['size'],
                    'file_type' => $fileData['type'],
                    'download_count' => $fileData['downloads'],
                ]);
            }
        }
    }
}

