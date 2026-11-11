<?php

namespace App\Services\GoldQuest;

use App\Events\GoldQuest\LeaderboardUpdated;
use App\Events\GoldQuest\PlayerTargeted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Predis\Client;
use Throwable;

class GoldQuestEngine
{
    protected ?bool $redisAvailable = null;

    /**
     * Kiểm tra xem Redis có sẵn sàng kết nối hay không
     */
    public function isRedisAvailable(): bool
    {
        if ($this->redisAvailable !== null) {
            return $this->redisAvailable;
        }

        try {
            if (! extension_loaded('redis') && ! class_exists(Client::class)) {
                $this->redisAvailable = false;

                return false;
            }

            Redis::ping();
            $this->redisAvailable = true;

            return true;
        } catch (Throwable $e) {
            $this->redisAvailable = false;

            return false;
        }
    }

    public static function zsetKey(string $pin): string
    {
        return "gold_quest:room:{$pin}:zset";
    }

    public static function grantKey(string $pin, string $playerId): string
    {
        return "gold_quest:room:{$pin}:grant:{$playerId}";
    }

    public static function pendingKey(string $pin, string $playerId): string
    {
        return "gold_quest:room:{$pin}:pending:{$playerId}";
    }

    public static function playerInfoKey(string $pin, string $playerId): string
    {
        return "gold_quest:room:{$pin}:player:{$playerId}";
    }

    /**
     * Khởi tạo người chơi trong ZSET với điểm 0
     */
    public function registerPlayer(string $pin, string $playerId, array $metadata): void
    {
        if ($this->isRedisAvailable()) {
            Redis::zadd(self::zsetKey($pin), 0, $playerId);
            Redis::hmset(self::playerInfoKey($pin, $playerId), $metadata);
        } else {
            $scores = Cache::get(self::zsetKey($pin), []);
            $scores[$playerId] = 0;
            Cache::put(self::zsetKey($pin), $scores, 7200);
            Cache::put(self::playerInfoKey($pin, $playerId), $metadata, 7200);
        }
    }

    /**
     * Lấy thông tin player metadata
     *
     * @return array<string, mixed>
     */
    public function getPlayerInfo(string $pin, string $playerId): array
    {
        if ($this->isRedisAvailable()) {
            return Redis::hgetall(self::playerInfoKey($pin, $playerId)) ?: [];
        }

        return Cache::get(self::playerInfoKey($pin, $playerId), []);
    }

    /**
     * Cập nhật thông tin thống kê người chơi (correct, wrong, chests)
     */
    public function incrementPlayerStat(string $pin, string $playerId, string $field, int $by = 1): int
    {
        if ($this->isRedisAvailable()) {
            return (int) Redis::hincrby(self::playerInfoKey($pin, $playerId), $field, $by);
        }

        $info = $this->getPlayerInfo($pin, $playerId);
        $current = (int) ($info[$field] ?? 0);
        $new = $current + $by;
        $info[$field] = $new;
        Cache::put(self::playerInfoKey($pin, $playerId), $info, 7200);

        return $new;
    }

    /**
     * Cấp Single-Use Chest Grant Token
     */
    public function issueChestGrant(string $pin, string $playerId, string $token, int $ttlSeconds = 45): void
    {
        $key = self::grantKey($pin, $playerId);
        if ($this->isRedisAvailable()) {
            Redis::setex($key, $ttlSeconds, $token);
        } else {
            Cache::put($key, $token, $ttlSeconds);
        }
    }

    /**
     * Xác thực và Hủy Chest Token (Single-Use Token) nguyên tử
     */
    public function consumeChestGrant(string $pin, string $playerId, string $token): bool
    {
        $key = self::grantKey($pin, $playerId);

        if ($this->isRedisAvailable()) {
            $lua = <<<'LUA'
                if redis.call('GET', KEYS[1]) == ARGV[1] then
                    redis.call('DEL', KEYS[1])
                    return 1
                else
                    return 0
                end
            LUA;

            $result = Redis::eval($lua, 1, $key, $token);

            return (int) $result === 1;
        }

        $cached = Cache::get($key);
        if ($cached && $cached === $token) {
            Cache::forget($key);

            return true;
        }

        return false;
    }

    /**
     * Lưu trạng thái chờ chọn nạn nhân (STEAL / SWAP)
     */
    public function setPendingAction(string $pin, string $playerId, string $actionType, int $ttlSeconds = 15): void
    {
        $key = self::pendingKey($pin, $playerId);
        if ($this->isRedisAvailable()) {
            Redis::setex($key, $ttlSeconds, $actionType);
        } else {
            Cache::put($key, $actionType, $ttlSeconds);
        }
    }

