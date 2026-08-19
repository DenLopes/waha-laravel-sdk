<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\StopRequestData;
use DenLopes\Waha\Data\Output\HealthData;
use DenLopes\Waha\Data\Output\PingData;
use DenLopes\Waha\Data\Output\ServerStatusData;
use DenLopes\Waha\Data\Output\StopResponseData;
use DenLopes\Waha\Data\Output\WahaEnvironmentData;
use DenLopes\Waha\Support\WahaSession;

class ObservabilityService
{
    use SendsWahaRequests;

    /**
     * Ping the server.
     *
     * The /ping endpoint is public, so it does not require an API key.
     */
    public function ping(): PingData
    {
        $data = $this->send(
            'get',
            '/ping',
            [],
            'Communication with WAHA failed while pinging the server.',
            authenticated: false,
        );

        return PingData::fromArray($data);
    }

    /**
     * Check the server health.
     */
    public function getHealth(): HealthData
    {
        $data = $this->send('get', '/health', [], 'Communication with WAHA failed while checking the server health.');

        return HealthData::fromArray($data);
    }

    /**
     * Get the server version.
     */
    public function getVersion(): WahaEnvironmentData
    {
        $data = $this->send('get', '/api/server/version', [], 'Communication with WAHA failed while fetching the server version.');

        return WahaEnvironmentData::fromArray($data);
    }

    /**
     * Get the server version via the deprecated endpoint.
     */
    public function getVersionDeprecated(): WahaEnvironmentData
    {
        $data = $this->send('get', '/api/version', [], 'Communication with WAHA failed while fetching the server version.');

        return WahaEnvironmentData::fromArray($data);
    }

    /**
     * Get the server environment.
     */
    public function getServerEnvironment(bool $all = false): array
    {
        return $this->send('get', '/api/server/environment', [
            'all' => $all,
        ], 'Communication with WAHA failed while fetching the server environment.');
    }

    /**
     * Get the server status.
     */
    public function getServerStatus(): ServerStatusData
    {
        $data = $this->send('get', '/api/server/status', [], 'Communication with WAHA failed while fetching the server status.');

        return ServerStatusData::fromArray($data);
    }

    /**
     * Stop (and restart) the server.
     */
    public function stopServer(StopRequestData $request): StopResponseData
    {
        $data = $this->send('post', '/api/server/stop', $request->toArray(), 'Communication with WAHA failed while stopping the server.');

        return StopResponseData::fromArray($data);
    }

    /**
     * Collect and return a CPU profile for the current node process.
     */
    public function getCpuProfile(int $seconds = 30): string
    {
        return $this->download('/api/server/debug/cpu', [
            'seconds' => $seconds,
        ], 'Communication with WAHA failed while collecting the CPU profile.');
    }

    /**
     * Return a heap snapshot for the current node process.
     */
    public function getHeapsnapshot(): string
    {
        return $this->download('/api/server/debug/heapsnapshot', [], 'Communication with WAHA failed while collecting the heap snapshot.');
    }

    /**
     * Collect and return a trace.json for Chrome DevTools.
     *
     * @param  string[]  $categories
     */
    public function getBrowserTrace(WahaSession $session, int $seconds = 30, array $categories = ['*']): string
    {
        return $this->download("/api/server/debug/browser/trace/{$this->session($session)}", [
            'seconds'    => $seconds,
            'categories' => $categories,
        ], 'Communication with WAHA failed while collecting the browser trace.');
    }
}
