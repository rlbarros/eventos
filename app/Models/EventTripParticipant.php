<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTripParticipant extends Model
{
    protected $table = 'events_trips_participants';

    protected $fillable = [
        'event_trip_id',
        'person_id'
    ];

    public function EventTrip(): BelongsTo
    {
        return $this->belongsTo(EventTrip::class, 'event_trip_id');
    }

    public function Person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
