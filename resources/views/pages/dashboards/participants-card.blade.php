<?php

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\EventSiteRoom;

new class extends \Livewire\Component
{
    public int $eventId;


    public int $bedsCount;
    public int $participantsCount;
    public int $allocatedParticipantsCount;
    public int $availableBeds;


    public function mount()
    {
        $event = Event::find($this->eventId);
        $eventSite = $event->event_site;

        $rooms = EventSiteRoom::where('event_site_id', $eventSite->id)->get();
        $this->bedsCount = 0;
        foreach ($rooms as $room) {
            $this->bedsCount += $room->event_site_room_type->beds;
        }

        $totalParticipants = EventParticipantAllocation::where('event_id', $this->eventId)->get();
        $this->participantsCount = $totalParticipants->count();

        $this->allocatedParticipantsCount = $totalParticipants->whereNotNull('event_site_room_id')->count();
        $this->availableBeds = $this->bedsCount - $this->allocatedParticipantsCount;
    }
};

?>

<div class="relative flex flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700 flex-col gap-1">

    <flux:callout variant="warning" icon="information-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Participantes</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="yellow" size="xs" rounded>{{$this->participantsCount}}</flux:badge>
        </x-slot>
    </flux:callout>

    <flux:callout variant="indigo" icon="information-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Leitos do Evento </flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="indigo" size="xs" rounded>{{$this->bedsCount}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="green" icon="check-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Leitos Disponíveis</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="green" size="xs" rounded>{{$this->availableBeds}}</flux:badge>
        </x-slot>
    </flux:callout>
    <flux:callout color="red" icon="exclamation-circle" inline>
        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Leitos Alocados</flux:callout.heading>
        <x-slot name="controls" class="mt-1">
            <flux:badge color="red" size="xs" rounded>{{$this->allocatedParticipantsCount}}</flux:badge>
        </x-slot>
    </flux:callout>

</div>