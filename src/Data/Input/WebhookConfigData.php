<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * A webhook subscription configuration.
 */
final readonly class WebhookConfigData extends WahaData
{
    /**
     * @param  string[]  $events
     * @param  CustomHeaderData[]|null  $customHeaders
     */
    public function __construct(
        public string $url,
        public array $events,
        public ?HmacConfigurationData $hmac = null,
        public ?RetriesConfigurationData $retries = null,
        public ?array $customHeaders = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            events: (array) ($data['events'] ?? []),
            hmac: isset($data['hmac']) && is_array($data['hmac'])
                ? HmacConfigurationData::fromArray($data['hmac'])
                : null,
            retries: isset($data['retries']) && is_array($data['retries'])
                ? RetriesConfigurationData::fromArray($data['retries'])
                : null,
            customHeaders: isset($data['customHeaders']) && is_array($data['customHeaders'])
                ? array_map(
                    static fn (array $header) => CustomHeaderData::fromArray($header),
                    $data['customHeaders'],
                )
                : null,
        );
    }
}
