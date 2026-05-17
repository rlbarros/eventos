<?php

namespace App\Models;

use App\Utils\DateUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventServiceParticipantConsumption extends GenericModel
{
    protected $table = 'events_services_participants_comsuption';

    protected $fillable = [
        'event_id',
        'event_service_id',
        'person_id',
        'payment_date',
        'amount'
    ];

    public static function modelName(): string
    {
        return  "Consumos de Serviços do Participante";
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

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function event_service(): BelongsTo
    {
        return $this->belongsTo(EventService::class, 'event_service_id');
    }
}
