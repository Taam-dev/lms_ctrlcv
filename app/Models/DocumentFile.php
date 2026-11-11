<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'download_count',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 1, ',', '.').' '.$units[$power];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.files.download', $this->id);
    }

    /**
     * Trả về thông tin màu sắc và định danh icon cho file
     *
     * @return array{badge: string, label: string, color: string, bg: string, border: string}
     */
    public function getFileTypeStyleAttribute(): array
    {
        $ext = strtolower($this->file_type ?: pathinfo($this->file_name, PATHINFO_EXTENSION));

        $res = match ($ext) {
            'pdf' => [
                'badge' => 'PDF',
                'color' => 'text-rose-400',
                'bg' => 'bg-rose-500/10',
                'border' => 'border-rose-500/30',
            ],
            'doc', 'docx' => [
                'badge' => 'DOCX',
                'color' => 'text-sky-400',
                'bg' => 'bg-sky-500/10',
                'border' => 'border-sky-500/30',
            ],
            'ppt', 'pptx' => [
                'badge' => 'PPTX',
                'color' => 'text-amber-400',
                'bg' => 'bg-amber-500/10',
                'border' => 'border-amber-500/30',
            ],
            'xls', 'xlsx', 'csv' => [
                'badge' => 'EXCEL',
                'color' => 'text-emerald-400',
                'bg' => 'bg-emerald-500/10',
                'border' => 'border-emerald-500/30',
            ],
            'zip', 'rar', '7z', 'tar', 'gz' => [
                'badge' => 'ARCHIVE',
                'color' => 'text-purple-400',
                'bg' => 'bg-purple-500/10',
                'border' => 'border-purple-500/30',
            ],
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => [
                'badge' => 'IMAGE',
                'color' => 'text-pink-400',
                'bg' => 'bg-pink-500/10',
                'border' => 'border-pink-500/30',
            ],
            'js', 'ts', 'php', 'py', 'java', 'cpp', 'c', 'cs', 'go', 'html', 'css', 'json', 'sql' => [
                'badge' => 'CODE',
                'color' => 'text-indigo-400',
                'bg' => 'bg-indigo-500/10',
                'border' => 'border-indigo-500/30',
            ],
            default => [
                'badge' => strtoupper($ext ?: 'FILE'),
                'color' => 'text-zinc-400',
                'bg' => 'bg-zinc-800',
                'border' => 'border-zinc-700',
            ],
        };

        $res['label'] = $res['badge'];

        return $res;
    }
}
