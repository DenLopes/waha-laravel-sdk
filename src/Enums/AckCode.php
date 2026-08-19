<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Numeric acknowledgment status for a WhatsApp message.
 *
 * The string representation of each status is available separately through
 * {@see Ack}, which is used for the `filter.ack` query parameter.
 */
enum AckCode: int
{
    case ERROR = -1;
    case PENDING = 0;
    case SERVER = 1;
    case DEVICE = 2;
    case READ = 3;
    case PLAYED = 4;
}
