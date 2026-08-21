<?php

declare(strict_types=1);

namespace DenLopes\Waha;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\CircuitBreaker;
use DenLopes\Waha\Contracts\ColdTargetLimiter;
use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Contracts\ConversationStateStore;
use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Contracts\HttpClient as HttpClientContract;
use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Contracts\ReachoutGuard;
use DenLopes\Waha\Contracts\SessionRateLimiter;
use DenLopes\Waha\Contracts\SessionRouter;
use DenLopes\Waha\Contracts\WarmupTracker;
use DenLopes\Waha\Debug\DebugStore;
use DenLopes\Waha\Http\HttpClient;
use DenLopes\Waha\Pin\DbPinStore;
use DenLopes\Waha\Registry\ConfigHostRegistry;
use DenLopes\Waha\Registry\DbHostRegistry;
use DenLopes\Waha\Routing\NullRouter;
use DenLopes\Waha\Routing\PinningRouter;
use DenLopes\Waha\Security\ConfigApiKeyProvider;
use DenLopes\Waha\Services\SessionService;
use DenLopes\Waha\Support\CacheCircuitBreaker;
use DenLopes\Waha\Support\CacheColdTargetLimiter;
use DenLopes\Waha\Support\CacheContactStageStore;
use DenLopes\Waha\Support\CacheConversationStateStore;
use DenLopes\Waha\Support\CacheSessionRateLimiter;
use DenLopes\Waha\Support\CacheWarmupTracker;
use DenLopes\Waha\Support\ConversationFactory;
use DenLopes\Waha\Support\NullCircuitBreaker;
use DenLopes\Waha\Support\ReachoutGuard as CacheReachoutGuard;
use DenLopes\Waha\Support\RedisColdTargetLimiter;
use DenLopes\Waha\Support\RedisSessionRateLimiter;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;
use DenLopes\Waha\Webhooks\Listeners\AntiAbuseWebhookListener;
use DenLopes\Waha\Webhooks\WebhookController;
use DenLopes\Waha\Webhooks\WebhookGuard;
use Illuminate\Cache\RedisStore;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');
        $this->mergeConfigFrom(__DIR__.'/../config/logging.php', 'logging.channels');

        $this->app->bind(HttpClientContract::class, HttpClient::class);
        $this->app->singleton(DebugStore::class);
        $this->app->singleton(Client::class);

        $this->app->bind(HostRegistry::class, function () {
            return (string) config('waha.registry.driver', 'config') === 'db'
                ? new DbHostRegistry
                : new ConfigHostRegistry;
        });

        $this->app->bind(ApiKeyProvider::class, ConfigApiKeyProvider::class);
        $this->app->bind(PinStore::class, fn () => new DbPinStore);

        $cacheStore = config('waha.conversations.cache_store');
        $cacheStore = is_string($cacheStore) && $cacheStore !== '' ? $cacheStore : null;
        $cachePrefix = (string) config('waha.conversations.cache_prefix', 'waha:conversation:');

        $this->app->bind(ConversationStateStore::class, fn () => new CacheConversationStateStore(
            prefix: $cachePrefix,
            store: $cacheStore,
        ));

        $this->app->bind(ContactStageStore::class, fn () => new CacheContactStageStore(
            prefix: $cachePrefix,
            store: $cacheStore,
            ttlSeconds: (int) config('waha.conversations.contact_stage_ttl', 2592000),
        ));

        $driver = (string) config('waha.conversations.limiter_driver', 'auto');

        if (!in_array($driver, ['auto', 'redis', 'cache'], true)) {
            throw new RuntimeException("Unsupported waha.conversations.limiter_driver [{$driver}].");
        }

        $this->app->bind(SessionRateLimiter::class, function () use ($cacheStore, $driver): SessionRateLimiter {
            if ($this->shouldUseRedis($cacheStore, $driver)) {
                return new RedisSessionRateLimiter($this->resolveRedisConnection($cacheStore));
            }

            return new CacheSessionRateLimiter($cacheStore);
        });

        $this->app->bind(ColdTargetLimiter::class, function () use ($cacheStore, $driver): ColdTargetLimiter {
            if ($this->shouldUseRedis($cacheStore, $driver)) {
                return new RedisColdTargetLimiter($this->resolveRedisConnection($cacheStore));
            }

            return new CacheColdTargetLimiter($cacheStore);
        });

        $this->app->bind(ReachoutGuard::class, function () use ($cacheStore, $cachePrefix): CacheReachoutGuard {
            $reachout = (array) config('waha.conversations.reachout', []);

            return new CacheReachoutGuard(
                sessionService: $this->app->make(SessionService::class),
                enabled: (bool) ($reachout['enabled'] ?? true),
                cappingCacheSeconds: (int) ($reachout['capping_cache_seconds'] ?? 30),
                timelockCacheSeconds: (int) ($reachout['timelock_cache_seconds'] ?? 60),
                prefix: $cachePrefix,
                store: $cacheStore,
            );
        });

        $this->app->bind(WarmupTracker::class, function () use ($cacheStore, $cachePrefix): CacheWarmupTracker {
            $warmup = (array) config('waha.conversations.warmup', []);

            return new CacheWarmupTracker(
                enabled: (bool) ($warmup['enabled'] ?? true),
                ageSeconds: (int) ($warmup['age_seconds'] ?? 1209600),
                multiplier: (float) ($warmup['multiplier'] ?? 0.2),
                store: $cacheStore,
                prefix: $cachePrefix,
            );
        });

        $this->app->bind(CircuitBreaker::class, function () use ($cacheStore, $cachePrefix): CircuitBreaker {
            $breaker = (array) config('waha.conversations.circuit_breaker', []);

            if (!(bool) ($breaker['enabled'] ?? false)) {
                return new NullCircuitBreaker;
            }

            return new CacheCircuitBreaker(
                failureWindowSeconds: (int) ($breaker['failure_window_seconds'] ?? 900),
                failureRateThreshold: (float) ($breaker['failure_rate_threshold'] ?? 0.3),
                minSamples: (int) ($breaker['min_samples'] ?? 20),
                cooldownSeconds: (int) ($breaker['cooldown_seconds'] ?? 300),
                store: $cacheStore,
                prefix: $cachePrefix,
            );
        });

        $this->app->singleton(ConversationFactory::class, function () {
            $reachout = (array) config('waha.conversations.reachout', []);
            $breaker = (array) config('waha.conversations.circuit_breaker', []);

            return new ConversationFactory(
                stateStore: $this->app->make(ConversationStateStore::class),
                contactStageStore: $this->app->make(ContactStageStore::class),
                sessionLimiter: $this->app->make(SessionRateLimiter::class),
                coldTargetLimiter: $this->app->make(ColdTargetLimiter::class),
                reachoutGuard: $this->app->make(ReachoutGuard::class),
                warmupTracker: $this->app->make(WarmupTracker::class),
                circuitBreaker: $this->app->make(CircuitBreaker::class),
                throwOnColdUrls: (bool) ($reachout['throw_on_cold_urls'] ?? false),
                circuitBreakerCooldownSeconds: (int) ($breaker['cooldown_seconds'] ?? 300),
            );
        });

        $this->app->bind(SessionRouter::class, function ($app) {
            $defaultHost = (string) config('waha.default_host', 'primary');

            return (string) config('waha.routing.driver', 'none') === 'pin'
                ? new PinningRouter($app->make(PinStore::class), $defaultHost)
                : new NullRouter($defaultHost);
        });

        $this->app->bind(WebhookGuard::class, function (): WebhookGuard {
            $webhooks = (array) config('waha.webhooks', []);
            $replay = (array) ($webhooks['replay'] ?? []);

            return new WebhookGuard(
                maxClockSkewMs: (int) ($webhooks['max_clock_skew_ms'] ?? 300000),
                replayEnabled: (bool) ($replay['enabled'] ?? true),
                replayTtlSeconds: (int) ($replay['ttl_seconds'] ?? 900),
                replayCachePrefix: (string) ($replay['cache_prefix'] ?? 'waha:webhook:'),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/waha.php' => config_path('waha.php'),
        ], 'waha-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'waha-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Event::listen(WebhookReceived::class, AntiAbuseWebhookListener::class);

        $this->registerWebhookRoutes();
    }

    private function shouldUseRedis(?string $store, string $driver): bool
    {
        return $driver === 'redis'
            || ($driver === 'auto' && $this->isRedisCacheStore($store));
    }

    private function isRedisCacheStore(?string $store): bool
    {
        return Cache::store($store)->getStore() instanceof RedisStore;
    }

    private function resolveRedisConnection(?string $store): PhpRedisConnection
    {
        $cacheStore = Cache::store($store)->getStore();

        if ($cacheStore instanceof RedisStore) {
            $connection = $cacheStore->connection();
        } else {
            /** @var RedisManager $redis */
            $redis = $this->app->make('redis');
            $connection = $redis->connection();
        }

        if (!$connection instanceof PhpRedisConnection) {
            throw new RuntimeException('The Redis limiters require the PhpRedis driver (phpredis extension).');
        }

        return $connection;
    }

    private function registerWebhookRoutes(): void
    {
        $webhooks = (array) config('waha.webhooks', []);

        if (!(bool) ($webhooks['enabled'] ?? true)) {
            return;
        }

        $route = (array) ($webhooks['route'] ?? []);
        $prefix = trim((string) ($route['prefix'] ?? '/webhooks/waha'), '/');
        $middleware = array_values((array) ($route['middleware'] ?? ['api']));

        Route::middleware($middleware)->post($prefix, WebhookController::class);
    }
}
