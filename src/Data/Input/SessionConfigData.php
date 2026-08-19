<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Session configuration (metadata, webhooks, engine-specific settings).
 */
final readonly class SessionConfigData extends WahaData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  WebhookConfigData[]|null  $webhooks
     */
    public function __construct(
        public ?array $metadata = null,
        public ?ProxyConfigData $proxy = null,
        public ?bool $debug = null,
        public ?IgnoreConfigData $ignore = null,
        public ?ClientSessionConfigData $client = null,
        public ?NowebConfigData $noweb = null,
        public ?GowsConfigData $gows = null,
        public ?WebjsConfigData $webjs = null,
        public ?array $webhooks = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            metadata: $data['metadata'] ?? null,
            proxy: isset($data['proxy']) && is_array($data['proxy'])
                ? ProxyConfigData::fromArray($data['proxy'])
                : null,
            debug: $data['debug'] ?? null,
            ignore: isset($data['ignore']) && is_array($data['ignore'])
                ? IgnoreConfigData::fromArray($data['ignore'])
                : null,
            client: isset($data['client']) && is_array($data['client'])
                ? ClientSessionConfigData::fromArray($data['client'])
                : null,
            noweb: isset($data['noweb']) && is_array($data['noweb'])
                ? NowebConfigData::fromArray($data['noweb'])
                : null,
            gows: isset($data['gows']) && is_array($data['gows'])
                ? GowsConfigData::fromArray($data['gows'])
                : null,
            webjs: isset($data['webjs']) && is_array($data['webjs'])
                ? WebjsConfigData::fromArray($data['webjs'])
                : null,
            webhooks: isset($data['webhooks']) && is_array($data['webhooks'])
                ? array_map(
                    static fn (array $webhook) => WebhookConfigData::fromArray($webhook),
                    $data['webhooks'],
                )
                : null,
        );
    }
}
