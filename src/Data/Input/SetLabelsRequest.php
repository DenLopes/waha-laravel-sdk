<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\Data;

/**
 * Payload for assigning a set of labels to a chat.
 */
final readonly class SetLabelsRequest extends Data
{
    /**
     * @param  LabelId[]  $labels
     */
    public function __construct(public array $labels) {}

    public static function fromArray(array $data): static
    {
        return new self(
            labels: array_map(
                static fn (array $label) => LabelId::fromArray($label),
                $data['labels'] ?? [],
            ),
        );
    }
}
