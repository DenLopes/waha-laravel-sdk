<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\RejectCallRequest;
use DenLopes\Waha\Session;

class CallsService
{
    use SendsRequests;

    /**
     * Reject an incoming call.
     */
    public function rejectCall(Session $session, RejectCallRequest $request): array
    {
        return $this->send('post', '/api/{session}/calls/reject', $request->toArray(), 'Communication with WAHA failed while rejecting the call.', session: $session);
    }
}
