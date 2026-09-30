<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\IncrementalFeed;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\SyncDeletion;
use App\Services\AdministrationPeople;
use App\Services\AdministrationReplica;
use App\Services\Sync\SyncWatermarks;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Exposição da fonte (source) para o worker data-sync sincronizar
 * eventos e participantes com a administração (administracao-api / dest).
 *
 * - GET /sync                deltas (última atualização por modelo)
 * - GET /events-sync?desde=  eventos (filtro incremental)
 * - GET /participants-sync?desde=  participantes (filtro incremental)
 *
 * O parâmetro `desde` (ISO-8601) traz apenas o delta: registros criados,
 * atualizados ou excluídos a partir daquele instante. Sem `desde`, retorna a
 * carga completa. Excluídos vêm de `sync_deletions` com `active = false`, e
 * tudo sai ordenado por `changed_at` (o destino guarda até onde recebeu).
 *
 * Este sistema é a fonte da verdade dos eventos. Participações vão e voltam:
 * o caminho administração → eventos é o AdminSyncController.
 *
 * No sentido contrário (administração → eventos, esta API é o destino):
 * - POST /anfitrioes-sync  anfitriões (quem pode ter conta e em que jurisdição)
 * - POST /igrejas-sync     catálogo de igrejas da administração
 * - POST /pessoas-sync    pessoas da administração (achadas pelo CPF; ver AdministrationPeople)
 * O `desde` desses sai de /sync (`anfitrioes`, `igrejas`, `pessoas`): o último carimbo recebido.
 */
class SyncController extends Controller
{
    use IncrementalFeed;

    public function __construct(private AdministrationReplica $replica, private AdministrationPeople $people)
    {
    }

    /** Últimas atualizações por modelo (chaveadas pelo nome usado no data-sync). */
    public function deltas(SyncWatermarks $watermarks)
    {
        return response()->json([
            'data' => [
                'events'       => $this->ultimaAtualizacao('events', SyncDeletion::EVENTS),
                'participants' => $this->ultimaAtualizacao('events_participants_allocations', SyncDeletion::PARTICIPANTS),
                // administração → eventos: até onde já recebemos de lá (relógio da administração)
                'participants_admin' => $watermarks->last('administracao', 'participants')
                    ?? Carbon::parse('1970-01-01 00:00:00', 'UTC')->toIso8601String(),
                // destino do sync administração → eventos: carimbo da administração, como veio
                'anfitrioes'   => $this->replica->lastSyncedAt('administration_hosts'),
                'igrejas'      => $this->replica->lastSyncedAt('administration_churches'),
                'pessoas'      => $this->people->lastSyncedAt(),
                // eventos → superapp (rotas /superapp/*)
                'superapp_eventos'    => $this->ultimaAtualizacao('events', SyncDeletion::EVENTS),
                'superapp_lotes'      => $this->ultimaAtualizacao('events_batches', SyncDeletion::BATCHES),
                'superapp_precos'     => $this->ultimaAtualizacao('events_fees', SyncDeletion::FEES),
                'superapp_inscricoes' => $this->ultimaAtualizacao('events_participants_allocations', SyncDeletion::PARTICIPANTS),
                'superapp_pagamentos' => $this->ultimaAtualizacao('events_participants_payments', SyncDeletion::PAYMENTS),
                // superapp → eventos: até onde já recebemos de lá (relógio do superapp)
                'inscricoes_superapp' => $watermarks->last('superapp', 'inscricoes')
                    ?? Carbon::parse('1970-01-01 00:00:00', 'UTC')->toIso8601String(),
            ],
        ]);
    }

    /** Anfitrião vindo da administração (um registro por usuário e jurisdição). */
    public function receiveHost(Request $request)
    {
        $data = $request->validate([
            'id'                  => ['required', 'string', 'max:80'],
            'usuario_id'          => ['required', 'integer'],
            'nome'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255'],
            'nivel'               => ['required', 'in:nacional,superintendencia,igreja'],
            'superintendencia_id' => ['nullable', 'integer'],
            'igreja_id'           => ['nullable', 'integer'],
            'ativo'               => ['required', 'boolean'],
            'sincronizado_em'     => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->replica->saveHost($data)]);
    }

