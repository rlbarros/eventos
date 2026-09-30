<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\IncrementalFeed;
use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Event;
use App\Models\EventBatch;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\EventParticipantPayment;
use App\Models\EventSiteRoomType;
use App\Models\Person;
use App\Models\SyncDeletion;
use App\Services\Sync\SyncWatermarks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Eventos ⇄ superapp pelo data-sync (ADR-008: o superapp nunca lê este sistema direto).
 *
 * Fonte (eventos → superapp), `?desde=` incremental como o /events-sync:
 * - GET /superapp/eventos       evento com local, contato e idade infantil
 * - GET /superapp/lotes         lotes (vigência)
 * - GET /superapp/precos        taxa por lote × tipo de hospedagem × categoria × faixa de ocupação
 * - GET /superapp/inscricoes    participações com o tipo de hospedagem
 * - GET /superapp/pagamentos    pagamentos por pessoa e evento
 *
 * A pessoa vai só como `cpf_hmac` (HMAC-SHA256 dos dígitos com SUPERAPP_CPF_HMAC_CHAVE, a mesma
 * chave da administração e do superapp): o superapp compara com o cadastro vinculado do usuário
 * e nunca recebe o CPF daqui.
 *
 * Destino (superapp → eventos):
 * - POST /superapp/inscricoes   inscrição feita pelo usuário no app (uma por chamada)
 */
class SuperappSyncController extends Controller
{
    use IncrementalFeed;

    public function events(Request $request)
    {
        $desde = $this->desde($request);

        $query = Event::query()->with(['church:id,administration_system_id', 'event_site.city:id,name', 'event_site.state:id,code']);
        $this->aplicarDelta($query, $desde);

        $eventos = $query->get()->map(fn (Event $e) => [
            'id'                       => $e->id,
            'name'                     => $e->name,
            'scope'                    => $e->scope,
            'start_date'               => $this->data($e->start_date),
            'end_date'                 => $this->data($e->end_date),
            'administration_church_id' => $e->church?->administration_system_id,
            'children_age'             => $e->children_age,
            'contact_name'             => $e->contact_name,
            'contact_phone'            => $e->contact_phone,
            'pix_key'                  => $e->pix_key,
            'pix_beneficiary'          => $e->pix_beneficiary,
            'site_name'                => $e->event_site?->name,
            'site_city'                => $e->event_site?->city?->name,
            'site_state'               => $e->event_site?->state?->code,
            'active'                   => true,
            'changed_at'               => $this->alteradoEm($e),
        ]);

        return response()->json(['data' => $this->ordenar($eventos->concat($this->tombstones(SyncDeletion::EVENTS, $desde)))]);
    }

    public function batches(Request $request)
    {
        $desde = $this->desde($request);

        $query = EventBatch::query();
        $this->aplicarDelta($query, $desde);

        $lotes = $query->get()->map(fn (EventBatch $b) => [
            'id'         => $b->id,
            'event_id'   => $b->event_id,
            'batch'      => $b->batch,
            'start_date' => $this->data($b->start_date),
            'end_date'   => $this->data($b->end_date),
            'active'     => true,
            'changed_at' => $this->alteradoEm($b),
        ]);

        return response()->json(['data' => $this->ordenar($lotes->concat($this->tombstones(SyncDeletion::BATCHES, $desde)))]);
    }

    public function fees(Request $request)
    {
        $desde = $this->desde($request);

        $query = EventFee::query()->with(['event_site_room_type:id,name,type,amenities', 'event_batch:id,batch']);
        $this->aplicarDelta($query, $desde);

        $precos = $query->get()->map(fn (EventFee $f) => [
            'id'             => $f->id,
            'event_id'       => $f->event_id,
            'batch_id'       => $f->event_batch_id,
            'batch'          => $f->event_batch?->batch,
            'room_type_id'   => $f->event_site_room_type_id,
            'room_type_name' => $f->event_site_room_type?->name,
            'room_type_kind' => $f->event_site_room_type?->type,
            'room_type_amenities' => $f->event_site_room_type?->amenities,
            'category'       => $f->category,
            'min_occupants'  => $f->min_occupants,
            'max_occupants'  => $f->max_occupants,
            'fee'            => $f->fee === null ? null : (float) $f->fee,
            'active'         => true,
            'changed_at'     => $this->alteradoEm($f),
        ]);

        return response()->json(['data' => $this->ordenar($precos->concat($this->tombstones(SyncDeletion::FEES, $desde)))]);
    }

