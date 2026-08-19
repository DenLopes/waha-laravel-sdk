<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Webhook delivery retry back-off policies.
 */
enum WahaRetriesPolicyEnum: string
{
    case LINEAR = 'linear';
    case EXPONENTIAL = 'exponential';
    case CONSTANT = 'constant';
}
