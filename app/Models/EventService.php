<?php

namespace App\Models;

use App\Utils\CurrencyUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventService extends GenericModel
{
    protected $table = 'events_services';

    protected $fillable = [
        'event_id',
        'name',
        'fee'
    ];

    public static function modelName(): string
    {
        return  "Serviço de Evento";
    }

    public function descriptor(): string
    {
        if (empty($this->id) || empty($this->name) || empty($this->fee)) {
            return '';
        }
        return $this->id . ' - ' . $this->name . ' - ' . CurrencyUtil::formatCurrencyToBr($this->fee, true);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function hasRequesters(): bool
    {
        return EventServiceParticipantConsumption::where('event_service_id', $this->id)->exists();
    }
}
