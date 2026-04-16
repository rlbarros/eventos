<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends GenericModel
{
    use WithNameDescriptor;

    protected $table = 'churches';

    protected $fillable = [
        'name',
        'state_id',
        'city_id',
    ];

    public static function modelName(): string
    {
        return  "Igreja";
    }


    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
