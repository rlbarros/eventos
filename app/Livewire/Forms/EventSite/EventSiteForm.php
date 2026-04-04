<?php

namespace App\Livewire\Forms\EventSite;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\EventSite;
use App\Models\GenericModel;

class EventSiteForm extends GenericForm
{

    public $name = '';
    public $phone = '';
    public $zip_code = '';
    public $state_id = 12;
    public $city_id = 53;
    public $address = '';
    public $number = '';
    public $complement = '';
    public $neighborhood = '';

    public function fixedRules(): array
    {
        return [
            'phone' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|min:9|max:9',
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
            'address' => 'required|string|min:3|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:191',
            'neighborhood' => 'nullable|string|max:200'
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:event_sites,name'
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

        /** @var EventSite */
        $eventSite = $model;

        if (empty($eventSite) || empty($eventSite->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $eventSite->id;
        $this->name = $eventSite->name;
        $this->phone = $eventSite->phone;
        $this->zip_code = $eventSite->zip_code;
        $this->state_id = $eventSite->state_id;
        $this->city_id = $eventSite->city_id;
        $this->address = $eventSite->address;
        $this->number = $eventSite->number;
        $this->complement = $eventSite->complement;
        $this->neighborhood = $eventSite->neighborhood;
    }
}
