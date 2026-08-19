<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * The action type of an interactive button.
 */
enum WahaButtonTypeEnum: string
{
    case REPLY = 'reply';
    case URL = 'url';
    case CALL = 'call';
    case COPY = 'copy';
}
