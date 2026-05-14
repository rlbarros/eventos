<?php

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\EventSite;
use App\Models\EventSiteRoom;
use App\Models\EventSiteRoomType;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use App\Utils\DescriptorUtil;
use App\Utils\JSUtil;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    use WithEventParticipantProperties;

    public int $totalParticipants = 0;
    public int $totalDeallocatedParticipants = 0;
    public int $totalBeds = 0;
    public int $availableBeds = 0;
    public int $occupedBeds = 0;

    public array $eventSiteRooms;
    public array $deallocatedRoomTypeChurchesParticipants;

    public function mount()
    {
        $this->eventSiteRooms = $this->eventSiteRooms();
        $this->deallocatedRoomTypeChurchesParticipants = $this->deallocatedRoomTypeChurchesParticipants();
        $this->removerChavesLocalStorage();
    }

    public function removerChavesLocalStorage()
    {
        $js = "(function() {
            // Cria uma lista separada para evitar problemas de índice ao deletar durante o loop
            var chavesParaRemover = [];
            
            for (let i = 0; i < localStorage.length; i++) {
                var key = localStorage.key(i);
                if (key && key.startsWith('deallocated-selected-participants')) {
                    chavesParaRemover.push(key);
                }
            }

            chavesParaRemover.push('allocated-selected-participants');
            
            // Deleta as chaves filtradas
            chavesParaRemover.forEach(function(key) {
                localStorage.removeItem(key);
            });
        })();";

        $this->js($js);
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
    public function deallocatedRoomTypeChurchesParticipants()
    {
        $eventParticipants = EventParticipantAllocation::where('event_id', $this->eventId)->get();
        $this->totalParticipants = count($eventParticipants);
        $deallocatedParticipants = $eventParticipants->whereNull('event_site_room_id');
        $this->totalDeallocatedParticipants = count($deallocatedParticipants);
        $roomTypes = [];
        foreach ($deallocatedParticipants as $deallocatedParticipant) {
            $participantEventSiteRoomType = $deallocatedParticipant->event_site_room_type;
            if (!array_key_exists($participantEventSiteRoomType->id, $roomTypes)) {
                $roomTypes[$participantEventSiteRoomType->id] = [
                    'roomType' => $participantEventSiteRoomType->name,
                    'churches' => []
                ];
            }
            $roomType = $roomTypes[$participantEventSiteRoomType->id];
            $churches = $roomType['churches'];

            $participantChurch = $deallocatedParticipant->person->church;
            if (!array_key_exists($participantChurch->id, $churches)) {
                $churches[$participantChurch->id] =  [
                    'church' => $participantChurch->name,
                    'participants' => []
                ];
            }

            $church = $churches[$participantChurch->id];
            $participants = $church['participants'];
            array_push($participants, [
                'id' => $deallocatedParticipant->id,
                'name' => DescriptorUtil::functionAbreviation($deallocatedParticipant->person->function) . ' ' . $deallocatedParticipant->person->name
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
                    'allocations' => $roomAllocations->map(fn($allocation) =>
                    [
                        'id' => $allocation->id,
                        'name' => DescriptorUtil::functionAbreviation($allocation->person->function) . ' ' . $allocation->person->name . ' (' . $allocation->person->church->name . ')'
                    ])
                ];
                $roomTypeArray['rooms'][] = $roomArray;
            }
            array_push($roomsAllocations, $roomTypeArray);
        }

        return $roomsAllocations;
    }

    public function allocateSelected()
    {
        $this->dispatch('events.allocate-participants-create');
    }

    public function deallocateSelected()
    {
        $js = JSUtil::retrieveFromLocalStorageAndDispatch('allocated-selected-participants', 'events.deallocate-participants');
        $this->js($js);
    }

    #[On('events.deallocate-participants')]
    public function processDeallocation(array $participants)
    {
        DB::transaction(function () use ($participants) {
            EventParticipantAllocation::whereIn('id', $participants)
                ->update(['event_site_room_id' => null]);
        });

        Toaster::success('Participantes dealocados com sucesso');
        $this->js('(function() { setTimeout(() => {window.location.reload()}, 1000); })();');
    }
}
?>

<div>
    <livewire:pages::events.allocations.allocate-participants-form />

    <div class="grid grid-cols-3 w-full justify-items-center items-center">
        <flux:card class="space-y-6 w-140">
            <div>
                <flux:heading size="lg" class="ml-2">
                    <flux:callout inline>
                        <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start" style="font-size: 1.2rem;">Participantes não alocados</flux:callout.heading>
                        <x-slot name="controls" class="mt-2">
                            <flux:badge color="green" size="xs" rounded>{{$this->totalDeallocatedParticipants}}</flux:badge>
                        </x-slot>
                    </flux:callout>

                </flux:heading>
            </div>
            <div class="grid grid-cols-1 gap-2 w-full">
                <x=mary-accordion>
                    @foreach($this->deallocatedRoomTypeChurchesParticipants as $roomType)
                    <livewire:pages::events.allocations.deallocated-room-type :roomType="$roomType" />
                    @endforeach
                </x=mary-accordion>
            </div>
        </flux:card>


        <flux:card class="flex flex-col gap-8 w-90">

            <flux:callout variant="warning" icon="information-circle" inline>
                <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">Total de Participantes</flux:callout.heading>
                <x-slot name="controls" class="mt-1">
                    <flux:badge color="yellow" size="xs" rounded>{{$this->totalParticipants}}</flux:badge>
                </x-slot>
            </flux:callout>

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

            <flux:button variant="primary" icon:trailing="chevron-right" wire:click="allocateSelected">
                Alocar Selecionados
            </flux:button>

            <flux:button variant="primary" icon="chevron-left" wire:click="deallocateSelected">
                Desalocar Selecionados
            </flux:button>
        </flux:card>


        <flux:card class="space-y-6 w-140">
            <x=mary-accordion>
                @foreach($this->eventSiteRooms as $roomTypeArray)
                <livewire:pages::events.allocations.available-room-type :room-type="$roomTypeArray['roomType']" :rooms="$roomTypeArray['rooms']" :wire:key="$roomTypeArray['roomType']->id" />
                @endforeach
            </x=mary-accordion>
        </flux:card>
    </div>
</div>