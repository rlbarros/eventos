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

<flux:checkbox.group label="{{$name}}">
    @if(count($participants) > 1)
    <flux:checkbox.all label="Todos" />
    @endif
    @foreach($participants as $participant)
    @if($loop->iteration > 1)
    <flux:separator variant="subtle" style="width: 365px!important;" />
    @endif
    <livewire:pages::events.allocations.event-participant :participant="$participant" :wire:key="$participant['id']" />
    @endforeach
</flux:checkbox.group>