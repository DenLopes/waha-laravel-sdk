<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for updating a group subject.
 */
final readonly class SubjectRequestData extends WahaData
{
    public function __construct(public string $subject) {}

    public static function fromArray(array $data): static
    {
        return new self(subject: (string) ($data['subject'] ?? ''));
    }
}
