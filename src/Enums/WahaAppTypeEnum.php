<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Built-in WAHA application integrations.
 */
enum WahaAppTypeEnum: string
{
    case CHATWOOT = 'chatwoot';
    case CALLS = 'calls';
    case MCP = 'mcp';
}
