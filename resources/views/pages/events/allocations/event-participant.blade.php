<?php

use Livewire\Component;

new class extends Component
{
    public array $participant;

    public int $id;
    public string $name;

    public function mount()
    {
        $this->id = $this->participant['id'] ?? 0;
        $this->name = $this->participant['name'] ?? '';
    }
}
?>

<flux:checkbox wire:key="id" value="{{ (string) $id }}" label="{{$name}}" />