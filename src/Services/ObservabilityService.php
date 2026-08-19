<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\StopRequest;
use DenLopes\Waha\Data\Output\Environment;
use DenLopes\Waha\Data\Output\Health;
use DenLopes\Waha\Data\Output\Ping;
use DenLopes\Waha\Data\Output\ServerStatus;
use DenLopes\Waha\Data\Output\StopResponse;
use DenLopes\Waha\Session;

class ObservabilityService
{
    use SendsRequests;

    /**
     * Ping the server.
     *
     * The /ping endpoint is public, so it does not require an API key.
     */
    public function ping(): Ping
    {
        $data = $this->send(
            'get',
            '/ping',
            [],
            'Communication with WAHA failed while pinging the server.',
            authenticated: false,
        );

        return Ping::fromArray($data);
    }

    /**
     * Check the server health.
     */
    public function getHealth(): Health
    {
        $data = $this->send('get', '/health', [], 'Communication with WAHA failed while checking the server health.');

        return Health::fromArray($data);
    }

    /**
     * Get the server version.
     */
    public function getVersion(): Environment
    {
        $data = $this->send('get', '/api/server/version', [], 'Communication with WAHA failed while fetching the server version.');

        return Environment::fromArray($data);
    }

    /**
     * Get the server version via the deprecated endpoint.
     */
    public function getVersionDeprecated(): Environment
    {
        $data = $this->send('get', '/api/version', [], 'Communication with WAHA failed while fetching the server version.');

        return Environment::fromArray($data);
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
    public function getServerStatus(): ServerStatus
    {
        $data = $this->send('get', '/api/server/status', [], 'Communication with WAHA failed while fetching the server status.');

        return ServerStatus::fromArray($data);
    }

    /**
     * Stop (and restart) the server.
     */
    public function stopServer(StopRequest $request): StopResponse
    {
        $data = $this->send('post', '/api/server/stop', $request->toArray(), 'Communication with WAHA failed while stopping the server.');

        return StopResponse::fromArray($data);
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
    public function getBrowserTrace(Session $session, int $seconds = 30, array $categories = ['*']): string
    {
        return $this->download('/api/server/debug/browser/trace/{session}', [
            'seconds'    => $seconds,
            'categories' => $categories,
        ], 'Communication with WAHA failed while collecting the browser trace.', session: $session);
    }
}
