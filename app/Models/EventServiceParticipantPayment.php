<?php

namespace App\Models;

use App\Utils\DateUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventServiceParticipantPayment extends GenericModel
{
    protected $table = 'events_services_participants_payments';

    protected $fillable = [
        'consumption_id',
        'payment_date',
        'amount',
    ];

    public static function modelName(): string
    {
        return  "Pagamentos do Requisitante de Serviço";
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

    public function event_service_participant_consumption(): BelongsTo
    {
        return $this->belongsTo(EventServiceParticipantConsumption::class, 'consumption_id');
    }
}
