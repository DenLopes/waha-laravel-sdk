<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use Illuminate\Redis\Connections\PhpRedisConnection;

/**
 * Redis-backed sliding-window {@see SessionRateLimiter}.
 */
final class RedisSessionRateLimiter implements SessionRateLimiter
{
    private const LUA = <<<'LUA'
        local key = KEYS[1]
        local now = tonumber(ARGV[1])
        local window = tonumber(ARGV[2])
        local max_limit = tonumber(ARGV[3])
        local member = ARGV[4]
        local cutoff = now - (window * 1000)

        redis.call('ZREMRANGEBYSCORE', key, '-inf', cutoff)
        local count = redis.call('ZCARD', key)

        if count >= max_limit then
            local oldest = redis.call('ZRANGE', key, 0, 0, 'WITHSCORES')
            local available = 0
            if oldest[2] then
                available = math.max(0, math.ceil((tonumber(oldest[2]) + (window * 1000)) - now))
            end
            return {0, available}
        end

        redis.call('ZADD', key, now, member)
        redis.call('EXPIRE', key, window + 60)
        return {1, 0}
        LUA;

    public function __construct(
        private readonly PhpRedisConnection $redis,
        private readonly string $prefix = 'waha:limiter:',
    ) {}

    public function hit(string $sessionName, ContactStage $stage, TierConfig $tier): void
    {
        $max = $tier->sessionMaxMessages;
        $window = $tier->sessionWindowSeconds;

        if ($max <= 0 || $window <= 0) {
            return;
        }

        $key = $this->prefix.$sessionName.':'.$stage->value.':messages';
        $now = (int) (microtime(true) * 1000);
        $member = $now.':'.bin2hex(random_bytes(8));

        $result = $this->redis->eval(self::LUA, 1, $key, (string) $now, (string) $window, (string) $max, $member);

        $status = (int) ($result[0] ?? 0);
        $availableMs = (int) ($result[1] ?? 0);

        if ($status === 1) {
            return;
        }

        $availableInSeconds = (int) ceil($availableMs / 1000);

        throw new SessionRateLimitedException(
            message: sprintf(
                'Session %s reached its limit of %d message(s) per %d second(s).',
                $sessionName,
                $max,
                $window,
            ),
            context: [
                'session'              => $sessionName,
                'stage'                => $stage->value,
                'max_attempts'         => $max,
                'window_seconds'       => $window,
                'available_in_seconds' => $availableInSeconds,
            ],
            session: $sessionName,
            stage: $stage,
            maxAttempts: $max,
            windowSeconds: $window,
            availableInSeconds: $availableInSeconds,
        );
    }
}
