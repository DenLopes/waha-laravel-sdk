<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\RejectCallRequestData;
use DenLopes\Waha\Support\WahaSession;

class CallsService
{
    use SendsWahaRequests;

    /**
     * Reject an incoming call.
     */
    public function rejectCall(WahaSession $session, RejectCallRequestData $request): array
    {
        return $this->send('post', '/api/{session}/calls/reject', $request->toArray(), 'Communication with WAHA failed while rejecting the call.', session: $session);
    }
}
