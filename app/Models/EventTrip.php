<?php

namespace App\Models;

use App\Models\EventTripParticipant;
use App\Utils\DateUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTrip extends GenericModel
{
    protected $table = 'events_trips';

    protected $fillable = [
        'event_id',
        'event_driver_id',
        'from',
        'start_date',
        'to',
        'end_date',
    ];

    public static function modelName(): string
    {
        return  "Viagem do Evento";
    }

    public function descriptor(): string
    {
        if (empty($this->event_driver_id) || empty($this->from) || empty($this->to) || empty($this->start_date) || empty($this->end_date)) {
            return '';
        }
        return 'de ' . $this->from . ' (' . DateUtil::formatDateTimeToBr($this->start_date)
            . ') para ' . $this->to . ' (' . DateUtil::formatDateTimeToBr($this->end_date)
            . ' | motorista: ' . $this->event_driver->name
            . ' | veículo: ' . $this->event_driver->vehicle;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function event_driver(): BelongsTo
    {
        return $this->belongsTo(EventDriver::class, 'event_driver_id');
    }

    public function event_trip_participants(): HasMany
    {
        return $this->hasMany(EventTripParticipant::class, 'event_trip_id');
    }

    public function hasPassengers(): bool
    {
        return EventTripParticipant::where('event_trip_id', $this->id)->exists();
    }

    public function capacity(): string
    {
        $count = $this->event_trip_participants()->count();
        $capacity = $this->event_driver?->capacity ?? 0;

        return $count . ' / ' . $capacity;
    }
}
