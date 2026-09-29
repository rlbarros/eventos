<?php

namespace App\Services\Sync;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Até onde este sistema já recebeu de cada origem, no relógio da origem. A marca só anda para
 * frente; o data-sync a lê no GET /sync e manda como `desde` na próxima rodada.
 */
class SyncWatermarks
{
    private const TABLE = 'sync_watermarks';

    public function record(string $source, string $model, CarbonInterface $changedAt): void
    {
        $value = $changedAt->copy()->utc()->format('Y-m-d H:i:s');
        $current = DB::table(self::TABLE)->where('source', $source)->where('model', $model)->value('last_changed_at');

        if ($current === null) {
            DB::table(self::TABLE)->insertOrIgnore([
                'source' => $source,
                'model' => $model,
                'last_changed_at' => $value,
            ]);
            return;
        }

        if ($value > $current) {
            DB::table(self::TABLE)->where('source', $source)->where('model', $model)
                ->update(['last_changed_at' => $value]);
        }
    }

    public function last(string $source, string $model): ?string
    {
        $value = DB::table(self::TABLE)->where('source', $source)->where('model', $model)->value('last_changed_at');

        return $value === null ? null : Carbon::parse($value, 'UTC')->toIso8601String();
    }
}
