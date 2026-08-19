<?php

declare(strict_types=1);

namespace DenLopes\Waha;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Contracts\SessionRouter;
use DenLopes\Waha\Contracts\WahaClientInterface;
use DenLopes\Waha\Debug\WahaDebugStore;
use DenLopes\Waha\Http\WahaRequest;
use DenLopes\Waha\Pin\DbPinStore;
use DenLopes\Waha\Registry\ConfigHostRegistry;
use DenLopes\Waha\Registry\DbHostRegistry;
use DenLopes\Waha\Routing\NullRouter;
use DenLopes\Waha\Routing\PinningRouter;
use DenLopes\Waha\Security\ConfigApiKeyProvider;
use DenLopes\Waha\Webhooks\WebhookController;
use DenLopes\Waha\Webhooks\WebhookGuard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WahaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/waha.php', 'waha');
        $this->mergeConfigFrom(__DIR__.'/../config/logging.php', 'logging.channels');

        $this->app->bind(WahaClientInterface::class, WahaRequest::class);
        $this->app->singleton(WahaDebugStore::class);

        $this->app->bind(HostRegistry::class, function () {
            return (string) config('waha.registry.driver', 'config') === 'db'
                ? new DbHostRegistry
                : new ConfigHostRegistry;
        });

        $this->app->bind(ApiKeyProvider::class, ConfigApiKeyProvider::class);
        $this->app->bind(PinStore::class, fn () => new DbPinStore);

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

        $this->registerWebhookRoutes();
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
