<?php

namespace App\Livewire\Forms\Church;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\Church;
use App\Models\GenericModel;

class ChurchForm extends GenericForm
{

    public $name = '';
    public $state_id = '';
    public $city_id = '';

    public function fixedRules(): array
    {
        return [
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:churches,name'
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

        /** @var Church */
        $church = $model;

        if (empty($church) || empty($church->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $church->id;
        $this->name = $church->name;
        $this->state_id = $church->state_id;
        $this->city_id = $church->city_id;
    }
}
