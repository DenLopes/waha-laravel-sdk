<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Possible answers to an event (RSVP) message.
 */
enum EventResponseStatus: string
{
    case UNKNOWN = 'UNKNOWN';
    case GOING = 'GOING';
    case NOT_GOING = 'NOT_GOING';
    case MAYBE = 'MAYBE';
}
