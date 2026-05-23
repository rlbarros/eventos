<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class AgeUtil
{
    public static function filterEventFeesByAge(Collection $eventFees, Person $person, Event $event)
    {
        $age = $person->age_at_date($event->start_date);
        if ($age <= $event->children_age) {
            return $eventFees->where('category', 'Infantil');
        } else {
            return $eventFees->where('category', 'Integral');
        }
    }
}
