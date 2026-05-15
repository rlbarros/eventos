<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDriver extends GenericModel
{
    use WithNameDescriptor;

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
