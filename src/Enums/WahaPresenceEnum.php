<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum WahaPresenceEnum: string
{
    case OFFLINE = 'offline';
    case ONLINE = 'online';
    case TYPING = 'typing';
    case RECORDING = 'recording';
    case PAUSED = 'paused';
}
