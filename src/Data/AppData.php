<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

use DenLopes\Waha\Data\Input\CallsAppConfigData;
use DenLopes\Waha\Data\Input\ChatWootAppConfigData;
use DenLopes\Waha\Data\Input\McpAppConfigData;
use DenLopes\Waha\Enums\WahaAppTypeEnum;

/**
 * A built-in WAHA application integration.
 */
final readonly class AppData extends WahaData
{
    /**
     * @param  WahaData|array  $config  App-specific configuration, narrowed by {@see self::$app}.
     * @param  WahaAppTypeEnum|null  $app  Built-in app type (null when WAHA returns an
     *                                     unknown/forward-compatible value).
     */
    public function __construct(
        public string $id,
        public string $session,
        public ?WahaAppTypeEnum $app,
        public WahaData|array $config,
        public bool $enabled = true,
    ) {}

    public static function fromArray(array $data): static
    {
        $app = WahaAppTypeEnum::tryFrom((string) ($data['app'] ?? ''));
        $config = (array) ($data['config'] ?? []);

        return new self(
            id: (string) ($data['id'] ?? ''),
            session: (string) ($data['session'] ?? ''),
            app: $app,
            config: match ($app) {
                WahaAppTypeEnum::CHATWOOT => ChatWootAppConfigData::fromArray($config),
                WahaAppTypeEnum::CALLS    => CallsAppConfigData::fromArray($config),
                WahaAppTypeEnum::MCP      => McpAppConfigData::fromArray($config),
                default                   => $config,
            },
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }
}
