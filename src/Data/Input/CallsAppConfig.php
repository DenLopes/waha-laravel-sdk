<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Configuration for the built-in Calls app.
 */
final readonly class CallsAppConfig extends Data
{
    public function __construct(
        public CallsAppChannelConfig $dm,
        public CallsAppChannelConfig $group,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            dm: isset($data['dm']) && is_array($data['dm'])
                ? CallsAppChannelConfig::fromArray($data['dm'])
                : CallsAppChannelConfig::fromArray(['reject' => true]),
            group: isset($data['group']) && is_array($data['group'])
                ? CallsAppChannelConfig::fromArray($data['group'])
                : CallsAppChannelConfig::fromArray(['reject' => true]),
        );
    }
}
