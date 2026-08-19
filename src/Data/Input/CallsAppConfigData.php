<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Configuration for the built-in Calls app.
 */
final readonly class CallsAppConfigData extends WahaData
{
    public function __construct(
        public CallsAppChannelConfigData $dm,
        public CallsAppChannelConfigData $group,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            dm: isset($data['dm']) && is_array($data['dm'])
                ? CallsAppChannelConfigData::fromArray($data['dm'])
                : CallsAppChannelConfigData::fromArray(['reject' => true]),
            group: isset($data['group']) && is_array($data['group'])
                ? CallsAppChannelConfigData::fromArray($data['group'])
                : CallsAppChannelConfigData::fromArray(['reject' => true]),
        );
    }
}
