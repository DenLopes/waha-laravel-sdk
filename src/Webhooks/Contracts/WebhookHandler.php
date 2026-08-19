<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Contracts;

use DenLopes\Waha\Webhooks\Events\WebhookReceived;

interface WebhookHandler
{
    public function handle(WebhookReceived $event): void;
}
