<?php

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\EventSite;
use App\Models\EventSiteRoom;
use App\Models\EventSiteRoomType;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use WithEventParticipantProperties;

    public function indexArray(): array
    {
        return [
            'header' => 'Alocações',
            'subHeader' => 'acomodação dos participantes nos quartos dos eventos.',
            'createButtonLabel' => 'Adicionar Alocação',
            'createActionEventName' => 'events.allocations.allocation-create'
        ];
    }

    public function customOrderingColumn(): string
    {
        return 'id';
    }

    public function customWhereIndex(): array
    {
        return [
            ['event_id', '=', $this->eventId]
        ];
    }

    #[On('events.alllocation.alo-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }

    #[Computed()]
    public function unallocatedParticipants()
    {
        return EventParticipantAllocation::where('event_id', $this->eventId)->whereNull('event_site_room_id')->get();
    }


    #[Computed()]
    public function eventSiteRooms()
    {
        $event = Event::find($this->eventId);
        $allocatedPersons = EventParticipantAllocation::where('event_id', $event->id)->get();

        $eventSite = EventSite::find($event->event_site_id);
        $roomsTypes = EventSiteRoomType::where('event_site_id', $eventSite->id)->get();

        $roomsAllocations = [];
        foreach ($roomsTypes as $roomType) {
            $rooms = EventSiteRoom::where('event_site_id', $eventSite->id)->where('event_site_room_type_id', $roomType->id)->get();
            $roomTypeArray = [
                'roomType' => $roomType,
                'rooms' => []
            ];
            foreach ($rooms as $room) {
                $roomAllocations = $allocatedPersons->where('event_site_room_id', $room->id);
                $roomArray = [
                    'room' => $room,
                    'availableBeds' => $roomType->beds - $roomAllocations->count(),
                    'allocations' => $roomAllocations
                ];
                $roomTypeArray['rooms'][] = $roomArray;
            }
            array_push($roomsAllocations, $roomTypeArray);
        }

        return $roomsAllocations;
    }
};
?>

<div class="grid grid-cols-2 gap-4 w-full">
    <x-mary-card title="Participantes Não Alocados" shadow separator>
        <div class="grid grid-cols-1 gap-2 w-full" wire:sort:group="participants">
            @foreach($this->unallocatedParticipants() as $participantAllocation)
            <livewire:pages::events.allocations.event-participant :person="$participantAllocation->person" :wire:key="$participantAllocation->id" />
            @endforeach
        </div>
    </x-mary-card>

    <x-mary-card title="Quartos Disponíveis" shadow separator>
        <x=mary-accordion>
            @foreach($this->eventSiteRooms() as $roomTypeArray)
            <livewire:pages::events.allocations.available-room-type :room-type="$roomTypeArray['roomType']" :rooms="$roomTypeArray['rooms']" :wire:key="$roomTypeArray['roomType']->id" />
            @endforeach
        </x=mary-accordion>
    </x-mary-card>
</div>