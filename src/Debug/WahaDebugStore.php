<?php

declare(strict_types=1);

namespace DenLopes\Waha\Debug;

use DenLopes\Waha\Fluent\WahaManager;
use DenLopes\Waha\Http\WahaRequest;

/**
 * Holds the last masked WAHA request/response for debugging.
 *
 * Populated by {@see WahaRequest} on every request and
 * exposed through {@see WahaManager::lastHttp()} /
 * {@see WahaManager::lastHttpCurl()}.
 */
final class WahaDebugStore
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $last = null;

    /**
     * @param  array<string, mixed>  $entry
     */
    public function setLast(array $entry): void
    {
        $this->last = $entry;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function last(): ?array
    {
        return $this->last;
    }

    /**
     * Render the last request as a copy-pasteable curl command.
     */
    public function lastCurl(): ?string
    {
        $request = $this->last['request'] ?? null;

        if (!is_array($request)) {
            return null;
        }

        $method = strtoupper((string) ($request['method'] ?? 'GET'));
        $url = (string) ($request['url'] ?? '');
        $headers = $request['headers'] ?? [];
        $payload = $request['payload'] ?? null;
        $query = $request['query'] ?? [];

        $parts = ['curl -i'];

        if (is_array($headers)) {
            foreach ($headers as $name => $value) {
                $parts[] = '-H '.escapeshellarg((string) $name.': '.(string) $value);
            }
        }

        if ($method === 'GET' && is_array($query) && $query !== []) {
            $parts[] = '-G';

            foreach ($query as $name => $value) {
                foreach ((array) $value as $item) {
                    $parts[] = '--data-urlencode '.escapeshellarg((string) $name.'='.(string) $item);
                }
            }
        } elseif ($payload !== null && $payload !== [] && $payload !== '') {
            $parts[] = '--data-raw '.escapeshellarg(is_array($payload) ? json_encode($payload) : (string) $payload);
        }

        $parts[] = '-X '.escapeshellarg($method);
        $parts[] = escapeshellarg($url);

        return implode(" \\\n  ", $parts);
    }

    public function clear(): void
    {
        $this->last = null;
    }
}
