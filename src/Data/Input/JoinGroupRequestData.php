<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for joining a group by code or invite URL.
 */
final readonly class JoinGroupRequestData extends WahaData
{
    public function __construct(public string $code) {}

    public static function fromArray(array $data): static
    {
        return new self(code: (string) ($data['code'] ?? ''));
    }
}
