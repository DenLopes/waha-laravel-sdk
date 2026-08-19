<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum Engine: string
{
    case WEBJS = 'WEBJS';
    case WPP = 'WPP';
    case NOWEB = 'NOWEB';
    case GOWS = 'GOWS';
}
