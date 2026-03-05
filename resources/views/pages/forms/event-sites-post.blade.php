<?php

use App\Actions\CEP\QueryZipCode;
use App\DTOs\OpenCEPResponse;
use App\Models\EventSite;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {


    public $zipCodeLoading = false;

    #[Validate('required')]
    public $name = '';
    public $zipCode = '';
    #[Validate('required')]
    public $stateId = 12;
    protected $state;
    #[Validate('required')]
    public $cityId = 53;
    protected $city;
    #[Validate('required')]
    public $address = '';
    public $number = '';
    public $complement = '';
    public $neighborhood = '';

    public $submitDisabled = true;


    public function resetForm()
    {
        $this->name = '';
        $this->zipCode = '';
        $this->stateId = 12;
        $this->state;
        $this->cityId = 0;
        $this->city;
        $this->address = '';
        $this->number = '';
        $this->complement = '';
        $this->neighborhood = '';
        $this->dispatchStateCityExternalySelected();
    }

    public function handleModalCloseEvent()
    {
        $this->resetForm();
    }

    public function dispatchStateCityExternalySelected()
    {
        $this->dispatch('state-city-externaly-selected', stateId: $this->stateId, cityId: $this->cityId);
    }

    #[On('state-city-internaly-selected')]
    public function handleStateCityInternalySelected($stateId, $cityId)
    {
        $this->stateId = $stateId;
        $this->cityId = $cityId;
    }

    public function queryZipCode()
    {
        if (empty($this->zipCode) || strlen($this->zipCode) != 9) {
            return;
        }


        $this->zipCodeLoading = true;
        /** @var OpenCEPResponse */
        try {

            $openCepResponse = QueryZipCode::query($this->zipCode);

            $this->state = $openCepResponse->getState();
            $this->stateId = $this->state->id;
            $this->city = $openCepResponse->getCity();
            $this->cityId = $this->city->id;
            $this->address = $openCepResponse->getLogradouro();
            $this->neighborhood = $openCepResponse->getBairro();
            $this->dispatchStateCityExternalySelected();
        } catch (Exception $e) {
            Toaster::warning('não foi possível consultar informações de cep');
            Log::error('error at query open cep ' . $e->getMessage(), $e->getTrace());
        } finally {

            $this->zipCodeLoading = false;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->chechSubmitButtonDisabled();
        // A full validation check is complex here without more code,
        // so it's easier to use a computed property or Alpine.js for this specific use case.
    }

    public function chechSubmitButtonDisabled()
    {

        $this->submitDisabled = empty($this->name)
            || empty($this->stateId)
            || empty($this->cityId)
            || empty($this->address);
    }

    public function saveEventSite()
    {
        $this->validate();

        $eventSite = new EventSite();
        $eventSite->name = $this->name;
        $eventSite->zip_code = $this->zipCode;
        $eventSite->state_id = $this->stateId;
        $eventSite->city_id = $this->cityId;
        $eventSite->address = $this->address;
        $eventSite->number = $this->number;
        $eventSite->complement = $this->complement;
        $eventSite->neighborhood = $this->neighborhood;

        try {
            $eventSite->save();
            Toaster::success('local de evento ' . $this->name . ' salvo com sucesso');
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  salvar local de evento ' . $this->name());
        }
    }
};

?>

<div>
    <flux:modal name="create-event-site" wire:close="handleModalCloseEvent" class="md:w-350">
        <form class="space-y-8" wire:submit.prevent="saveEventSite">
            <div class="space-y-2">
                <flux:heading size="lg">
                    Cadastrar Local de Evento
                </flux:heading>
            </div>

            <div class="space-y-6">
                <flux:field>
                    <flux:label>Nome</flux:label>
                    <flux:input placeholder="insira o nome do local" wire:model="name" />
                    <flux:error name="name" />
                </flux:field>
                <flux:input label="CEP" placeholder="00000-000" mask="99999-999" wire:model="zipCode" wire:blur="queryZipCode">
                    <x-slot name="iconTrailing">
                        @if($zipCodeLoading)
                        <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
                        </svg>
                        @else
                        <flux:button size="sm" variant="subtle" icon="magnifying-glass-circle" class="-mr-1"
                            wire:click="queryZipCode" />
                        @endif
                    </x-slot>
                </flux:input>
                <livewire:autocompletes::states-cities :stateId="$stateId" :cityId="$cityId" :width="200" class="max-w-200 w-200 space-x-2" />
                <flux:field>
                    <flux:label>Endereço</flux:label>
                    <flux:input placeholder="insira o endereço" wire:model="address" wire:change="chechSubmitButtonDisabled" />
                    <flux:error name="address" />
                </flux:field>
                <flux:input label="Número" placeholder="insira o número" wire:model="number" />
                <flux:input label="Complemento" placeholder="insira o complemento" wire:model="complement" />
                <flux:input label="Bairro" placeholder="insira o bairro" wire:model="neighborhood" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <flux:modal.close>
                    <flux:button variant="subtle">
                        Fechar
                    </flux:button>

                </flux:modal.close>
                <flux:button type="submit" variant="primary" :disabled="$submitDisabled" color="navy">
                    Cadastrar
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>