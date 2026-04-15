<?php

namespace App\Livewire\Forms\EventSite;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\EventSiteRoom;
use App\Models\GenericModel;

class EventSiteRoomForm extends GenericForm
{

    public $name = '';
    public $event_site_room_type_id = 0;
    public $event_site_id = 0;

    public function fixedRules(): array
    {
        return [
            'event_site_id' => 'required|integer|exists:event_sites,id',
            'event_site_room_type_id' => 'required|integer|exists:event_site_room_types,id',
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:event_site_rooms,name'
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

        /** @var EventSiteRoom */
        $eventSiteRoom = $model;

        if (empty($eventSiteRoom) || empty($eventSiteRoom->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventSiteRoom->id;
        $this->event_site_id = $eventSiteRoom->event_site_id;
        $this->event_site_room_type_id = $eventSiteRoom->event_site_room_type_id;
        $this->name = $eventSiteRoom->name;
    }
}