    /**
     * Lấy và xóa pending action
     */
    public function consumePendingAction(string $pin, string $playerId): ?string
    {
        $key = self::pendingKey($pin, $playerId);

        if ($this->isRedisAvailable()) {
            $lua = <<<'LUA'
                local action = redis.call('GET', KEYS[1])
                if action then
                    redis.call('DEL', KEYS[1])
                    return action
                else
                    return nil
                end
            LUA;

            $action = Redis::eval($lua, 1, $key);

            return $action ?: null;
        }

        $action = Cache::get($key);
        if ($action) {
            Cache::forget($key);

            return (string) $action;
        }

        return null;
    }

    /**
     * Cộng / trừ điểm vàng của người chơi trên Redis ZSET
     */
    public function adjustGold(string $pin, string $playerId, int $delta): int
    {
        $zset = self::zsetKey($pin);

        if ($this->isRedisAvailable()) {
            $current = (int) Redis::zscore($zset, $playerId);
            if ($current + $delta < 0) {
                $delta = -$current;
            }

            $newGold = (int) Redis::zincrby($zset, $delta, $playerId);
            $this->throttleBroadcastLeaderboard($pin);

            return $newGold;
        }

        $scores = Cache::get($zset, []);
        $current = (int) ($scores[$playerId] ?? 0);
        $newGold = max(0, $current + $delta);
        $scores[$playerId] = $newGold;
        Cache::put($zset, $scores, 7200);

        $this->throttleBroadcastLeaderboard($pin);

        return $newGold;
    }

    /**
     * Lấy số vàng hiện tại của người chơi
     */
    public function getGold(string $pin, string $playerId): int
    {
        if ($this->isRedisAvailable()) {
            return (int) Redis::zscore(self::zsetKey($pin), $playerId);
        }

        $scores = Cache::get(self::zsetKey($pin), []);

        return (int) ($scores[$playerId] ?? 0);
    }

    /**
     * Lấy Thứ hạng hiện tại (1-based index) qua ZREVRANK
     */
    public function getRank(string $pin, string $playerId): int
    {
        if ($this->isRedisAvailable()) {
            $rank = Redis::zrevrank(self::zsetKey($pin), $playerId);

            return $rank !== null ? ((int) $rank + 1) : 1;
        }

        $scores = Cache::get(self::zsetKey($pin), []);
        arsort($scores);
        $rank = 1;
        foreach (array_keys($scores) as $pid) {
            if ((string) $pid === (string) $playerId) {
                return $rank;
            }
            $rank++;
        }

        return 1;
    }

