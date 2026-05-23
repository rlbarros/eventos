<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventServiceParticipantConsumption extends GenericModel
{
    protected $table = 'events_services_participants_consumption';

    protected $fillable = [
        'event_id',
        'event_service_id',
        'person_id',
        'quantity'
    ];

    public static function modelName(): string
    {
        return  "Consumos de Serviços do Participante";
    }

    public function descriptor(): string
    {
        if (!empty($this->id) && !empty($this->person_id)) {
            return $this->id . ' - ' . $this->person->name;
        }

        return '';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function event_service(): BelongsTo
    {
        return $this->belongsTo(EventService::class, 'event_service_id');
    }

    public function hasPayments(): bool
    {
        return EventServiceParticipantPayment::where('consumption_id', $this->id)->exists();
    }
}
