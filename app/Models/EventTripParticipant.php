<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTripParticipant extends GenericModel
{
    protected $table = 'events_trips_participants';

    public $timestamps = false;

    protected $fillable = [
        'event_trip_id',
        'person_id'
    ];

    public static function modelName(): string
    {
        return  "Participantes da Viagem";
    }

    public function descriptor(): string
    {
        if (empty($this->id) || empty($this->person_id)) {
            return '';
        }
        return $this->id . ' - '  . $this->person->name;
    }


    public function event_trip(): BelongsTo
    {
        return $this->belongsTo(EventTrip::class, 'event_trip_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
