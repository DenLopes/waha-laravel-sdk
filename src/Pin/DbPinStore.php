<?php

declare(strict_types=1);

namespace DenLopes\Waha\Pin;

use DenLopes\Waha\Contracts\PinStore;
use DenLopes\Waha\Models\SessionPin;
use DenLopes\Waha\Routing\PinningRouter;

/**
 * Database-backed {@see PinStore}.
 *
 * Stores the "session name → host key" mapping used by {@see PinningRouter}
 * to route each WAHA session to the server that owns it. This is what lets a
 * multi-tenant application give each company its own WhatsApp number — and,
 * when a company grows, its own WAHA host — without the SDK knowing anything
 * about companies, tenants, or events.
 *
 * The mapping is only consulted when `waha.routing.driver` is set to `pin`.
 * Write it through {@see PinStore} (resolve it from the container), then read
 * it implicitly on every normal SDK call:
 *
 *     $pins = app(\DenLopes\Waha\Contracts\PinStore::class);
 *     $pins->pin('company-123', 'company-host');
 *     $pins->forget('company-123');
 */
final class DbPinStore implements PinStore
{
    public function getHostForSession(string $sessionName): ?string
    {
        $pin = SessionPin::query()->where('session_name', $sessionName)->first();

        if ($pin === null) {
            return null;
        }

        if ($pin->expires_at !== null && $pin->expires_at->isPast()) {
            $pin->delete();

            return null;
        }

        return $pin->host_key;
    }

    public function pin(string $sessionName, string $hostKey, ?int $ttlSeconds = null): void
    {
        $attributes = [
            'host_key'     => $hostKey,
            'last_seen_at' => now(),
        ];

        if ($ttlSeconds !== null && $ttlSeconds > 0) {
            $attributes['expires_at'] = now()->addSeconds($ttlSeconds);
        } else {
            $attributes['expires_at'] = null;
        }

        SessionPin::query()->updateOrCreate(
            ['session_name' => $sessionName],
            $attributes,
        );
    }

    public function forget(string $sessionName): void
    {
        SessionPin::query()->where('session_name', $sessionName)->delete();
    }
}
