<?php

namespace App\Services\GoldQuest;

class LootTableService
{
    /**
     * Bảng tỷ lệ phần thưởng mở rương (Loot Table Weights)
     *
     * @return array<int, array{type: string, value: float|int, weight: int, label: string, icon: string}>
     */
    public static function getLootTable(bool $allowSteal = true, bool $allowSwap = true): array
    {
        $table = [
            ['type' => 'GOLD_FLAT', 'value' => 20,   'weight' => 25, 'label' => '+20 Vàng', 'icon' => '🪙'],
            ['type' => 'GOLD_FLAT', 'value' => 50,   'weight' => 20, 'label' => '+50 Vàng', 'icon' => '💰'],
            ['type' => 'GOLD_FLAT', 'value' => 100,  'weight' => 15, 'label' => '+100 Vàng', 'icon' => '💎'],
            ['type' => 'GOLD_FLAT', 'value' => 250,  'weight' => 8,  'label' => '+250 Vàng Siêu Khủng!', 'icon' => '👑'],
            ['type' => 'PERCENT_ADD', 'value' => 0.10, 'weight' => 8, 'label' => '+10% Tổng Vàng', 'icon' => '📈'],
            ['type' => 'PERCENT_ADD', 'value' => 0.25, 'weight' => 5, 'label' => '+25% Tổng Vàng!', 'icon' => '🚀'],
            ['type' => 'LOSE_PERCENT', 'value' => -0.20, 'weight' => 6, 'label' => 'Bẫy Gai! Mất -20% Vàng', 'icon' => '💀'],
            ['type' => 'NOTHING', 'value' => 0, 'weight' => 5, 'label' => 'Rương Rỗng (Không có gì)', 'icon' => '💨'],
        ];

        if ($allowSteal) {
            $table[] = ['type' => 'STEAL', 'value' => 0, 'weight' => 8, 'label' => 'CƯỚP VÀNG ĐỐI THỦ', 'icon' => '🥷'];
        }

        if ($allowSwap) {
            $table[] = ['type' => 'SWAP', 'value' => 0, 'weight' => 5, 'label' => 'HOÁN ĐỔI VỊ TRÍ & VÀNG', 'icon' => '🔄'];
        }

        return $table;
    }

    /**
     * Thuật toán bốc thăm ngẫu nhiên theo trọng số (Weighted Random Selection)
     *
     * @return array{type: string, value: float|int, weight: int, label: string, icon: string}
     */
    public static function roll(bool $allowSteal = true, bool $allowSwap = true): array
    {
        $lootTable = self::getLootTable($allowSteal, $allowSwap);
        $totalWeight = array_sum(array_column($lootTable, 'weight'));

        $randomWeight = random_int(1, $totalWeight);
        $currentWeight = 0;

        foreach ($lootTable as $item) {
            $currentWeight += $item['weight'];
            if ($randomWeight <= $currentWeight) {
                return $item;
            }
        }

        return $lootTable[0];
    }
}
