<?php

declare(strict_types=1);

namespace DenLopes\Waha\Pin;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Models\WahaSessionPin;

/**
 * Stores the session name → host key mapping in the `waha_session_pins` table.
 */
final class DbPinStore implements PinStore
{
    public function getHostForSession(string $sessionName): ?string
    {
        return WahaSessionPin::query()->where('session_name', $sessionName)->value('host_key');
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        WahaSessionPin::query()->updateOrCreate(
            ['session_name' => $sessionName],
            ['host_key' => $hostKey],
        );
    }

    public function forget(string $sessionName): void
    {
        WahaSessionPin::query()->where('session_name', $sessionName)->delete();
    }
}
