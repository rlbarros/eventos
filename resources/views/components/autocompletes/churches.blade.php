<?php

namespace App\Livewire;

use App\Models\Church;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public bool $readonly;

    #[Modelable]
    public $form;



    public Collection $churches;
    public $churchesLoading = false;

    public function mount()
    {
        $this->loadChurches();
    }

    public function loadChurches()
    {
        $this->churchesLoading = true;
        try {
            $this->churches = Church::orderBy('name')->get();
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de igrejas');
            Log::error('error consulting churches ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->churchesLoading = false;
        }
    }


    #[On('church-externaly-selected')]
    public function handleStateChurchExternalySelected($churchId)
    {
        $this->form->church_id = $churchId;
    }

    public function dispatchSelections()
    {
        $this->dispatch('church-internaly-selected', churchId: $this->form->church_id);
    }
}

?>

<flux:field class="w-full">
    <flux:label>Igreja</flux:label>
    @if ($churchesLoading)
    <flux:input readonly color="zinc">
        <x-slot name="iconTrailing">
            <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
            </svg>
        </x-slot>
    </flux:input>
    @else
    <flux:select wire:model.live="form.church_id" wire:change="dispatchSelections" required :disabled="$readonly">
        <flux:select.option value="0">Selecione uma igreja...</flux:select.option>
        @foreach ($churches as $church)
        <flux:select.option :wire:key="$church->id" :value="$church->id">{{ $church->name }}</flux:select.option>
        @endforeach
    </flux:select>
    @endif
    <flux:error name="form.church_id" />
</flux:field>