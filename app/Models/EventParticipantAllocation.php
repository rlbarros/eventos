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
        'payer_person_id',
        'event_site_room_id',
        'event_site_room_type_id',
    ];

    protected static function booted(): void
    {
        // quem pagava por outros deixa de ser pagador: cada um volta a pagar a própria taxa
        static::deleted(function (EventParticipantAllocation $allocation) {
            static::where('event_id', $allocation->event_id)
                ->where('payer_person_id', $allocation->person_id)
                ->update(['payer_person_id' => null]);
        });

        // a exclusão chega à administração pelo /participants-sync (active = false)
        static::deleted(function (EventParticipantAllocation $allocation) {
            SyncDeletion::create([
                'model' => SyncDeletion::PARTICIPANTS,
                'record_id' => $allocation->id,
                'event_id' => $allocation->event_id,
                'cpf' => Person::whereKey($allocation->person_id)->value('cpf'),
                'deleted_at' => now(),
            ]);
        });
    }

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

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'payer_person_id');
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
