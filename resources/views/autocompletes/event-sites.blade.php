<?php

namespace App\Livewire;

use App\Models\EventSite;
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

    public Collection $eventSites;
    public $eventSitesLoading = false;

    public function mount()
    {
        $this->loadEventSites();
    }

    public function loadEventSites()
    {
        $this->eventSitesLoading = true;
        try {
            $this->eventSites = EventSite::orderBy('name')->get();
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de locais de evento');
            Log::error('error consulting eventSites ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->eventSitesLoading = false;
        }
    }


    #[On('event-site-externaly-selected')]
    public function handleStateeventSiteExternalySelected($eventSiteId)
    {
        $this->form->event_site_id = $eventSiteId;
    }

    public function dispatchSelections()
    {
        $this->dispatch('event-site-internaly-selected', eventSiteId: $this->form->event_site_id);
    }
}

?>

<flux:field class="w-full">
    <flux:label>Local de Evento</flux:label>
    @if ($eventSitesLoading)
    <flux:input readonly color="zinc">
        <x-slot name="iconTrailing">
            <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
            </svg>
        </x-slot>
    </flux:input>
    @else
    <flux:select wire:model.live="form.event_site_id" wire:change="dispatchSelections" required :disabled="$readonly">
        <flux:select.option value="0">Selecione um local de evento...</flux:select.option>
        @foreach ($eventSites as $eventSite)
        <flux:select.option :wire:key="$eventSite->id" :value="$eventSite->id">{{ $eventSite->name }}</flux:select.option>
        @endforeach
    </flux:select>
    @endif
    <flux:error name="form.event_site_id" />
</flux:field>