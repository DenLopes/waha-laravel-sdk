<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Contracts;

use DenLopes\Waha\Webhooks\Events\WahaWebhookReceived;

interface WahaWebhookHandler
{
    public function handle(WahaWebhookReceived $event): void;
}
