<?php

use App\Actions\CEP\QueryZipCode;
use App\DTOs\OpenCEPResponse;
use App\Enum\FormModeEnum;
use App\Livewire\Forms\Forms\EventSiteForm;
use App\Models\EventSite;
use App\Utils\FormVisibilityUtil;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {


    public $zipCodeLoading = false;
    public $submitDisabled = true;
    public EventSiteForm $form;

    public function formTitle(): string
    {
        return FormVisibilityUtil::formTitle($this->form->formMode, EventSite::modelName(), $this->form->getModel()->descriptor());
    }

    public function submitButtonTitle(): string
    {
        return FormVisibilityUtil::submitButtonTitle($this->form->formMode);
    }

    public function submitButtonVisible(): bool
    {
        return FormVisibilityUtil::submitButtonVisible($this->form->formMode);
    }

    public function isReadonly(): bool
    {
        return FormVisibilityUtil::isReadonly($this->form->formMode);
    }


    #[On('forms.event-site-create')]
    public function handleEventSiteCreatingRequest()
    {
        $this->form->setModel(FormModeEnum::Create, new EventSite());
        $this->submitDisabled = true;
        $this->checkSubmitButtonDisabled();
        $this->showModal();
    }

    private function  showModal()
    {
        Flux::modal('forms.event-site')->show();
    }

    private function findModelByIdAndShowModal(int $id, FormModeEnum $formMode)
    {
        $eventSite = EventSite::findOrFail($id);
        $this->form->setModel($formMode, $eventSite);
        $this->checkSubmitButtonDisabled();
        $this->dispatchStateCityExternalySelected();
        $this->showModal();
    }

    #[On('forms.event-site-edit')]
    public function handleEventSiteEditRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::Edit);
    }

    #[On('forms.event-site-view')]
    public function handleEventSiteViewRequest(int $id)
    {
        $this->findModelByIdAndShowModal($id, FormModeEnum::View);
    }

    public function handleModalCloseEvent()
    {
        $this->form->gerericReset();
    }

    public function dispatchStateCityExternalySelected()
    {
        $this->dispatch('state-city-externaly-selected', stateId: $this->form->state_id, cityId: $this->form->city_id);
    }

    public function placeHolder(string $placeholder)
    {
        FormVisibilityUtil::resolvePlaceholder($this->form->formMode, $placeholder);
    }



    #[On('state-city-internaly-selected')]
    public function handleStateCityInternalySelected($stateId, $cityId)
    {
        $this->form->state_id = $stateId;
        $this->form->city_id = $cityId;
    }

    public function queryZipCode()
    {
        if (empty($this->form->zip_code) || strlen($this->form->zip_code) != 9 || $this->form->formMode === FormModeEnum::View) {
            return;
        }

        $this->zipCodeLoading = true;
        /** @var OpenCEPResponse */
        try {

            $openCepResponse = QueryZipCode::query($this->form->zip_code);

            $state = $openCepResponse->getState();
            $this->form->state_id = $state->id;
            $city = $openCepResponse->getCity();
            $this->form->city_id = $city->id;
            $this->form->address = $openCepResponse->getLogradouro();
            $this->form->neighborhood = $openCepResponse->getBairro();
            $this->dispatchStateCityExternalySelected();
            $this->checkSubmitButtonDisabled();
        } catch (Exception $e) {
            Toaster::warning('não foi possível consultar informações de cep');
            Log::error('error at query open cep ' . $e->getMessage(), $e->getTrace());
        } finally {

            $this->zipCodeLoading = false;
        }
    }

    public function updated($propertyName)
    {
        $this->form->validateOnly($propertyName);
        $this->checkSubmitButtonDisabled();
    }

    public function checkSubmitButtonDisabled()
    {
        //$this->dispatch('log-event', ['obj' => $this->form, 'level' => 'info']);

        $emptyName = empty($this->form->name);
        $emptyStateId = empty($this->form->state_id);
        $emptyCityId = empty($this->form->city_id);
        $emptyAddress = empty($this->form->address);

        $this->submitDisabled = $emptyName || $emptyStateId || $emptyCityId || $emptyAddress;
    }

    private function closeAndRedirectIndex()
    {
        $this->redirectRoute('event-sites');
        Flux::modal('forms.event-site')->close();
    }


    public function save()
    {
        try {
            $this->form->store();
            Toaster::success('local de evento ' . $this->form->getModel()->descriptor() . ' salvo com sucesso');
            $this->closeAndRedirectIndex();
        } catch (\Exception $e) {
            dd($e);
            Toaster::warning('erro ' . $e->getMessage() . 'ao  salvar ' . EventSite::modelName() . ' ' . $this->form->getModel()->descriptor());
        }
    }
};

