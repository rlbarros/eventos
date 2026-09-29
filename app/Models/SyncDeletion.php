<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Evento ou participação excluídos aqui, a caminho da administração (ver a migration
 * 2026_09_29_120000). Gravado pelos próprios modelos, no evento `deleted`.
 */
class SyncDeletion extends Model
{
    public const EVENTS = 'events';
    public const PARTICIPANTS = 'participants';

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
