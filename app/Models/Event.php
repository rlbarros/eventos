<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends GenericModel
{
    use WithNameDescriptor;

    protected $table = 'events';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'church_id',
        'event_site_id',
        'children_age'
    ];

    public static function modelName(): string
    {
        return  "Evento";
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }

    public function hasDependencies()
    {
        $hasParticipants = EventParticipantAllocation::where('event_id', $this->id)->exists();
        $hasBatches = EventBatch::where('event_id', $this->id)->exists();
        $hasFees = EventFee::where('event_id', $this->id)->exists();
        $hasServices = EventService::where('event_id', $this->id)->exists();
        $hasTrips = EventTrip::where('event_id', $this->id)->exists();

        return $hasParticipants || $hasBatches || $hasFees || $hasServices || $hasTrips;
    }
}