    /**
     * Thực hiện CƯỚP VÀNG (Steal) nguyên tử
     *
     * @return array{stolen_amount: int, thief_gold: int, victim_gold: int}
     */
    public function executeSteal(string $pin, string $thiefId, string $victimId, float $stealRate = 0.20): array
    {
        $zset = self::zsetKey($pin);
        $thiefInfo = $this->getPlayerInfo($pin, $thiefId);
        $thiefName = $thiefInfo['nickname'] ?? 'Kẻ bí ẩn';

        if ($this->isRedisAvailable()) {
            $lua = <<<'LUA'
                local victim_gold = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[1]) or 0)
                if victim_gold <= 0 then
                    local thief_gold = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[3]) or 0)
                    return {0, victim_gold, thief_gold}
                end

                local rate = tonumber(ARGV[2])
                local stolen = math.floor(victim_gold * rate)
                if stolen < 1 then stolen = 1 end
                if stolen > victim_gold then stolen = victim_gold end

                redis.call('ZINCRBY', KEYS[1], -stolen, ARGV[1])
                redis.call('ZINCRBY', KEYS[1], stolen, ARGV[3])

                local victim_new = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[1]) or 0)
                local thief_new = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[3]) or 0)
                return {stolen, victim_new, thief_new}
            LUA;

            $res = Redis::eval($lua, 1, $zset, $victimId, (string) $stealRate, $thiefId);
            $stolen = (int) ($res[0] ?? 0);
            $victimNew = (int) ($res[1] ?? 0);
            $thiefNew = (int) ($res[2] ?? 0);
        } else {
            $scores = Cache::get($zset, []);
            $victimGold = (int) ($scores[$victimId] ?? 0);
            $thiefGold = (int) ($scores[$thiefId] ?? 0);

            if ($victimGold <= 0) {
                $stolen = 0;
            } else {
                $stolen = max(1, min($victimGold, (int) floor($victimGold * $stealRate)));
                $victimGold -= $stolen;
                $thiefGold += $stolen;
                $scores[$victimId] = $victimGold;
                $scores[$thiefId] = $thiefGold;
                Cache::put($zset, $scores, 7200);
            }

            $victimNew = $victimGold;
            $thiefNew = $thiefGold;
        }

        // Bắn Socket Event cho nạn nhân kích hoạt hiệu ứng Rung lắc màn hình
        broadcast(new PlayerTargeted(
            roomPin: $pin,
            targetPlayerId: $victimId,
            actorPlayerId: $thiefId,
            actorNickname: $thiefName,
            actionType: 'STEAL',
            amount: $stolen
        ));

        $this->throttleBroadcastLeaderboard($pin);

        return [
            'stolen_amount' => $stolen,
            'thief_gold' => $thiefNew,
            'victim_gold' => $victimNew,
        ];
    }

    /**
     * Thực hiện TRÁO ĐỔI VỊ TRÍ / VÀNG (Swap) nguyên tử
     *
     * @return array{my_new_gold: int, victim_new_gold: int}
     */
    public function executeSwap(string $pin, string $actorId, string $victimId): array
    {
        $zset = self::zsetKey($pin);
        $actorInfo = $this->getPlayerInfo($pin, $actorId);
        $actorName = $actorInfo['nickname'] ?? 'Kẻ bí ẩn';

        if ($this->isRedisAvailable()) {
            $lua = <<<'LUA'
                local gold_a = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[1]) or 0)
                local gold_b = tonumber(redis.call('ZSCORE', KEYS[1], ARGV[2]) or 0)

                redis.call('ZADD', KEYS[1], gold_b, ARGV[1])
                redis.call('ZADD', KEYS[1], gold_a, ARGV[2])

                return {gold_b, gold_a}
            LUA;

            $res = Redis::eval($lua, 1, $zset, $actorId, $victimId);
            $myNewGold = (int) ($res[0] ?? 0);
            $victimNewGold = (int) ($res[1] ?? 0);
        } else {
            $scores = Cache::get($zset, []);
            $goldA = (int) ($scores[$actorId] ?? 0);
            $goldB = (int) ($scores[$victimId] ?? 0);

            $scores[$actorId] = $goldB;
            $scores[$victimId] = $goldA;
            Cache::put($zset, $scores, 7200);

            $myNewGold = $goldB;
            $victimNewGold = $goldA;
        }

        // Bắn Socket Event cảnh báo tráo đổi
        broadcast(new PlayerTargeted(
            roomPin: $pin,
            targetPlayerId: $victimId,
            actorPlayerId: $actorId,
            actorNickname: $actorName,
            actionType: 'SWAP',
            amount: 0
        ));

        $this->throttleBroadcastLeaderboard($pin);

        return [
            'my_new_gold' => $myNewGold,
            'victim_new_gold' => $victimNewGold,
        ];
    }

    /**
     * Lấy Top Leaderboard
     *
     * @return array<int, array{rank: int, player_id: string, nickname: string, blook: string, gold: int}>
     */
    public function getLeaderboard(string $pin, int $limit = 10): array
    {
        $zset = self::zsetKey($pin);
        $leaderboard = [];
        $rank = 1;

        if ($this->isRedisAvailable()) {
            $raw = Redis::zrevrange($zset, 0, $limit - 1, 'WITHSCORES');
            foreach ($raw as $playerId => $gold) {
                $info = $this->getPlayerInfo($pin, (string) $playerId);
                $leaderboard[] = [
                    'rank' => $rank++,
                    'player_id' => (string) $playerId,
                    'nickname' => $info['nickname'] ?? "Player {$playerId}",
                    'blook' => $info['blook'] ?? 'dragon',
                    'gold' => (int) $gold,
                ];
            }
        } else {
            $scores = Cache::get($zset, []);
            arsort($scores);
            $sliced = array_slice($scores, 0, $limit, true);

            foreach ($sliced as $playerId => $gold) {
                $info = $this->getPlayerInfo($pin, (string) $playerId);
                $leaderboard[] = [
                    'rank' => $rank++,
                    'player_id' => (string) $playerId,
                    'nickname' => $info['nickname'] ?? "Player {$playerId}",
                    'blook' => $info['blook'] ?? 'dragon',
                    'gold' => (int) $gold,
                ];
            }
        }

        return $leaderboard;
    }

    /**
     * Lấy toàn bộ người chơi kèm điểm để sync sang DB khi kết thúc game
     *
     * @return array<string, int>
     */
    public function getAllScores(string $pin): array
    {
        $zset = self::zsetKey($pin);

        if ($this->isRedisAvailable()) {
            $raw = Redis::zrevrange($zset, 0, -1, 'WITHSCORES');
            $scores = [];
            foreach ($raw as $pid => $gold) {
                $scores[(string) $pid] = (int) $gold;
            }

            return $scores;
        }

        $scores = Cache::get($zset, []);
        arsort($scores);

        return $scores;
    }

    /**
     * Chống nghẽn Socket (Throttle Leaderboard Broadcast 1.5 - 2s/lần)
     */
    public function throttleBroadcastLeaderboard(string $pin): void
    {
        $lockKey = "gold_quest:room:{$pin}:lb_lock";

        if ($this->isRedisAvailable()) {
            $acquired = Redis::set($lockKey, '1', 'EX', 2, 'NX');
            if ($acquired) {
                $topList = $this->getLeaderboard($pin, 10);
                broadcast(new LeaderboardUpdated($pin, $topList));
            }
        } else {
            $locked = Cache::get($lockKey);
            if (! $locked) {
                Cache::put($lockKey, '1', 2);
                $topList = $this->getLeaderboard($pin, 10);
                broadcast(new LeaderboardUpdated($pin, $topList));
            }
        }
    }
}
