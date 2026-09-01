<?php

use App\Models\Event;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $eventId;
    public string $email = '';

    public function mount()
    {
        $event = Event::findOrFail($this->eventId);
        abort_unless($event->isOwner(auth()->user()), 403);
    }

    #[Computed]
    public function event(): Event
    {
        return Event::findOrFail($this->eventId);
    }

    #[Computed]
    public function allowedUsers()
    {
        return $this->event->allowedUsers()->get();
    }

    public function addUser()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            Toaster::warning('Nenhum usuário encontrado com este e-mail.');
            return;
        }

        if ($this->event->isOwner($user)) {
            Toaster::warning('Este usuário já é o dono do evento.');
            return;
        }

        if ($this->event->allowedUsers()->whereKey($user->id)->exists()) {
            Toaster::warning('Este usuário já tem acesso ao evento.');
            return;
        }

        $this->event->allowedUsers()->attach($user->id);

        unset($this->allowedUsers);
        $this->email = '';

        Toaster::success('Usuário adicionado com sucesso!');
    }

    public function removeUser(int $userId)
    {
        $this->event->allowedUsers()->detach($userId);
        unset($this->allowedUsers);

        Toaster::success('Usuário removido com sucesso!');
    }
};
?>

<div class="w-full mx-auto space-y-4">
    <flux:callout inline>
        <flux:callout.heading>Usuários com acesso ao evento</flux:callout.heading>
        <flux:callout.text>Além do dono do evento, os usuários abaixo também podem gerenciá-lo.</flux:callout.text>
    </flux:callout>

    <form wire:submit="addUser" class="flex items-end gap-3">
        <flux:field class="flex-1">
            <flux:label>E-mail do usuário</flux:label>
            <flux:input wire:model="email" type="email" placeholder="usuario@email.com" />
            <flux:error name="email" />
        </flux:field>
        <flux:button type="submit" variant="primary" icon="plus">Adicionar</flux:button>
    </form>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nome</flux:table.column>
            <flux:table.column>E-mail</flux:table.column>
            <flux:table.column>Ações</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->allowedUsers as $user)
            <flux:table.row :key="$user->id">
                <flux:table.cell>{{ $user->name }}</flux:table.cell>
                <flux:table.cell>{{ $user->email }}</flux:table.cell>
                <flux:table.cell>
                    <flux:button variant="danger" icon="trash" size="sm" wire:click="removeUser({{ $user->id }})" wire:confirm="Remover o acesso deste usuário ao evento?" />
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="3" class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                    Nenhum usuário adicional com acesso a este evento
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
