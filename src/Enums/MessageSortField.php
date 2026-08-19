<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum MessageSortField: string
{
    case TIMESTAMP = 'timestamp';
    case MESSAGE_TIMESTAMP = 'messageTimestamp';
}
