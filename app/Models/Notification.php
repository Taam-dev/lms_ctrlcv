<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'target_url',
        'read_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    /**
     * Người dùng nhận thông báo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kiểm tra thông báo đã đọc chưa
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Scope lọc thông báo chưa đọc
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Gửi thông báo đến một hoặc nhiều người dùng
     *
     * @param  int|User|iterable<int|User>  $recipients
     */
    public static function send(
        int|User|iterable $recipients,
        string $title,
        string $message,
        string $targetUrl,
        string $type = 'general'
    ): void {
        $userIds = [];

        if ($recipients instanceof User) {
            $userIds = [$recipients->id];
        } elseif (is_int($recipients) || is_numeric($recipients)) {
            $userIds = [(int) $recipients];
        } elseif (is_iterable($recipients)) {
            foreach ($recipients as $recipient) {
                if ($recipient instanceof User) {
                    $userIds[] = $recipient->id;
                } elseif (is_int($recipient) || is_numeric($recipient)) {
                    $userIds[] = (int) $recipient;
                }
            }
        }

        $userIds = array_unique(array_filter($userIds));
        if (empty($userIds)) {
            return;
        }

        $now = now();
        $records = [];
        foreach ($userIds as $userId) {
            $records[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'target_url' => $targetUrl,
                'read_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        static::insert($records);
    }
}
