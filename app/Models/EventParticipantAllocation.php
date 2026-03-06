<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipantAllocation extends Model
{
    protected $table = 'events_participants_allocations';

    protected $fillable = [
        'event_id',
        'event_site_room_id',
        'person_id'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function event_site_room(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoom::class, 'event_site_room_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
