<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks;

use DenLopes\Waha\Exceptions\WebhookException;
use DenLopes\Waha\Webhooks\Contracts\WebhookHandler;
use DenLopes\Waha\Webhooks\Events\WebhookReceived;
use DenLopes\Waha\Webhooks\Handlers\NullWebhookHandler;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves the handler configured for a webhook event and invokes it.
 *
 * Handlers are mapped in `config('waha.webhooks.handlers')` by event name.
 * A `*` wildcard suffix (e.g. `message.*`) matches any event under that prefix.
 */
final class WebhookRouter
{
    public function __construct(private Container $container) {}

    /**
     * @throws WebhookException When a configured handler does not implement the contract.
     */
    public function handle(WebhookReceived $event): void
    {
        $handlerClass = $this->resolveHandlerClass($event->eventName());

        if ($handlerClass === null) {
            (new NullWebhookHandler)->handle($event);

            return;
        }

        $handler = $this->container->make($handlerClass);

        if (!$handler instanceof WebhookHandler) {
            throw new WebhookException("Webhook handler must implement WebhookHandler: {$handlerClass}");
        }

        $handler->handle($event);
    }

    private function resolveHandlerClass(string $eventName): ?string
    {
        /** @var array<int|string, mixed> $map */
        $map = (array) config('waha.webhooks.handlers', []);

        if (isset($map[$eventName]) && is_string($map[$eventName])) {
            return $map[$eventName];
        }

        foreach ($map as $key => $class) {
            if (!is_string($key) || !str_ends_with($key, '.*')) {
                continue;
            }

            $prefix = substr($key, 0, -2);

            if ($prefix !== '' && str_starts_with($eventName, $prefix.'.')) {
                return is_string($class) ? $class : null;
            }
        }

        return null;
    }
}
