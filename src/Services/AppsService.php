<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\AppData;
use DenLopes\Waha\Support\WahaSession;

class AppsService
{
    use SendsWahaRequests;

    /**
     * List all apps for a session.
     *
     * @return AppData[]
     */
    public function listApps(WahaSession $session): array
    {
        $data = $this->send('get', '/api/apps', [
            'session' => $session->value(),
        ], 'Communication with WAHA failed while listing apps.');

        return array_map(
            static fn (array $item) => AppData::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new app.
     */
    public function createApp(AppData $app): AppData
    {
        $data = $this->send('post', '/api/apps', $app->toArray(), 'Communication with WAHA failed while creating the app.');

        return AppData::fromArray($data);
    }

    /**
     * Get an app by ID.
     */
    public function getApp(string $id): AppData
    {
        $data = $this->send('get', "/api/apps/{$id}", [], 'Communication with WAHA failed while fetching the app.');

        return AppData::fromArray($data);
    }

    /**
     * Update an existing app.
     */
    public function updateApp(string $id, AppData $app): AppData
    {
        $data = $this->send('put', "/api/apps/{$id}", $app->toArray(), 'Communication with WAHA failed while updating the app.');

        return AppData::fromArray($data);
    }

    /**
     * Delete an app.
     */
    public function deleteApp(string $id): array
    {
        return $this->send('delete', "/api/apps/{$id}", [], 'Communication with WAHA failed while deleting the app.');
    }

    /**
     * Send a raw JSON-RPC request to the WAHA MCP endpoint.
     */
    public function postMcp(array $payload): array
    {
        return $this->send('post', '/mcp', $payload, 'Communication with WAHA failed while calling the MCP endpoint.');
    }
}
