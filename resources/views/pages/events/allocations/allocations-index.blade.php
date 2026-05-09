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

    public int $totalBeds = 0;
    public int $availableBeds = 0;
    public int $occupedBeds = 0;

    public array $eventSiteRooms;

    public function mount()
    {
        $this->eventSiteRooms = $this->eventSiteRooms();
    }


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
    public function unallocatedArray()
    {
        $unnalocatedParticipants = EventParticipantAllocation::where('event_id', $this->eventId)->whereNull('event_site_room_id')->get();
        $roomTypes = [];
        foreach ($unnalocatedParticipants as $unnalocatedParticipant) {
            $participantEventSiteRoomType = $unnalocatedParticipant->event_site_room_type;
            if (!array_key_exists($participantEventSiteRoomType->id, $roomTypes)) {
                $roomTypes[$participantEventSiteRoomType->id] = [
                    'roomType' => $participantEventSiteRoomType->name,
                    'churches' => []
                ];
            }
            $roomType = $roomTypes[$participantEventSiteRoomType->id];
            $churches = $roomType['churches'];

            $participantChurch = $unnalocatedParticipant->person->church;
            if (!array_key_exists($participantChurch->id, $churches)) {
                $churches[$participantChurch->id] =  [
                    'church' => $participantChurch->name,
                    'participants' => []
                ];
            }

            $church = $churches[$participantChurch->id];
            $participants = $church['participants'];
            array_push($participants, [
                'id' => $unnalocatedParticipant->id,
                'name' => $unnalocatedParticipant->person->function . ' ' . $unnalocatedParticipant->person->name
            ]);
            $church['participants'] = $participants;
            $churches[$participantChurch->id] = $church;
            $roomType['churches'] = $churches;
            $roomTypes[$participantEventSiteRoomType->id] = $roomType;
        }


        $roomTypesValues = array_values($roomTypes);
        $roomTypesChurchesValues = [];
        foreach ($roomTypesValues as $newRoomType) {
            $churches = $newRoomType['churches'];
            $churches = array_values($churches);
            $newRoomType['churches'] = $churches;
            array_push($roomTypesChurchesValues, $newRoomType);
        }

        return $roomTypesChurchesValues;
    }


    #[Computed()]
    public function eventSiteRooms()
    {
        $event = Event::find($this->eventId);
        $allocatedPersons = EventParticipantAllocation::where('event_id', $event->id)->get();

        $eventSite = EventSite::find($event->event_site_id);
        $roomsTypes = EventSiteRoomType::where('event_site_id', $eventSite->id)->get();

        $roomsAllocations = [];
        $this->totalBeds = 0;
        $this->availableBeds = 0;
        $this->occupedBeds = 0;
        foreach ($roomsTypes as $roomType) {
            $rooms = EventSiteRoom::where('event_site_id', $eventSite->id)->where('event_site_room_type_id', $roomType->id)->get();
            $roomTypeArray = [
                'roomType' => $roomType,
                'rooms' => []
            ];

            foreach ($rooms as $room) {

                $roomAllocations = $allocatedPersons->where('event_site_room_id', $room->id);

                $availableBeds = $roomType->beds - $roomAllocations->count();

                $this->totalBeds += $roomType->beds;
                $this->availableBeds += $availableBeds;
                $this->occupedBeds += $roomAllocations->count();

                $roomArray = [
                    'room' => $room,
                    'totalBeds' => $roomType->beds,
                    'availableBeds' => $availableBeds,
                    'occupedBeds' => $roomAllocations->count(),
                    'allocations' => $roomAllocations
                ];
                $roomTypeArray['rooms'][] = $roomArray;
            }
            array_push($roomsAllocations, $roomTypeArray);
        }

        return $roomsAllocations;
    }

    public function alocarSelecionados() {}

    public function desalocarSelecionados() {}
}
?>

<div class="grid grid-cols-3 gap-4 w-full justify-items-center items-center">
    <flux:card class="space-y-6 w-120">
        <div>
            <flux:heading size="lg">Participantes não alocados</flux:heading>
        </div>
        <div class="grid grid-cols-1 gap-2 w-full">
            <x=mary-accordion>
                @foreach($this->unallocatedArray() as $roomType)
                <livewire:pages::events.allocations.unnalocated-room-type :roomType="$roomType" />
                @endforeach
            </x=mary-accordion>
        </div>
    </flux:card>


    <flux:card class="flex flex-col gap-10 w-80">

        <flux:callout variant="indigo" icon="information-circle" inline>
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Leitos do Evento </flux:callout.heading>
            <x-slot name="controls" class="mt-1">
                <flux:badge color="indigo" size="xs" rounded>{{$this->totalBeds}}</flux:badge>
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
                <flux:badge color="red" size="xs" rounded>{{$this->occupedBeds}}</flux:badge>
            </x-slot>
        </flux:callout>

        <flux:button variant="primary" icon:trailing="chevron-right" wire:click="alocarSelecionados">
            Alocar Selecionados
        </flux:button>

        <flux:button variant="primary" icon="chevron-left" wire:click="desalocarSelecionados">
            Desalocar Selecionados
        </flux:button>
    </flux:card>


    <flux:card class="space-y-6 w-120">
        <x=mary-accordion>
            @foreach($this->eventSiteRooms as $roomTypeArray)
            <livewire:pages::events.allocations.available-room-type :room-type="$roomTypeArray['roomType']" :rooms="$roomTypeArray['rooms']" :wire:key="$roomTypeArray['roomType']->id" />
            @endforeach
        </x=mary-accordion>
    </flux:card>
</div>