<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Session configuration (metadata, webhooks, engine-specific settings).
 */
final readonly class SessionConfig extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  WebhookConfig[]|null  $webhooks
     */
    public function __construct(
        public ?array $metadata = null,
        public ?ProxyConfig $proxy = null,
        public ?bool $debug = null,
        public ?IgnoreConfig $ignore = null,
        public ?ClientSessionConfig $client = null,
        public ?NowebConfig $noweb = null,
        public ?GowsConfig $gows = null,
        public ?WebjsConfig $webjs = null,
        public ?array $webhooks = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            metadata: self::arrayValue($data, 'metadata'),
            proxy: isset($data['proxy']) && is_array($data['proxy'])
                ? ProxyConfig::fromArray($data['proxy'])
                : null,
            debug: self::boolValue($data, 'debug'),
            ignore: isset($data['ignore']) && is_array($data['ignore'])
                ? IgnoreConfig::fromArray($data['ignore'])
                : null,
            client: isset($data['client']) && is_array($data['client'])
                ? ClientSessionConfig::fromArray($data['client'])
                : null,
            noweb: isset($data['noweb']) && is_array($data['noweb'])
                ? NowebConfig::fromArray($data['noweb'])
                : null,
            gows: isset($data['gows']) && is_array($data['gows'])
                ? GowsConfig::fromArray($data['gows'])
                : null,
            webjs: isset($data['webjs']) && is_array($data['webjs'])
                ? WebjsConfig::fromArray($data['webjs'])
                : null,
            webhooks: isset($data['webhooks']) && is_array($data['webhooks'])
                ? array_map(
                    static fn (array $webhook) => WebhookConfig::fromArray($webhook),
                    $data['webhooks'],
                )
                : null,
        );
    }
}
