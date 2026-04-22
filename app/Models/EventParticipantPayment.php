<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipantPayment extends GenericModel
{
    protected $table = 'events_participants_payments';

    protected $fillable = [
        'event_id',
        'event_fee_id',
        'person_id',
        'batch',
        'paymaent_date',
        'payment_method',
        'amount',
    ];

    public static function modelName(): string
    {
        return  "Pagamentos do Participante";
    }

    public function descriptor(): string
    {
        if (!empty($this->person_id)) {
            $person = $this->person;
            $church = $this->person->church;
            return $person->function . ' - ' . $person->name . ' (' . $church->name . ')';
        }

        return '';
    }


    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function event_fee(): BelongsTo
    {
        return $this->belongsTo(EventFee::class, 'event_fee_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }
}
