<?php

namespace App\Models;

use App\Utils\DescriptorUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipantAllocation extends GenericModel
{
    protected $table = 'events_participants_allocations';

    // timestamps habilitados para a sincronização incremental (?desde=)
    protected $fillable = [
        'event_id',
        'person_id',
        'event_site_room_id',
        'event_site_room_type_id',
    ];

    public static function modelName(): string
    {
        return 'Alocação de Participante';
    }

    public function descriptor(): string
    {
        if (!empty($this->person_id)) {
            $person = $this->person;
            $church = $this->person->church;
            $abv = DescriptorUtil::functionAbreviation(($person->function));

            return $abv . ' ' . $person->name . ' (' . $church->name . ')';
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

    public function event_site_room_type(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoomType::class, 'event_site_room_type_id');
    }

    public function event_site_room(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoom::class, 'event_site_room_id');
    }
}
