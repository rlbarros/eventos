<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventService extends Model
{
    protected $table = 'events_services';

    protected $fillable = [
        'event_id',
        'name',
        'fee'
    ];

    public function Event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
