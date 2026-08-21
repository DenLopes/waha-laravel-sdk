<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;
use DenLopes\Waha\Exceptions\SessionRateLimitedException;
use DenLopes\Waha\Support\RedisColdTargetLimiter;
use DenLopes\Waha\Support\RedisSessionRateLimiter;
use DenLopes\Waha\Support\TierConfig;
use Illuminate\Redis\Connections\PhpRedisConnection;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

#[Group('redis')]
final class WahaRedisLimiterTest extends WahaTestCase
{
    private PhpRedisConnection $redis;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.redis.client', 'phpredis');
        config()->set('database.redis.default', [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => (int) env('REDIS_PORT', '6379'),
            'password' => env('REDIS_PASSWORD'),
            'database' => (int) env('REDIS_DB', '15'),
        ]);

        try {
            $redis = app('redis')->connection();

            if (!$redis instanceof PhpRedisConnection) {
                $this->markTestSkipped('Redis is not using the PhpRedis driver.');
            }

            $redis->command('ping');
        } catch (Throwable $e) {
            $this->markTestSkipped('Redis is not available: '.$e->getMessage());
        }

        $this->redis = $redis;
        $this->redis->command('flushdb');
    }

    public function test_session_limiter_allows_up_to_limit_then_throws(): void
    {
        $limiter = new RedisSessionRateLimiter($this->redis);
        $tier = new TierConfig(sessionMaxMessages: 2, sessionWindowSeconds: 60);

        $limiter->hit('sales', ContactStage::Cold, $tier);
        $limiter->hit('sales', ContactStage::Cold, $tier);

        $this->expectException(SessionRateLimitedException::class);
        $limiter->hit('sales', ContactStage::Cold, $tier);
    }

    public function test_session_limiter_reports_available_in_seconds(): void
    {
        $limiter = new RedisSessionRateLimiter($this->redis);
        $tier = new TierConfig(sessionMaxMessages: 1, sessionWindowSeconds: 60);

        $limiter->hit('sales', ContactStage::Cold, $tier);

        try {
            $limiter->hit('sales', ContactStage::Cold, $tier);
            $this->fail('Expected SessionRateLimitedException.');
        } catch (SessionRateLimitedException $e) {
            $this->assertSame('sales', $e->session);
            $this->assertSame(ContactStage::Cold, $e->stage);
            $this->assertGreaterThan(0, $e->availableInSeconds);
        }
    }

    public function test_session_limiter_recovers_after_window_expires(): void
    {
        $limiter = new RedisSessionRateLimiter($this->redis);
        $tier = new TierConfig(sessionMaxMessages: 1, sessionWindowSeconds: 1);

        $limiter->hit('sales', ContactStage::Cold, $tier);

        try {
            $limiter->hit('sales', ContactStage::Cold, $tier);
            $this->fail('Expected SessionRateLimitedException.');
        } catch (SessionRateLimitedException) {
            // throttled inside the window
        }

        usleep(1_200_000);

        $limiter->hit('sales', ContactStage::Cold, $tier);
        $this->addToAssertionCount(1);
    }

    public function test_session_limiter_tracks_each_hit_as_a_distinct_member(): void
    {
        $limiter = new RedisSessionRateLimiter($this->redis);
        $tier = new TierConfig(sessionMaxMessages: 3, sessionWindowSeconds: 60);

        $limiter->hit('sales', ContactStage::Cold, $tier);
        $limiter->hit('sales', ContactStage::Cold, $tier);
        $limiter->hit('sales', ContactStage::Cold, $tier);

        $count = $this->redis->command('zcard', ['waha:limiter:sales:cold:messages']);

        $this->assertSame(3, $count);
    }

    public function test_cold_target_limiter_enforces_unique_budget(): void
    {
        $limiter = new RedisColdTargetLimiter($this->redis);

        $limiter->acquire('sales', '11111111111@c.us', 2, 3600);
        $limiter->acquire('sales', '22222222222@c.us', 2, 3600);

        $this->expectException(ColdFanoutThrottledException::class);
        $limiter->acquire('sales', '33333333333@c.us', 2, 3600);
    }

    public function test_cold_target_limiter_reacquire_is_idempotent(): void
    {
        $limiter = new RedisColdTargetLimiter($this->redis);

        $limiter->acquire('sales', '11111111111@c.us', 1, 3600);
        $limiter->acquire('sales', '11111111111@c.us', 1, 3600);

        $count = $this->redis->command('zcard', ['waha:limiter:sales:cold:unique_targets']);

        $this->assertSame(1, $count);
    }

    public function test_cold_target_limiter_reports_available_in_seconds(): void
    {
        $limiter = new RedisColdTargetLimiter($this->redis);

        $limiter->acquire('sales', '11111111111@c.us', 1, 3600);

        try {
            $limiter->acquire('sales', '22222222222@c.us', 1, 3600);
            $this->fail('Expected ColdFanoutThrottledException.');
        } catch (ColdFanoutThrottledException $e) {
            $this->assertSame('sales', $e->session);
            $this->assertSame(1, $e->maxUniqueTargets);
            $this->assertGreaterThan(0, $e->availableInSeconds);
        }
    }
}
