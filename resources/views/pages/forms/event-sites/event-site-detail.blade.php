<?php

use App\Models\EventSite;
use App\Models\EventSiteRoom;
use App\Models\EventSiteRoomType;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public $eventSiteId;

    public function mount($eventSiteId)
    {
        $this->eventSiteId = $eventSiteId;
    }

    public $selectedTab = 'rooms-types-tab';

    use WithPagination;

    #[Computed]
    public function eventSite()
    {
        return EventSite::findOrFail($this->eventSiteId);
    }

    public function eventSiteLocation()
    {
        if ($this->eventSite()->city) {
            return $this->eventSite()->city->name . ' / ' . $this->eventSite()->state->name;
        } else {
            return 'Localização não informada';
        }
    }

    #[Computed]
    public function roomTypesIndex()
    {
        return EventSiteRoomType::where('event_site_id', '=', $this->eventSiteId)->paginate(10);
    }

    #[Computed]
    public function roomsIndex()
    {
        return EventSiteRoom::where('event_site_id', '=', $this->eventSiteId)->paginate(10);
    }

    #[On('forms.event-sites.room-type-delete-confirmed')]
    public function handleEventSiteDeleteConfirmed(int $id)
    {
        try {
            $eventSite = EventSite::findOrFail($id);
            $eventSite->delete();

            Toaster::success(EventSite::modelName() . $this->eventSite->descriptor() . ' excluído com sucesso');
            Flux::modal('dialogs.delete-confirmation')->close();
            $this->redirectRoute('event-sites');
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  apagar local de evento ' . $eventSite->descriptor());
        }
    }
};

?>

<x-pages::forms.layout>
    <div class="w-full mx-auto space-y-4">
        <div class="flex items-start max-md:flex-col">
            <div class="flex-1">
                <flux:heading sixe="xl" level="1">{{ $this->eventSite()->name }}</flux:heading>
                <flux:subheading size="lg" class="mb-4">{{ $this->eventSiteLocation() }}</flux:subheading>
            </div>
        </div>
        <flux:separator variant="subtle" />
        <x-mary-tabs wire:model="selectedTab">
            <x-mary-tab name="rooms-types-tab" icon="o-cube-transparent">
                <x-slot:label>
                    Tipos de Quarto
                </x-slot:label>
                <div class="w-full mx-auto space-y-4">
                    <flux:button icon="plus" wire:click="$dispatch('forms.event-sites.room-type-create')" class="ml-2" size="sm">
                        Adicionar Tipo de Quarto
                    </flux:button>

                    <livewire:pages::forms.event-sites.event-site-room-type-form eventSiteId="{{ $this->eventSiteId }}" />

                    <flux:separator variant="subtle" />

                    <div class="overflow-x-auto">

                        <flux:table :paginate="$this->roomTypesIndex()" pagination:scroll-to>
                            <flux:table.columns>
                                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                                <flux:table.column sortable>Nome</flux:table.column>
                                <flux:table.column sortable>Tipo</flux:table.column>
                                <flux:table.column sortable>Nº de leitos</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->roomTypesIndex() as $roomType)
                                <flux:table.row :key="$roomType->id">
                                    <flux:table.cell>{{ $roomType->id }}</flux:table.cell>
                                    <flux:table.cell>{{ $roomType->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $roomType->type }}</flux:table.cell>
                                    <flux:table.cell>{{ $roomType->beds }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-3">
                                            <flux:button wire:click="$dispatch('forms.event-sites.room-type-view', { id: {{ $roomType->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                                                size="sm" />
                                            <flux:button wire:click="$dispatch('forms.event-sites.room-type-edit', { id: {{ $roomType->id }} })" icon="pencil-square" style="cursor: pointer;"
                                                size="sm" />
                                            <flux:button variant="danger" icon="trash" size="sm"
                                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $roomType->id }}, modelName: '{{EventSiteRoomType::modelName()}}', descriptor: '{{$roomType->descriptor()}}', callbackEvent: 'forms.event-sites.room-type-delete-confirmed' })" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </x-mary-tab>
            <x-mary-tab name="room-tab" icon="o-building-office">
                <x-slot:label>
                    Quartos
                </x-slot:label>
                <div class="w-full mx-auto space-y-4">
                    <flux:button icon="plus" wire:click="$dispatch('forms.event-sites.room-create')" class="ml-2" size="sm">
                        Adicionar Quarto
                    </flux:button>

                    <livewire:pages::forms.event-sites.event-site-room-form eventSiteId="{{ $this->eventSiteId }}" />

                    <flux:separator variant="subtle" />

                    <div class="overflow-x-auto">

                        <flux:table :paginate="$this->roomsIndex()" pagination:scroll-to>
                            <flux:table.columns>
                                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                                <flux:table.column sortable>Nome</flux:table.column>
                                <flux:table.column sortable>Tipo</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->roomsIndex() as $room)
                                <flux:table.row :key="$room->id">
                                    <flux:table.cell>{{ $room->id }}</flux:table.cell>
                                    <flux:table.cell>{{ $room->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $room->type }}</flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex gap-3">
                                            <flux:button wire:click="$dispatch('forms.event-sites.room-view', { id: {{ $roomType->id }} })" icon="document-magnifying-glass" style="cursor: pointer;"
                                                size="sm" />
                                            <flux:button wire:click="$dispatch('forms.event-sites.room-edit', { id: {{ $roomType->id }} })" icon="pencil-square" style="cursor: pointer;"
                                                size="sm" />
                                            <flux:button variant="danger" icon="trash" size="sm"
                                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $roomType->id }}, modelName: '{{EventSiteRoomType::modelName()}}', descriptor: '{{$roomType->descriptor()}}', callbackEvent: 'forms.event-sites.room-delete-confirmed' })" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </div>
            </x-mary-tab>
        </x-mary-tabs>
    </div>
    <livewire:dialogs::delete-confirmation />
</x-pages::forms.layout>