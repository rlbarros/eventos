<?php

namespace App\Models;

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
        if (empty($this->id) || empty($this->fee)) {
            return '';
        }
        return $this->id . ' - ' . $this->fee;
    }


    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
