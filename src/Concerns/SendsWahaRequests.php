<?php

declare(strict_types=1);

namespace DenLopes\Waha\Concerns;

use DenLopes\Waha\Contracts\WahaClientInterface;
use DenLopes\Waha\Exception\WahaException;
use DenLopes\Waha\Exception\WahaIntegrationException;
use DenLopes\Waha\Support\WahaSession;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shared WAHA HTTP helpers for service classes.
 *
 * The constructor injects {@see WahaClientInterface}, so the Laravel container
 * can resolve it for any class that consumes this trait. Consuming classes should
 * not declare their own constructor unless they also inject WahaClientInterface.
 */
trait SendsWahaRequests
{
    /**
     * @param  WahaClientInterface  $wahaRequest  The HTTP client for WAHA API requests.
     */
    public function __construct(protected WahaClientInterface $wahaRequest) {}

    /**
     * Resolve a session name to its string value, falling back to the
     * configured default when no explicit session is provided.
     */
    protected function session(?WahaSession $session = null): string
    {
        return ($session ?? WahaSession::default())->value();
    }

    /**
     * Perform a JSON request and normalize failures into domain exceptions.
     *
     * @param  array  $query  Additional query parameters (e.g. for POST with query).
     * @param  bool  $authenticated  Whether to require/send the X-Api-Key header.
     * @return mixed Decoded JSON response body.
     *
     * @throws WahaException
     */
    protected function send(
        string $method,
        string $endpoint,
        array $payload,
        string $failureMessage,
        array $query = [],
        bool $authenticated = true,
        ?string $session = null,
    ): mixed {
        $session ??= is_string($payload['session'] ?? null) ? $payload['session'] : null;

        try {
            return $this->wahaRequest->make($method, $endpoint, $payload, $query, $authenticated, $session);
        } catch (WahaException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('wahaError')->error('WAHA request failed.', [
                'method'   => $method,
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            throw new WahaIntegrationException($failureMessage, 0, $e, [
                'method'   => $method,
                'endpoint' => $endpoint,
            ]);
        }
    }

    /**
     * Download binary content and normalize failures into domain exceptions.
     *
     * @throws WahaException
     */
    protected function download(
        string $endpoint,
        array $payload,
        string $failureMessage,
        ?string $expectedContentType = null,
        bool $authenticated = true,
        ?string $session = null,
    ): string {
        $session ??= is_string($payload['session'] ?? null) ? $payload['session'] : null;

        try {
            return $this->wahaRequest->download($endpoint, $payload, $expectedContentType, $authenticated, $session);
        } catch (WahaException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('wahaError')->error('WAHA download failed.', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            throw new WahaIntegrationException($failureMessage, 0, $e, [
                'method'   => 'get',
                'endpoint' => $endpoint,
            ]);
        }
    }

    /**
     * Download binary content from a POST endpoint and normalize failures.
     *
     * @throws WahaException
     */
    protected function downloadPost(
        string $endpoint,
        array $payload,
        string $failureMessage,
        ?string $expectedContentType = null,
        bool $authenticated = true,
        ?string $session = null,
    ): string {
        $session ??= is_string($payload['session'] ?? null) ? $payload['session'] : null;

        try {
            return $this->wahaRequest->downloadPost($endpoint, $payload, $expectedContentType, $authenticated, $session);
        } catch (WahaException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('wahaError')->error('WAHA download failed.', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            throw new WahaIntegrationException($failureMessage, 0, $e, [
                'method'   => 'post',
                'endpoint' => $endpoint,
            ]);
        }
    }
}
