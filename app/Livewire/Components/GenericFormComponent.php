<?php

namespace App\Livewire\Components;

use App\Enum\FormModeEnum;
use App\Utils\FormVisibilityUtil;
use Flux\Flux;
use Livewire\Component;
use Masmerise\Toaster\Toaster;


abstract class GenericFormComponent extends Component
{

    public $submitDisabled = true;

    abstract public function form();
    abstract public function model();
    abstract public function modalName(): string;
    abstract public function routeName(): string;
    abstract public function routeParameters(): array;
    abstract public function successMessage(): string;
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
    }

    public function updated($propertyName)
    {
        $this->form()->validateOnly($propertyName);
        $this->checkSubmitButtonDisabled();
    }

    public function checkSubmitButtonDisabled()
    {
        $this->submitDisabled = $this->submitDisabledCondition();

        $this->dispatch('log-event', [
            'obj' => [`form` => $this->form, 'submitDisabled' => $this->submitDisabled],
            'level' => 'info',
        ]);
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
        $this->redirectRoute($this->routeName(), $this->routeParameters());
        $this->closeModal();
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
        // $this->dispatch('log-event', ['obj' => $this->form, 'level' => 'info']);
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    public function save()
    {
        try {
            $this->beforeSave();
            $this->form()->store();
            Toaster::success($this->successMessage());
            $this->closeAndRedirectIndex();
        } catch (\Exception $e) {
            dd($e);
            Toaster::warning('erro ' . $e->getMessage() . ' ao salvar ' . $this->modelName() . ' ' . $this->form()->getModel()->descriptor());
        }
    }
};
