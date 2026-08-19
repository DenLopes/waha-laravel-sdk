<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Handlers;

use DenLopes\Waha\Webhooks\Contracts\WebhookHandler;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;

final class NullWebhookHandler implements WebhookHandler
{
    public function handle(WebhookReceived $event): void
    {
        // Intentionally a no-op: an event with no configured handler.
    }
}
