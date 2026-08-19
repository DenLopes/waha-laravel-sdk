<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for joining a group by code or invite URL.
 */
final readonly class JoinGroupRequest extends Data
{
    public function __construct(public string $code) {}

    public static function fromArray(array $data): static
    {
        return new self(code: (string) ($data['code'] ?? ''));
    }
}
