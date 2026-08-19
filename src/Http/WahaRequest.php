<?php

declare(strict_types=1);

namespace DenLopes\Waha\Http;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Contracts\SessionRouter;
use DenLopes\Waha\Contracts\WahaClientInterface;
use DenLopes\Waha\Debug\WahaDebugStore;
use DenLopes\Waha\Exception\NoDataException;
use DenLopes\Waha\Exception\WahaConnectionException;
use DenLopes\Waha\Exception\WahaCredentialsException;
use DenLopes\Waha\Exception\WahaException;
use DenLopes\Waha\Exception\WahaIntegrationException;
use DenLopes\Waha\Exception\WahaNotImplementedException;
use DenLopes\Waha\Exception\WahaRateLimitException;
use DenLopes\Waha\Exception\WahaRequestException;
use DenLopes\Waha\Exception\WahaServerException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WahaRequest implements WahaClientInterface
{
    public function __construct(
        private ?WahaDebugStore $debugStore = null,
        private ?HostRegistry $hosts = null,
        private ?ApiKeyProvider $keys = null,
        private ?SessionRouter $router = null,
    ) {}

    /**
     * Make a JSON HTTP request to the WAHA API.
     *
     * @param  string  $method  HTTP method (get, post, put, delete, etc.).
     * @param  string  $endpoint  API endpoint path (e.g. "/api/sessions").
     * @param  array  $payload  Query parameters (GET) or JSON body (POST/PUT/PATCH).
     * @param  array  $query  Additional query parameters (e.g. for POST endpoints that
     *                        accept query params). Array values are serialized by Laravel's
     *                        query builder, which WAHA's Express/qs parser accepts.
     * @param  bool  $authenticated  When false, the X-Api-Key header is omitted for public
     *                               endpoints (e.g. /ping).
     * @return mixed Decoded JSON response body (array for objects, scalar for values).
     */
    public function make(string $method, string $endpoint, array $payload = [], array $query = [], bool $authenticated = true, ?string $session = null): mixed
    {
        $client = $this->client(true, $authenticated, $session);

        if ($query !== []) {
            $client->withQueryParameters($query);
        }

        $response = $this->sendRequest($method, $endpoint, $payload, $query, $client, $authenticated, $session);

        $body = trim($response->body());

        // Several WAHA endpoints return an empty body (e.g. delete/clear operations).
        if ($body === '') {
            return [];
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $decodeError = json_last_error_msg();
            $exception = new WahaIntegrationException(
                'Failed to decode WAHA response: '.$decodeError,
                0,
                null,
                $this->requestContext($method, $endpoint, $response->status(), substr($body, 0, 1000)),
            );

            $this->logRequestFailed($method, $endpoint, $payload, $exception);

            throw $exception;
        }

        return $data ?? [];
    }

    /**
     * Download binary content (e.g. QR image) from a GET endpoint.
     *
     * @param  string  $endpoint  API endpoint path.
     * @param  array  $payload  Query parameters.
     * @return string Raw binary response body.
     */
    public function download(
        string $endpoint,
        array $payload = [],
        ?string $expectedContentType = null,
        bool $authenticated = true,
        ?string $session = null,
    ): string {
        return $this->downloadRaw('get', $endpoint, $payload, $expectedContentType, $authenticated, $session);
    }

    /**
     * Download binary content from a POST endpoint (e.g. media conversion).
     *
     * @param  string  $endpoint  API endpoint path.
     * @param  array  $payload  JSON request body.
     * @return string Raw binary response body.
     */
    public function downloadPost(
        string $endpoint,
        array $payload = [],
        ?string $expectedContentType = null,
        bool $authenticated = true,
        ?string $session = null,
    ): string {
        return $this->downloadRaw('post', $endpoint, $payload, $expectedContentType, $authenticated, $session);
    }

    /**
     * Perform a binary download, optionally sending a JSON body for non-GET methods.
     */
    private function downloadRaw(
        string $method,
        string $endpoint,
        array $payload,
        ?string $expectedContentType,
        bool $authenticated,
        ?string $session = null,
    ): string {
        $client = $this->client(false, $authenticated, $session);

        if ($method !== 'get') {
            $client->asJson();
        }

        $response = $this->sendRequest($method, $endpoint, $payload, [], $client, $authenticated, $session);

        if ($expectedContentType !== null) {
            $contentType = strtolower((string) $response->header('Content-Type'));

            if (!str_contains($contentType, $expectedContentType)) {
                $exception = new WahaIntegrationException(
                    "WAHA did not return the expected {$expectedContentType} content. Content-Type: {$contentType}",
                    0,
                    null,
                    $this->requestContext($method, $endpoint, $response->status(), substr($response->body(), 0, 500)),
                );

                $this->logRequestFailed($method, $endpoint, $payload, $exception);

                throw $exception;
            }
        }

        return $response->body();
    }

    /**
     * Build the HTTP client with the API key header.
     *
     * @param  bool  $json  When true, send/receive JSON. When false, accept raw
     *                      binary responses (e.g. QR code images or converted media).
     * @param  bool  $authenticated  When false, skip the API key requirement and header.
     *
     * @throws WahaCredentialsException
     */
    private function client(bool $json = true, bool $authenticated = true, ?string $session = null): PendingRequest
    {
        $hostKey = $this->resolveHostKey($session);
        $baseUrl = $this->resolveBaseUrl($hostKey);
        $apiKey = (string) ($this->keys()->adminKey($hostKey) ?? '');

        if ($authenticated && $apiKey === '') {
            $exception = new WahaCredentialsException;

            Log::channel('wahaError')->error('WAHA API key not configured', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $client = Http::baseUrl($baseUrl)
            ->connectTimeout((int) config('waha.connect_timeout', 5))
            ->timeout((int) config('waha.timeout', 30));

        if ($apiKey !== '') {
            $client->withHeaders([
                $this->keys()->headerName($hostKey) => $apiKey,
            ]);
        }

        if ($json) {
            $client->acceptJson()->asJson();
        }

        return $client;
    }

    private function resolveHostKey(?string $session = null): string
    {
        return $this->router()->resolveHostKey(null, $session);
    }

    private function resolveBaseUrl(?string $hostKey = null): string
    {
        $hostKey ??= $this->resolveHostKey();
        $host = $this->hosts()->get($hostKey);

        return (string) ($host['base_url'] ?? config('waha.base_url', 'http://localhost:3000'));
    }

    private function hosts(): HostRegistry
    {
        return $this->hosts ??= app(HostRegistry::class);
    }

    private function keys(): ApiKeyProvider
    {
        return $this->keys ??= app(ApiKeyProvider::class);
    }

    private function router(): SessionRouter
    {
        return $this->router ??= app(SessionRouter::class);
    }

    /**
     * Send a request with logging, timing and error normalization.
     */
    private function sendRequest(
        string $method,
        string $endpoint,
        array $payload,
        array $query,
        PendingRequest $client,
        bool $authenticated,
        ?string $session = null,
    ): Response {
        $this->logRequestStart($method, $endpoint, $payload, $query, $authenticated, $session);

        $start = microtime(true);

        try {
            $response = $this->sendWithRetry($method, $endpoint, $payload, $client);
        } catch (Throwable $e) {
            $this->logRequestFailed($method, $endpoint, $payload, $e, microtime(true) - $start);

            throw $e;
        }

        $duration = microtime(true) - $start;

        try {
            $this->throwOnFailure($response, $method, $endpoint);
        } catch (Throwable $e) {
            $this->logRequestFailed($method, $endpoint, $payload, $e, $duration);
            $this->captureDebug($method, $endpoint, $payload, $response, $duration, $session);

            throw $e;
        }

        $this->logRequestCompleted($method, $endpoint, $response->status(), $duration);
        $this->captureDebug($method, $endpoint, $payload, $response, $duration, $session);

        return $response;
    }

    /**
     * Send a request, retrying transient connection failures and HTTP 429/5xx.
     */
    private function sendWithRetry(string $method, string $endpoint, array $payload, PendingRequest $client): Response
    {
        $attempts = max(0, (int) config('waha.retry_attempts', 3));
        $delayMs = max(0, (int) config('waha.retry_delay_ms', 200));

        for ($attempt = 0; $attempt <= $attempts; $attempt++) {
            try {
                $response = $payload === []
                    ? $client->send(strtoupper($method), $endpoint)
                    : $client->$method($endpoint, $payload);
            } catch (ConnectionException $e) {
                if ($attempt === $attempts) {
                    throw new WahaConnectionException(
                        'Unable to connect to the WAHA server: '.$e->getMessage(),
                        0,
                        $e,
                        $this->requestContext($method, $endpoint),
                    );
                }

                $this->sleepBeforeRetry($delayMs, $attempt);

                continue;
            }

            if ($this->isTransient($response) && $attempt < $attempts) {
                $this->sleepBeforeRetry($delayMs, $attempt);

                continue;
            }

            return $response;
        }

        throw new WahaConnectionException(
            'Unable to connect to the WAHA server.',
            0,
            null,
            $this->requestContext($method, $endpoint),
        );
    }

    /**
     * Whether a response represents a transient failure worth retrying.
     */
    private function isTransient(Response $response): bool
    {
        return $response->tooManyRequests() || $response->serverError();
    }

    /**
     * Sleep with exponential backoff before the next retry attempt.
     */
    private function sleepBeforeRetry(int $delayMs, int $attempt): void
    {
        $backoffMs = $delayMs * (2 ** $attempt);

        usleep($backoffMs * 1000);
    }

    /**
     * Translate a failed HTTP response into a domain exception.
     */
    private function throwOnFailure(Response $response, string $method, string $endpoint): void
    {
        $status = $response->status();

        if ($status < 400) {
            return;
        }

        $context = $this->requestContext($method, $endpoint, $status, substr((string) $response->body(), 0, 1000));

        if (in_array($status, [401, 403], true)) {
            throw new WahaCredentialsException('WAHA authentication failed.', $status, null, $context);
        }

        if ($status === 404) {
            throw new NoDataException('WAHA resource not found: '.$endpoint, 404, null, $context);
        }

        if ($status === 429) {
            throw new WahaRateLimitException('WAHA rate limit exceeded.', 429, null, $context);
        }

        if (in_array($status, [400, 422], true)) {
            throw new WahaRequestException('WAHA rejected the request.', $status, null, $context);
        }

        if ($status === 501) {
            throw new WahaNotImplementedException('WAHA endpoint not implemented by the current engine.', 501, null, $context);
        }

        if ($status >= 500) {
            throw new WahaServerException('WAHA server error.', $status, null, $context);
        }

        throw new WahaIntegrationException(
            "WAHA returned an unexpected HTTP status code {$status}.",
            $status,
            null,
            $context,
        );
    }

    /**
     * Log the outgoing request (method, endpoint, masked payload and query).
     */
    private function logRequestStart(string $method, string $endpoint, array $payload, array $query, bool $authenticated, ?string $session = null): void
    {
        Log::channel('waha')->info('Sending request to WAHA API', [
            'base_url'      => $this->resolveBaseUrl($this->resolveHostKey($session)),
            'method'        => strtoupper($method),
            'endpoint'      => $endpoint,
            'payload'       => $this->maskSensitiveData($payload),
            'query'         => $query,
            'authenticated' => $authenticated,
        ]);
    }

    /**
     * Log a successful HTTP response (status and total duration).
     */
    private function logRequestCompleted(string $method, string $endpoint, int $status, float $durationSeconds): void
    {
        Log::channel('waha')->info('WAHA API request completed', [
            'method'      => strtoupper($method),
            'endpoint'    => $endpoint,
            'status'      => $status,
            'duration_ms' => (int) round($durationSeconds * 1000),
        ]);
    }

    /**
     * Log a failed request (connection, HTTP status or response processing).
     */
    private function logRequestFailed(string $method, string $endpoint, array $payload, Throwable $e, ?float $durationSeconds = null): void
    {
        $context = [
            'message'  => $e->getMessage(),
            'code'     => $e->getCode(),
            'method'   => strtoupper($method),
            'endpoint' => $endpoint,
            'payload'  => $this->maskSensitiveData($payload),
        ];

        if ($durationSeconds !== null) {
            $context['duration_ms'] = (int) round($durationSeconds * 1000);
        }

        if ($e instanceof WahaException && $e->context() !== []) {
            $context['context'] = $e->context();
        }

        Log::channel('wahaError')->error('WAHA API error', $context);
    }

    /**
     * Recursively redact sensitive fields and truncate oversized strings before logging.
     */
    private function maskSensitiveData(mixed $value): mixed
    {
        if (is_array($value)) {
            $masked = [];

            foreach ($value as $key => $item) {
                if (is_string($key) && $this->isSensitiveKey($key)) {
                    $masked[$key] = '[REDACTED]';

                    continue;
                }

                $masked[$key] = $this->maskSensitiveData($item);
            }

            return $masked;
        }

        if (is_string($value) && strlen($value) > 1000) {
            return substr($value, 0, 1000).'...[truncated]';
        }

        return $value;
    }

    /**
     * Whether a payload key holds sensitive data that should never be logged.
     */
    private function isSensitiveKey(string $key): bool
    {
        return in_array(strtolower($key), [
            'data',
            'password',
            'key',
            'token',
            'secret',
            'api_key',
            'apikey',
            'access_token',
            'ccv',
            'cpfcnpj',
        ], true);
    }

    /**
     * Record the last masked request/response for debugging.
     */
    private function captureDebug(string $method, string $endpoint, array $payload, Response $response, float $durationSeconds, ?string $session = null): void
    {
        $this->debugStore?->setLast([
            'request' => [
                'method'  => strtoupper($method),
                'url'     => $this->resolveBaseUrl($this->resolveHostKey($session)).$endpoint,
                'payload' => $this->maskSensitiveData($payload),
            ],
            'response' => [
                'status' => $response->status(),
                'body'   => $this->truncateBody((string) $response->body()),
            ],
            'duration_ms' => (int) round($durationSeconds * 1000),
        ]);
    }

    /**
     * Truncate a response body to a reasonable size for debugging.
     */
    private function truncateBody(string $body, int $maxBytes = 1000): string
    {
        if (strlen($body) <= $maxBytes) {
            return $body;
        }

        return substr($body, 0, $maxBytes).'...[truncated]';
    }

    /**
     * Build a structured context array for diagnostics.
     *
     * @return array<string, mixed>
     */
    private function requestContext(string $method, string $endpoint, ?int $status = null, ?string $body = null): array
    {
        $context = [
            'method'   => $method,
            'endpoint' => $endpoint,
        ];

        if ($status !== null) {
            $context['status'] = $status;
        }

        if ($body !== null && $body !== '') {
            $context['body'] = $body;
        }

        return $context;
    }
}
