<?php

use App\Models\Event;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use App\Utils\AgeUtil;
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
        $this->eventFees = AgeUtil::filterEventFeesByAge($eventFees, $this->person, $event);
    }
};

?>


<div class="w-full mx-auto space-y-4">
    <div class="flex items-start max-md:flex-col">
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

            <flux:callout inline class="mb-4" style="max-height: 50px;">
                <flux:callout.heading>
                    <div class="flex flex-row" style="column-gap: 1rem; padding-bottom: 0px!important;padding-top: 10px!important;">
                        <flux:heading size="sm" style="font-size:1.1rem;">{{ $this->person->descriptor()  }}</flux:heading>
                        <flux:subheading sixe="xl" class="font-bold" style="font-size:1rem; margin-top:2px;">{{ $this->roomType->descriptor() }}</flux:subheading>
                        @foreach($eventFees as $eventFee)
                        <flux:subheading sixe="lg" style="margin-top: 4px;">Lote {{ $eventFee->event_batch->batch }} | <strong> {{ \App\Utils\CurrencyUtil::formatCurrencyToBr($eventFee->fee, true)     }}</strong></flux:subheading>
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