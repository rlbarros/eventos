<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use App\Models\SyncDeletion;
use App\Services\Sync\SyncWatermarks;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Destino (dest) do caminho administração → eventos no data-sync: participações incluídas ou
 * removidas à mão na administração (aba Eventos do ministro).
 *
 * - POST /participants-admin-sync   uma participação por chamada; `active = false` remove.
 *
 * A pessoa é achada pelo CPF; se não existir aqui, é criada com o que a administração manda
 * (nome, nascimento, telefone, e-mail, igreja). Conflito na mesma participação: vence a
 * alteração mais recente (`alterado_em` de lá contra `updated_at`/exclusão daqui).
 *
 * Participação criada aqui por esta rota volta à administração pelo /participants-sync, que
 * assim fica sabendo o id daqui e para de mandá-la.
 */
class AdminSyncController extends Controller
{
    public function participants(Request $request, SyncWatermarks $watermarks)
    {
        $dados = $request->validate([
            'id'                     => ['required', 'integer'],
            'participants_system_id' => ['nullable', 'integer'],
            'event_id'               => ['required', 'integer'],
            'cpf'                    => ['nullable', 'string'],
            'nome'                   => ['nullable', 'string', 'max:255'],
            'data_nascimento'        => ['nullable', 'date'],
            'telefone'               => ['nullable', 'string'],
            'email'                  => ['nullable', 'string'],
            'igreja_id'              => ['nullable', 'integer'],
            'ativo'                  => ['nullable', 'boolean'],
            'alterado_em'            => ['nullable', 'date'],
        ]);

        $alteradoEm = ! empty($dados['alterado_em'])
            ? Carbon::parse($dados['alterado_em'])->setTimezone(config('app.timezone'))
            : now();
        $ativo = $dados['ativo'] ?? true;
        $cpf = $this->cpf($dados['cpf'] ?? null);
        $person = $cpf ? $this->pessoaPorCpf($cpf) : null;

        $allocation = null;
        if (! empty($dados['participants_system_id'])) {
            $allocation = EventParticipantAllocation::find($dados['participants_system_id']);
        }
        if (! $allocation && $person) {
            $allocation = EventParticipantAllocation::where('event_id', $dados['event_id'])
                ->where('person_id', $person->id)
                ->orderBy('id')
                ->first();
        }

        $resposta = function (string $mensagem, $registro = null) use ($watermarks, $alteradoEm) {
            $watermarks->record('administracao', 'participants', $alteradoEm);

            return response()->json(['data' => $registro, 'message' => $mensagem]);
        };

        if (! $ativo) {
            if (! $allocation) {
                return $resposta('participação já não existia.');
            }
            if ($allocation->updated_at !== null && $allocation->updated_at->gt($alteradoEm)) {
                return $resposta('exclusão ignorada: a participação foi alterada depois aqui.', $allocation);
            }
            $allocation->delete();

            return $resposta('participação removida.', $allocation);
        }

        if ($allocation) {
            // a administração ainda não conhece o id daqui: toca a linha para ela voltar pelo
            // /participants-sync com o id, e lá a participação sai do que é enviado para cá
            if ((int) ($dados['participants_system_id'] ?? 0) !== $allocation->id) {
                $allocation->touch();
            }

            return $resposta('participação já existe.', $allocation);
        }

        if (! Event::whereKey($dados['event_id'])->exists()) {
            return $resposta('participação ignorada: o evento não existe mais aqui.');
        }
        if (! $cpf) {
            return $resposta('participação ignorada: pessoa sem CPF.');
        }

        // removida aqui depois dessa alteração da administração: não recria (a exclusão vai para lá)
        $removidaDepois = SyncDeletion::where('model', SyncDeletion::PARTICIPANTS)
            ->where('deleted_at', '>', $alteradoEm)
            ->where(function ($q) use ($dados, $cpf) {
                $q->where(fn ($q) => $q->where('event_id', $dados['event_id'])
                    ->whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)]));
                if (! empty($dados['participants_system_id'])) {
                    $q->orWhere('record_id', $dados['participants_system_id']);
                }
            })
            ->exists();
        if ($removidaDepois) {
            return $resposta('participação ignorada: foi removida depois aqui.');
        }

        $person ??= Person::create([
            'cpf'        => $cpf,
            'name'       => $dados['nome'] ?: $cpf,
            'birth_date' => $dados['data_nascimento'] ?? null,
            'phone'      => isset($dados['telefone']) ? mb_substr($dados['telefone'], 0, 20) : null,
            'email'      => $dados['email'] ?? null,
            'church_id'  => empty($dados['igreja_id'])
                ? null
                : Church::where('administration_system_id', $dados['igreja_id'])->value('id'),
        ]);

        $allocation = EventParticipantAllocation::create([
            'event_id'  => $dados['event_id'],
            'person_id' => $person->id,
        ]);

        return $resposta('participação criada.', $allocation);
    }

    /** CPF no formato gravado pelo cadastro daqui (000.000.000-00), ou null se inválido. */
    private function cpf(?string $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);
        if (strlen($digitos) !== 11) {
            return null;
        }

        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digitos);
    }

    private function pessoaPorCpf(string $cpf): ?Person
    {
        return Person::whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)])->orderBy('id')->first();
    }
}
