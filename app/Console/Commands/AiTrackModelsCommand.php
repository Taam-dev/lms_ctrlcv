<?php

namespace App\Console\Commands;

use App\Services\Ai\AiClientManager;
use App\Services\Ai\AiModelTracker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ai:track-models {--refresh : Làm mới bộ nhớ đệm và kết nối trực tiếp đến API} {--reset-cooldown : Đặt lại trạng thái cooldown của các API backup}')]
#[Description('Theo dõi và kiểm tra các model AI đang hoạt động từ API của các Provider chính và backup')]
class AiTrackModelsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AiClientManager $clientManager, AiModelTracker $modelTracker): int
    {
        $refresh = (bool) $this->option('refresh');
        $resetCooldown = (bool) $this->option('reset-cooldown');

        $this->info('===========================================================');
        $this->info('  Ctrl C+V LMS - Hệ Thống Dynamic AI Model Tracking & Backup');
        $this->info('===========================================================');

        if ($resetCooldown) {
            $clientManager->resetAllCooldowns();
            $this->info('✓ Đã đặt lại trạng thái hạ nhiệt (cooldown) của tất cả API.');
        }

        $targets = $clientManager->getTargets();

        if (empty($targets)) {
            $this->warn('⚠ Chưa có API Provider nào được cấu hình trong .env (Kiểm tra AI_PROVIDER_1_URL và AI_PROVIDER_1_KEY).');

            return 0;
        }

        $rows = [];

        foreach ($targets as $target) {
            $inCooldown = $clientManager->isTargetInCooldown($target['id']);
            $maskedKey = ! empty($target['api_key'])
                ? (substr($target['api_key'], 0, 7).'...'.substr($target['api_key'], -4))
                : '(Không dùng key)';

            $models = $modelTracker->fetchActiveModels($target['base_url'], $target['api_key'], $refresh);
            $selected = $clientManager->resolveActiveModel($target['base_url'], $target['api_key'], $target['model']);

            $statusText = $inCooldown
                ? 'Hết token (Đang Cooldown)'
                : (! empty($models) ? 'Online (Hoạt động)' : 'Offline / Chưa kết nối');

            $rows[] = [
                $target['name'],
                $target['is_backup'] ? 'Backup' : 'Chính (Primary)',
                $target['base_url'],
                $maskedKey,
                $statusText,
                count($models),
                $selected,
            ];
        }

        $this->table(
            ['Nhà cung cấp', 'Vai trò', 'Base URL', 'API Key', 'Trạng thái', 'Model hoạt động', 'Model đang dùng'],
            $rows
        );

        $this->line('');
        $this->info('✓ Gợi ý: Khi API Chính hết token, hệ thống sẽ tự động chuyển đổi sang API Backup tiếp theo.');
        $this->info('✓ Tham số --refresh: `php artisan ai:track-models --refresh` để cập nhật lại danh sách model mới nhất.');

        return 0;
    }
}
