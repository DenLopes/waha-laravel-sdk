<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Handlers;

use DenLopes\Waha\Webhooks\Contracts\WahaWebhookHandler;
use DenLopes\Waha\Webhooks\Events\WahaWebhookReceived;

final class NullWebhookHandler implements WahaWebhookHandler
{
    public function handle(WahaWebhookReceived $event): void
    {
        // Intentionally a no-op: an event with no configured handler.
    }
}
