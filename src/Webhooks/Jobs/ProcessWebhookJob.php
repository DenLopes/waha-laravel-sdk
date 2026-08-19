<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Jobs;

use DenLopes\Waha\Data\Output\Webhook;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;
use DenLopes\Waha\Webhooks\WebhookRouter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Webhook $webhook,
        public readonly string $rawBody = '',
        public readonly ?string $requestId = null,
        public readonly ?int $timestampMs = null,
    ) {}

    public function handle(WebhookRouter $router): void
    {
        $event = new WebhookReceived(
            webhook: $this->webhook,
            rawBody: $this->rawBody,
            requestId: $this->requestId,
            timestampMs: $this->timestampMs,
        );

        event($event);
        $router->handle($event);
    }
}
