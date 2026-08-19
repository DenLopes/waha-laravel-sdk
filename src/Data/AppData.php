<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

use DenLopes\Waha\Enums\WahaAppTypeEnum;

/**
 * A built-in WAHA application integration.
 */
final readonly class AppData extends WahaData
{
    /**
     * @param  array|null  $config  App-specific configuration.
     * @param  WahaAppTypeEnum|null  $app  Built-in app type (null when WAHA returns an
     *                                     unknown/forward-compatible value).
     */
    public function __construct(
        public string $id,
        public string $session,
        public ?WahaAppTypeEnum $app,
        public array $config,
        public bool $enabled = true,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            session: (string) ($data['session'] ?? ''),
            app: WahaAppTypeEnum::tryFrom((string) ($data['app'] ?? '')),
            config: (array) ($data['config'] ?? []),
            enabled: (bool) ($data['enabled'] ?? true),
        );
    }
}
