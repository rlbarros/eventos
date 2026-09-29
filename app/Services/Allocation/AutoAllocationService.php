<?php

namespace App\Services\Allocation;

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\EventSiteRoom;
use App\Utils\DescriptorUtil;
use Illuminate\Support\Facades\DB;

/**
 * Liga o RoomDistributor ao banco: monta a prévia da distribuição automática
 * de um evento e grava a prévia confirmada pelo organizador.
 */
class AutoAllocationService
{
    public function __construct(private RoomDistributor $distributor = new RoomDistributor())
    {
    }

    /**
     * @return array{
     *   assignments: array<int, int>,
     *   rooms: list<array{id: int, name: string, roomType: string, beds: int, occupied: int, newParticipants: list<string>}>,
     *   unplaced: list<array{name: string, reason: string}>,
     *   totalPending: int
     * }
     */
    public function plan(Event $event): array
    {
        $allocations = EventParticipantAllocation::where('event_id', $event->id)
            ->with(['person.church', 'event_site_room_type'])
            ->orderBy('id')
            ->get();

        $rooms = EventSiteRoom::where('event_site_id', $event->event_site_id)
            ->with('event_site_room_type')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $pending = $allocations->whereNull('event_site_room_id');
        $unplaced = [];

        $participants = [];
        foreach ($pending as $allocation) {
            if (empty($allocation->event_site_room_type_id)) {
                $unplaced[] = ['name' => $this->name($allocation), 'reason' => 'sem tipo de quarto definido'];
                continue;
            }
            $person = $allocation->person;
            $participants[] = [
                'id' => $allocation->id,
                'person_id' => $allocation->person_id,
                'room_type_id' => $allocation->event_site_room_type_id,
                'church_id' => $person?->church_id,
                'relatives' => array_values(array_filter([$person?->spouse_id, $person?->father_id, $person?->mother_id])),
            ];
        }

        $roomsInput = [];
        foreach ($rooms as $room) {
            $occupants = $allocations->where('event_site_room_id', $room->id);
            $roomsInput[] = [
                'id' => $room->id,
                'room_type_id' => $room->event_site_room_type_id,
                'free' => ($room->event_site_room_type?->beds ?? 0) - $occupants->count(),
                'person_ids' => $occupants->pluck('person_id')->values()->all(),
                'church_ids' => $occupants->map(fn($a) => $a->person?->church_id)->values()->all(),
            ];
        }

        $result = $this->distributor->distribute($participants, $roomsInput);

        $byId = $pending->keyBy('id');
        foreach ($result['unplaced'] as $allocationId) {
            $allocation = $byId[$allocationId];
            $unplaced[] = [
                'name' => $this->name($allocation),
                'reason' => 'sem leito livre em ' . ($allocation->event_site_room_type?->name ?? 'seu tipo de quarto'),
            ];
        }

        $roomsPreview = [];
        foreach ($result['assignments'] as $allocationId => $roomId) {
            if (!isset($roomsPreview[$roomId])) {
                $room = $rooms[$roomId];
                $roomsPreview[$roomId] = [
                    'id' => $room->id,
                    'name' => $room->name,
                    'roomType' => $room->event_site_room_type?->name ?? '',
                    'beds' => $room->event_site_room_type?->beds ?? 0,
                    'occupied' => $allocations->where('event_site_room_id', $room->id)->count(),
                    'newParticipants' => [],
                ];
            }
            $roomsPreview[$roomId]['newParticipants'][] = $this->name($byId[$allocationId]);
        }

        $roomsPreview = array_values($roomsPreview);
        usort($roomsPreview, fn($a, $b) => [$a['roomType'], $a['name'], $a['id']] <=> [$b['roomType'], $b['name'], $b['id']]);

        return [
            'assignments' => $result['assignments'],
            'rooms' => $roomsPreview,
            'unplaced' => $unplaced,
            'totalPending' => $pending->count(),
        ];
    }

    /**
     * Grava a prévia. Confere tudo de novo dentro da transação, porque alguém
     * pode ter alocado à mão entre a prévia e a confirmação: só grava quem
     * continua sem quarto, no quarto do tipo certo e com leito livre.
     *
     * @param array<int, int> $assignments id da alocação => id do quarto
     * @return array{applied: int, skipped: int}
     */
    public function apply(Event $event, array $assignments): array
    {
        return DB::transaction(function () use ($event, $assignments) {
            $rooms = EventSiteRoom::where('event_site_id', $event->event_site_id)
                ->whereIn('id', array_unique(array_values($assignments)))
                ->with('event_site_room_type')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $allocations = EventParticipantAllocation::where('event_id', $event->id)
                ->whereIn('id', array_keys($assignments))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $occupied = EventParticipantAllocation::where('event_id', $event->id)
                ->whereIn('event_site_room_id', $rooms->keys())
                ->selectRaw('event_site_room_id, count(*) as total')
                ->groupBy('event_site_room_id')
                ->pluck('total', 'event_site_room_id');

            $applied = 0;
            foreach ($assignments as $allocationId => $roomId) {
                $allocation = $allocations[$allocationId] ?? null;
                $room = $rooms[$roomId] ?? null;
                if (!$allocation || !$room || $allocation->event_site_room_id !== null) {
                    continue;
                }
                if ((int) $allocation->event_site_room_type_id !== (int) $room->event_site_room_type_id) {
                    continue;
                }
                $used = (int) ($occupied[$roomId] ?? 0);
                if ($used >= ($room->event_site_room_type?->beds ?? 0)) {
                    continue;
                }

                $allocation->update(['event_site_room_id' => $roomId]);
                $occupied[$roomId] = $used + 1;
                $applied++;
            }

            return ['applied' => $applied, 'skipped' => count($assignments) - $applied];
        });
    }

    private function name(EventParticipantAllocation $allocation): string
    {
        $person = $allocation->person;
        if (!$person) {
            return '';
        }
        $name = trim(($person->function ? DescriptorUtil::functionAbreviation($person->function) : '') . ' ' . $person->name);
        $church = $person->church?->name;

        return $church ? $name . ' (' . $church . ')' : $name;
    }
}
