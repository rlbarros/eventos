<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends GenericModel
{
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

    public function descriptor(): string
    {
        if (empty($this->id) && empty($this->name)) {
            return '';
        } else if (!empty($this->name)) {
            return $this->name;
        } else {
            return $this->id . ' - ' . $this->name;
        }
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
