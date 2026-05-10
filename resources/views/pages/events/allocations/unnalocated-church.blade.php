<?php

use Livewire\Component;

new class extends Component
{
    public array $church;

    public string $name;
    public array $participants;

    public function mount()
    {
        $this->name = $this->church['church'] ?? '';
        $this->participants = $this->church['participants'] ?? [];
    }
};
?>

<flux:field>
    <flux:label class="text-lg font-semibold">
        {{$name}}
        <flux:badge color="green" rounded size="xs" class="ml-2">{{count($participants)}}</flux:badge>
    </flux:label>

    <flux:checkbox.group class="mt-2">
        @if(count($participants) > 1)
        <flux:checkbox.all label="Todos" />
        @endif
        @foreach($participants as $participant)
        @if($loop->iteration
        <= count($participants))
            <flux:separator class="mb-4" variant="subtle" style="width: 365px!important;" />
        @endif
        <livewire:pages::events.allocations.event-participant :participant="$participant" :wire:key="$participant['id']" />
        @endforeach
    </flux:checkbox.group>
</flux:field>