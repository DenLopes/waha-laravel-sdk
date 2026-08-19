<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Contracts\WahaClientInterface;

/**
 * In-memory WahaClientInterface double for service tests.
 *
 * It records every request and returns canned JSON/binary responses, so service
 * tests can assert payload/endpoint construction and typed DTO mapping without
 * booting the Laravel HTTP layer.
 */
final class FakeWahaClient implements WahaClientInterface
{
    /**
     * @var array<int, array{method:string,endpoint:string,payload:array,query:array,authenticated:bool,session:string|null}>
     */
    public array $requests = [];

    /**
     * @param  mixed  $response  The decoded JSON response returned by make().
     */
    public function __construct(
        public mixed $response = [],
        public string $downloadResponse = '',
    ) {}

    public function make(string $method, string $endpoint, array $payload = [], array $query = [], bool $authenticated = true, ?string $session = null): mixed
    {
        $this->requests[] = compact('method', 'endpoint', 'payload', 'query', 'authenticated', 'session');

        return $this->response;
    }

    public function download(string $endpoint, array $payload = [], ?string $expectedContentType = null, bool $authenticated = true, ?string $session = null): string
    {
        $this->requests[] = [
            'method'        => 'get',
            'endpoint'      => $endpoint,
            'payload'       => $payload,
            'query'         => [],
            'authenticated' => $authenticated,
            'session'       => $session,
        ];

        return $this->downloadResponse;
    }

    public function downloadPost(string $endpoint, array $payload = [], ?string $expectedContentType = null, bool $authenticated = true, ?string $session = null): string
    {
        $this->requests[] = [
            'method'        => 'post',
            'endpoint'      => $endpoint,
            'payload'       => $payload,
            'query'         => [],
            'authenticated' => $authenticated,
            'session'       => $session,
        ];

        return $this->downloadResponse;
    }
}
