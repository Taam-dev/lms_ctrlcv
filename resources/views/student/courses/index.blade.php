<x-app-layout
    title="Tất cả khóa học trực tuyến | Ctrl C+V"
    meta-description="Khám phá kho khóa học lập trình, kỹ năng trực tuyến chất lượng cao tại Ctrl C+V. Học bài giảng miễn phí, luyện quizzes tự động chấm điểm và cấp chứng chỉ tiến độ."
    meta-keywords="tất cả khóa học, học online, lập trình laravel, quizzes trực tuyến, ctrl c+v lms"
    :canonical="route('courses.index')"
    :og-image="asset('images/hero-bg.jpg')"
    og-type="website">

    @push('schema')
    <!-- Schema.org BreadcrumbList JSON-LD -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Trang chủ',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Tất cả khóa học',
                'item' => route('courses.index'),
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @endpush

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-16">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-pink-500 mb-4">Thư viện học tập</p>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight mb-3">Tất cả khóa học</h1>
            <p class="text-neutral-300 max-w-xl text-sm sm:text-base leading-relaxed">
                Chọn khóa học phù hợp, đăng ký để bắt đầu xem bài giảng và làm bài thi trắc nghiệm.
                Hiện có <strong class="text-white">{{ $courses->count() }}</strong> khóa học đã được phê duyệt.
            </p>
        </div>
    </section>

    <section class="py-14 lg:py-16 min-h-[420px]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($courses->isEmpty())
                <div class="bg-white border border-pink-100 p-14 text-center">
                    <h3 class="font-black text-xl text-neutral-900 mb-2">Hiện chưa có khóa học nào được duyệt</h3>
                    <p class="text-sm text-neutral-500">Các bài giảng và khóa học mới sẽ sớm được giảng viên tải lên và kiểm duyệt.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
