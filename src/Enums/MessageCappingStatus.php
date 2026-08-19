<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * How close an account is to its new-chat message quota.
 *
 * WhatsApp may introduce new values, so consumers should tolerate an unknown
 * value (represented by a null enum and the preserved raw string).
 */
enum MessageCappingStatus: string
{
    case NONE = 'NONE';
    case FIRST_WARNING = 'FIRST_WARNING';
    case SECOND_WARNING = 'SECOND_WARNING';
    case CAPPED = 'CAPPED';
}
