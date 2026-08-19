<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * The HTTP transport contract for the WAHA API.
 *
 * This is the seam services depend on instead of the concrete {@see \DenLopes\Waha\Http\WahaRequest},
 * so tests can bind a fake implementation and exercise services without hitting
 * a real WAHA server.
 */
interface WahaClientInterface
{
    /**
     * Perform a JSON request and return the decoded response body.
     *
     * @param  string  $method  HTTP method (get, post, put, delete, etc.).
     * @param  string  $endpoint  API endpoint path (e.g. "/api/sessions").
     * @param  array<string, mixed>  $payload  Query parameters (GET) or JSON body (other methods).
     * @param  array<string, mixed>  $query  Additional query parameters.
     * @param  bool  $authenticated  When false, omit the X-Api-Key header.
     * @param  string|null  $session  Session name used for host routing.
     * @return mixed Decoded JSON response body.
     */
    public function make(string $method, string $endpoint, array $payload = [], array $query = [], bool $authenticated = true, ?string $session = null): mixed;

    /**
     * Download binary content from a GET endpoint.
     *
     * @param  array<string, mixed>  $payload  Query parameters.
     */
    public function download(string $endpoint, array $payload = [], ?string $expectedContentType = null, bool $authenticated = true, ?string $session = null): string;

    /**
     * Download binary content from a POST endpoint.
     *
     * @param  array<string, mixed>  $payload  JSON request body.
     */
    public function downloadPost(string $endpoint, array $payload = [], ?string $expectedContentType = null, bool $authenticated = true, ?string $session = null): string;
}
