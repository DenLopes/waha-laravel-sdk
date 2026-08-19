<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Group info returned before joining via an invite code or URL.
 *
 * The OpenAPI document types this response as a generic object, so the known
 * fields are typed here and the full payload is preserved in {@see self::$raw}.
 */
final readonly class GroupJoinInfo extends Data
{
    /**
     * @param  array<int, array<string, mixed>>|null  $participants  Raw participant list.
     * @param  array<string, mixed>  $raw  The complete, unmodified payload.
     */
    public function __construct(
        public ?string $id = null,
        public ?string $subject = null,
        public ?string $description = null,
        public ?array $participants = null,
        public array $raw = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: isset($data['id']) ? (string) $data['id'] : null,
            subject: isset($data['subject']) ? (string) $data['subject'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            participants: $data['participants'] ?? null,
            raw: $data,
        );
    }
}
