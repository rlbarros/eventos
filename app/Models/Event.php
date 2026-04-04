<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'church_id',
        'event_site_id',
    ];

    public static function modelName(): string
    {
        return  "Evento";
    }


    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }
}
