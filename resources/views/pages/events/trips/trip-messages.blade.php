<?php

use App\Models\EventTrip;
use App\Services\Messaging\TripMessages;
use App\Utils\WhatsAppUtil;
use Livewire\Component;

new class extends Component {

    public int $tripId;

    /** Um item por destinatário: rótulo, telefone (como cadastrado) e link wa.me (null se sem telefone válido). */
    public function recipients(): array
    {
        $trip = EventTrip::with(['event', 'event_driver', 'event_trip_participants.person'])->findOrFail($this->tripId);

        $recipients = [];

        if (!empty($trip->transporter_phone) || !empty($trip->transporter_name)) {
            $recipients[] = $this->recipient('Transportador', $trip->transporter_name, $trip->transporter_phone, TripMessages::forTransporter($trip));
        }

        $recipients[] = $this->recipient('Motorista', $trip->event_driver->name, $trip->event_driver->phone, TripMessages::forDriver($trip));

        foreach ($trip->event_trip_participants as $tripParticipant) {
            $person = $tripParticipant->person;
            $recipients[] = $this->recipient('Passageiro', $person->name, $person->phone, TripMessages::forPassenger($trip, $person));
        }

        return $recipients;
    }

    private function recipient(string $role, ?string $name, ?string $phone, string $message): array
    {
        return [
            'role' => $role,
            'name' => $name ?: '(sem nome)',
            'phone' => $phone,
            'link' => WhatsAppUtil::link($phone, $message),
        ];
    }
};

?>

<div class="space-y-2">
    <flux:heading size="lg">Mensagens por WhatsApp</flux:heading>
    <flux:text>Abre o WhatsApp com a mensagem pronta; nada é enviado até você confirmar lá.</flux:text>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Destinatário</flux:table.column>
            <flux:table.column>Nome</flux:table.column>
            <flux:table.column>Telefone</flux:table.column>
            <flux:table.column>Ação</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->recipients() as $recipient)
            <flux:table.row>
                <flux:table.cell>{{ $recipient['role'] }}</flux:table.cell>
                <flux:table.cell>{{ $recipient['name'] }}</flux:table.cell>
                <flux:table.cell>{{ $recipient['phone'] ?: '—' }}</flux:table.cell>
                <flux:table.cell>
                    @if ($recipient['link'])
                    <flux:button size="sm" icon="chat-bubble-left-right" href="{{ $recipient['link'] }}" target="_blank" rel="noopener">Enviar WhatsApp</flux:button>
                    @else
                    <flux:text>Sem telefone válido</flux:text>
                    @endif
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
