<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Sort order used by the ChatWoot conversations config.
 */
enum WahaChatWootSortEnum: string
{
    case ACTIVITY_NEWEST = 'activity_newest';
    case CREATED_NEWEST = 'created_newest';
    case CREATED_OLDEST = 'created_oldest';
    case ACTIVITY_OLDEST = 'activity_oldest';
}
