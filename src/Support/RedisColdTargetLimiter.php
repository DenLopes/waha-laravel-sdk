<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;
use Illuminate\Redis\Connections\PhpRedisConnection;

/**
 * Redis-backed unique-target {@see ColdTargetLimiter}.
 */
final class RedisColdTargetLimiter implements ColdTargetLimiter
{
    private const LUA = <<<'LUA'
        local key = KEYS[1]
        local now = tonumber(ARGV[1])
        local window = tonumber(ARGV[2])
        local max_unique = tonumber(ARGV[3])
        local chat_id = ARGV[4]
        local cutoff = now - (window * 1000)

        redis.call('ZREMRANGEBYSCORE', key, '-inf', cutoff)

        local score = redis.call('ZSCORE', key, chat_id)
        if score then
            redis.call('ZADD', key, now, chat_id)
            redis.call('EXPIRE', key, window + 60)
            return {1, 0}
        end

        local count = redis.call('ZCARD', key)
        if count >= max_unique then
            local oldest = redis.call('ZRANGE', key, 0, 0, 'WITHSCORES')
            local available = 0
            if oldest[2] then
                available = math.max(0, math.ceil((tonumber(oldest[2]) + (window * 1000)) - now))
            end
            return {0, available}
        end

        redis.call('ZADD', key, now, chat_id)
        redis.call('EXPIRE', key, window + 60)
        return {1, 0}
        LUA;

    public function __construct(
        private readonly PhpRedisConnection $redis,
        private readonly string $prefix = 'waha:limiter:',
    ) {}

    public function acquire(string $sessionName, string $chatId, int $maxUniqueTargets, int $windowSeconds): void
    {
        if ($maxUniqueTargets <= 0 || $windowSeconds <= 0) {
            return;
        }

        $key = $this->prefix.$sessionName.':cold:unique_targets';
        $now = (int) (microtime(true) * 1000);

        $result = $this->redis->eval(self::LUA, 1, $key, (string) $now, (string) $windowSeconds, (string) $maxUniqueTargets, $chatId);

        $status = (int) ($result[0] ?? 0);
        $availableMs = (int) ($result[1] ?? 0);

        if ($status === 1) {
            return;
        }

        $availableInSeconds = (int) ceil($availableMs / 1000);

        throw new ColdFanoutThrottledException(
            message: sprintf(
                'Session %s reached its limit of %d unique cold target(s) per %d second(s).',
                $sessionName,
                $maxUniqueTargets,
                $windowSeconds,
            ),
            context: [
                'session'              => $sessionName,
                'max_unique_targets'   => $maxUniqueTargets,
                'window_seconds'       => $windowSeconds,
                'available_in_seconds' => $availableInSeconds,
            ],
            session: $sessionName,
            maxUniqueTargets: $maxUniqueTargets,
            windowSeconds: $windowSeconds,
            availableInSeconds: $availableInSeconds,
        );
    }
}