?>

<div>
    <flux:modal name="forms.event-site" wire:close="handleModalCloseEvent" class="md:w-350">
        <form class="space-y-8" wire:submit.prevent="save">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $this->formTitle() }}
                </flux:heading>
            </div>

            <div class="space-y-6">
                <flux:field>
                    <flux:label>Nome</flux:label>
                    <flux:input placeholder="insira o nome do local" wire:model="form.name"
                        :readonly="$this->isReadonly()" />
                    <flux:error name="form.name" />
                </flux:field>
                <div class="flex gap-4">
                    <flux:field class="w-50">
                        <flux:label>Telefone</flux:label>
                        <flux:input placeholder="{{ $this->placeHolder('(00) 00000-0000') }}" mask="(99) 99999-9999"
                            wire:model="form.phone" :readonly="$this->isReadonly()" />
                        <flux:error name="form.phone" />
                    </flux:field>
                    <flux:field class="flex-1">
                        <flux:label>CEP</flux:label>
                        <flux:input placeholder="{{ $this->placeHolder('00000-000') }}" mask="99999-999"
                            wire:model="form.zip_code" wire:blur="queryZipCode" :readonly="$this->isReadonly()">
                            <x-slot name="iconTrailing">
                                @if($zipCodeLoading)
                                <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
                                </svg>
                                @else
                                <flux:button size="sm" variant="subtle" icon="magnifying-glass-circle" class="-mr-1"
                                    wire:click="queryZipCode" :disabled="$this->isReadonly()" />
                                @endif
                            </x-slot>
                        </flux:input>
                        <flux:error name="form.zip_code" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Endereço</flux:label>
                    <flux:input placeholder="insira o endereço" wire:model="form.address"
                        wire:change="checkSubmitButtonDisabled" :readonly="$this->isReadonly()" />
                    <flux:error name="form.address" />
                </flux:field>
                <div class="flex gap-4">
                    <flux:field class="w-50">
                        <flux:label>Número</flux:label>
                        <flux:input placeholder="{{ $this->placeHolder('insira o número') }}" wire:model="form.number"
                            class="w-20" :readonly="$this->isReadonly()" />
                        <flux:error name="form.number" />
                    </flux:field>
                    <flux:field class="flex-1">
                        <flux:label>Complemento</flux:label>
                        <flux:input placeholder="{{ $this->placeHolder('insira o complemento') }}"
                            wire:model="form.complement" class="flex-1" :readonly="$this->isReadonly()" />
                        <flux:error name="form.complement" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Bairro</flux:label>
                    <flux:input placeholder="{{ $this->placeHolder('insira o bairro') }}" wire:model="form.neighborhood"
                        :readonly="$this->isReadonly()" />
                    <flux:error name="form.neighborhood" />
                </flux:field>
                <livewire:autocompletes::states-cities readonly="$this->isReadonly()" class="space-x-2" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <flux:modal.close>
                    <flux:button variant="subtle">
                        Fechar
                    </flux:button>

                </flux:modal.close>
                @if ($this->submitButtonVisible())
                <flux:button type="submit" variant="primary" :disabled="$submitDisabled" color="navy">
                    {{ $this->submitButtonTitle() }}
                </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
</div>