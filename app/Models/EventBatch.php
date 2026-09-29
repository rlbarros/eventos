<?php

namespace App\Models;

use App\Utils\DateUtil;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBatch extends GenericModel
{
    protected $table = 'events_batches';

    // timestamps para a sincronização incremental com o superapp (?desde=)
    protected $fillable = [
        'event_id',
        'batch',
        'start_date',
        'end_date'
    ];

    protected static function booted(): void
    {
        // a exclusão chega ao superapp como active = false
        static::deleted(function (EventBatch $batch) {
            SyncDeletion::create([
                'model' => SyncDeletion::BATCHES,
                'record_id' => $batch->id,
                'event_id' => $batch->event_id,
                'deleted_at' => now(),
            ]);
        });
    }

    public static function modelName(): string
    {
        return  "Lote do Evento";
    }

    public function descriptor(): string
    {
        if (empty($this->batch) || empty($this->start_date) || empty($this->end_date)) {
            return '';
        }
        return $this->batch . ' | ' . DateUtil::formatDateToBr($this->start_date) . ' - ' . DateUtil::formatDateToBr($this->end_date);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
