<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTrip extends Model
{
    protected $table = 'events_drivers';

    protected $fillable = [
        'event_driver_id',
        'from',
        'start_date',
        'to',
        'end_date',
    ];

    public function event_driver(): BelongsTo
    {
        return $this->belongsTo(EventDriver::class, 'event_driver_id');
    }
}
