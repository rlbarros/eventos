<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventServiceParticipantConsumption extends Model
{
    protected $table = 'events_services_participants_consumptions';

    protected $fillable = [
        'event_id',
        'person_id',
        'paymanent_date',
        'amount'
    ];

    public function Event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function Person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
