<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum WahaMessageSortFieldEnum: string
{
    case TIMESTAMP = 'timestamp';
    case MESSAGE_TIMESTAMP = 'messageTimestamp';
}
