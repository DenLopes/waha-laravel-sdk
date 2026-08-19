<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks;

use DenLopes\Waha\Data\Output\WebhookData;
use DenLopes\Waha\Exception\WahaIntegrationException;
use DenLopes\Waha\Exception\WahaWebhookException;
use DenLopes\Waha\WahaServiceProvider;
use DenLopes\Waha\Webhooks\Events\WahaWebhookReceived;
use DenLopes\Waha\Webhooks\Jobs\ProcessWahaWebhookJob;
use DenLopes\Waha\Webhooks\Models\WahaWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Stateless HTTP entry point for WAHA webhook deliveries.
 *
 * The route is registered by {@see WahaServiceProvider} when webhooks
 * are enabled. It verifies the signature/timestamp/replay, parses the payload
 * into a {@see WebhookData}, then dispatches it (inline or through the queue).
 */
final class WebhookController
{
    public function __construct(
        private readonly WebhookVerifier $verifier,
        private readonly WebhookGuard $guard,
        private readonly WahaWebhookRouter $router,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (!(bool) config('waha.webhooks.enabled', true)) {
            return response()->json(['ok' => false, 'reason' => 'webhooks_disabled'], 404);
        }

        $rawBody = (string) $request->getContent();
        $secret = (string) config('waha.webhooks.secret');

        $hmac = $this->stringHeader($request, WebhookVerifier::HEADER_HMAC);
        $algo = $this->stringHeader($request, WebhookVerifier::HEADER_HMAC_ALGO);
        $requestId = $this->stringHeader($request, WebhookVerifier::HEADER_REQUEST_ID);
        $timestampMs = $this->timestampMs($request);

        // Fall back to legacy headers when the standard ones are missing.
        if ($hmac === null) {
            $hmac = $this->stringHeader($request, WebhookVerifier::LEGACY_HEADER_HMAC);
            $algo = $this->stringHeader($request, WebhookVerifier::LEGACY_HEADER_HMAC_ALGO);
        }

        try {
            $this->assertValid($rawBody, $secret, $hmac, $algo, $timestampMs);
        } catch (WahaWebhookException $e) {
            Log::channel('wahaError')->warning('WAHA webhook rejected.', [
                'reason'     => $e->reason,
                'request_id' => $requestId,
                'context'    => $e->context(),
            ]);

            return response()->json(['ok' => false, 'reason' => $e->reason], $e->status);
        }

        if ($this->guard->isReplay($requestId)) {
            Log::channel('waha')->info('WAHA webhook replay ignored.', [
                'request_id' => $requestId,
            ]);

            return response()->json(['ok' => true, 'ignored' => true]);
        }

        try {
            $webhook = WebhookData::fromJson($rawBody);
        } catch (WahaIntegrationException $e) {
            Log::channel('wahaError')->warning('WAHA webhook payload is invalid.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'reason' => 'invalid_payload'], 400);
        }

        Log::channel('waha')->info('WAHA webhook accepted.', [
            'event'      => $webhook->event?->value,
            'session'    => $webhook->session,
            'request_id' => $requestId,
        ]);

        $this->store($webhook, $rawBody, $requestId);
        $this->dispatch($webhook, $rawBody, $requestId, $timestampMs);

        return response()->json(['ok' => true]);
    }

    /**
     * Persist the verified delivery when webhook storage is enabled.
     */
    private function store(WebhookData $webhook, string $rawBody, ?string $requestId): void
    {
        if (!(bool) config('waha.webhooks.store.enabled', false)) {
            return;
        }

        WahaWebhookEvent::query()->create([
            'event'      => $webhook->event,
            'session'    => $webhook->session !== '' ? $webhook->session : null,
            'request_id' => $requestId,
            'host_key'   => config('waha.default_host'),
            'payload'    => json_decode($rawBody, true),
        ]);
    }

    /**
     * @throws WahaWebhookException
     */
    private function assertValid(string $rawBody, string $secret, ?string $hmac, ?string $algo, ?int $timestampMs): void
    {
        if ((bool) config('waha.webhooks.require_hmac', true) && $hmac === null) {
            throw new WahaWebhookException(
                'Missing webhook HMAC.',
                reason: 'missing_hmac',
                status: 401,
            );
        }

        if ($hmac !== null) {
            $this->verifier->verify($secret, $rawBody, $hmac, $algo);
        }

        $this->guard->assertFreshTimestamp($timestampMs);
    }

    private function dispatch(WebhookData $webhook, string $rawBody, ?string $requestId, ?int $timestampMs): void
    {
        $mode = (string) config('waha.webhooks.processing.mode', 'sync');

        if ($mode === 'queue') {
            $job = new ProcessWahaWebhookJob($webhook, $rawBody, $requestId, $timestampMs);

            $connection = config('waha.webhooks.processing.queue_connection');
            $queueName = (string) config('waha.webhooks.processing.queue_name', 'default');

            if (is_string($connection) && $connection !== '') {
                $job->onConnection($connection);
            }

            if ($queueName !== '') {
                $job->onQueue($queueName);
            }

            dispatch($job);

            return;
        }

        $event = new WahaWebhookReceived($webhook, $rawBody, $requestId, $timestampMs);

        event($event);
        $this->router->handle($event);
    }

    private function stringHeader(Request $request, string $name): ?string
    {
        $value = $request->header($name);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function timestampMs(Request $request): ?int
    {
        $value = $request->header(WebhookVerifier::HEADER_TIMESTAMP);

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }
}
