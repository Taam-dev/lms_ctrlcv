<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    // mấy trường này cho phép nhập từ form
    protected $fillable = ['teacher_id', 'title', 'slug', 'description', 'status', 'thumbnail'];

    /**
     * Tự động tạo slug chuẩn SEO không dấu khi thêm hoặc cập nhật khóa học
     */
    protected static function booted(): void
    {
        static::saving(function (Course $course) {
            if (empty($course->slug) || ($course->isDirty('title') && ! $course->isDirty('slug'))) {
                $baseSlug = Str::slug($course->title) ?: 'khoa-hoc';
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->where('id', '!=', $course->id ?? 0)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $course->slug = $slug;
            }
        });
    }

    /**
     * Lấy URL chuẩn SEO cho khóa học
     */
    public function getUrlAttribute(): string
    {
        return route('courses.show', ['slug' => $this->slug ?: $this->id]);
    }

    // khóa học này là do ông giảng viên nào tạo ra
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // 1 khóa học thì có nhiều bài giảng bên trong
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    // khóa học này có bao nhiêu đứa đăng ký
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // các bài kiểm tra trắc nghiệm của khóa học
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function approvedQuizzes()
    {
        return $this->hasMany(Quiz::class)->where('status', 'approved');
    }

    // các lượt hoàn thành bài học trong khóa này
    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * Tính % tiến độ hoàn thành khóa học của một học viên
     */
    public function progressForUser(?int $userId = null): int
    {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            return 0;
        }

        $totalLessons = $this->lessons_count ?? $this->lessons()->count();
        if ($totalLessons === 0) {
            return 0;
        }

        $completedCount = $this->lessonCompletions()
            ->where('student_id', $userId)
            ->count();

        return (int) round(($completedCount / $totalLessons) * 100);
    }

    public function getIsEnrolledAttribute(): bool
    {
        if (array_key_exists('is_enrolled', $this->attributes)) {
            return (bool) $this->attributes['is_enrolled'];
        }

        if (! auth()->check()) {
            return false;
        }

        return $this->enrollments()->where('student_id', auth()->id())->exists();
    }

    public function getProgressPercentAttribute(): int
    {
        if (array_key_exists('progress_percent', $this->attributes)) {
            return (int) $this->attributes['progress_percent'];
        }

        return $this->progressForUser(auth()->id());
    }

    public function getCompletedLessonsCountAttribute(): int
    {
        if (array_key_exists('completed_lessons_count', $this->attributes)) {
            return (int) $this->attributes['completed_lessons_count'];
        }

        if (! auth()->check()) {
            return 0;
        }

        return $this->lessonCompletions()
            ->where('student_id', auth()->id())
            ->count();
    }

    /**
     * Đường dẫn ảnh banner tùy chỉnh hoặc mặc định cho khóa học
     */
    public function getBannerUrlAttribute(): string
    {
        if (! empty($this->thumbnail)) {
            if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
                return $this->thumbnail;
            }

            if (str_starts_with($this->thumbnail, 'images/')) {
                return asset($this->thumbnail);
            }

            if (str_starts_with($this->thumbnail, 'storage/')) {
                return asset($this->thumbnail);
            }

            return asset('storage/'.ltrim($this->thumbnail, '/'));
        }

        $banners = [
            asset('images/course-banner-1.jpg'),
            asset('images/course-banner-2.jpg'),
            asset('images/course-banner-3.jpg'),
        ];

        $index = abs($this->id ?: 1) % count($banners);

        return $banners[$index];
    }
}
