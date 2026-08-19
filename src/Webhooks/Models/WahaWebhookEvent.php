<?php

declare(strict_types=1);

namespace DenLopes\Waha\Webhooks\Models;

use DenLopes\Waha\Enums\WahaWebhookEventEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * A persisted WAHA webhook delivery.
 *
 * Kept intentionally minimal (matching the application's webhook storage style):
 * the `payload` column holds the full JSON envelope, so no event-specific schema
 * is required.
 *
 * @property int $id
 * @property string|null $event
 * @property string|null $session
 * @property string|null $request_id
 * @property string|null $host_key
 * @property string $payload
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WahaWebhookEvent extends Model
{
    protected $table = 'waha_webhook_events';

    protected $guarded = ['id'];

    protected $casts = [
        'event'   => WahaWebhookEventEnum::class,
        'payload' => 'array',
    ];
}
