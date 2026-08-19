<?php

declare(strict_types=1);

namespace DenLopes\Waha\Services;

use DenLopes\Waha\Concerns\SendsRequests;
use DenLopes\Waha\Data\Input\ApiKeyRequest;
use DenLopes\Waha\Data\Input\ScopedApiKeyRequest;
use DenLopes\Waha\Data\Output\ApiKey;
use DenLopes\Waha\Session;

class ApiKeysService
{
    use SendsRequests;

    /**
     * Get all API keys.
     *
     * @return ApiKey[]
     */
    public function listApiKeys(): array
    {
        $data = $this->send('get', '/api/keys', [], 'Communication with WAHA failed while listing API keys.');

        return array_map(
            static fn (array $item) => ApiKey::fromArray($item),
            $data,
        );
    }

    /**
     * Create a new API key.
     */
    public function createApiKey(ApiKeyRequest $payload): ApiKey
    {
        $data = $this->send('post', '/api/keys', $payload->toArray(), 'Communication with WAHA failed while creating the API key.');

        return ApiKey::fromArray($data);
    }

    /**
     * Create or get a media-download-only API key for a session.
     */
    public function createMediaApiKey(Session $session): ApiKey
    {
        $data = $this->send(
            'post',
            '/api/keys/media',
            (new ScopedApiKeyRequest($session->value()))->toArray(),
            'Communication with WAHA failed while creating the media API key.',
        );

        return ApiKey::fromArray($data);
    }

    /**
     * Create or get a control-only API key for a session.
     */
    public function createControlApiKey(Session $session): ApiKey
    {
        $data = $this->send(
            'post',
            '/api/keys/control',
            (new ScopedApiKeyRequest($session->value()))->toArray(),
            'Communication with WAHA failed while creating the control API key.',
        );

        return ApiKey::fromArray($data);
    }

    /**
     * Update an API key.
     */
    public function updateApiKey(string $id, ApiKeyRequest $payload): ApiKey
    {
        $data = $this->send('put', "/api/keys/{$id}", $payload->toArray(), 'Communication with WAHA failed while updating the API key.');

        return ApiKey::fromArray($data);
    }

    /**
     * Delete an API key.
     */
    public function deleteApiKey(string $id): array
    {
        return $this->send('delete', "/api/keys/{$id}", [], 'Communication with WAHA failed while deleting the API key.');
    }
}
