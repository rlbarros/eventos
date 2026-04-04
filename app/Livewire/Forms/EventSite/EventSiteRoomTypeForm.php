<?php

namespace App\Livewire\Forms\EventSite;

use App\Enum\FormModeEnum;
use App\Enum\RoomTypesEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\EventSiteRoomType;
use App\Models\GenericModel;

class EventSiteRoomTypeForm extends GenericForm
{

    public $name = '';
    public $type = RoomTypesEnum::Alojamento->value;
    public $beds = 1;
    public $event_site_id = 0;

    public function fixedRules(): array
    {
        $roomTypes = array_map(fn(RoomTypesEnum $roomType) => $roomType->value, RoomTypesEnum::cases());
        return [
            'type' => 'in:' . implode(',', $roomTypes),
            'beds' => 'required|integer|min:1',
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:event_site_room_types,name'
        ];
    }

    public function updateRules(): array
    {
        return [
            'name' => 'required|string|min:3|max:200'
        ];
    }

    public function setModel(FormModeEnum $formMode, GenericModel $model): void
    {
        $this->formMode = $formMode;
        $this->model = $model;

        /** @var EventSiteRoomType */
        $eventSiteRoomType = $model;

        if (empty($eventSiteRoomType) || empty($eventSiteRoomType->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventSiteRoomType->id;
        $this->event_site_id = $eventSiteRoomType->event_site_id;
        $this->name = $eventSiteRoomType->name;
        $this->type = $eventSiteRoomType->type;
        $this->beds = $eventSiteRoomType->beds;
    }
}