    public function registrations(Request $request)
    {
        $desde = $this->desde($request);
        $chave = $this->chaveHmac();

        $query = EventParticipantAllocation::query()->with(['person:id,cpf', 'event_site_room_type:id,name,type']);
        $this->aplicarDelta($query, $desde);

        $inscricoes = $query->get()->map(fn (EventParticipantAllocation $a) => [
            'id'             => $a->id,
            'event_id'       => $a->event_id,
            'cpf_hmac'       => $this->hmac($a->person?->cpf, $chave),
            'room_type_id'   => $a->event_site_room_type_id,
            'room_type_name' => $a->event_site_room_type?->name,
            'room_type_kind' => $a->event_site_room_type?->type,
            'active'         => true,
            'changed_at'     => $this->alteradoEm($a),
        ]);

        return response()->json(['data' => $this->ordenar($inscricoes->concat($this->tombstones(SyncDeletion::PARTICIPANTS, $desde)))]);
    }

    public function payments(Request $request)
    {
        $desde = $this->desde($request);
        $chave = $this->chaveHmac();

        $query = EventParticipantPayment::query()->with('person:id,cpf');
        $this->aplicarDelta($query, $desde);

        $pagamentos = $query->get()->map(fn (EventParticipantPayment $p) => [
            'id'           => $p->id,
            'event_id'     => $p->event_id,
            'cpf_hmac'     => $this->hmac($p->person?->cpf, $chave),
            'fee_id'       => $p->event_fee_id,
            'amount'       => $p->amount === null ? null : (float) $p->amount,
            'payment_date' => $this->data($p->payment_date),
            'active'       => true,
            'changed_at'   => $this->alteradoEm($p),
        ]);

        return response()->json(['data' => $this->ordenar($pagamentos->concat($this->tombstones(SyncDeletion::PAYMENTS, $desde)))]);
    }

