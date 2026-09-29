<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Evento, participação, lote, preço ou pagamento excluídos aqui, a caminho da administração ou do superapp (ver a migration
 * 2026_09_29_120000). Gravado pelos próprios modelos, no evento `deleted`.
 */
class SyncDeletion extends Model
{
    public const EVENTS = 'events';
    public const PARTICIPANTS = 'participants';
    // só para o superapp (lotes, preços e pagamentos não vão à administração)
    public const BATCHES = 'batches';
    public const FEES = 'fees';
    public const PAYMENTS = 'payments';

    protected $table = 'sync_deletions';

    public $timestamps = false;

    protected $fillable = [
        'model',
        'record_id',
        'event_id',
        'cpf',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
