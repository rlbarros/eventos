<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Exposição da fonte (source) para o worker data-sync sincronizar
 * eventos e participantes com a administração (administracao-api / dest).
 *
 * - GET /sync                deltas (última atualização por modelo)
 * - GET /events-sync?desde=  eventos (filtro incremental)
 * - GET /participants-sync?desde=  participantes (filtro incremental)
 *
 * O parâmetro `desde` (ISO-8601) traz apenas o delta: registros criados ou
 * atualizados a partir daquele instante. Sem `desde`, retorna a carga completa.
 */
class SyncController extends Controller
{
    /** Últimas atualizações por modelo (chaveadas pelo nome usado no data-sync). */
    public function deltas()
    {
        return response()->json([
            'data' => [
                'events'       => $this->ultimaAtualizacao('events'),
                'participants' => $this->ultimaAtualizacao('events_participants_allocations'),
            ],
        ]);
    }

    /** Eventos para a administração (mapeados no data-sync para a tabela `eventos`). */
    public function events(Request $request)
    {
        $query = Event::query()->with('church:id,administration_system_id');
        $this->aplicarDelta($query, $request->query('desde'));

        $eventos = $query->orderBy('id')->get()->map(fn (Event $e) => [
            'id'                       => $e->id,
            'name'                     => $e->name,
            'scope'                    => $e->scope,
            'start_date'               => $e->start_date,
            'end_date'                 => $e->end_date,
            // id da igreja na administração (igrejas.id), para o escopo por igreja
            'administration_church_id' => $e->church?->administration_system_id,
            'children_age'             => $e->children_age,
        ]);

        return response()->json(['data' => $eventos]);
    }

    /** Participações para a administração (mapeadas para `eventos_pessoas`). */
    public function participants(Request $request)
    {
        $query = EventParticipantAllocation::query()->with('person:id,cpf');
        $this->aplicarDelta($query, $request->query('desde'));

        $participantes = $query->orderBy('id')->get()->map(fn (EventParticipantAllocation $a) => [
            'id'                       => $a->id,
            'event_id'                 => $a->event_id,
            // pessoa é resolvida na administração pelo CPF (persons não guarda o id de lá)
            'administration_person_id' => null,
            'cpf'                      => $a->person?->cpf,
            'present'                  => true,
        ]);

        return response()->json(['data' => $participantes]);
    }

    /** Filtro incremental: criado_em/atualizado_em (created_at/updated_at) >= desde. */
    private function aplicarDelta(Builder $query, ?string $desde): void
    {
        if (empty($desde)) {
            return;
        }

        $query->where(function (Builder $q) use ($desde) {
            $q->where('created_at', '>=', $desde)
                ->orWhere('updated_at', '>=', $desde);
        });
    }

    private function ultimaAtualizacao(string $tabela): string
    {
        $valor = DB::table($tabela)
            ->selectRaw("GREATEST(
                COALESCE(MAX(created_at), '1970-01-01 00:00:00'),
                COALESCE(MAX(updated_at), '1970-01-01 00:00:00')
            ) as ultima_atualizacao")
            ->value('ultima_atualizacao');

        return Carbon::parse($valor ?? '1970-01-01 00:00:00')->toIso8601String();
    }
}
