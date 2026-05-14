<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBatch extends GenericModel
{
    protected $table = 'events_batches';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'batch',
        'start_date',
        'end_date'
    ];

    public static function modelName(): string
    {
        return  "Lote do Evento";
    }

    public function descriptor(): string
    {
        if (empty($this->id) || empty($this->batch)) {
            return '';
        }
        return $this->id . ' - ' . $this->batch;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
