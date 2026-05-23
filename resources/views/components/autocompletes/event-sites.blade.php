<?php

use App\Models\EventSite;
use Livewire\Attributes\Json;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    #[Reactive]
    public int $eventSiteId;

    public string $query;

    #[On('event-site-injected')]
    public function handleEventSiteInjected(int $eventSiteId)
    {
        if (empty($eventSiteId)) {
            $this->query = '';
        } else {
            $this->query = EventSite::find($eventSiteId)->name;
        }
    }

    #[Json]
    public function search(string $query)
    {
        $eventSites = EventSite::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        if ($eventSites->count() === 1) {
            $eventSite = $eventSites->first();
            $this->dispatch('event-site-selected', $eventSite->id);
        } else {
        }

        return $eventSites;
    }
};

?>

<div x-data="{ query: @entangle('query'), datalistVisible: false, eventSites: [] }">

    <flux:field class="w-full">
        <flux:label>Local do Evento</flux:label>

        <flux:input x-model.debounce.300ms="query" wire:model="query" x-on:input.debounce.300ms="$wire.search(query).then(data => {eventSites = data; datalistVisible = data.length > 1})"
            list="event-sites-list" autocomplete="off" :readonly="$readonly" />

        <datalist id="event-sites-list" class="w-full hide-only-child" style="max-height: 200px; overflow-y: auto;" x-show="datalistVisible" x-cloak>
            <template x-for="eventSite in eventSites">
                <option x-value="eventSite.id" x-text="eventSite.name"></option>
            </template>
        </datalist>

    </flux:field>
</div>