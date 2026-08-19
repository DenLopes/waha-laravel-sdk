<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * A webhook subscription configuration.
 */
final readonly class WebhookConfig extends Data
{
    /**
     * @param  string[]  $events
     * @param  CustomHeader[]|null  $customHeaders
     */
    public function __construct(
        public string $url,
        public array $events,
        public ?HmacConfiguration $hmac = null,
        public ?RetriesConfiguration $retries = null,
        public ?array $customHeaders = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            events: (array) ($data['events'] ?? []),
            hmac: isset($data['hmac']) && is_array($data['hmac'])
                ? HmacConfiguration::fromArray($data['hmac'])
                : null,
            retries: isset($data['retries']) && is_array($data['retries'])
                ? RetriesConfiguration::fromArray($data['retries'])
                : null,
            customHeaders: isset($data['customHeaders']) && is_array($data['customHeaders'])
                ? array_map(
                    static fn (array $header) => CustomHeader::fromArray($header),
                    $data['customHeaders'],
                )
                : null,
        );
    }
}
