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
     * Resolve a session to its normalized name, falling back to the configured
     * default session when none is provided.
     */
    protected function session(WahaSession|string|null $session = null): string
    {
        return $this->resolveSession($session);
    }

    /**
     * Normalize a WahaSession object, raw session name, or null into a string.
     */
    private function resolveSession(WahaSession|string|null $session = null): string
    {
        if ($session instanceof WahaSession) {
            return $session->value();
        }

        if (is_string($session) && trim($session) !== '') {
            return WahaSession::from($session)->value();
        }

        return WahaSession::default()->value();
    }

    /**
     * Substitute the `{session}` placeholder in a path-based endpoint and
     * determine the session name used for host routing.
     *
     * Precedence:
     *   1. Path endpoints (`{session}` placeholder) use the resolved session.
     *   2. Legacy body/query endpoints use the `session` payload key.
     *   3. Global endpoints return null (default host).
     *
     * @param  array<string, mixed>  $payload
     */
    private function prepareRequest(string &$endpoint, array $payload, WahaSession|string|null $session): ?string
    {
        if (str_contains($endpoint, '{session}')) {
            $resolved = $this->resolveSession($session);
            $endpoint = str_replace('{session}', $resolved, $endpoint);

            return $resolved;
        }

        if ($session !== null) {
            return $this->resolveSession($session);
        }

        if (is_string($payload['session'] ?? null)) {
            return $payload['session'];
        }

        return null;
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
        WahaSession|string|null $session = null,
    ): mixed {
        $routingSession = $this->prepareRequest($endpoint, $payload, $session);

        try {
            return $this->wahaRequest->make($method, $endpoint, $payload, $query, $authenticated, $routingSession);
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
        WahaSession|string|null $session = null,
    ): string {
        $routingSession = $this->prepareRequest($endpoint, $payload, $session);

        try {
            return $this->wahaRequest->download($endpoint, $payload, $expectedContentType, $authenticated, $routingSession);
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
        WahaSession|string|null $session = null,
    ): string {
        $routingSession = $this->prepareRequest($endpoint, $payload, $session);

        try {
            return $this->wahaRequest->downloadPost($endpoint, $payload, $expectedContentType, $authenticated, $routingSession);
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
