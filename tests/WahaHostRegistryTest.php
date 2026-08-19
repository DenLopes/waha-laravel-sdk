<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Exception\UnknownHostException;
use DenLopes\Waha\Registry\ConfigHostRegistry;
use DenLopes\Waha\Security\ConfigApiKeyProvider;

final class WahaHostRegistryTest extends WahaTestCase
{
    public function test_legacy_single_host_fallback(): void
    {
        config()->set('waha.hosts', []);
        config()->set('waha.base_url', 'http://waha.test');
        config()->set('waha.api_key', 'test-key');
        config()->set('waha.default_session', 'default');

        $registry = new ConfigHostRegistry();

        $this->assertSame('http://waha.test', $registry->get('primary')['base_url']);
        $this->assertSame('test-key', $registry->get('primary')['api_key']);
        $this->assertTrue($registry->exists('primary'));
    }

    public function test_configured_hosts_are_returned(): void
    {
        config()->set('waha.hosts', [
            'primary'   => ['base_url' => 'http://primary.test', 'api_key' => 'p-key'],
            'secondary' => ['base_url' => 'http://secondary.test', 'api_key' => 's-key'],
        ]);

        $registry = new ConfigHostRegistry();

        $this->assertSame('http://secondary.test', $registry->get('secondary')['base_url']);
        $this->assertFalse($registry->exists('missing'));
    }

    public function test_unknown_host_throws(): void
    {
        config()->set('waha.hosts', []);
        config()->set('waha.base_url', 'http://waha.test');

        $this->expectException(UnknownHostException::class);

        (new ConfigHostRegistry())->get('missing');
    }

    public function test_api_key_provider_resolves_keys(): void
    {
        config()->set('waha.hosts', [
            'primary' => [
                'base_url'       => 'http://primary.test',
                'api_key'        => 'admin-key',
                'api_key_header' => 'X-Custom-Key',
                'session_keys'   => ['sales' => 'sales-key'],
                'mode'           => 'strict_session_key',
            ],
        ]);

        $keys = new ConfigApiKeyProvider(new ConfigHostRegistry());

        $this->assertSame('admin-key', $keys->adminKey('primary'));
        $this->assertSame('sales-key', $keys->sessionKey('primary', 'sales'));
        $this->assertSame('X-Custom-Key', $keys->headerName('primary'));
        $this->assertSame('strict_session_key', $keys->mode('primary'));
    }
}
