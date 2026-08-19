<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;
use DenLopes\Waha\Enums\ButtonType;

/**
 * A single interactive button.
 */
final readonly class Button extends Data
{
    public function __construct(
        public string $text,
        public ButtonType $type = ButtonType::REPLY,
        public ?string $id = null,
        public ?string $url = null,
        public ?string $phoneNumber = null,
        public ?string $copyCode = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            type: ButtonType::tryFrom((string) ($data['type'] ?? '')) ?? ButtonType::REPLY,
            id: isset($data['id']) ? (string) $data['id'] : null,
            url: isset($data['url']) ? (string) $data['url'] : null,
            phoneNumber: isset($data['phoneNumber']) ? (string) $data['phoneNumber'] : null,
            copyCode: isset($data['copyCode']) ? (string) $data['copyCode'] : null,
        );
    }
}
