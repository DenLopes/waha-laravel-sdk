<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * How API keys are resolved for a WAHA host.
 */
enum ApiKeyMode: string
{
    case ADMIN_FALLBACK = 'admin_fallback';
    case STRICT_SESSION_KEY = 'strict_session_key';
}
