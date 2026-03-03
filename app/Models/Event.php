<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'church_id',
        'event_site_id',
    ];

    public function Church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function EventSite(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }
}
