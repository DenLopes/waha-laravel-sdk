<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Input;

use DenLopes\Waha\Data\WahaData;

/**
 * Payload for assigning a set of labels to a chat.
 */
final readonly class SetLabelsRequestData extends WahaData
{
    /**
     * @param  LabelIdData[]  $labels
     */
    public function __construct(public array $labels) {}

    public static function fromArray(array $data): static
    {
        return new self(
            labels: array_map(
                static fn (array $label) => LabelIdData::fromArray($label),
                $data['labels'] ?? [],
            ),
        );
    }
}