    /**
     * Inscrição feita pelo usuário no superapp. A pessoa é achada pelo CPF ou criada com o que o
     * app manda; a participação é criada com o tipo de hospedagem escolhido. Já inscrito: só
     * completa a hospedagem se ainda não tinha. Tudo que não dá para gravar é ignorado com 200
     * (o data-sync não reenvia); o app mostra "aguardando confirmação" até a inscrição voltar
     * pelo GET /superapp/inscricoes.
     */
    public function receiveRegistration(Request $request, SyncWatermarks $watermarks)
    {
        $dados = $request->validate([
            'id'              => ['required', 'integer'],
            'event_id'        => ['required', 'integer'],
            'cpf'             => ['nullable', 'string'],
            'nome'            => ['nullable', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
            'telefone'        => ['nullable', 'string'],
            'email'           => ['nullable', 'string'],
            'igreja_id'       => ['nullable', 'integer'],
            'hospedagem_id'   => ['nullable', 'integer'],
            'alterado_em'     => ['nullable', 'date'],
        ]);

        $alteradoEm = ! empty($dados['alterado_em'])
            ? Carbon::parse($dados['alterado_em'])->setTimezone(config('app.timezone'))
            : now();

        $resposta = function (string $mensagem, $registro = null) use ($watermarks, $alteradoEm) {
            $watermarks->record('superapp', 'inscricoes', $alteradoEm);

            return response()->json(['data' => $registro, 'message' => $mensagem]);
        };

        $event = Event::find($dados['event_id']);
        if (! $event) {
            return $resposta('inscrição ignorada: o evento não existe aqui.');
        }
        $cpf = $this->cpfFormatado($dados['cpf'] ?? null);
        if (! $cpf) {
            return $resposta('inscrição ignorada: CPF inválido.');
        }

        $roomTypeId = null;
        if (! empty($dados['hospedagem_id'])) {
            $roomTypeId = EventSiteRoomType::whereKey($dados['hospedagem_id'])
                ->where('event_site_id', $event->event_site_id)
                ->value('id');
        }

        $person = Person::whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)])->orderBy('id')->first();

        if ($person) {
            $allocation = EventParticipantAllocation::where('event_id', $event->id)
                ->where('person_id', $person->id)
                ->orderBy('id')
                ->first();
            if ($allocation) {
                if ($allocation->event_site_room_type_id === null && $roomTypeId !== null) {
                    $allocation->update(['event_site_room_type_id' => $roomTypeId]);
                }

                return $resposta('já inscrito.', $allocation);
            }
        }

        // removida aqui depois da inscrição no app: não recria
        $removidaDepois = SyncDeletion::where('model', SyncDeletion::PARTICIPANTS)
            ->where('event_id', $event->id)
            ->whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)])
            ->where('deleted_at', '>', $alteradoEm)
            ->exists();
        if ($removidaDepois) {
            return $resposta('inscrição ignorada: a participação foi removida depois aqui.');
        }

        if ($person) {
            // completa só o que falta no cadastro daqui; o que a organização digitou não muda
            $person->fill(array_filter([
                'birth_date' => $person->birth_date ? null : ($dados['data_nascimento'] ?? null),
                'phone'      => $person->phone ? null : (isset($dados['telefone']) ? mb_substr($dados['telefone'], 0, 20) : null),
                'email'      => $person->email ? null : ($dados['email'] ?? null),
            ]))->save();
        } else {
            $person = Person::create([
                'cpf'        => $cpf,
                'name'       => $dados['nome'] ?: $cpf,
                'birth_date' => $dados['data_nascimento'] ?? null,
                'phone'      => isset($dados['telefone']) ? mb_substr($dados['telefone'], 0, 20) : null,
                'email'      => $dados['email'] ?? null,
                'church_id'  => empty($dados['igreja_id'])
                    ? null
                    : Church::where('administration_system_id', $dados['igreja_id'])->value('id'),
            ]);
        }

        $allocation = EventParticipantAllocation::create([
            'event_id'                => $event->id,
            'person_id'               => $person->id,
            'event_site_room_type_id' => $roomTypeId,
        ]);

        return $resposta('inscrição criada.', $allocation);
    }

    /** Excluídos: só o id, `event_id` e `active = false` (o superapp inativa a linha). */
    private function tombstones(string $model, ?string $desde): Collection
    {
        return $this->exclusoes($model, $desde)->map(fn (SyncDeletion $d) => [
            'id'         => $d->record_id,
            'event_id'   => $d->event_id,
            'active'     => false,
            'changed_at' => $d->deleted_at->toIso8601String(),
        ]);
    }

    private function data($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return $valor instanceof \DateTimeInterface ? $valor->format('Y-m-d') : substr((string) $valor, 0, 10);
    }

    private function chaveHmac(): string
    {
        $chave = (string) config('services.superapp.cpf_hmac_chave');
        if ($chave === '') {
            throw new RuntimeException('SUPERAPP_CPF_HMAC_CHAVE não configurada');
        }

        return $chave;
    }

    private function hmac(?string $cpf, string $chave): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $cpf);

        return $digitos === '' ? null : hash_hmac('sha256', $digitos, $chave);
    }

    /** CPF no formato gravado pelo cadastro daqui (000.000.000-00), ou null se inválido. */
    private function cpfFormatado(?string $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);
        if (strlen($digitos) !== 11) {
            return null;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos);
    }
}
