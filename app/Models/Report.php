<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'details',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Đối tượng bị báo cáo (User hoặc CourseReview)
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Người thực hiện báo cáo
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Admin xử lý báo cáo
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Nhãn hiển thị lý do tiếng Việt
     */
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'avatar_inappropriate' => 'Avatar không phù hợp',
            'name_inappropriate' => 'Tên không phù hợp',
            'harassment' => 'Hành vi xúc phạm / Quấy rối',
            'negative_comment' => 'Bình luận tiêu cực / Thô tục',
            'spam' => 'Spam / Quảng cáo rác',
            'misinformation' => 'Thông tin sai lệch / Lừa đảo',
            'other' => 'Lý do khác',
            default => $this->reason,
        };
    }

    /**
     * Nhãn phân loại đối tượng báo cáo
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->reportable_type === User::class || $this->reportable_type === 'user') {
            return 'Tài khoản người dùng';
        }

        return 'Bình luận / Đánh giá';
    }

    /**
     * Nhãn trạng thái báo cáo
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xử lý',
            'resolved' => 'Đã xử lý',
            'dismissed' => 'Đã bác bỏ',
            default => $this->status,
        };
    }
}
