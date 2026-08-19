<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Output\CountResponseData;
use DenLopes\Waha\Data\Output\LidToPhoneNumberData;
use DenLopes\Waha\Support\WahaSession;

class LidsService
{
    use SendsWahaRequests;

    /**
     * Get all known lids to phone number mappings.
     *
     * @return LidToPhoneNumberData[]
     */
    public function getAll(WahaSession $session, int $limit = 100, int $offset = 0): array
    {
        $data = $this->send('get', "/api/{$this->session($session)}/lids", [
            'limit'  => $limit,
            'offset' => $offset,
        ], 'Communication with WAHA failed while listing lids.');

        return array_map(
            static fn (array $item) => LidToPhoneNumberData::fromArray($item),
            $data,
        );
    }

    /**
     * Get the number of known lids.
     */
    public function getCount(WahaSession $session): CountResponseData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/lids/count", [], 'Communication with WAHA failed while counting lids.');

        return CountResponseData::fromArray($data);
    }

    /**
     * Get the phone number by lid.
     */
    public function findPhoneNumberByLid(WahaSession $session, string $lid): LidToPhoneNumberData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/lids/{$lid}", [], 'Communication with WAHA failed while finding the phone number by lid.');

        return LidToPhoneNumberData::fromArray($data);
    }

    /**
     * Get the lid by phone number.
     */
    public function findLidByPhoneNumber(WahaSession $session, string $phoneNumber): LidToPhoneNumberData
    {
        $data = $this->send('get', "/api/{$this->session($session)}/lids/pn/{$phoneNumber}", [], 'Communication with WAHA failed while finding the lid by phone number.');

        return LidToPhoneNumberData::fromArray($data);
    }
}
