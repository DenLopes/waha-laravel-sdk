<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

use DenLopes\Waha\Data\Input\CallsAppConfig;
use DenLopes\Waha\Data\Input\ChatWootAppConfig;
use DenLopes\Waha\Data\Input\McpAppConfig;
use DenLopes\Waha\Enums\AppType;

/**
 * A built-in WAHA application integration.
 */
final readonly class App extends Data
{
    /**
     * @param  Data|array  $config  App-specific configuration, narrowed by {@see self::$app}.
     * @param  AppType|null  $app  Built-in app type (null when WAHA returns an
     *                             unknown/forward-compatible value).
     */
    public function __construct(
        public string $id,
        public string $session,
        public ?AppType $app,
        public Data|array $config,
        public bool $enabled = true,
    ) {}

    public static function fromArray(array $data): static
    {
        $app = AppType::tryFrom((string) ($data['app'] ?? ''));
        $config = (array) ($data['config'] ?? []);

        return new self(
            id: (string) ($data['id'] ?? ''),
            session: (string) ($data['session'] ?? ''),
            app: $app,
            config: match ($app) {
                AppType::CHATWOOT         => ChatWootAppConfig::fromArray($config),
                AppType::CALLS            => CallsAppConfig::fromArray($config),
                AppType::MCP              => McpAppConfig::fromArray($config),
                default                   => $config,
            },
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }
}
