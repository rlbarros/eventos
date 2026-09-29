<?php

use App\Models\Event;
use App\Services\Allocation\AutoAllocationService;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component {

    #[Locked]
    public int $eventId = 0;

    #[Locked]
    public array $assignments = [];

    public array $rooms = [];
    public array $unplaced = [];
    public int $totalPending = 0;

    private function event(): Event
    {
        $event = Event::findOrFail($this->eventId);
        abort_unless($event->isAllowedUser(auth()->user()), 403);

        return $event;
    }

    #[On('events.auto-distribution-preview')]
    public function preview()
    {
        $plan = (new AutoAllocationService())->plan($this->event());

        $this->assignments = $plan['assignments'];
        $this->rooms = $plan['rooms'];
        $this->unplaced = $plan['unplaced'];
        $this->totalPending = $plan['totalPending'];

        if ($this->totalPending === 0) {
            Toaster::info('Todos os participantes já estão alocados');
            return;
        }

        Flux::modal('events.auto-distribution')->show();
    }

    public function handleModalCloseEvent()
    {
        Flux::modal('events.auto-distribution')->close();
    }

    public function save()
    {
        if (empty($this->assignments)) {
            $this->handleModalCloseEvent();
            return;
        }

        $result = (new AutoAllocationService())->apply($this->event(), $this->assignments);

        if ($result['skipped'] > 0) {
            Toaster::warning($result['applied'] . ' participantes alocados; ' . $result['skipped'] . ' mudaram desde a prévia e ficaram de fora');
        } else {
            Toaster::success($result['applied'] . ' participantes alocados automaticamente');
        }

        $this->handleModalCloseEvent();
        $this->js('(function() { setTimeout(() => {window.location.reload()}, 1000); })();');
    }
}

?>

<flux:modal name="events.auto-distribution" wire:close="handleModalCloseEvent" class="w-full md:w-200">
    <form class="space-y-6" wire:submit.prevent="save">

        <div class="space-y-2">
            <flux:heading size="lg">Distribuição automática</flux:heading>
            <flux:text>
                {{ count($assignments) }} de {{ $totalPending }} participantes sem quarto serão alocados em {{ count($rooms) }} quartos.
                Nada é gravado até você confirmar.
            </flux:text>
            <flux:text size="sm" class="text-zinc-500">
                Cada pessoa vai para um quarto do tipo que contratou, sem passar dos leitos.
                Famílias (cônjuge, pai e mãe) ficam juntas e a mesma igreja fica no mesmo quarto sempre que possível.
                Quem já está alocado não é movido.
            </flux:text>
        </div>

        <div class="space-y-3 max-h-[55vh] overflow-y-auto pr-1">
            @foreach ($rooms as $room)
            <flux:card class="space-y-2 p-3" wire:key="auto-room-{{ $room['id'] }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <flux:heading size="sm">{{ $room['name'] }} <span class="font-normal text-zinc-500">· {{ $room['roomType'] }}</span></flux:heading>
                    <flux:badge color="indigo" size="sm" rounded>
                        {{ $room['occupied'] + count($room['newParticipants']) }}/{{ $room['beds'] }} leitos
                    </flux:badge>
                </div>
                @if ($room['occupied'] > 0)
                <flux:text size="sm" class="text-zinc-500">{{ $room['occupied'] }} já alocados neste quarto</flux:text>
                @endif
                <ul class="text-sm space-y-1">
                    @foreach ($room['newParticipants'] as $name)
                    <li>{{ $name }}</li>
                    @endforeach
                </ul>
            </flux:card>
            @endforeach

            @if (count($unplaced) > 0)
            <flux:callout variant="warning" icon="exclamation-triangle">
                <flux:callout.heading>{{ count($unplaced) }} participantes ficariam sem quarto</flux:callout.heading>
                <flux:callout.text>
                    <ul class="space-y-1">
                        @foreach ($unplaced as $item)
                        <li>{{ $item['name'] }} <span class="text-zinc-500">({{ $item['reason'] }})</span></li>
                        @endforeach
                    </ul>
                </flux:callout.text>
            </flux:callout>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t">
            <flux:modal.close>
                <flux:button variant="subtle">Cancelar</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" color="navy" :disabled="count($assignments) === 0">
                Confirmar distribuição
            </flux:button>
        </div>
    </form>
</flux:modal>
