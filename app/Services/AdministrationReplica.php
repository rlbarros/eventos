<?php

namespace App\Services;

use App\Models\AdministrationChurch;
use App\Models\AdministrationHost;
use App\Models\User;

/**
 * Grava o que o data-sync traz da administração (ADR-008): anfitriões e catálogo de igrejas.
 * Upsert pelo id de lá; exclusões e revogações chegam como `ativo = false`.
 */
class AdministrationReplica
{
    /** @param array{id:string, usuario_id:int, nome:string, email:string, nivel:string, superintendencia_id:?int, igreja_id:?int, ativo:bool, sincronizado_em:?string} $data */
    public function saveHost(array $data): AdministrationHost
    {
        $host = AdministrationHost::updateOrCreate(['grant_key' => $data['id']], [
            'administration_user_id' => $data['usuario_id'],
            'name' => $data['nome'],
            'email' => mb_strtolower(trim($data['email'])),
            'level' => $data['nivel'],
            'administration_superintendence_id' => $data['superintendencia_id'] ?? null,
            'administration_church_id' => $data['igreja_id'] ?? null,
            'active' => (bool) $data['ativo'],
            'synced_at' => $data['sincronizado_em'] ?? null,
        ]);

        if ($host->active) {
            $this->syncAccount($host);
        }

        return $host;
    }

    /** @param array{id:int, nome:string, superintendencia_id:?int, superintendencia:?string, ativo:bool, sincronizado_em:?string} $data */
    public function saveChurch(array $data): AdministrationChurch
    {
        return AdministrationChurch::updateOrCreate(['id' => $data['id']], [
            'name' => $data['nome'],
            'superintendence_id' => $data['superintendencia_id'] ?? null,
            'superintendence_name' => $data['superintendencia'] ?? null,
            'active' => (bool) $data['ativo'],
            'synced_at' => $data['sincronizado_em'] ?? null,
        ]);
    }

    /**
     * Mantém a conta do anfitrião presa ao usuário da administração: a conta antiga com o mesmo
     * e-mail passa a ser dele, e o e-mail trocado lá troca o login aqui (se nenhuma outra conta
     * já usa o e-mail novo).
     */
    private function syncAccount(AdministrationHost $host): void
    {
        $user = User::where('administration_user_id', $host->administration_user_id)->first()
            ?? User::whereNull('administration_user_id')->whereRaw('LOWER(TRIM(email)) = ?', [$host->email])->first();

        if (! $user) {
            return;
        }

        $user->administration_user_id = $host->administration_user_id;

        if (mb_strtolower(trim($user->email)) !== $host->email
            && ! User::whereKeyNot($user->id)->whereRaw('LOWER(TRIM(email)) = ?', [$host->email])->exists()) {
            $user->email = $host->email;
        }

        $user->save();
    }

    /** Último carimbo recebido de cada réplica: é o `desde` do próximo delta. */
    public function lastSyncedAt(string $table): ?string
    {
        return match ($table) {
            'administration_hosts' => AdministrationHost::max('synced_at'),
            'administration_churches' => AdministrationChurch::max('synced_at'),
        };
    }
}
