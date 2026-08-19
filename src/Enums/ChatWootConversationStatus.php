<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * ChatWoot conversation statuses that the integration can filter on.
 */
enum ChatWootConversationStatus: string
{
    case OPEN = 'open';
    case PENDING = 'pending';
    case SNOOZED = 'snoozed';
    case RESOLVED = 'resolved';
}
