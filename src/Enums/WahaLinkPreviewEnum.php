<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Link preview quality levels used by the ChatWoot app config.
 */
enum WahaLinkPreviewEnum: string
{
    case OFF = 'OFF';
    case LG = 'LG';
    case HG = 'HG';
}
