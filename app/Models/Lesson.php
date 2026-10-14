<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Phân tích và tạo thông tin embed video từ nội dung bài giảng
     * Hỗ trợ: Youtube, Facebook, Google Drive, Vimeo, Loom, file MP4/video trực tiếp, và iframe nhúng sẵn
     *
     * @return array{type: string, url: string, original_url: string}|null
     */
    public function getVideoEmbedInfoAttribute(): ?array
    {
        if ($this->content_type !== 'video' || empty($this->content)) {
            return null;
        }

        $raw = trim($this->content);

        // dán nguyên thẻ iframe
        if (str_contains($raw, '<iframe') && preg_match('/src=["\']([^"\']+)["\']/i', $raw, $iframeMatches)) {
            return [
                'type' => 'iframe',
                'url' => $iframeMatches[1],
                'original_url' => $iframeMatches[1],
            ];
        }

        // lấy url đầu tiên
        if (preg_match('/https?:\/\/[^\s"\'<>]+/i', $raw, $urlMatch)) {
            $url = $urlMatch[0];
        } else {
            $url = $raw;
        }

        // youtube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $ytMatches)) {
            return [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/embed/'.$ytMatches[1].'?rel=0',
                'original_url' => $url,
            ];
        }

        // tiktok
        if (preg_match('/(?:tiktok\.com\/(?:@[^\/]+\/video\/|v\/|embed\/v2\/|embed\/|player\/v1\/))(\d+)/i', $url, $ttMatches)) {
            return [
                'type' => 'tiktok',
                'url' => 'https://www.tiktok.com/player/v1/'.$ttMatches[1],
                'original_url' => $url,
            ];
        }

        if (str_contains($url, 'tiktok.com') && preg_match('/\/video\/(\d+)/i', $url, $ttMatches)) {
            return [
                'type' => 'tiktok',
                'url' => 'https://www.tiktok.com/player/v1/'.$ttMatches[1],
                'original_url' => $url,
            ];
        }

        if (preg_match('/(?:(?:vt|vm)\.tiktok\.com\/|tiktok\.com\/t\/)([a-zA-Z0-9_\-]+)/i', $url)) {
            $cacheKey = 'tt_resolve_'.md5($url);
            $resolvedId = Cache::remember($cacheKey, now()->addDays(7), function () use ($url) {
                try {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                    curl_exec($ch);
                    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    curl_close($ch);

                    if ($effectiveUrl && preg_match('/\/video\/(\d+)/i', $effectiveUrl, $m)) {
                        return $m[1];
                    }
                } catch (\Throwable $e) {
                    // Ignore error
                }

                return null;
            });

            if ($resolvedId) {
                return [
                    'type' => 'tiktok',
                    'url' => 'https://www.tiktok.com/player/v1/'.$resolvedId,
                    'original_url' => $url,
                ];
            }
        }

        // facebook
        if (preg_match('/(?:facebook\.com|fb\.watch|fb\.com)/i', $url)) {
            return [
                'type' => 'facebook',
                'url' => 'https://www.facebook.com/plugins/video.php?href='.urlencode($url).'&show_text=0&width=1280&t=0',
                'original_url' => $url,
            ];
        }

        // google drive
        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]+)/i', $url, $driveMatches)) {
            return [
                'type' => 'drive',
                'url' => 'https://drive.google.com/file/d/'.$driveMatches[1].'/preview',
                'original_url' => $url,
            ];
        }

        // vimeo
        if (preg_match('/(?:vimeo\.com\/)(?:video\/|channels\/[^\/]+\/|groups\/[^\/]+\/videos\/|album\/[^\/]+\/video\/|)(\d+)/i', $url, $vimeoMatches)) {
            return [
                'type' => 'vimeo',
                'url' => 'https://player.vimeo.com/video/'.$vimeoMatches[1],
                'original_url' => $url,
            ];
        }

        // loom
        if (preg_match('/loom\.com\/share\/([a-zA-Z0-9]+)/i', $url, $loomMatches)) {
            return [
                'type' => 'loom',
                'url' => 'https://www.loom.com/embed/'.$loomMatches[1],
                'original_url' => $url,
            ];
        }

        // file video trực tiếp
        if (preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $url) || str_starts_with($url, 'storage/') || str_starts_with($url, 'videos/')) {
            $directUrl = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))
                ? $url
                : asset(ltrim($url, '/'));

            return [
                'type' => 'direct',
                'url' => $directUrl,
                'original_url' => $directUrl,
            ];
        }

        // iframe embed
        if (str_contains($url, 'embed') && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://'))) {
            return [
                'type' => 'iframe',
                'url' => $url,
                'original_url' => $url,
            ];
        }

        return [
            'type' => 'unknown',
            'url' => $url,
            'original_url' => $url,
        ];
    }
}
