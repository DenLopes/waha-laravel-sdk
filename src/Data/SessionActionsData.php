<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data;

final readonly class SessionActionsData extends WahaData
{
    public function __construct(
        public bool $read,
        public bool $send,
        public bool $control,
        public bool $setting,
        public bool $app,
        public bool $delete,
    ) {}

    /**
     * Create an instance from a raw WAHA API response array.
     *
     * @param  array  $data  Raw WAHA API response array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            read: (bool) ($data['read'] ?? false),
            send: (bool) ($data['send'] ?? false),
            control: (bool) ($data['control'] ?? false),
            setting: (bool) ($data['setting'] ?? false),
            app: (bool) ($data['app'] ?? false),
            delete: (bool) ($data['delete'] ?? false),
        );
    }
}
