<?php

namespace App\Services\Pricing;

use App\Models\Event;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use Illuminate\Support\Collection;

/**
 * Preço da inscrição quando a taxa depende de quantas pessoas ficam no quarto.
 *
 * Uma reserva é o pagador mais quem aponta para ele em `payer_person_id` (mesmo evento). A
 * ocupação é o tamanho da reserva; cada pessoa paga a taxa da sua categoria (Integral ou Infantil,
 * pela idade) na faixa de ocupação correspondente, e o pagador responde pelo total.
 */
class OccupancyPricing
{
    /**
     * Taxa que vale para a categoria e a ocupação. Faixas nulas valem para qualquer ocupação e só
     * entram quando nenhuma faixa específica serve; entre as específicas vence a mais estreita.
     * Sem taxa Infantil para a ocupação, a pessoa paga a Integral.
     *
     * @param  iterable<object>  $fees  objetos com category, min_occupants e max_occupants
     */
    public function pick(iterable $fees, string $category, int $occupancy): ?object
    {
        $fees = collect($fees);

        foreach (array_unique([$category, 'Integral']) as $wanted) {
            $found = $fees
                ->filter(fn ($fee) => $fee->category === $wanted && $this->covers($fee, $occupancy))
                ->sortBy(fn ($fee) => $this->width($fee))
                ->first();
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /** Pagador da reserva de uma participação (ela mesma quando não tem outro pagador). */
    public function payerAllocation(EventParticipantAllocation $allocation): EventParticipantAllocation
    {
        if (empty($allocation->payer_person_id) || $allocation->payer_person_id === $allocation->person_id) {
            return $allocation;
        }

        return EventParticipantAllocation::where('event_id', $allocation->event_id)
            ->where('person_id', $allocation->payer_person_id)
            ->first() ?? $allocation;
    }

    public function isPayer(EventParticipantAllocation $allocation): bool
    {
        return $this->payerAllocation($allocation)->is($allocation);
    }

    /** Participações da reserva cujo pagador é `$payer`, incluindo a dele. */
    public function reservation(EventParticipantAllocation $payer): Collection
    {
        $members = EventParticipantAllocation::where('event_id', $payer->event_id)
            ->where('payer_person_id', $payer->person_id)
            ->where('id', '!=', $payer->id)
            ->with('person')
            ->get();

        return collect([$payer->loadMissing('person')])->concat($members);
    }

    public function occupancy(EventParticipantAllocation $allocation): int
    {
        return $this->reservation($this->payerAllocation($allocation))->count();
    }

    /** Taxa que uma pessoa paga na ocupação dada, entre as taxas do lote. */
    public function feeForPerson(Collection $batchFees, EventParticipantAllocation $allocation, Event $event, int $occupancy): ?EventFee
    {
        $age = $allocation->person->age_at_date($event->start_date);
        $category = $age <= $event->children_age ? 'Infantil' : 'Integral';

        return $this->pick($batchFees, $category, $occupancy);
    }

    /**
     * Total da reserva: soma da taxa de cada pessoa na ocupação da reserva.
     *
     * @param  Collection<int, EventFee>  $batchFees  taxas do tipo de quarto, já no lote escolhido
     */
    public function reservationTotal(EventParticipantAllocation $payer, Collection $batchFees, Event $event): float
    {
        $members = $this->reservation($payer);
        $occupancy = $members->count();

        return (float) $members->sum(fn ($member) => $this->feeForPerson($batchFees, $member, $event, $occupancy)?->fee ?? 0);
    }

    private function covers(object $fee, int $occupancy): bool
    {
        return ($fee->min_occupants === null || $fee->min_occupants <= $occupancy)
            && ($fee->max_occupants === null || $occupancy <= $fee->max_occupants);
    }

    /** Faixas específicas antes das sem limite; entre elas, a mais estreita. */
    private function width(object $fee): int
    {
        if ($fee->min_occupants === null && $fee->max_occupants === null) {
            return PHP_INT_MAX;
        }

        return ($fee->max_occupants ?? 99) - ($fee->min_occupants ?? 1);
    }
}
