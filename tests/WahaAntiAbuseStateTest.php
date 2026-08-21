<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Exceptions\ColdFanoutThrottledException;
use DenLopes\Waha\Support\CacheCircuitBreaker;
use DenLopes\Waha\Support\CacheColdTargetLimiter;
use DenLopes\Waha\Support\CacheWarmupTracker;

final class WahaAntiAbuseStateTest extends WahaTestCase
{
    public function test_warmup_tracker_returns_one_when_disabled(): void
    {
        $tracker = new CacheWarmupTracker(enabled: false, ageSeconds: 3600, multiplier: 0.2);

        $this->assertSame(1.0, $tracker->multiplier('sales'));
    }

    public function test_warmup_tracker_returns_multiplier_while_young(): void
    {
        $tracker = new CacheWarmupTracker(enabled: true, ageSeconds: 3600, multiplier: 0.2);

        $this->assertSame(0.2, $tracker->multiplier('sales'));
    }

    public function test_warmup_tracker_returns_one_once_aged(): void
    {
        $tracker = new CacheWarmupTracker(enabled: true, ageSeconds: 0, multiplier: 0.2);

        $this->assertSame(1.0, $tracker->multiplier('sales'));
    }

    public function test_warmup_tracker_touch_stamps_first_seen(): void
    {
        $tracker = new CacheWarmupTracker(enabled: true, ageSeconds: 3600, multiplier: 0.2);

        $tracker->touch('sales');

        $this->assertSame(0.2, $tracker->multiplier('sales'));
    }

    public function test_breaker_opens_when_failure_rate_exceeds_threshold(): void
    {
        $breaker = new CacheCircuitBreaker(
            failureWindowSeconds: 60,
            failureRateThreshold: 0.5,
            minSamples: 2,
            cooldownSeconds: 60,
        );

        $breaker->recordFailure('sales');
        $breaker->recordFailure('sales');

        $this->assertTrue($breaker->isOpen('sales'));
    }

    public function test_breaker_stays_closed_below_min_samples(): void
    {
        $breaker = new CacheCircuitBreaker(minSamples: 3, failureRateThreshold: 0.5);

        $breaker->recordFailure('sales');
        $breaker->recordFailure('sales');

        $this->assertFalse($breaker->isOpen('sales'));
    }

    public function test_breaker_stays_closed_when_rate_below_threshold(): void
    {
        $breaker = new CacheCircuitBreaker(minSamples: 2, failureRateThreshold: 0.6);

        $breaker->recordSuccess('sales');
        $breaker->recordFailure('sales');

        $this->assertFalse($breaker->isOpen('sales'));
    }

    public function test_breaker_holds_open_during_cooldown(): void
    {
        $breaker = new CacheCircuitBreaker(
            failureWindowSeconds: 60,
            failureRateThreshold: 0.5,
            minSamples: 2,
            cooldownSeconds: 60,
        );

        $breaker->recordFailure('sales');
        $breaker->recordFailure('sales');

        $this->assertTrue($breaker->isOpen('sales'));
        $this->assertTrue($breaker->isOpen('sales'));
    }

    public function test_cache_cold_target_limiter_enforces_budget_and_is_idempotent(): void
    {
        $limiter = new CacheColdTargetLimiter;

        $limiter->acquire('sales', '11111111111@c.us', 2, 3600);
        $limiter->acquire('sales', '22222222222@c.us', 2, 3600);
        $limiter->acquire('sales', '11111111111@c.us', 2, 3600);

        $this->expectException(ColdFanoutThrottledException::class);
        $limiter->acquire('sales', '33333333333@c.us', 2, 3600);
    }
}
