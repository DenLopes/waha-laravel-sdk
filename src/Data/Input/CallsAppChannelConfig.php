<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Per-channel call-rejection rules for the Calls app.
 */
final readonly class CallsAppChannelConfig extends Data
{
    public function __construct(
        public bool $reject = true,
        public ?string $message = null,
        public ?int $waitBeforeDecline = null,
        public ?int $waitBeforeResponse = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            reject: (bool) ($data['reject'] ?? true),
            message: self::string($data, 'message'),
            waitBeforeDecline: self::intValue($data, 'waitBeforeDecline'),
            waitBeforeResponse: self::intValue($data, 'waitBeforeResponse'),
        );
    }
}
