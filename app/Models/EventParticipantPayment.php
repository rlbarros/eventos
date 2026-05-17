<?php

namespace App\Models;

use App\Utils\DateUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventParticipantPayment extends GenericModel
{
    protected $table = 'events_participants_payments';

    protected $fillable = [
        'event_id',
        'event_fee_id',
        'person_id',
        'payment_date',
        'amount',
    ];

    public static function modelName(): string
    {
        return  "Pagamentos do Participante";
    }

    public function descriptor(): string
    {
        if (!empty($this->amount) && !empty($this->payment_date)) {
            return $this->amount . ' - ' . DateUtil::formatDateToBr($this->payment_date);
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
