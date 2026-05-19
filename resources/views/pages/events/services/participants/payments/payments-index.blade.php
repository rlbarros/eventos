<flux:modal :name="$modalName" wire:close="handleModalCloseEvent" class="md:w-350">
    <livewire:pages::forms.generic-list :indexArray="$this->indexArray()">
        <livewire:pages::events.services.participants.payments.payment-form :eventId="$this->eventId" :serviceId="$this->serviceId" />

        <flux:table :paginate="$this->index()" pagination:scroll-to>
            <flux:table.columns>
                <flux:table.column sortable sorted direction="desc">#</flux:table.column>
                <flux:table.column sortable>Participante</flux:table.column>
                <flux:table.column sortable>Ações</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->participants as $participant)
                <flux:table.row :key="$participant->id">
                    <flux:table.cell>{{ $participant->id }}</flux:table.cell>
                    <flux:table.cell>{{ $participant->person->descriptor() }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-3">
                            <flux:button wire:click="$dispatch('events.services.1participants.participant-edit', { id: {{ $participant->id }}, personId: {{ $participant->person_id }} })" icon="pencil-square" style="cursor: pointer;"
                                size="sm" />
                            <flux:button variant="danger" icon="trash" size="sm"
                                wire:click="$dispatch('dialogs.delete-confirmation', { objectId: {{ $participant->id }}, modelName: '{{$this->modelName()}}', descriptor: '{{$participant->descriptor()}}', callbackDeleteEvent: 'events.participants.participant-delete-confirmed' })" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </livewire:pages::forms.generic-list>
</flux:modal>