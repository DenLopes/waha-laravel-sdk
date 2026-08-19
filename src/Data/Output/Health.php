<?php

declare(strict_types=1);

namespace DenLopes\Waha\Data\Output;

use DenLopes\Waha\Data\Data;

/**
 * Server health check result.
 */
final readonly class Health extends Data
{
    /**
     * @param  array|null  $info  Successful health checks.
     * @param  array|null  $error  Failed health checks.
     * @param  array|null  $details  Detailed health check output.
     */
    public function __construct(
        public string $status,
        public ?array $info,
        public ?array $error,
        public ?array $details,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
            info: $data['info'] ?? null,
            error: $data['error'] ?? null,
            details: $data['details'] ?? null,
        );
    }
}
