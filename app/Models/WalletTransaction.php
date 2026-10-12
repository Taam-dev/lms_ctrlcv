<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'description',
        'reference_id',
        'payment_method',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'reference_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return '0 đ';
        }

        $prefix = $this->type === 'deposit' ? '+' : '-';

        return $prefix.number_format($this->amount, 0, ',', '.').' đ';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'deposit' => 'Nạp tiền vào ví',
            'course_purchase' => 'Mua khóa học',
            'refund' => 'Hoàn tiền',
            default => 'Giao dịch khác',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Thành công',
            'pending' => 'Đang chờ',
            'cancelled' => 'Đã hủy',
            default => $this->status,
        };
    }
}
