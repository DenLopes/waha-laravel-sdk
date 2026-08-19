<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\WahaHostConfigData;

/**
 * Provides host definitions (base_url, api_key, default_session, mode, ...).
 */
interface HostRegistry
{
    public function get(string $hostKey): WahaHostConfigData;

    /**
     * @return array<string, WahaHostConfigData>
     */
    public function all(): array;

    public function exists(string $hostKey): bool;
}
