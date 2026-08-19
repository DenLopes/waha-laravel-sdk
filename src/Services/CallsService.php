<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Support\WahaSession;

class CallsService
{
    use SendsWahaRequests;

    /**
     * Reject an incoming call.
     */
    public function rejectCall(WahaSession $session, string $from, string $id): array
    {
        return $this->send('post', "/api/{$this->session($session)}/calls/reject", [
            'from' => $from,
            'id'   => $id,
        ], 'Communication with WAHA failed while rejecting the call.');
    }
}
