<?php

declare(strict_types=1);

namespace DenLopes\Waha\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps a WAHA session name to the host that owns it.
 *
 * @property int $id
 * @property string $session_name
 * @property string $host_key
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WahaSessionPin extends Model
{
    protected $table = 'waha_session_pins';

    protected $guarded = ['id'];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];
}
