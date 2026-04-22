<?php

namespace App\Livewire\Forms\Person;

use App\Enum\FormModeEnum;
use App\Livewire\Forms\GenericForm;
use App\Models\GenericModel;
use App\Models\Person;

class PersonForm extends GenericForm
{

    public $church_id = 0;
    public $name = '';
    public $birth_date = '';
    public $phone = '';
    public $avatar = null;
    public $father_id = null;
    public $mother_id = null;
    public $spouse_id = null;
    public $function = '';


    public function fixedRules(): array
    {
        return [
            'church_id' => 'required|integer|exists:churches,id',
            'function' => 'required|in:Membro,Pastor,Convidado,Obreiro,Diácono,Pregador de Conferência,Presbítero,Evangelista',
            'birth_date' => 'required|date',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
            'father_id' => 'nullable|integer|exists:persons,id',
            'mother_id' => 'nullable|integer|exists:persons,id',
            'spouse_id' => 'nullable|integer|exists:persons,id',
        ];
    }

    public function insertRules(): array
    {
        return [
            'name' => 'unique:persons,name'
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

        /** @var Person */
        $person = $model;

        if (empty($person) || empty($person->id)) {
            $this->genericReset();
            return;
        }

        $this->id = $person->id;
        $this->church_id = $person->church_id;
        $this->function = $person->function;
        $this->name = $person->name;
        $this->birth_date = $person->birth_date;
        $this->phone = $person->phone;
        $this->avatar = $person->avatar;
        $this->father_id = $person->father_id;
        $this->mother_id = $person->mother_id;
        $this->spouse_id = $person->spouse_id;
    }
}
