<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\ReachoutGuard as ReachoutGuardContract;
use DenLopes\Waha\Data\Output\MessageCapping;
use DenLopes\Waha\Data\Output\ReachoutTimelock;
use DenLopes\Waha\Exceptions\ApiException;
use DenLopes\Waha\Exceptions\ReachoutQuotaExhaustedException;
use DenLopes\Waha\Exceptions\ReachoutTimelockActiveException;
use DenLopes\Waha\Services\SessionService;
use DenLopes\Waha\Session;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Gates cold sends on WhatsApp's own reachout signals.
 *
 * Capping and timelock snapshots are cached briefly to avoid an HTTP call on
 * every send. A local cold counter bridges the gap between snapshots so the SDK
 * does not overshoot the server quota while the cached value is stale.
 */
final class ReachoutGuard implements ReachoutGuardContract
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly bool $enabled = true,
        private readonly int $cappingCacheSeconds = 30,
        private readonly int $timelockCacheSeconds = 60,
        private readonly string $prefix = 'waha:conversation:',
        private readonly ?string $store = null,
    ) {}

    public function assertAllowed(string $sessionName): void
    {
        if (!$this->enabled) {
            return;
        }

        $session = Session::from($sessionName);

        $timelock = $this->timelock($session);

        if ($timelock !== null && $timelock->isActive) {
            $availableInSeconds = $this->timelockAvailableSeconds($timelock);

            throw new ReachoutTimelockActiveException(
                message: sprintf('Session %s has an active reachout timelock.', $sessionName),
                context: [
                    'session'               => $sessionName,
                    'enforcement_type'      => $timelock->enforcementTypeRaw,
                    'time_enforcement_ends' => $timelock->timeEnforcementEnds,
                    'available_in_seconds'  => $availableInSeconds,
                ],
                session: $sessionName,
                availableInSeconds: $availableInSeconds,
            );
        }

        $capping = $this->capping($session);

        if ($capping === null || $capping->totalQuota === null) {
            return;
        }

        $usedQuota = $capping->usedQuota ?? 0;
        $totalQuota = $capping->totalQuota;

        if ($totalQuota === 0) {
            throw new ReachoutQuotaExhaustedException(
                message: sprintf('Session %s has no reachout quota allowance.', $sessionName),
                context: [
                    'session'              => $sessionName,
                    'used_quota'           => $usedQuota,
                    'total_quota'          => $totalQuota,
                    'available_in_seconds' => 0,
                ],
                session: $sessionName,
                usedQuota: $usedQuota,
                totalQuota: $totalQuota,
                availableInSeconds: 0,
            );
        }

        if ($usedQuota + $this->localCounter($session) >= $totalQuota) {
            throw new ReachoutQuotaExhaustedException(
                message: sprintf('Session %s reachout quota is exhausted.', $sessionName),
                context: [
                    'session'              => $sessionName,
                    'used_quota'           => $usedQuota,
                    'total_quota'          => $totalQuota,
                    'available_in_seconds' => $this->quotaAvailableSeconds($capping),
                ],
                session: $sessionName,
                usedQuota: $usedQuota,
                totalQuota: $totalQuota,
                availableInSeconds: $this->quotaAvailableSeconds($capping),
            );
        }
    }

    public function recordColdSent(string $sessionName): void
    {
        if (!$this->enabled) {
            return;
        }

        $key = $this->counterKey(Session::from($sessionName));
        $ttl = max(1, $this->cappingCacheSeconds);

        if (!$this->cache()->add($key, 1, $ttl)) {
            $this->cache()->increment($key, 1);
        }
    }

    private function timelock(Session $session): ?ReachoutTimelock
    {
        $key = $this->prefix.'reachout:timelock:'.$session->value();

        $cached = $this->cache()->get($key);

        if ($cached instanceof ReachoutTimelock) {
            return $cached;
        }

        try {
            $timelock = $this->sessionService->fetchReachoutTimelock($session);
        } catch (ApiException) {
            return null;
        }

        $ttl = $this->timelockCacheSeconds;

        if ($timelock->isActive && $timelock->timeEnforcementEnds !== null) {
            $ttl = max(1, $timelock->timeEnforcementEnds - time());
        }

        $this->cache()->put($key, $timelock, max(1, $ttl));

        return $timelock;
    }

    private function capping(Session $session): ?MessageCapping
    {
        $key = $this->prefix.'reachout:capping:'.$session->value();

        $cached = $this->cache()->get($key);

        if ($cached instanceof MessageCapping) {
            return $cached;
        }

        try {
            $capping = $this->sessionService->fetchMessageCapping($session);
        } catch (ApiException) {
            return null;
        }

        $this->cache()->put($key, $capping, max(1, $this->cappingCacheSeconds));

        return $capping;
    }

    private function localCounter(Session $session): int
    {
        return (int) $this->cache()->get($this->counterKey($session), 0);
    }

    private function counterKey(Session $session): string
    {
        return $this->prefix.'reachout:cold:'.$session->value();
    }

    private function timelockAvailableSeconds(ReachoutTimelock $timelock): int
    {
        if ($timelock->timeEnforcementEnds === null) {
            return 0;
        }

        return max(0, $timelock->timeEnforcementEnds - time());
    }

    private function quotaAvailableSeconds(MessageCapping $capping): int
    {
        if ($capping->cycleEnd === null) {
            return 0;
        }

        return max(0, $capping->cycleEnd - time());
    }

    private function cache(): Repository
    {
        return $this->store !== null ? Cache::store($this->store) : Cache::store();
    }
}
