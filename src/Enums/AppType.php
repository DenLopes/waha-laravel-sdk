<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

/**
 * Built-in WAHA application integrations.
 */
enum AppType: string
{
    case CHATWOOT = 'chatwoot';
    case CALLS = 'calls';
    case MCP = 'mcp';
}
