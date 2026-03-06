<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSiteRoom extends Model
{
    protected $table = 'event_site_rooms';

    protected $fillable = [
        'event_site_id',
        'event_site_room_type_id',
        'name',
    ];

    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }

    public function event_site_room_type(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoomType::class, 'event_site_room_type_id');
    }
}
