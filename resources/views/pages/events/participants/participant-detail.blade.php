<?php

use App\Models\Event;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use App\Services\Pricing\OccupancyPricing;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    public int $eventId;
    public int $allocationId;

    public object $allocation;
    public Person $person;
    public object $roomType;

    public Collection $eventFees;
    public int $occupancy = 1;

    #[Url]
    public string $selectedTab = 'payments-tab';

    use WithPagination;

    public function mount()
    {
        $this->allocation = EventParticipantAllocation
            ::where('id', '=', $this->allocationId)
            ->with('person')
            ->with('event_site_room_type')
            ->get()->first();

        $this->person = $this->allocation->person;

        $this->roomType = $this->allocation->event_site_room_type;

        /* var Collectiom */
        $eventFees = EventFee::where('event_id', $this->eventId)
            ->where('event_site_room_type_id', $this->roomType->id)
            ->with('event_batch')
            ->get();

        $event = Event::find($this->eventId);
        $pricing = new OccupancyPricing();
        $this->occupancy = $pricing->occupancy($this->allocation);

        // uma taxa por lote: a da categoria da pessoa na ocupação da reserva
        $this->eventFees = new Collection($eventFees->groupBy('event_batch_id')
            ->map(fn ($batchFees) => $pricing->feeForPerson($batchFees, $this->allocation, $event, $this->occupancy))
            ->filter()
            ->sortBy(fn ($fee) => $fee->event_batch->batch)
            ->values()
            ->all());
    }
};

?>


<div class="w-full mx-auto space-y-4">
    <div class="flex items-start max-md:flex-col max-md:items-stretch">
        <div class="flex-1">
            <flux:callout inline class="mb-4">
                <flux:callout.heading>
                    <flux:breadcrumbs>
                        <flux:breadcrumbs.item icon="calendar" href="{{ route('events') }}">Eventos </flux:breadcrumbs.item>
                        <flux:breadcrumbs.item separator="slash" href="{{ route('event-detail', ['eventId' => $this->eventId]) }}" separator="slash">Evento {{ $this->eventId }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item separator="slash">Alocação {{ $this->allocationId }}</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                </flux:callout.heading>
            </flux:callout>

            <flux:callout inline class="mb-4">
                <flux:callout.heading>
                    <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1 pt-2">
                        <flux:heading size="sm" style="font-size:1.1rem;">{{ $this->person->descriptor()  }}</flux:heading>
                        <flux:subheading sixe="xl" class="font-bold" style="font-size:1rem; margin-top:2px;">{{ $this->roomType->descriptor() }}</flux:subheading>
                        @foreach($eventFees as $eventFee)
                        <flux:subheading sixe="lg" style="margin-top: 4px;">Lote {{ $eventFee->event_batch->batch }}{{ $eventFee->min_occupants !== null || $eventFee->max_occupants !== null ? ' (' . $eventFee->occupancyLabel() . ')' : '' }} | <strong> {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($eventFee->fee, true)     }}</strong></flux:subheading>
                        @endforeach
                    </div>
                </flux:callout.heading>
            </flux:callout>
        </div>
    </div>
    <flux:separator variant="subtle" />
    <x-mary-tabs wire:model="selectedTab">
        <x-mary-tab name="payments-tab" icon="o-users">
            <x-slot:label>
                pagamentos
            </x-slot:label>
            <livewire:pages::events.participants.payments.payments-index
                :eventId="$this->eventId"
                :personId="$this->person->id"
                :allocationId="$this->allocationId"
                :eventSiteRoomTypeId="$this->roomType->id" />
        </x-mary-tab>
        <x-mary-tab name="services-tab" icon="o-building-office">
            <x-slot:label>
                serviços
            </x-slot:label>
            <livewire:pages::events.participants.services.services-index
                :eventId="$this->eventId"
                :personId="$this->person->id"
                :allocationId="$this->allocationId" />
        </x-mary-tab>
        <x-mary-tab name="trips-tab" icon="o-building-office">
            <x-slot:label>
                viagens
            </x-slot:label>
            <livewire:pages::events.participants.trips.trips-index
                :eventId="$this->eventId"
                :personId="$this->person->id"
                :allocationId="$this->allocationId" /> </x-mary-tab>
    </x-mary-tabs>
</div>