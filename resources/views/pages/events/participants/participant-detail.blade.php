<?php

use App\Models\EventParticipantAllocation;
use App\Models\EventSite;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

new class extends Component {

    public int $allocationId;

    public object $allocation;
    public object $person;
    public object $roomType;

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
    }
};

?>


<div class="w-full mx-auto space-y-4">
    <div class="flex items-start max-md:flex-col">
        <div class="flex-1">
            <flux:heading size="lg" class="mb-4">Detalhamento de participante</flux:heading>
            <flux:heading size="sm" class="mb-4">{{ $this->person->descriptor()  }}</flux:heading>
            <flux:subheading sixe="lg" class="mb-4">{{ $this->roomType->descriptor() }}</flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />
    <x-mary-tabs wire:model="selectedTab">
        <x-mary-tab name="payments-tab" icon="o-users">
            <x-slot:label>
                Pagamentos
            </x-slot:label>
        </x-mary-tab>
        <x-mary-tab name="services-tab" icon="o-building-office">
            <x-slot:label>
                Serviços
            </x-slot:label>

        </x-mary-tab>
    </x-mary-tabs>
</div>