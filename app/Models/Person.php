<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends Model
{
    protected $table = 'event_sites';

    protected $fillable = [
        'church_id',
        'name',
        'birth_date',
        'phone',
        'avatar',
        'father_id',
        'mother_id',
        'spouse_id',
        'function',
        'complement',
    ];

    public function Church(): BelongsTo
    {
        return $this->belongsTo(Church::class, 'church_id');
    }

    public function Father(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'father_id');
    }

    public function Mother(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'mother_id');
    }

    public function Spouse(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'spouse_id');
    }
}
