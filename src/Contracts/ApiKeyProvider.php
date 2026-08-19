<?php

declare(strict_types=1);

namespace DenLopes\Waha\Contracts;

use DenLopes\Waha\Enums\WahaApiKeyModeEnum;

interface ApiKeyProvider
{
    public function headerName(string $hostKey): string;

    public function adminKey(string $hostKey): ?string;

    public function sessionKey(string $hostKey, string $sessionName): ?string;

    public function mode(string $hostKey): WahaApiKeyModeEnum;
}
