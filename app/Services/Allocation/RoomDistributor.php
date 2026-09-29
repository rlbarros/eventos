<?php

namespace App\Services\Allocation;

/**
 * Algoritmo puro (sem banco) que distribui participantes ainda sem quarto
 * nos leitos livres dos quartos do local do evento.
 *
 * Regras, em ordem de prioridade:
 *  1. cada participante só vai para um quarto do tipo que ele contratou
 *     (event_site_room_type_id) e a capacidade (leitos) nunca é excedida;
 *  2. quem já está alocado não é movido: só os leitos livres são usados;
 *  3. famílias (cônjuge, pai e mãe ligados no cadastro) ficam no mesmo quarto,
 *     de preferência junto de um parente que já esteja alocado;
 *  4. pessoas da mesma igreja ficam juntas; as igrejas maiores escolhem
 *     primeiro e ocupam quartos vazios antes de misturar igrejas;
 *  5. uma família maior que o quarto é dividida no menor número de quartos.
 *
 * Quem não couber em nenhum quarto do seu tipo fica fora da distribuição.
 */
class RoomDistributor
{
    // prioridade de escolha do quarto para um grupo (menor = melhor)
    private const TIER_FAMILY = 0;
    private const TIER_SAME_CHURCH = 1;
    private const TIER_EMPTY = 2;
    private const TIER_MIXED = 3;

    /**
     * @param array<int, array{id: int, person_id: int, room_type_id: int, church_id: ?int, relatives: list<int>}> $participants
     *        participantes sem quarto; relatives são ids de pessoas ligadas (cônjuge, pai, mãe)
     * @param array<int, array{id: int, room_type_id: int, free: int, person_ids: list<int>, church_ids: list<int>}> $rooms
     *        quartos do local com os leitos livres e quem já está neles
     * @return array{assignments: array<int, int>, unplaced: list<int>}
     *         assignments: id do participante => id do quarto; unplaced: ids que não couberam
     */
    public function distribute(array $participants, array $rooms): array
    {
        $assignments = [];
        $unplaced = [];

        $participantsByType = [];
        foreach ($participants as $participant) {
            $participantsByType[$participant['room_type_id']][] = $participant;
        }
        ksort($participantsByType);

        $roomsByType = [];
        foreach ($rooms as $room) {
            $roomsByType[$room['room_type_id']][$room['id']] = [
                'id' => $room['id'],
                'free' => max(0, $room['free']),
                'person_ids' => array_values($room['person_ids']),
                'church_ids' => array_values(array_filter($room['church_ids'], fn($id) => $id !== null)),
                'occupied' => count($room['person_ids']) > 0,
            ];
        }

        foreach ($participantsByType as $typeId => $typeParticipants) {
            $typeRooms = $roomsByType[$typeId] ?? [];
            ksort($typeRooms);

            foreach ($this->orderedUnits($typeParticipants) as $unit) {
                $this->placeUnit($unit, $typeRooms, $assignments, $unplaced);
            }
        }

        sort($unplaced);

        return ['assignments' => $assignments, 'unplaced' => $unplaced];
    }

    /**
     * Agrupa em famílias e ordena: igrejas com mais participantes primeiro e,
     * dentro de cada igreja, as famílias maiores primeiro.
     *
     * @return list<array{church_id: ?int, members: list<array>}>
     */
    private function orderedUnits(array $participants): array
    {
        usort($participants, fn($a, $b) => $a['id'] <=> $b['id']);

        $byPerson = [];
        foreach ($participants as $participant) {
            $byPerson[$participant['person_id']] = $participant;
        }

        // ligações nos dois sentidos (o filho aponta para o pai; o pai não aponta para o filho)
        $links = [];
        foreach ($participants as $participant) {
            foreach ($participant['relatives'] as $relativeId) {
                if ($relativeId === null || !isset($byPerson[$relativeId]) || $relativeId === $participant['person_id']) {
                    continue;
                }
                $links[$participant['person_id']][] = $relativeId;
                $links[$relativeId][] = $participant['person_id'];
            }
        }

        $visited = [];
        $units = [];
        foreach ($participants as $participant) {
            if (isset($visited[$participant['person_id']])) {
                continue;
            }

            $members = [];
            $queue = [$participant['person_id']];
            $visited[$participant['person_id']] = true;
            while ($queue) {
                $personId = array_shift($queue);
                $members[] = $byPerson[$personId];
                $next = array_unique($links[$personId] ?? []);
                sort($next);
                foreach ($next as $relativeId) {
                    if (!isset($visited[$relativeId])) {
                        $visited[$relativeId] = true;
                        $queue[] = $relativeId;
                    }
                }
            }

            $units[] = ['church_id' => $this->mainChurch($members), 'members' => $members];
        }

        $churchSizes = [];
        foreach ($units as $unit) {
            $key = $unit['church_id'] ?? 0;
            $churchSizes[$key] = ($churchSizes[$key] ?? 0) + count($unit['members']);
        }

        usort($units, function ($a, $b) use ($churchSizes) {
            $churchA = $a['church_id'] ?? 0;
            $churchB = $b['church_id'] ?? 0;

            // sem igreja vai por último, para não abrir quartos vazios antes das igrejas
            return [$churchA === 0, -$churchSizes[$churchA], $churchA, -count($a['members']), $a['members'][0]['id']]
                <=> [$churchB === 0, -$churchSizes[$churchB], $churchB, -count($b['members']), $b['members'][0]['id']];
        });

        return $units;
    }

