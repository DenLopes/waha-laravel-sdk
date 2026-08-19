<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Data\HostConfig;

/**
 * Provides host definitions (base_url, api_key, default_session, mode, ...).
 */
interface HostRegistry
{
    public function get(string $hostKey): HostConfig;

    /**
     * @return array<string, HostConfig>
     */
    public function all(): array;

    public function exists(string $hostKey): bool;
}
