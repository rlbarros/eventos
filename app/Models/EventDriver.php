<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDriver extends Model
{
    protected $table = 'events_drivers';

    protected $fillable = [
        'event_id',
        'name',
        'phone',
        'vehicle',
        'capacity'
    ];

    public static function modelName(): string
    {
        return  "Motorista";
    }


    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
