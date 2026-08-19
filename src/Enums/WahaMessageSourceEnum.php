<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * The device that produced a message.
 *
 * Present in webhook/websocket events only and only for "fromMe: true" messages.
 */
enum WahaMessageSourceEnum: string
{
    case API = 'api';
    case APP = 'app';
}
