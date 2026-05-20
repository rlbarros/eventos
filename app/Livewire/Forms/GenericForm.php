<?php

namespace App\Livewire\Forms;

use App\Enum\FormModeEnum;
use App\Models\EventSite;
use App\Models\GenericModel;
use Livewire\Form;

abstract class GenericForm extends Form
{
    public FormModeEnum $formMode = FormModeEnum::Create;
    protected int $id = 0;
    public GenericModel $model;


    public abstract function setModel(FormModeEnum $formMode, GenericModel $model): void;
    public abstract function fixedRules(): array;
    public abstract function updateRules(): array;
    public abstract function insertRules(): array;

    public function additionalExcepts(): array
    {
        return [];
    }

    private function getAttributes(): array
    {
        $excepts = ['formMode', 'model'];
        if ($this->formMode === FormModeEnum::Create) {
            array_push($excepts, 'id');
        }
        $excepts = array_merge($excepts, $this->additionalExcepts());
        return $this->except($excepts);
    }

    public function getModel(): GenericModel
    {
        if (empty($this->model)) {
            $this->model = new EventSite();
        }

        $attributes = $this->getAttributes();

        foreach ($attributes as $k => $v) {

            $this->model[$k] = $v;
        }
        return $this->model;
    }

    protected function rules()
    {
        $variableRules = $this->updateRules();
        if ($this->formMode === FormModeEnum::Create) {
            $variableRules = $this->insertRules();
        }

        $returnRules = [
            ...$variableRules,
            ...$this->fixedRules()
        ];
        return $returnRules;
    }

    public function store()
    {

        $this->validate();

        if ($this->formMode === FormModeEnum::Create) {
            $attributes = $this->getAttributes();
            $this->model::create($attributes);
        } else {
            $this->getModel()->save();
        }

        $this->genericReset();
    }

    public function genericReset()
    {
        parent::resetExcept(['model']);
    }
}
