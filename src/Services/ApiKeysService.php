<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsWahaRequests;
use DenLopes\Waha\Data\Input\ApiKeyRequestData;
use DenLopes\Waha\Data\Input\ScopedApiKeyRequestData;
use DenLopes\Waha\Data\Output\ApiKeyData;
use DenLopes\Waha\Support\WahaSession;

class ApiKeysService
{
    use SendsWahaRequests;

    /**
     * Get all API keys.
     *
     * @return ApiKeyData[]
     */
    public function listApiKeys(): array
    {
        $data = $this->send('get', '/api/keys', [], 'Communication with WAHA failed while listing API keys.');

        return array_map(
            static fn (array $item) => ApiKeyData::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new API key.
     */
    public function createApiKey(ApiKeyRequestData $payload): ApiKeyData
    {
        $data = $this->send('post', '/api/keys', $payload->toArray(), 'Communication with WAHA failed while creating the API key.');

        return ApiKeyData::fromArray($data);
    }

    /**
     * Create or get a media-download-only API key for a session.
     */
    public function createMediaApiKey(WahaSession $session): ApiKeyData
    {
        $data = $this->send(
            'post',
            '/api/keys/media',
            (new ScopedApiKeyRequestData($session->value()))->toArray(),
            'Communication with WAHA failed while creating the media API key.',
        );

        return ApiKeyData::fromArray($data);
    }

    /**
     * Create or get a control-only API key for a session.
     */
    public function createControlApiKey(WahaSession $session): ApiKeyData
    {
        $data = $this->send(
            'post',
            '/api/keys/control',
            (new ScopedApiKeyRequestData($session->value()))->toArray(),
            'Communication with WAHA failed while creating the control API key.',
        );

        return ApiKeyData::fromArray($data);
    }

    /**
     * Update an API key.
     */
    public function updateApiKey(string $id, ApiKeyRequestData $payload): ApiKeyData
    {
        $data = $this->send('put', "/api/keys/{$id}", $payload->toArray(), 'Communication with WAHA failed while updating the API key.');

        return ApiKeyData::fromArray($data);
    }

    /**
     * Delete an API key.
     */
    public function deleteApiKey(string $id): array
    {
        return $this->send('delete', "/api/keys/{$id}", [], 'Communication with WAHA failed while deleting the API key.');
    }
}
