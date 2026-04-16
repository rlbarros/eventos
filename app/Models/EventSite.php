<?php

namespace App\Models;

use App\Models\GenericModel;
use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSite extends GenericModel
{
    use WithNameDescriptor;

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

    public static function modelName(): string
    {
        return  "Local de Eventos";
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
