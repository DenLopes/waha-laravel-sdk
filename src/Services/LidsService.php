<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Output\CountResponse;
use DenLopes\Waha\Data\Output\LidToPhoneNumber;
use DenLopes\Waha\Session;

class LidsService
{
    use SendsRequests;

    /**
     * Get all known lids to phone number mappings.
     *
     * @return LidToPhoneNumber[]
     */
    public function getAll(Session $session, int $limit = 100, int $offset = 0): array
    {
        $data = $this->send('get', '/api/{session}/lids', [
            'limit'  => $limit,
            'offset' => $offset,
        ], 'Communication with WAHA failed while listing lids.', session: $session);

        return array_map(
            static fn (array $item) => LidToPhoneNumber::fromArray($item),
            $data,
        );
    }

    /**
     * Get the number of known lids.
     */
    public function getCount(Session $session): CountResponse
    {
        $data = $this->send('get', '/api/{session}/lids/count', [], 'Communication with WAHA failed while counting lids.', session: $session);

        return CountResponse::fromArray($data);
    }

    /**
     * Get the phone number by lid.
     */
    public function findPhoneNumberByLid(Session $session, string $lid): LidToPhoneNumber
    {
        $data = $this->send('get', "/api/{session}/lids/{$lid}", [], 'Communication with WAHA failed while finding the phone number by lid.', session: $session);

        return LidToPhoneNumber::fromArray($data);
    }

    /**
     * Get the lid by phone number.
     */
    public function findLidByPhoneNumber(Session $session, string $phoneNumber): LidToPhoneNumber
    {
        $data = $this->send('get', "/api/{session}/lids/pn/{$phoneNumber}", [], 'Communication with WAHA failed while finding the lid by phone number.', session: $session);

        return LidToPhoneNumber::fromArray($data);
    }
}
