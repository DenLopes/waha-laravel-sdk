<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\App;
use DenLopes\Waha\Session;

class AppsService
{
    use SendsRequests;

    /**
     * List all apps for a session.
     *
     * @return App[]
     */
    public function listApps(Session $session): array
    {
        $data = $this->send('get', '/api/apps', [
            'session' => $session->value(),
        ], 'Communication with WAHA failed while listing apps.');

        return array_map(
            static fn (array $item) => App::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new app.
     */
    public function createApp(App $app): App
    {
        $data = $this->send('post', '/api/apps', $app->toArray(), 'Communication with WAHA failed while creating the app.');

        return App::fromArray($data);
    }

    /**
     * Get an app by ID.
     */
    public function getApp(string $id): App
    {
        $data = $this->send('get', "/api/apps/{$id}", [], 'Communication with WAHA failed while fetching the app.');

        return App::fromArray($data);
    }

    /**
     * Update an existing app.
     */
    public function updateApp(string $id, App $app): App
    {
        $data = $this->send('put', "/api/apps/{$id}", $app->toArray(), 'Communication with WAHA failed while updating the app.');

        return App::fromArray($data);
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
