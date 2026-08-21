<?php

declare(strict_types=1);

namespace DenLopes\Waha\Support;

use DenLopes\Waha\Contracts\CircuitBreaker;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * Session-scoped delivery-failure circuit breaker backed by cache.
 *
 * Samples are stored as a pruned list of `[timestamp, ok]` pairs. The breaker
 * opens once the failure rate over the window clears the threshold with at least
 * {@see self::$minSamples} samples, then stays open for the cooldown period.
 */
final class CacheCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        private readonly int $failureWindowSeconds = 900,
        private readonly float $failureRateThreshold = 0.3,
        private readonly int $minSamples = 20,
        private readonly int $cooldownSeconds = 300,
        private readonly ?string $store = null,
        private readonly string $prefix = 'waha:conversation:',
    ) {}

    public function isOpen(string $sessionName): bool
    {
        $state = $this->load($sessionName);
        $now = time();

        if ($state['opened_at'] !== null && ($now - $state['opened_at']) < $this->cooldownSeconds) {
            return true;
        }

        $samples = $this->prune($state['samples'], $now);
        $total = count($samples);
        $failures = count(array_filter($samples, static fn (array $sample): bool => !$sample['ok']));

        if ($total >= $this->minSamples && ($failures / $total) >= $this->failureRateThreshold) {
            $this->save($sessionName, ['samples' => $samples, 'opened_at' => $now]);

            return true;
        }

        $this->save($sessionName, ['samples' => $samples, 'opened_at' => null]);

        return false;
    }

    public function recordSuccess(string $sessionName): void
    {
        $this->record($sessionName, true);
    }

    public function recordFailure(string $sessionName): void
    {
        $this->record($sessionName, false);
    }

    private function record(string $sessionName, bool $ok): void
    {
        $state = $this->load($sessionName);
        $now = time();

        $state['samples'][] = ['t' => $now, 'ok' => $ok];
        $state['samples'] = $this->prune($state['samples'], $now);

        $this->save($sessionName, $state);
    }

    /**
     * @return array{samples: list<array{t: int, ok: bool}>, opened_at: ?int}
     */
    private function load(string $sessionName): array
    {
        $state = $this->cache()->get($this->key($sessionName));

        if (!is_array($state)) {
            return ['samples' => [], 'opened_at' => null];
        }

        return [
            'samples'   => is_array($state['samples'] ?? null) ? $state['samples'] : [],
            'opened_at' => is_int($state['opened_at'] ?? null) ? $state['opened_at'] : null,
        ];
    }

    /**
     * @param  array{samples: list<array{t: int, ok: bool}>, opened_at: ?int}  $state
     */
    private function save(string $sessionName, array $state): void
    {
        $this->cache()->put($this->key($sessionName), $state, $this->ttlSeconds());
    }

    /**
     * @param  list<array{t: int, ok: bool}>  $samples
     * @return list<array{t: int, ok: bool}>
     */
    private function prune(array $samples, int $now): array
    {
        $cutoff = $now - $this->failureWindowSeconds;

        return array_values(array_filter(
            $samples,
            static fn (array $sample): bool => $sample['t'] > $cutoff,
        ));
    }

    private function key(string $sessionName): string
    {
        return $this->prefix.'breaker:'.$sessionName;
    }

    private function ttlSeconds(): int
    {
        return $this->failureWindowSeconds + $this->cooldownSeconds + 60;
    }

    private function cache(): Repository
    {
        return $this->store !== null ? Cache::store($this->store) : Cache::store();
    }
}
