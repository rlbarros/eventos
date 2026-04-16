<?php

namespace App\Livewire\Components;

use App\Enum\FormModeEnum;
use App\Interfaces\IProperties;
use App\Utils\FormVisibilityUtil;
use Flux\Flux;

use Livewire\Component;
use Masmerise\Toaster\Toaster;


abstract class GenericFormComponent extends Component implements IProperties
{

    public $submitDisabled = true;

    abstract public function form();
    abstract public function model();
    abstract public function modalName(): string;
    abstract public function routeName(): string;
    abstract public function routeParameters(): array;
    abstract public function generMale(): bool;
    abstract public function submitDisabledCondition(): bool;
    abstract public function beforeSave(): void;

    public function modelName(): string
    {
        return $this->model()::modelName();
    }

    public function formTitle(): string
    {
        return FormVisibilityUtil::formTitle($this->formMode(), $this->modelName(), $this->form()->getModel()->descriptor());
    }

    public function formMode(): FormModeEnum
    {
        return $this->form()->formMode;
    }

    public function submitButtonLabel(): string
    {
        return FormVisibilityUtil::submitButtonLabel($this->formMode());
    }

    public function submitButtonVisible(): bool
    {
        return FormVisibilityUtil::submitButtonVisible($this->formMode());
    }

    public function isReadonly(): bool
    {
        return FormVisibilityUtil::isReadonly($this->formMode());
    }

    public function placeHolder(string $placeholder)
    {
        FormVisibilityUtil::resolvePlaceholder($this->formMode(), $placeholder);
    }

    public function handleModalCloseEvent()
    {
        $this->form()->genericReset();
        $this->closeModal();
    }

    public function updated($propertyName)
    {
        $this->form()->validateOnly($propertyName);
        $this->checkSubmitButtonDisabled();
    }

    public function checkSubmitButtonDisabled()
    {
        $this->submitDisabled = $this->submitDisabledCondition();
    }

    public function modalArray(): array
    {
        return [
            'modalName' => $this->modalName(),
            'formTitle' => $this->formTitle(),
            'submitButtonVisible' => $this->submitButtonVisible(),
            'submitButtonLabel' => $this->submitButtonLabel(),
        ];
    }

    public function showModal(): void
    {
        Flux::modal($this->modalName())->show();
    }

    public function closeModal(): void
    {
        Flux::modal($this->modalName())->close();
    }


    private function closeAndRedirectIndex()
    {
        $this->closeModal();
        $this->redirectRoute($this->routeName(), $this->routeParameters());
    }

    public function resetFormAndShowModal()
    {
        $this->form->setModel(FormModeEnum::Create, $this->model());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    public function findModelByIdAndShowModal(int $id, FormModeEnum $formMode)
    {
        $model = $this->model()->findOrFail($id);
        $this->form->setModel($formMode, $model);
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    public function successMessage($model): string
    {
        $article = $this->generMale() ? 'o' : 'a';
        return $this->modelName() .  ' ' . $model->descriptor() . ' salv' . $article . ' com sucesso!';
    }

    public function save()
    {

        $this->beforeSave();
        $model = $this->form()->getModel();
        $this->form()->store();
        Toaster::success($this->successMessage($model));
        $this->closeAndRedirectIndex();
    }
};
