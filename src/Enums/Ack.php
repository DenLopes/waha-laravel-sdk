<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum Ack: string
{
    case ERROR = 'ERROR';
    case PENDING = 'PENDING';
    case SERVER = 'SERVER';
    case DEVICE = 'DEVICE';
    case READ = 'READ';
    case PLAYED = 'PLAYED';
}
