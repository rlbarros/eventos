<?php

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public $eventId;

    #[Url]
    public $selectedTab = 'participants-tab';

    use WithPagination;

    #[Computed]
    public function event()
    {
        return Event::findOrFail($this->eventId);
    }

    public function eventSiteLocation()
    {
        if ($this->event()->event_site->city) {
            return $this->event()->event_site->city->name . ' / ' . $this->event()->event_site->state->name;
        } else {
            return 'Localização não informada';
        }
    }

    public function eventDates()
    {
        $startDate = \App\Utils\DateUtil::formatDateToBr($this->event()->start_date);
        $endDate = \App\Utils\DateUtil::formatDateToBr($this->event()->end_date);
        return "De {$startDate} até {$endDate}";
    }


    #[Computed]
    public function participantsIndex()
    {
        return EventParticipantAllocation::where('event_id', '=', $this->eventId)->paginate(10);
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
    <livewire:dialogs::delete-confirmation />
    <div class="flex items-start max-md:flex-col">
        <div class="flex-1">
            <flux:heading size="lg" class="mb-4">Detalhamento de Evento</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->event()->name }}</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->event()->event_site->name }}</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->eventSiteLocation() }}</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->eventDates() }}</flux:heading>
        </div>
    </div>
    <flux:separator variant="subtle" />
    <x-mary-tabs wire:model="selectedTab">
        <x-mary-tab name="participants-tab" icon="o-users">
            <x-slot:label>
                Participantes
            </x-slot:label>
            <livewire:pages::events.participants.participants-index :eventId="$this->eventId" />
        </x-mary-tab>
        <x-mary-tab name="allocations-tab" icon="o-building-office">
            <x-slot:label>
                Alocação de Quartos
            </x-slot:label>
        </x-mary-tab>
        <x-mary-tab name="fees-tab" icon="o-building-office">
            <x-slot:label>
                taxas
            </x-slot:label>
        </x-mary-tab>
    </x-mary-tabs>
</div>