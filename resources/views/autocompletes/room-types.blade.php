<?php

namespace App\Livewire;

use App\Models\EventSiteRoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public bool $readonly;

    public $eventSiteId;
    #[Modelable]
    public $form;



    public Collection $roomTypes;
    public $roomTypesLoading = false;

    public function mount()
    {
        $this->loadRoomTypes();
    }

    public function loadRoomTypes()
    {
        $this->roomTypesLoading = true;
        try {
            $this->roomTypes = EventSiteRoomType::where('event_site_id', '=', $this->eventSiteId)->orderBy('name')->get();
        } catch (\Exception $e) {
            Toaster::warning('não foi possível consultar informações de tipos de quartos');
            Log::error('error consulting room types ' . $e->getMessage(), $e->getTrace());
        } finally {
            $this->roomTypesLoading = false;
        }
    }


    #[On('room-type-externaly-selected')]
    public function handleStateCityExternalySelected($eventSiteRoomTypeId)
    {
        $this->form->event_site_room_type_id = $eventSiteRoomTypeId;
        $this->loadRoomTypes();
    }

    public function dispatchSelections()
    {
        $this->dispatch('room-type-internaly-selected', eventSiteRoomTypeId: $this->form->event_site_room_type_id);
    }
}

?>

<flux:field class="w-50">
    <flux:label>Tipo de quarto</flux:label>
    @if ($roomTypesLoading)
    <flux:input readonly color="zinc">
        <x-slot name="iconTrailing">
            <svg class="mr-3 size-5 animate-spin text-shadow-blue-800" viewBox="0 0 24 24">
            </svg>
        </x-slot>
    </flux:input>
    @else
    <flux:select wire:model.live="form.event_site_room_type_id" wire:change="dispatchSelections" required :disabled="$readonly">
        @foreach ($roomTypes as $roomType)
        <flux:select.option :wire:key="$roomType->id" :value="$roomType->id">{{ $roomType->name }}</flux:select.option>
        @endforeach
    </flux:select>
    @endif
    <flux:error name="form.event_site_room_type_id" />
</flux:field>