<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends Model
{
    protected $table = 'churches';

    protected $fillable = [
        'name',
        'state_id',
        'city_id',
    ];

    public function State(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function City(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
