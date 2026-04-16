<?php

namespace App\Models;

use App\Traits\WithNameDescriptor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class EventSiteRoom extends GenericModel
{
    use WithNameDescriptor;

    protected $table = 'event_site_rooms';

    protected $fillable = [
        'event_site_id',
        'event_site_room_type_id',
        'name',
    ];

    public static function modelName(): string
    {
        return  "Quarto";
    }

    public function type(): string
    {
        /** @var EventSiteRoomType|null $roomType */
        $roomType = $this->event_site_room_type()->first();
        if (empty($roomType)) {
            return '';
        }
        Log::info('Room Type: ' . $roomType->name . ' - ' . $roomType->beds . ' leitos');

        return $roomType->name . ' - ' . $roomType->beds . ' leitos';
    }


    public function event_site(): BelongsTo
    {
        return $this->belongsTo(EventSite::class, 'event_site_id');
    }

    public function event_site_room_type(): BelongsTo
    {
        return $this->belongsTo(EventSiteRoomType::class, 'event_site_room_type_id');
    }
}
