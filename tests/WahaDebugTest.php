<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Debug\DebugStore;
use PHPUnit\Framework\TestCase;

final class WahaDebugTest extends TestCase
{
    public function test_last_returns_null_initially(): void
    {
        $this->assertNull((new DebugStore)->last());
    }

    public function test_last_curl_renders_command(): void
    {
        $store = new DebugStore;
        $store->setLast([
            'request' => [
                'method'  => 'POST',
                'url'     => 'http://waha.test/api/sendText',
                'payload' => ['chatId' => '111@c.us', 'text' => 'Hello'],
            ],
        ]);

        $curl = $store->lastCurl();

        $this->assertStringContainsString('curl -i', $curl);
        $this->assertStringContainsString('-X', $curl);
        $this->assertStringContainsString('POST', $curl);
        $this->assertStringContainsString('http://waha.test/api/sendText', $curl);
        $this->assertStringContainsString('--data', $curl);
    }

    public function test_clear_resets_last(): void
    {
        $store = new DebugStore;
        $store->setLast(['request' => []]);
        $store->clear();

        $this->assertNull($store->last());
    }
}
