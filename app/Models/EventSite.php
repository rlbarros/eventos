<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSite extends Model
{
    protected $table = 'event_sites';

    protected $fillable = [
        'name',
        'zip_code',
        'state_id',
        'city_id',
        'address',
        'number',
        'complement',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
