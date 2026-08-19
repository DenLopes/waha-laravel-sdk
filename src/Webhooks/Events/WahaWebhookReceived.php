<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Events;

use DenLopes\Waha\Data\Output\WebhookData;

/**
 * Fired once a WAHA webhook has been verified and parsed.
 *
 * Consumers can either listen to this event through Laravel's event system or
 * register a handler for a specific event name in `config('waha.webhooks.handlers')`.
 */
final readonly class WahaWebhookReceived
{
    public function __construct(
        public WebhookData $webhook,
        public string $rawBody = '',
        public ?string $requestId = null,
        public ?int $timestampMs = null,
    ) {}

    /**
     * The normalized WAHA event name (e.g. "message" or "session.status").
     */
    public function eventName(): string
    {
        return $this->webhook->event?->value ?? 'unknown';
    }
}
