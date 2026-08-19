<?php

declare(strict_types=1);

namespace DenLopes\Waha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A WAHA server definition, used by the DB host registry.
 *
 * @property int $id
 * @property string $key
 * @property string $base_url
 * @property string|null $api_key
 * @property string $api_key_header
 * @property string|null $default_session
 * @property string $mode
 * @property array|null $session_keys
 * @property string|null $webhook_secret
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WahaHost extends Model
{
    protected $table = 'waha_hosts';

    protected $guarded = ['id'];

    protected $casts = [
        'session_keys' => 'array',
        'is_active'    => 'boolean',
    ];
}
