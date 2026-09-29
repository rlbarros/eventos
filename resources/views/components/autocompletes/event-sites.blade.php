<?php

use App\Models\EventSite;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {

    #[Reactive]
    public bool $readonly;

    public string $query = '';

    #[On('event-site-injected')]
    public function handleEventSiteInjected(int $eventSiteId)
    {
        $this->query = empty($eventSiteId) ? '' : (EventSite::find($eventSiteId)?->name ?? '');
    }

    public function search(string $query): array
    {
        return EventSite::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn ($eventSite) => ['id' => $eventSite->id, 'label' => $eventSite->name])
            ->all();
    }

    public function select(int $id): void
    {
        $this->dispatch('event-site-selected', $id);
    }
};

?>

<x-autocomplete label="Local do Evento" :readonly="$readonly" />
