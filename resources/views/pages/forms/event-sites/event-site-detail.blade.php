<?php

use App\Models\EventSite;
use App\Models\EventSiteRoom;
use App\Models\EventSiteRoomType;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public $eventSiteId;

    public function mount($eventSiteId)
    {
        $this->eventSiteId = $eventSiteId;
    }

    #[Url]
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
                <flux:heading size="lg" class="mb-4">Detalhamento de local de evento</flux:heading>
                <flux:heading size="sm" class="mb-4">{{ $this->eventSite()->name }}</flux:heading>
                <flux:subheading sixe="lg" class="mb-4">{{ $this->eventSiteLocation() }}</flux:subheading>
            </div>
        </div>
        <flux:separator variant="subtle" />
        <x-mary-tabs wire:model="selectedTab">
            <x-mary-tab name="rooms-types-tab" icon="o-cube-transparent">
                <x-slot:label>
                    Tipos de Quarto
                </x-slot:label>
                <livewire:pages::forms.event-sites.event-site-rooms-type-index :eventSiteId="$this->eventSiteId" />
            </x-mary-tab>
            <x-mary-tab name="rooms-tab" icon="o-building-office">
                <x-slot:label>
                    Quartos
                </x-slot:label>
                <livewire:pages::forms.event-sites.event-site-rooms-index :eventSiteId="$this->eventSiteId" />
            </x-mary-tab>
        </x-mary-tabs>
    </div>

</x-pages::forms.layout>