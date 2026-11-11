<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category',
        'description',
        'cover_image',
        'view_count',
        'download_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'download_count' => 'integer',
        ];
    }

    /**
     * Tự động tạo slug chuẩn SEO không dấu khi thêm hoặc cập nhật tài liệu
     */
    protected static function booted(): void
    {
        static::saving(function (Document $doc) {
            if (empty($doc->slug) || ($doc->isDirty('title') && ! $doc->isDirty('slug'))) {
                $baseSlug = Str::slug($doc->title) ?: 'tai-lieu';
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->where('id', '!=', $doc->id ?? 0)->exists()) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $doc->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocumentFile::class, 'document_id');
    }

    public function getUrlAttribute(): string
    {
        return route('documents.show', $this->slug ?: $this->id);
    }

    public function getCoverImageUrlAttribute(): string
    {
        if (empty($this->cover_image)) {
            return 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800&auto=format&fit=crop&q=80';
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset('storage/'.$this->cover_image);
    }

    public function getTotalSizeFormattedAttribute(): string
    {
        $bytes = $this->files->sum('file_size');
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 1, ',', '.').' '.$units[$power];
    }

    public function getFormattedDownloadCountAttribute(): string
    {
        return number_format($this->download_count, 0, ',', '.');
    }

    public function getFormattedViewCountAttribute(): string
    {
        return number_format($this->view_count, 0, ',', '.');
    }

    public function canEditBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $this->user_id;
    }

    public function canDeleteBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->id === $this->user_id;
    }
}

