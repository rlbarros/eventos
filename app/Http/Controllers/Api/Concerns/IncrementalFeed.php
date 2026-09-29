<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\SyncDeletion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Delta incremental das rotas que o data-sync lê daqui (`?desde=`): criados/alterados a partir
 * do instante, excluídos vindos de `sync_deletions` com `active = false`, tudo em ordem de
 * `changed_at`. Usado pelo caminho eventos → administração e eventos → superapp.
 */
trait IncrementalFeed
{
    /** `desde` (ISO-8601, qualquer fuso) no fuso em que as datas deste banco são gravadas. */
    protected function desde(Request $request): ?string
    {
        $desde = $request->query('desde');
        if (empty($desde)) {
            return null;
        }

        return Carbon::parse($desde)->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }

    /** Filtro incremental: criado_em/atualizado_em (created_at/updated_at) >= desde. */
    protected function aplicarDelta(Builder $query, ?string $desde): void
    {
        if (empty($desde)) {
            return;
        }

        $query->where(function (Builder $q) use ($desde) {
            $q->where('created_at', '>=', $desde)
                ->orWhere('updated_at', '>=', $desde);
        });
    }

    protected function exclusoes(string $model, ?string $desde): Collection
    {
        return SyncDeletion::where('model', $model)
            ->when($desde, fn ($q) => $q->where('deleted_at', '>=', $desde))
            ->get();
    }

    protected function alteradoEm($registro): ?string
    {
        $data = $registro->updated_at ?? $registro->created_at;

        return $data?->toIso8601String();
    }

    protected function ordenar(Collection $registros): Collection
    {
        return $registros->sortBy(fn ($r) => [$r['changed_at'] ?? '', $r['id']])->values();
    }

    protected function ultimaAtualizacao(string $tabela, string $modeloExclusao): string
    {
        $valor = DB::table($tabela)
            ->selectRaw("GREATEST(
                COALESCE(MAX(created_at), '1970-01-01 00:00:00'),
                COALESCE(MAX(updated_at), '1970-01-01 00:00:00')
            ) as ultima_atualizacao")
            ->value('ultima_atualizacao');

        $exclusao = SyncDeletion::where('model', $modeloExclusao)->max('deleted_at');
        $maior = max((string) $valor, (string) $exclusao) ?: '1970-01-01 00:00:00';

        return Carbon::parse($maior)->toIso8601String();
    }
}