    /** Igreja do catálogo da administração. */
    public function receiveChurch(Request $request)
    {
        $data = $request->validate([
            'id'                  => ['required', 'integer'],
            'nome'                => ['required', 'string', 'max:255'],
            'superintendencia_id' => ['nullable', 'integer'],
            'superintendencia'    => ['nullable', 'string', 'max:255'],
            'ativo'               => ['required', 'boolean'],
            'sincronizado_em'     => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->replica->saveChurch($data)]);
    }

    /** Pessoa da administração (upsert pelo CPF; inativa é ignorada). */
    public function receivePerson(Request $request)
    {
        $data = $request->validate([
            'id'              => ['required', 'integer'],
            'cpf'             => ['required', 'string', 'regex:/^\D*(\d\D*){11}$/'],
            'nome'            => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'email'           => ['nullable', 'string', 'max:200'],
            'telefone'        => ['nullable', 'string'],
            'igreja_id'       => ['nullable', 'integer'],
            'funcao'          => ['nullable', 'string', 'max:60'],
            'ativo'           => ['required', 'boolean'],
            'sincronizado_em' => ['nullable', 'date'],
        ]);

        return response()->json(['data' => $this->people->save($data)]);
    }

    /** Eventos para a administração (mapeados no data-sync para a tabela `eventos`). */
    public function events(Request $request)
    {
        $desde = $this->desde($request);

        $query = Event::query()->with('church:id,administration_system_id');
        $this->aplicarDelta($query, $desde);

        $eventos = $query->get()->map(fn (Event $e) => [
            'id'                       => $e->id,
            'name'                     => $e->name,
            'scope'                    => $e->scope,
            'start_date'               => $e->start_date,
            'end_date'                 => $e->end_date,
            // id da igreja na administração (igrejas.id), para o escopo por igreja
            'administration_church_id' => $e->church?->administration_system_id,
            'children_age'             => $e->children_age,
            'active'                   => true,
            'changed_at'               => $this->alteradoEm($e),
        ]);

        $excluidos = $this->exclusoes(SyncDeletion::EVENTS, $desde)->map(fn (SyncDeletion $d) => [
            'id'                       => $d->record_id,
            'name'                     => null,
            'scope'                    => null,
            'start_date'               => null,
            'end_date'                 => null,
            'administration_church_id' => null,
            'children_age'             => null,
            'active'                   => false,
            'changed_at'               => $d->deleted_at->toIso8601String(),
        ]);

        return response()->json(['data' => $this->ordenar($eventos->concat($excluidos))]);
    }

    /** Participações para a administração (mapeadas para `eventos_pessoas`). */
    public function participants(Request $request)
    {
        $desde = $this->desde($request);

        $query = EventParticipantAllocation::query()->with('person:id,cpf');
        $this->aplicarDelta($query, $desde);

        // sem `present`: a presença é marcada na administração e não existe aqui
        $participantes = $query->get()->map(fn (EventParticipantAllocation $a) => [
            'id'                       => $a->id,
            'event_id'                 => $a->event_id,
            // pessoa é resolvida na administração pelo CPF (persons não guarda o id de lá)
            'administration_person_id' => null,
            'cpf'                      => $a->person?->cpf,
            'active'                   => true,
            'changed_at'               => $this->alteradoEm($a),
        ]);

        $excluidos = $this->exclusoes(SyncDeletion::PARTICIPANTS, $desde)->map(fn (SyncDeletion $d) => [
            'id'                       => $d->record_id,
            'event_id'                 => $d->event_id,
            'administration_person_id' => null,
            'cpf'                      => $d->cpf,
            'active'                   => false,
            'changed_at'               => $d->deleted_at->toIso8601String(),
        ]);

        return response()->json(['data' => $this->ordenar($participantes->concat($excluidos))]);
    }
}
