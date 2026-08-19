<?php

declare(strict_types=1);

namespace DenLopes\Waha\Http;

use DenLopes\Waha\Contracts\ApiKeyProvider;
use DenLopes\Waha\Contracts\HostRegistry;
use DenLopes\Waha\Contracts\HttpClient as HttpClientContract;
use DenLopes\Waha\Contracts\SessionRouter;
use DenLopes\Waha\Debug\DebugStore;
use DenLopes\Waha\Enums\ApiKeyMode;
use DenLopes\Waha\Exceptions\AuthenticationException;
use DenLopes\Waha\Exceptions\ConnectionException;
use DenLopes\Waha\Exceptions\CredentialsException;
use DenLopes\Waha\Exceptions\IntegrationException;
use DenLopes\Waha\Exceptions\NoDataException;
use DenLopes\Waha\Exceptions\NotImplementedException;
use DenLopes\Waha\Exceptions\RateLimitException;
use DenLopes\Waha\Exceptions\RequestException;
use DenLopes\Waha\Exceptions\ServerException;
use DenLopes\Waha\Exceptions\SessionNotFoundException;
use DenLopes\Waha\Exceptions\WahaException;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class HttpClient implements HttpClientContract
{
    /**
     * HTTP methods that are safe to retry automatically. Non-idempotent writes
     * (e.g. POST /api/sendText) are never retried to avoid duplicate messages.
     */
    private const IDEMPOTENT_METHODS = ['GET', 'HEAD', 'PUT', 'DELETE'];

    /**
     * Global API roots that must not be classified as session-scoped endpoints.
     * Used only to select the right 404 exception type.
     */
    private const GLOBAL_API_ROOTS = [
        'send', 'keys', 'contacts', 'server', 'apps', 'messages', 'checkNumberStatus',
        'screenshot', 'version', 'forwardMessage', 'reaction', 'star', 'sendPoll',
        'sendPollVote', 'sendLocation', 'sendContactVcard', 'reply', 'sendLinkPreview',
        'sendButtons', 'sendList', 'startTyping', 'stopTyping',
    ];

    public function __construct(
        private readonly DebugStore $debugStore,
        private readonly HostRegistry $hosts,
        private readonly ApiKeyProvider $keys,
        private readonly SessionRouter $router,
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
            $exception = new IntegrationException(
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

        // WAHA negotiates the binary vs JSON representation via the Accept header.
        if ($expectedContentType !== null) {
            $client->withHeaders(['Accept' => $expectedContentType]);
        }

        $response = $this->sendRequest($method, $endpoint, $payload, [], $client, $authenticated, $session);

        if ($expectedContentType !== null) {
            $contentType = strtolower((string) $response->header('Content-Type'));

            if (!str_contains($contentType, $expectedContentType)) {
                $exception = new IntegrationException(
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
     * @throws CredentialsException
     */
    private function client(bool $json = true, bool $authenticated = true, ?string $session = null): PendingRequest
    {
        $hostKey = $this->resolveHostKey($session);
        $baseUrl = $this->resolveBaseUrl($hostKey);
        $apiKey = $this->resolveApiKey($hostKey, $session) ?? '';

        if ($authenticated && $apiKey === '') {
            $exception = new CredentialsException;

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
                $this->keys->headerName($hostKey) => $apiKey,
            ]);
        }

        if ($json) {
            $client->acceptJson()->asJson();
        }

        return $client;
    }

    private function resolveHostKey(?string $session = null): string
    {
        return $this->router->resolveHostKey(null, $session);
    }

    private function resolveBaseUrl(string $hostKey): string
    {
        return $this->hosts->get($hostKey)->baseUrl;
    }

    /**
     * Resolve the API key to use for a host/session pair.
     *
     * @throws CredentialsException When strict session key mode is used without a session.
     */
    private function resolveApiKey(string $hostKey, ?string $session): ?string
    {
        $mode = $this->keys->mode($hostKey);

        if ($mode === ApiKeyMode::STRICT_SESSION_KEY) {
            if ($session === null || $session === '') {
                throw new CredentialsException(
                    'A session name is required when the host uses strict_session_key mode.',
                );
            }

            return $this->keys->sessionKey($hostKey, $session);
        }

        if ($session !== null && $session !== '') {
            $sessionKey = $this->keys->sessionKey($hostKey, $session);

            if ($sessionKey !== null) {
                return $sessionKey;
            }
        }

        return $this->keys->adminKey($hostKey);
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
            $this->captureDebug($method, $endpoint, $payload, $response, $duration, $query, $session);

            throw $e;
        }

        $this->logRequestCompleted($method, $endpoint, $response->status(), $duration);
        $this->captureDebug($method, $endpoint, $payload, $response, $duration, $query, $session);

        return $response;
    }

    /**
     * Send a request, retrying transient connection failures and HTTP 429/5xx.
     *
     * Non-idempotent methods are never retried, even on connection errors, because
     * a connection failure after the request was sent is ambiguous and retrying
     * could duplicate the write.
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
            } catch (HttpConnectionException $e) {
                if (!$this->isIdempotent($method) || $attempt === $attempts) {
                    throw new ConnectionException(
                        'Unable to connect to the WAHA server: '.$e->getMessage(),
                        0,
                        $e,
                        $this->requestContext($method, $endpoint),
                    );
                }

                $this->sleepBeforeRetry($delayMs, $attempt);

                continue;
            }

            if ($this->isTransient($method, $response) && $attempt < $attempts) {
                $this->sleepBeforeRetry($delayMs, $attempt, $response);

                continue;
            }

            return $response;
        }

        // Defensive fallback; the loop returns or throws on every path above.
        throw new ConnectionException(
            'Unable to connect to the WAHA server.',
            0,
            null,
            $this->requestContext($method, $endpoint),
        );
    }

    private function isIdempotent(string $method): bool
    {
        return in_array(strtoupper($method), self::IDEMPOTENT_METHODS, true);
    }

    /**
     * Whether a response represents a transient failure worth retrying.
     */
    private function isTransient(string $method, Response $response): bool
    {
        if (!$this->isIdempotent($method)) {
            return false;
        }

        return $response->tooManyRequests() || $response->serverError();
    }

    /**
     * Sleep with exponential backoff (plus jitter) before the next retry attempt.
     */
    private function sleepBeforeRetry(int $delayMs, int $attempt, ?Response $response = null): void
    {
        $backoffMs = $delayMs * (2 ** $attempt);

        if ($response !== null) {
            $retryAfter = $response->header('Retry-After');

            if (ctype_digit($retryAfter)) {
                $backoffMs = max($backoffMs, ((int) $retryAfter) * 1000);
            }
        }

        $jitterMs = $backoffMs === 0 ? 0 : random_int(0, (int) ceil($backoffMs * 0.2));

        usleep(($backoffMs + $jitterMs) * 1000);
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
            throw new AuthenticationException('WAHA authentication failed.', $status, null, $context);
        }

        if ($status === 404) {
            throw $this->isSessionScopedEndpoint($endpoint)
                ? new SessionNotFoundException('WAHA session not found: '.$endpoint, 404, null, $context)
                : new NoDataException('WAHA resource not found: '.$endpoint, 404, null, $context);
        }

        if ($status === 429) {
            throw new RateLimitException('WAHA rate limit exceeded.', 429, null, $context);
        }

        if (in_array($status, [400, 422], true)) {
            throw new RequestException('WAHA rejected the request.', $status, null, $context);
        }

        if ($status === 501) {
            throw new NotImplementedException('WAHA endpoint not implemented by the current engine.', 501, null, $context);
        }

        if ($status >= 500) {
            throw new ServerException('WAHA server error.', $status, null, $context);
        }

        throw new IntegrationException(
            "WAHA returned an unexpected HTTP status code {$status}.",
            $status,
            null,
            $context,
        );
    }

    /**
     * Whether a 404 should be attributed to a missing session rather than a
     * missing (non-session) resource.
     */
    private function isSessionScopedEndpoint(string $endpoint): bool
    {
        // `/api/sessions/{session}...` — exclude the deprecated global actions.
        if (preg_match('#^/api/sessions/(?!start$|stop$|logout$)#', $endpoint) === 1) {
            return true;
        }

        // `/api/{session}/...` — a session name occupies the first segment after /api.
        foreach (self::GLOBAL_API_ROOTS as $root) {
            if (str_starts_with($endpoint, '/api/'.$root)) {
                return false;
            }
        }

        return str_starts_with($endpoint, '/api/');
    }

    /**
     * Log the outgoing request (method, endpoint, masked payload and query).
     */
    private function logRequestStart(string $method, string $endpoint, array $payload, array $query, bool $authenticated, ?string $session = null): void
    {
        $baseUrl = null;

        try {
            $baseUrl = $this->resolveBaseUrl($this->resolveHostKey($session));
        } catch (WahaException) {
            $baseUrl = null;
        }

        Log::channel('waha')->info('Sending request to WAHA API', [
            'base_url'      => $baseUrl,
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
    private function captureDebug(string $method, string $endpoint, array $payload, Response $response, float $durationSeconds, array $query = [], ?string $session = null): void
    {
        $hostKey = $this->resolveHostKey($session);

        $this->debugStore->setLast([
            'request' => [
                'method'  => strtoupper($method),
                'url'     => $this->resolveBaseUrl($hostKey).$endpoint,
                'headers' => $this->debugHeaders($hostKey),
                'payload' => $this->maskSensitiveData($payload),
                'query'   => $query,
            ],
            'response' => [
                'status' => $response->status(),
                'body'   => $this->truncateBody((string) $response->body()),
            ],
            'duration_ms' => (int) round($durationSeconds * 1000),
        ]);
    }

    /**
     * Headers to include in debug output, with the API key redacted.
     *
     * @return array<string, string>
     */
    private function debugHeaders(string $hostKey): array
    {
        $apiKey = $this->keys->adminKey($hostKey);

        if ($apiKey === null || $apiKey === '') {
            return [];
        }

        return [
            $this->keys->headerName($hostKey) => '[REDACTED]',
        ];
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
