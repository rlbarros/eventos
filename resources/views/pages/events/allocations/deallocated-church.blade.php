<?php

use Livewire\Component;

new class extends Component
{
    public array $church;
    public string $name;
    public array $participants;
    public array $selectedParticipants = [];

    public function mount()
    {
        $this->name = $this->church['church'] ?? '';
        $this->participants = $this->church['participants'] ?? [];
    }

    public function updatedSelectedParticipants()
    {
        $key = 'deallocated-selected-participants-' . $this->name;
        $js = "localStorage.setItem('" . $key . "', '" . json_encode($this->selectedParticipants) . "');";
        $this->js($js);
    }
};
?>

<flux:field>
    <flux:label class="text-lg font-semibold">
        {{$name}}
        <flux:badge color="green" rounded size="xs" class="ml-2">{{count($participants)}}</flux:badge>
    </flux:label>

    <flux:checkbox.group class="mt-2" wire:model.live="selectedParticipants">
        @foreach($participants as $participant)


        <flux:checkbox wire:key="{{ $participant['id'] }}" value="{{ (string) $participant['id'] }}" label="{{$participant['name']}}" />

        @if(!$loop->last)
        <flux:separator class="my-2" variant="subtle" style="width: 365px!important;" />
        @endif
        @endforeach
    </flux:checkbox.group>
</flux:field>