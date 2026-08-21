<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests\Support;

use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\ReachoutTimelock;
use DenLopes\Waha\Exceptions\SessionNotFoundException;
use DenLopes\Waha\Services\SessionService;
use DenLopes\Waha\Session;

/**
 * Canned {@see SessionService} for reachout guard tests.
 */
final class FakeSessionService extends SessionService
{
    public ?MessageCapping $capping = null;

    public ?ReachoutTimelock $timelock = null;

    public bool $throwApiException = false;

    public function __construct()
    {
        parent::__construct(new FakeWahaClient);
    }

    public function fetchMessageCapping(Session $session): MessageCapping
    {
        if ($this->throwApiException) {
            throw new SessionNotFoundException;
        }

        return $this->capping ?? new MessageCapping(null, '', null, null, null, null, null, null);
    }

    public function fetchReachoutTimelock(Session $session): ReachoutTimelock
    {
        if ($this->throwApiException) {
            throw new SessionNotFoundException;
        }

        return $this->timelock ?? new ReachoutTimelock(null, '', false, null);
    }
}
