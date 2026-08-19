<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\Debug\WahaDebugStore;
use DenLopes\Waha\Exception\NoDataException;
use DenLopes\Waha\Exception\WahaNotImplementedException;
use DenLopes\Waha\Exception\WahaRateLimitException;
use DenLopes\Waha\Exception\WahaServerException;
use DenLopes\Waha\Http\WahaRequest;
use Illuminate\Support\Facades\Http;

final class WahaRequestTest extends WahaTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('waha.base_url', 'http://waha.test');
        config()->set('waha.api_key', 'test-key');
        config()->set('waha.retry_attempts', 0);
        config()->set('waha.retry_delay_ms', 0);
    }

    public function test_make_returns_decoded_json(): void
    {
        Http::fake([
            'waha.test/*' => Http::response(['name' => 'default', 'status' => 'WORKING'], 200),
        ]);

        $result = (new WahaRequest())->make('get', '/api/sessions');

        $this->assertSame(['name' => 'default', 'status' => 'WORKING'], $result);
    }

    public function test_empty_body_returns_empty_array(): void
    {
        Http::fake([
            'waha.test/*' => Http::response('', 200),
        ]);

        $result = (new WahaRequest())->make('delete', '/api/sessions/default');

        $this->assertSame([], $result);
    }

    public function test_404_maps_to_no_data_exception(): void
    {
        Http::fake([
            'waha.test/*' => Http::response([], 404),
        ]);

        $this->expectException(NoDataException::class);

        (new WahaRequest())->make('get', '/api/sessions/default');
    }

    public function test_429_maps_to_rate_limit_exception(): void
    {
        Http::fake([
            'waha.test/*' => Http::response([], 429),
        ]);

        $this->expectException(WahaRateLimitException::class);

        (new WahaRequest())->make('get', '/api/sessions/default');
    }

    public function test_500_maps_to_server_exception(): void
    {
        Http::fake([
            'waha.test/*' => Http::response([], 500),
        ]);

        $this->expectException(WahaServerException::class);

        (new WahaRequest())->make('get', '/api/sessions/default');
    }

    public function test_501_maps_to_not_implemented_exception(): void
    {
        Http::fake([
            'waha.test/*' => Http::response([], 501),
        ]);

        $this->expectException(WahaNotImplementedException::class);

        (new WahaRequest())->make('post', '/api/forwardMessage');
    }

    public function test_retries_transient_5xx_then_succeeds(): void
    {
        config()->set('waha.retry_attempts', 1);

        Http::fake([
            'waha.test/*' => Http::sequence()
                ->push(['error' => 'boom'], 500)
                ->push(['name' => 'default', 'status' => 'WORKING'], 200),
        ]);

        $result = (new WahaRequest())->make('get', '/api/sessions/default');

        $this->assertSame(['name' => 'default', 'status' => 'WORKING'], $result);
        Http::assertSentCount(2);
    }

    public function test_download_returns_binary_body(): void
    {
        Http::fake([
            'waha.test/*' => Http::response('PNGDATA', 200, ['Content-Type' => 'image/png']),
        ]);

        $result = (new WahaRequest())->download(
            '/api/sessions/default/auth/qr',
            ['format' => 'image'],
            'image/png',
        );

        $this->assertSame('PNGDATA', $result);
    }

    public function test_debug_capture_records_last_request(): void
    {
        Http::fake([
            'waha.test/*' => Http::response(['name' => 'default'], 200),
        ]);

        $store = new WahaDebugStore();

        (new WahaRequest($store))->make('get', '/api/sessions');

        $last = $store->last();

        $this->assertSame('GET', $last['request']['method']);
        $this->assertSame('http://waha.test/api/sessions', $last['request']['url']);
        $this->assertSame(200, $last['response']['status']);
        $this->assertNotNull($store->lastCurl());
    }
}
