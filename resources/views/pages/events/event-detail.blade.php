<?php

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public int $eventId;
    public int $eventSiteId;

    public string $eventName;
    public string $eventSiteName;
    public string $eventSiteLocation;
    public string $eventDates;

    #[Url]
    public string $selectedTab = 'participants-tab';

    public function mount()
    {
        $event = Event::findOrFail($this->eventId);

        $this->eventName = $event->name;
        $this->eventSiteId = $event->event_site->id;
        $this->eventSiteName = $event->event_site->name;

        if ($event->event_site->city) {
            $this->eventSiteLocation = $event->event_site->city->name . ' / ' . $event->event_site->state->name;
        } else {
            $this->eventSiteLocation = 'Localização não informada';
        }

        $startDate = \App\Utils\DateUtil::formatDateToBr($event->start_date);
        $endDate = \App\Utils\DateUtil::formatDateToBr($event->end_date);
        $this->eventDates = "De {$startDate} até {$endDate}";
    }


    #[On('events.event-participant-delete-confirmed')]
    public function handleEventParticipantDeleteConfirmed(int $id)
    {
        try {
            $eventParticipantAllocation = EventParticipantAllocation::findOrFail($id);
            $eventParticipantAllocation->delete();

            Toaster::success(EventParticipantAllocation::modelName() . $this->eventParticipantAllocation->descriptor() . ' excluído com sucesso');
            Flux::modal('dialogs.delete-confirmation')->close();
            $this->redirectRoute('events/event-detail', ['eventId' => $this->eventId, 'selectedTab' => 'participants-tab']);
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  apagar tipo de quarto de local de evento ' . $eventParticipantAllocation->descriptor());
        }
    }
};

?>


<div class="w-full mx-auto space-y-4">
    <flux:callout inline class="mb-4">
        <flux:callout.heading>
            <flux:breadcrumbs>
                <flux:breadcrumbs.item icon="calendar" href="{{ route('events') }}">Eventos </flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash" href="{{ route('event-detail', ['eventId' => $this->eventId]) }}" separator="slash">Evento {{ $this->eventId }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </flux:callout.heading>
    </flux:callout>
    <livewire:dialogs::delete-confirmation />
    <div class="flex items-start max-md:flex-col">
        <div class="flex-1">
            <flux:heading size="lg" class="mb-4">Detalhamento de Evento | {{ $this->eventName }}</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->eventSiteName }} | {{ $this->eventSiteLocation }} | {{ $this->eventDates }}</flux:heading>
        </div>
    </div>
    <flux:separator variant="subtle" />
    <x-mary-tabs wire:model.live="selectedTab">

        <!-- PARTICIPANTES -->
        <x-mary-tab name="participants-tab" icon="o-users">
            <x-slot:label>Participantes</x-slot:label>

            {{-- Show loader while the network request is processing --}}
            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            {{-- Only render the component when the tab matches and loading is finished --}}
            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'participants-tab')
                <livewire:pages::events.participants.participants-index :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- ALOCAÇÃO DE QUARTOS -->
        <x-mary-tab name="allocations-tab" icon="o-building-office">
            <x-slot:label>Alocação de Quartos</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'allocations-tab')
                <livewire:pages::events.allocations.allocations-index :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- LOTES -->
        <x-mary-tab name="batches-tab" icon="o-chart-bar-square">
            <x-slot:label>lotes</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'batches-tab')
                <livewire:pages::events.batches.batches-index :eventId="$this->eventId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- TAXAS -->
        <x-mary-tab name="fees-tab" icon="o-chart-bar">
            <x-slot:label>taxas</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'fees-tab')
                <livewire:pages::events.fees.fees-index :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- SERVIÇOS -->
        <x-mary-tab name="services-tab" icon="o-strikethrough">
            <x-slot:label>serviços</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'services-tab')
                <livewire:pages::events.services.services-index :eventId="$this->eventId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- MOTORISTAS -->
        <x-mary-tab name="drivers-tab" icon="o-user-circle">
            <x-slot:label>motoristas</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'drivers-tab')
                <livewire:pages::events.drivers.drivers-index :eventId="$this->eventId" />
                @endif
            </div>
        </x-mary-tab>

        <!-- VIAGENS -->
        <x-mary-tab name="trips-tab" icon="o-map">
            <x-slot:label>viagens</x-slot:label>

            <div wire:loading wire:target="selectedTab" class="w-full p-4">
                <flux:progress indeterminate />
            </div>

            <div wire:loading.remove wire:target="selectedTab">
                @if($this->selectedTab === 'trips-tab')
                <livewire:pages::events.trips.trips-index :eventId="$this->eventId" />
                @endif
            </div>
        </x-mary-tab>

    </x-mary-tabs>
</div>