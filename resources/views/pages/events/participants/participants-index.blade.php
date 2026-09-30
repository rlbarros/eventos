<?php

use App\Livewire\Components\GenericIndexComponent;
use App\Models\Church;
use App\Models\EventParticipantAllocation;
use App\Traits\Forms\Event\Participant\WithEventParticipantProperties;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use App\Mail\LodgingNoticeMail;
use App\Services\Messaging\LodgingNotice;
use App\Utils\WhatsAppUtil;
use Illuminate\Support\Facades\Mail;
use Masmerise\Toaster\Toaster;


new class extends GenericIndexComponent
{
    use WithEventParticipantProperties;

    public array $nonList;

    public \Illuminate\Support\Collection $churches;

    #[Url(history: true)]
    public int $churchId = 0;

    public function mount()
    {
        $this->nonList = EventParticipantAllocation::where('event_id', $this->eventId)
            ->pluck('person_id')
            ->values()
            ->toArray();

        $this->churches = Church::orderBy('name')->get(['id', 'name']);
    }

    public function customQueryScope($query)
    {
        if (!empty($this->churchId)) {
            $query->whereHas('person', function ($personQuery) {
                $personQuery->where('church_id', $this->churchId);
            });
        }
        return $query;
    }

    public function updatedChurchId()
    {
        $this->resetPage();
    }


    public function indexArray(): array
    {
        return [
            'header' => 'Participantes',
            'subHeader' => 'cadastre os participantes dos eventos.',
            'createButtonLabel' => 'Adicionar Participante',
            'createActionEventName' => 'events.participants.participant-create'
        ];
    }


    /** Link wa.me com o aviso de hospedagem, ou null se ainda sem quarto ou sem telefone válido. */
    public function lodgingWhatsappLink(EventParticipantAllocation $allocation): ?string
    {
        $notice = LodgingNotice::for($allocation);

        return $notice->isReady()
            ? WhatsAppUtil::link($allocation->person->phone, $notice->whatsappText())
            : null;
    }

    /** Disparado só pelo clique do organizador: envia o aviso de hospedagem por e-mail. */
    public function sendLodgingEmail(int $id): void
    {
        $allocation = EventParticipantAllocation::where('event_id', $this->eventId)->findOrFail($id);
        $notice = LodgingNotice::for($allocation);

        if (!$notice->isReady()) {
            Toaster::error('O quarto deste participante ainda não foi definido.');
            return;
        }

        if (empty($allocation->person->email)) {
            Toaster::error('O participante não tem e-mail cadastrado.');
            return;
        }

        Mail::to($allocation->person->email)->send(new LodgingNoticeMail($notice));
        Toaster::success('E-mail enviado para ' . $allocation->person->email);
    }

    #[On('events.participants.participant-delete-confirmed')]
    public function handleParticipantDeleteConfirmed(int $id)
    {
        $this->delete($id);
    }
}; ?>


<livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
    <livewire:slot name="extraFilters">
        <div class="w-full md:w-64">
            <flux:select wire:model.live="churchId" wire:island="list">
                <flux:select.option value="0">Todas as igrejas</flux:select.option>
                @foreach ($churches as $church)
                <flux:select.option value="{{ $church->id }}">{{ $church->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </livewire:slot>

    <livewire:pages::events.participants.participant-form :eventId="$this->eventId" :eventSiteId="$this->eventSiteId" :nonList="$this->nonList" />

    <flux:table :paginate="$this->index()" pagination:scroll-to>
        <flux:table.columns>
            <flux:table.column sortable sorted direction="desc">#</flux:table.column>
            <flux:table.column sortable>Participante</flux:table.column>
            <flux:table.column sortable>Tipo de Quarto</flux:table.column>
            <flux:table.column>Quarto</flux:table.column>
            <flux:table.column>Telefone</flux:table.column>
            <flux:table.column sortable>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->index() as $participant)
            <flux:table.row :key="$participant->id">
                <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                <flux:table.cell>
                    {{ $participant->descriptor() }}
                    @if ($participant->payer)
                    <div class="text-xs text-zinc-500 dark:text-zinc-400">Reserva paga por {{ $participant->payer->name }}</div>
                    @endif
                </flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room_type->name }}</flux:table.cell>
                <flux:table.cell>{{ $participant->event_site_room?->name ?: '—' }}</flux:table.cell>
                <flux:table.cell>{{ $participant->person->phone ?: '—' }}</flux:table.cell>
                <flux:table.cell>
                    <div class="flex gap-3">
                        @if ($participant->event_site_room_id)
                        @php($whatsappLink = $this->lodgingWhatsappLink($participant))
                        @if ($whatsappLink)
                        <flux:button href="{{ $whatsappLink }}" target="_blank" rel="noopener" icon="chat-bubble-left-right" size="sm" title="Avisar hospedagem por WhatsApp" />
                        @endif
                        @if ($participant->person->email)
                        <flux:button wire:click="sendLodgingEmail({{ $participant->id }})" wire:confirm="Enviar o aviso de hospedagem para {{ $participant->person->email }}?" icon="envelope" size="sm" title="Avisar hospedagem por e-mail" />
                        @endif
                        @endif
                        <flux:button href="{{ $eventId }}/participant/{{ $participant->id }}?selectedTab=payments-tab" icon="document-text" style="cursor: pointer;" wire:navigate
                            size="sm" />
                        <flux:button wire:click="$dispatch('events.participants.participant-edit', { id: {{ $participant->id }} })" icon="pencil-square" style="cursor: pointer;"
                            size="sm" />
                        <flux:button variant="danger" icon="trash" size="sm"
                            wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $participant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$participant->descriptor()}}', callbackDeleteEvent: 'events.participants.participant-delete-confirmed' })" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Sem participantes no evento
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</livewire:pages::forms.generic-list>