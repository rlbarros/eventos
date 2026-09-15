<?php

use App\Models\Church;
use App\Models\Event;
use App\Models\Person;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public string $churchName = '';
    public array $people = [];
    public array $events = [];

    #[On('forms.churchs.church-list')]
    public function handleListRequest(int $id): void
    {
        $church = Church::findOrFail($id);

        $this->churchName = $church->name;
        $this->people = Person::query()
            ->where('church_id', $id)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'function'])
            ->map(fn(Person $person): array => [
                'id' => $person->id,
                'name' => $person->name,
                'phone' => $person->phone,
                'function' => $person->function,
            ])
            ->all();
        $this->events = Event::query()
            ->where('church_id', $id)
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date'])
            ->map(fn(Event $event): array => [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => \App\Utils\DateUtil::formatDateToBr($event->start_date),
                'end_date' => \App\Utils\DateUtil::formatDateToBr($event->end_date),
            ])
            ->all();

        Flux::modal('forms.churchs.church-list')->show();
    }
};
?>

<flux:modal name="forms.churchs.church-list" class="md:w-650">
    <div x-data="{ activeTab: 'people', peopleSearch: '', eventsSearch: '' }" class="space-y-5">
        <div>
            <flux:heading size="lg">{{ $churchName }}</flux:heading>
            <flux:subheading>Pessoas e eventos vinculados à igreja</flux:subheading>
        </div>

        <div class="border-b border-zinc-200 dark:border-zinc-700" role="tablist" aria-label="Informações da igreja">
            <div class="flex gap-6">
                <button type="button" role="tab" :aria-selected="activeTab === 'people'" @click="activeTab = 'people'"
                    class="border-b-2 px-1 pb-2 text-sm font-medium"
                    :class="activeTab === 'people' ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500'">
                    Pessoas ({{ count($people) }})
                </button>
                <button type="button" role="tab" :aria-selected="activeTab === 'events'" @click="activeTab = 'events'"
                    class="border-b-2 px-1 pb-2 text-sm font-medium"
                    :class="activeTab === 'events' ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white' : 'border-transparent text-zinc-500'">
                    Eventos ({{ count($events) }})
                </button>
            </div>
        </div>

        <section x-show="activeTab === 'people'" role="tabpanel" class="space-y-3">
            <flux:input x-model="peopleSearch" placeholder="Pesquisar pessoas..." icon="magnifying-glass" />
            <div class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <template x-for="person in @js($people)" :key="person.id">
                    <div x-show="!peopleSearch || [person.name, person.phone, person.function].filter(Boolean).join(' ').toLowerCase().includes(peopleSearch.toLowerCase())"
                        class="border-b border-zinc-200 p-3 last:border-b-0 dark:border-zinc-700">
                        <p class="font-medium" x-text="person.name"></p>
                        <p class="text-sm text-zinc-500" x-text="[person.function, person.phone].filter(Boolean).join(' · ')"></p>
                    </div>
                </template>
                @if (count($people) === 0)
                <p class="p-6 text-center text-sm text-zinc-500">Nenhuma pessoa vinculada.</p>
                @endif
            </div>
        </section>

        <section x-show="activeTab === 'events'" role="tabpanel" class="space-y-3" x-cloak>
            <flux:input x-model="eventsSearch" placeholder="Pesquisar eventos..." icon="magnifying-glass" />
            <div class="max-h-80 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <template x-for="event in @js($events)" :key="event.id">
                    <div x-show="!eventsSearch || event.name.toLowerCase().includes(eventsSearch.toLowerCase())"
                        class="border-b border-zinc-200 p-3 last:border-b-0 dark:border-zinc-700">
                        <p class="font-medium" x-text="event.name"></p>
                        <p class="text-sm text-zinc-500" x-text="event.start_date + (event.end_date ? ' até ' + event.end_date : '')"></p>
                    </div>
                </template>
                @if (count($events) === 0)
                <p class="p-6 text-center text-sm text-zinc-500">Nenhum evento vinculado.</p>
                @endif
            </div>
        </section>

        <div class="flex justify-end border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:modal.close>
                <flux:button variant="subtle">Fechar</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>