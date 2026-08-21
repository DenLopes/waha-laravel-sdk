<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Listeners;

use DenLopes\Waha\Contracts\CircuitBreaker;
use DenLopes\Waha\Contracts\ContactStageStore;
use DenLopes\Waha\Data\Output\MessageAckBody;
use DenLopes\Waha\Data\Output\MessageData;
use DenLopes\Waha\Data\Output\Webhook;
use DenLopes\Waha\Enums\AckCode;
use DenLopes\Waha\Enums\ContactStage;
use DenLopes\Waha\Enums\WebhookEventType;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;

/**
 * Feeds the anti-abuse layer from inbound webhooks.
 *
 * Inbound 1:1 messages mark the contact warm; message acks feed the delivery
 * circuit breaker. Both paths are silent no-ops when their feature is disabled.
 */
final class AntiAbuseWebhookListener
{
    public function __construct(
        private readonly ContactStageStore $contactStageStore,
        private readonly CircuitBreaker $circuitBreaker,
    ) {}

    public function handle(WebhookReceived $event): void
    {
        $webhook = $event->webhook;

        if ($webhook->session === '') {
            return;
        }

        match ($webhook->event) {
            WebhookEventType::MESSAGE,
            WebhookEventType::MESSAGE_ANY => $this->markWarm($webhook),
            WebhookEventType::MESSAGE_ACK,
            WebhookEventType::MESSAGE_ACK_GROUP => $this->recordDelivery($webhook),
            default                             => null,
        };
    }

    private function markWarm(Webhook $webhook): void
    {
        $payload = $webhook->payload;

        if (!$payload instanceof MessageData || $payload->fromMe === true) {
            return;
        }

        $from = (string) $payload->from;

        if ($from === '' || $this->isGroup($from, $payload)) {
            return;
        }

        $this->contactStageStore->mark($webhook->session, $from, ContactStage::Warm);
    }

    private function recordDelivery(Webhook $webhook): void
    {
        $payload = $webhook->payload;

        if (!$payload instanceof MessageAckBody || $payload->ack === null) {
            return;
        }

        if ($payload->ack === AckCode::ERROR) {
            $this->circuitBreaker->recordFailure($webhook->session);

            return;
        }

        if (in_array($payload->ack, [AckCode::SERVER, AckCode::DEVICE, AckCode::READ, AckCode::PLAYED], true)) {
            $this->circuitBreaker->recordSuccess($webhook->session);
        }
    }

    private function isGroup(string $from, MessageData $payload): bool
    {
        return str_ends_with($from, '@g.us') || $payload->participant !== null;
    }
}
