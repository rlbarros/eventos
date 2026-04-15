<?php

namespace App\Livewire\Forms\Church;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\Church;
use App\Models\GenericModel;

class ChurchForm extends GenericForm
{

    public $name = '';
    public $state_id = 12;
    public $city_id = 53;

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
        $Church = $model;

        if (empty($Church) || empty($Church->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $Church->id;
        $this->name = $Church->name;


        $this->state_id = $Church->state_id;
        $this->city_id = $Church->city_id;
    }
}
