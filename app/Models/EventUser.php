<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventUser extends Model
{
    protected $table = 'events_users';

    protected $fillable = [
        'event_id',
        'user_id',
    ];

    public function Event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
