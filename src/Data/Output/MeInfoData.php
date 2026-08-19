<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\WahaData;

final readonly class MeInfoData extends WahaData
{
    public function __construct(
        public string $id,
        public ?string $lid,
        public ?string $jid,
        public string $pushName,
        public ?ReachoutTimelockData $reachoutTimelock,
        public ?MessageCappingData $messageCapping,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            lid: isset($data['lid']) ? (string) $data['lid'] : null,
            jid: isset($data['jid']) ? (string) $data['jid'] : null,
            pushName: (string) ($data['pushName'] ?? ''),
            reachoutTimelock: isset($data['reachoutTimelock']) && is_array($data['reachoutTimelock'])
                ? ReachoutTimelockData::fromArray($data['reachoutTimelock'])
                : null,
            messageCapping: isset($data['messageCapping']) && is_array($data['messageCapping'])
                ? MessageCappingData::fromArray($data['messageCapping'])
                : null,
        );
    }
}
