<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Group participant change event types.
 */
enum WahaGroupParticipantEventTypeEnum: string
{
    case JOIN = 'join';
    case LEAVE = 'leave';
    case PROMOTE = 'promote';
    case DEMOTE = 'demote';
}
