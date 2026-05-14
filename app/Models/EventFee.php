<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFee extends GenericModel
{
    protected $table = 'events_fees';

    protected $fillable = [
        'event_id',
        'event_site_room_type_id',
        'event_batch_id',
        'category',
        'batch',
        'fee'
    ];

    public static function modelName(): string
    {
        return  "Taxa de Evento";
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

    public function event_site_room_type(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoomType::class, 'event_site_room_type_id');
    }

    public function event_batch(): BelongsTo
    {
        return $this->belongsTo(EventBatch::class, 'event_batch_id');
    }
}
