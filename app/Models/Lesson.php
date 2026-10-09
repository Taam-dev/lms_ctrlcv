<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    // lưu thông tin bài học: thuộc khóa nào, tiêu đề, slug chuẩn SEO, dạng video hay text, nội dung, thứ tự bài
    protected $fillable = ['course_id', 'title', 'slug', 'content_type', 'content', 'order_number'];

    /**
     * Tự động tạo slug chuẩn SEO không dấu theo tiêu đề bài học
     */
    protected static function booted(): void
    {
        static::saving(function (Lesson $lesson) {
            if (empty($lesson->slug) || ($lesson->isDirty('title') && ! $lesson->isDirty('slug'))) {
                $baseSlug = Str::slug($lesson->title) ?: 'bai-hoc';
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('course_id', $lesson->course_id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $lesson->id ?? 0)
                    ->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $lesson->slug = $slug;
            }
        });
    }

    /**
     * Lấy URL chuẩn SEO cho bài giảng
     */
    public function getUrlAttribute(): string
    {
        $courseParam = $this->course?->slug ?: $this->course_id;
        $lessonParam = $this->slug ?: $this->id;

        return route('student.lessons.show', [$courseParam, $lessonParam]);
    }

    // bài giảng này nằm trong khóa học nào
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // các lượt hoàn thành của bài học này
    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function isCompletedBy(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            return false;
        }

        return $this->completions()->where('student_id', $userId)->exists();
    }
}
