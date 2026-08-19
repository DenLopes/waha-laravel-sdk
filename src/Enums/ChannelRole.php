<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum ChannelRole: string
{
    case OWNER = 'OWNER';
    case ADMIN = 'ADMIN';
    case SUBSCRIBER = 'SUBSCRIBER';
    case GUEST = 'GUEST';
}
