<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

/**
 * Provides host definitions (base_url, api_key, default_session, mode, ...).
 */
interface HostRegistry
{
    /**
     * @return array<string, mixed>
     */
    public function get(string $hostKey): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array;

    public function exists(string $hostKey): bool;
}
