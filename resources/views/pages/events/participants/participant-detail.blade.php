<?php

use App\Models\EventParticipantAllocation;
use App\Models\EventSite;
use App\Models\EventSiteRoom;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public $eventId;
    public $personId;

    #[Url]
    public $selectedTab = 'payments-tab';

    use WithPagination;

    #[Computed]
    public function participant()
    {
        return EventParticipantAllocation::where('event_id', '=', $this->eventId)->where('person_id', '=', $this->personId)->firstOrFail();
    }


    #[Computed]
    public function participantsIndex()
    {
        return EventParticipantAllocation::where('event_id', '=', $this->eventId)->paginate(10);
    }


    #[On('events.participants.participant-delete-confirmed')]
    public function handleEventSiteDeleteConfirmed(int $id)
    {
        try {
            $eventSite = EventSite::findOrFail($id);
            $eventSite->delete();

            Toaster::success(EventSite::modelName() . $this->eventSite->descriptor() . ' excluído com sucesso');
            Flux::modal('dialogs.delete-confirmation')->close();
            $this->redirectRoute('event-sites');
        } catch (\Exception $e) {
            Toaster::warning('erro ' . $e->getMessage() . 'ao  apagar local de evento ' . $eventSite->descriptor());
        }
    }
};

?>


<div class="w-full mx-auto space-y-4">
    <div class="flex items-start max-md:flex-col">
        <div class="flex-1">
            <flux:heading size="lg" class="mb-4">Detalhamento de local de evento</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->eventSite()->name }}</flux:heading>
            <flux:subheading sixe="lg" class="mb-4">{{ $this->eventSiteLocation() }}</flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />
    <x-mary-tabs wire:model="selectedTab">
        <x-mary-tab name="participants-tab" icon="o-users">
            <x-slot:label>
                Participantes
            </x-slot:label>
            <livewire:pages::forms.events.participants.participants-index :eventId="$this->eventId" />
        </x-mary-tab>
        <x-mary-tab name="rooms-tab" icon="o-building-office">
            <x-slot:label>
                Alocação de Quartos
            </x-slot:label>

        </x-mary-tab>
    </x-mary-tabs>
</div>