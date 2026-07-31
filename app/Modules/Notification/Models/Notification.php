<?php

declare(strict_types=1);

namespace App\Modules\Notification\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Standard Laravel-compatible Notification model.
 *
 * Maps to the `notifications` table with the polymorphic schema
 * required by Filament DatabaseNotifications widget and Laravel's Notifiable trait.
 */
class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    /** Polymorphic owner (User, Driver, Vendor, etc.) */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
