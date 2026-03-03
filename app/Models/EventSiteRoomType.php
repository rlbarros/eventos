<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSiteRoomType extends Model
{
    protected $table = 'event_site_room_types';

    protected $fillable = [
        'name',
        'event_site_id',
        'type',
        'beds'
    ];

    public function EventSite(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }
}