    private function mainChurch(array $members): ?int
    {
        $counts = [];
        foreach ($members as $member) {
            if ($member['church_id'] !== null) {
                $counts[$member['church_id']] = ($counts[$member['church_id']] ?? 0) + 1;
            }
        }
        if (!$counts) {
            return null;
        }
        ksort($counts);
        arsort($counts);

        return array_key_first($counts);
    }

    private function placeUnit(array $unit, array &$rooms, array &$assignments, array &$unplaced): void
    {
        $remaining = $unit['members'];
        $familyPersonIds = [];
        foreach ($unit['members'] as $member) {
            $familyPersonIds[] = $member['person_id'];
            foreach ($member['relatives'] as $relativeId) {
                if ($relativeId !== null) {
                    $familyPersonIds[] = $relativeId;
                }
            }
        }

        while ($remaining) {
            $roomId = $this->chooseRoom($rooms, count($remaining), $unit['church_id'], $familyPersonIds);
            if ($roomId === null) {
                foreach ($remaining as $member) {
                    $unplaced[] = $member['id'];
                }
                return;
            }

            $placing = array_splice($remaining, 0, min(count($remaining), $rooms[$roomId]['free']));
            foreach ($placing as $member) {
                $assignments[$member['id']] = $roomId;
                $rooms[$roomId]['person_ids'][] = $member['person_id'];
                if ($member['church_id'] !== null) {
                    $rooms[$roomId]['church_ids'][] = $member['church_id'];
                }
            }
            $rooms[$roomId]['free'] -= count($placing);
            $rooms[$roomId]['occupied'] = true;
        }
    }

    /**
     * Um quarto onde o grupo inteiro cabe, pela prioridade e depois pelo menor
     * número de leitos livres (encaixe mais justo). Se o grupo não couber inteiro
     * em nenhum, o quarto com mais leitos livres, para dividir o mínimo possível.
     */
    private function chooseRoom(array $rooms, int $size, ?int $churchId, array $familyPersonIds): ?int
    {
        $best = null;
        $bestKey = null;
        $largest = null;
        $largestKey = null;

        foreach ($rooms as $room) {
            if ($room['free'] <= 0) {
                continue;
            }

            $tier = $this->tier($room, $churchId, $familyPersonIds);

            if ($room['free'] >= $size) {
                $key = [$tier, $room['free'], $room['id']];
                if ($bestKey === null || $key < $bestKey) {
                    $best = $room['id'];
                    $bestKey = $key;
                }
            }

            $key = [-$room['free'], $tier, $room['id']];
            if ($largestKey === null || $key < $largestKey) {
                $largest = $room['id'];
                $largestKey = $key;
            }
        }

        return $best ?? $largest;
    }

    private function tier(array $room, ?int $churchId, array $familyPersonIds): int
    {
        if (array_intersect($room['person_ids'], $familyPersonIds)) {
            return self::TIER_FAMILY;
        }
        if ($churchId !== null && in_array($churchId, $room['church_ids'], true)) {
            return self::TIER_SAME_CHURCH;
        }
        if (!$room['occupied']) {
            return self::TIER_EMPTY;
        }

        return self::TIER_MIXED;
    }
}
