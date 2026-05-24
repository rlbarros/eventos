<?php

use App\Models\EventTrip;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {

    public int $eventId;
    public int $tripId;

    public object $trip;

    use WithPagination;

    public function mount()
    {
        $this->trip = EventTrip::where('id', '=', $this->tripId)->get()->first();
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
                        <flux:breadcrumbs.item separator="slash" href="{{ route('event-detail', ['eventId' => $this->eventId, 'selectedTab' => 'trips-tab']) }}" separator="slash">Evento {{ $this->eventId }}</flux:breadcrumbs.item>
                        <flux:breadcrumbs.item separator="slash">Viagem {{ $this->tripId }}</flux:breadcrumbs.item>
                    </flux:breadcrumbs>
                </flux:callout.heading>
            </flux:callout>

            <flux:callout inline class="mb-4" style="max-height: 50px;">
                <flux:callout.heading>
                    <div class="flex flex-row" style="column-gap: 1rem; padding-bottom: 0px!important;padding-top: 10px!important;">
                        <flux:heading size="sm" style="font-size:1.1rem;">{{ $this->trip->descriptor()  }} | lotação {{ $this->trip->capacity() }}</flux:heading>
                    </div>
                </flux:callout.heading>
            </flux:callout>

        </div>
    </div>
    <flux:separator variant="subtle" />

    <livewire:pages::events.trips.participants.participants-index
        :eventId="$this->eventId"
        :tripId="$this->tripId" />

</div>