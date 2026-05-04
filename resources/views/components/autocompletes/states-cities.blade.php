<?php

namespace App\Livewire;

use App\Models\City;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public bool $readonly;

    public $stateId = 0;
    public $cityId = 0;
    public $states;
    public $cities;
    public $statesLoading = false;
    public $citiesLoading = false;

    public function mount()
    {
        $this->loadStates();
    }

    public function loadStates()
    {
        $this->statesLoading = true;
        try {
            $this->states = State::orderBy('name')->get();
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de estados');
            Log::error('error consulting states ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->statesLoading = false;
        }
    }

    public function stateChanged()
    {
        $this->loadCititesOfState();
    }

    public function loadCititesOfState()
    {
        $this->citiesLoading = true;
        try {
            $stateId = $this->stateId;
            $cities = City::where('state_id', '=', $stateId)->orderBy('name')->get();
            $this->cities = $cities;
            $this->cityId = 0;
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de cidades');
            Log::error('error consulting cities ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->citiesLoading = false;
        }
    }

    #[On('state-city-externaly-selected')]
    public function handleStateCityExternalySelected($stateId, $cityId)
    {
        $this->stateId = $stateId;
        $this->loadStates();
        $this->loadCititesOfState();
        $this->cityId = $cityId;
    }

    public function dispatchSelections()
    {
        $this->dispatch('state-city-internaly-selected', stateId: $this->stateId, cityId: $this->cityId);
    }
}

?>
<div class="flex gap-4">
    <flux:field class="w-50">
        <flux:label>Estado</flux:label>
        @if ($statesLoading)
        <flux:input readonly color="zinc">
            <x-slot name="iconTrailing">
                <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
                </svg>
            </x-slot>
        </flux:input>
        @else
        <flux:select wire:model.live="stateId" wire:change="stateChanged" required :disabled="$readonly">
            <flux:select.option value="0">Selecione um estado...</flux:select.option>
            @foreach ($states as $state)
            <flux:select.option :wire:key="$state->id" :value="$state->id">{{ $state->name }}</flux:select.option>
            @endforeach
        </flux:select>
        @endif
        <flux:error name="stateId" />
    </flux:field>
    <flux:field class="flex-1">
        <flux:label>Cidade</flux:label>
        @if ($citiesLoading)
        <flux:input readonly color="zinc">
            <x-slot name="iconTrailing">
                <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
                </svg>
            </x-slot>
        </flux:input>
        @else
        <flux:select wire:model.live="cityId" wire:change="dispatchSelections" required :disabled="$readonly">
            <flux:select.option :wire:key="0" :value="0">Selecione uma cidade...</flux:select.option>
            @if (isset($cities))
            @foreach ($cities as $city)
            <flux:select.option :wire:key="$city->id" :value="$city->id">{{ $city->name }}</flux:select.option>
            @endforeach
            @endif
        </flux:select>
        @endif
        <flux:error name="cityId" />
    </flux:field>
</div>