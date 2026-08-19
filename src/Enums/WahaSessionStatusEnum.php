<?php

declare(strict_types=1);

namespace DenLopes\Waha\Enums;

enum WahaSessionStatusEnum: string
{
    case STOPPED = 'STOPPED';
    case STARTING = 'STARTING';
    case SCAN_QR_CODE = 'SCAN_QR_CODE';
    case PASSKEY_REQUIRED = 'PASSKEY_REQUIRED';
    case PASSKEY_CONFIRMATION_REQUIRED = 'PASSKEY_CONFIRMATION_REQUIRED';
    case WORKING = 'WORKING';
    case FAILED = 'FAILED';
}
