<?php

namespace App\Livewire\Forms;

use App\Enum\FormModeEnum;
use App\Models\EventSite;
use App\Models\GenericModel;
use Livewire\Form;

abstract class GenericForm extends Form
{
    public $formMode = FormModeEnum::Create;
    protected int $id = 0;
    public GenericModel $model;


    public abstract function setModel(FormModeEnum $formMode, GenericModel $model): void;
    public abstract function fixedRules(): array;
    public abstract function updateRules(): array;
    public abstract function insertRules(): array;

    public function getModel(): GenericModel
    {
        if (empty($this->model)) {
            return new EventSite();
        }
        return $this->model;
    }

    protected function rules()
    {
        $variableRules = $this->updateRules();
        if ($this->formMode === FormModeEnum::Create) {
            foreach ($variableRules as $k => $v) {
                $insertRule = $this->insertRules()[$k];
                $variableRules[$k] = $v . '|' . $insertRule;
            }
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

        $excepts = ['formMode', 'model'];
        if ($this->formMode === FormModeEnum::Create) {
            array_push($excepts, 'id');
            $this->model::create($this->except($excepts));
        } else {

            $attributes = $this->except($excepts);

            foreach ($attributes as $k => $v) {

                $this->model[$k] = $v;
            }
            $this->model->save();
        }

        $this->gerericReset();
    }

    public function gerericReset()
    {
        parent::resetExcept(['model']);
    }
}
