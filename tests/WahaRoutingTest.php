<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Routing\NullRouter;
use DenLopes\Waha\Routing\PinningRouter;
use PHPUnit\Framework\TestCase;

final class WahaRoutingTest extends TestCase
{
    public function test_null_router_uses_default_host(): void
    {
        $router = new NullRouter('primary');

        $this->assertSame('primary', $router->resolveHostKey(null, null));
        $this->assertSame('primary', $router->resolveHostKey(null, 'sales'));
    }

    public function test_pinning_router_returns_pinned_host(): void
    {
        $router = new PinningRouter(new FakePinStore(['sales' => 'secondary']), 'primary');

        $this->assertSame('secondary', $router->resolveHostKey(null, 'sales'));
        $this->assertSame('primary', $router->resolveHostKey(null, 'unknown'));
        $this->assertSame('primary', $router->resolveHostKey(null, null));
        $this->assertSame('tertiary', $router->resolveHostKey('tertiary', 'sales'));
    }
}

final class FakePinStore implements PinStore
{
    /**
     * @param  array<string, string>  $pins
     */
    public function __construct(private array $pins = []) {}

    public function getHostForSession(string $sessionName): ?string
    {
        return $this->pins[$sessionName] ?? null;
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        $this->pins[$sessionName] = $hostKey;
    }

    public function forget(string $sessionName): void
    {
        unset($this->pins[$sessionName]);
    }
}
