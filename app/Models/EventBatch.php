<?php

namespace App\Models;

use App\Utils\DateUtil;
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
