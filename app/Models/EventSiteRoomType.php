<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSiteRoomType extends GenericModel
{
    protected $table = 'event_site_room_types';

    protected $fillable = [
        'name',
        'event_site_id',
        'type',
        'beds'
    ];

    public $timestamps = false;

    public static function modelName(): string
    {
        return  "Tipo de Quarto";
    }

    public function descriptor(): string
    {
        if (empty($this->id) || empty($this->name)) {
            return '';
        }
        return $this->id . ' - ' . $this->name;
    }


    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }
}
