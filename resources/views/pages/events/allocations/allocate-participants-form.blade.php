<?php

use App\Models\EventParticipantAllocation;
use App\Models\EventSiteRoom;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public array $participants;
    public array $names;
    public array $availableRooms;
    public int $selectedRoom;
    public bool $submitDisabled = false;

    #[On('events.allocate-participants-create')]
    public function allocatingRequest()
    {

        $js = "(function() {
            let arrayConcatenado = [];

            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                
                if (key && key.startsWith('unnalocated-selected-participants')) {
                    try {
                        // Recupera a string do localStorage
                        let dadoRaw = localStorage.getItem(key);
                        // Converte de volta para Array JavaScript
                        let dadosArray = JSON.parse(dadoRaw); 
                        
                        if (Array.isArray(dadosArray)) {
                            // Adiciona os itens ao array principal (mesma coisa que usar .concat)
                            arrayConcatenado.push(...dadosArray);
                        }
                    } catch (e) {
                        console.error('Erro ao ler a chave do localStorage: ' + key, e);
                    }
                }
            }

            // Envia o array único concatenado de volta para o PHP do Livewire
            console.log(arrayConcatenado);
            Livewire.dispatch('events.allocate-participants-load-records', { participants: arrayConcatenado});
        })();";
        $this->js($js);
        Flux::modal('events.allocate-participants')->show();
    }

    #[On('events.allocate-participants-load-records')]
    public function loadAlocationRecords(array $participants)
    {
        if (empty($participants)) {
            Toaster::error('Nã há participantes selecionados, selecione-os no componente de participantes não alocados');
            $this->handleModalCloseEvent();
            return;
        }

        $this->participants = $participants;


        $eventParticipants = EventParticipantAllocation::whereIn('id', $participants)
            ->with('event_site_room_type')
            ->with('person')
            ->get();

        $this->names = $eventParticipants->map(fn($allocation) => $allocation->person->descriptor())->values()->all();



        $eventSiteRoomType = $eventParticipants->first()?->event_site_room_type;
        if (empty($eventSiteRoomType)) {
            Toaster::error('Tipo de quarto não encontrato, repita o processo de selecão no componente de participantes não alocados');
            $this->handleModalCloseEvent();
            return;
        }

        $eventSiteId = $eventSiteRoomType->event_site_id;

        if (!$eventSiteId) {
            $this->availableRooms = [];
            return;
        }

        $eventSiteRoomTypeId = $eventSiteRoomType->id;

        $rooms = EventSiteRoom::where('event_site_id', $eventSiteId)
            ->where('event_site_room_type_id', '=', $eventSiteRoomTypeId)
            ->with('event_site_room_type')
            ->get();

        $this->availableRooms = $rooms->map(function ($room) {
            $roomType = $room->event_site_room_type;
            $allocationsCount = EventParticipantAllocation::where('event_site_room_id', $room->id)->count();

            return [
                'id' => $room->id,
                'name' => $room->name,
                'totalBeds' => $roomType?->beds ?? 0,
                'availableBeds' => max(0, ($roomType?->beds ?? 0) - $allocationsCount),
                'occupedBeds' => $allocationsCount,
            ];
        })->filter(fn($room) => $room['availableBeds'] > 0 && $room['availableBeds'] > count($this->names))->values()->all();
    }

    public function handleModalCloseEvent()
    {
        Flux::modal('events.allocate-participants')->close();
    }

    public function save()
    {
        DB::transaction(function () {
            EventParticipantAllocation::whereIn('id', $this->participants)
                ->update(['event_site_room_id' => $this->selectedRoom]);
        });

        Toaster::success('Participantes alocados com sucesso');
        $this->handleModalCloseEvent();
        $this->js('window.location.reload()');
    }
}

?>

<flux:modal name="events.allocate-participants" wire:close="handleModalCloseEvent" class="md:w-350">
    <form class="space-y-8" wire:submit.prevent="save">

        <div class="space-y-2">
            <flux:heading size="lg">
                Alocar {{count($names)}} pariticipantes selecionados em qual carto?

                <flux:tooltip toggleable>
                    <flux:button icon="information-circle" size="xs" variant="ghost" />
                    <flux:tooltip.content class="max-w-[20rem] space-y-2">
                        @foreach($names as $name)
                        <p>{{$name}}</p>
                        @endforeach
                    </flux:tooltip.content>
                </flux:tooltip>
            </flux:heading>
        </div>

        <div class="space-y-6">
            <flux:field class="w-full">
                <flux:label>Quartos Dísponíveis</flux:label>

                <flux:select wire:model.live="selectedRoom" required>

                    <flux:select.option value="0">Selectioe</flux:select.option>
                    @foreach ($availableRooms as $room)
                    <flux:select.option :wire:key="$room['id']" :value="$room['id']">
                        {{ $room['name'] }}
                    </flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <flux:modal.close>
                <flux:button variant="subtle">
                    Fechar
                </flux:button>
            </flux:modal.close>

            @island
            <flux:button type="submit" variant="primary" :disabled="$submitDisabled" color="navy">
                Alocar
            </flux:button>
            @endisland
        </div>
    </form>
</flux:modal>