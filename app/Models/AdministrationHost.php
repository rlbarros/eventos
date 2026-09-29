<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Anfitrião cadastrado na administração (réplica do data-sync). Um registro por usuário e
 * jurisdição: nacional, superintendência ou igreja.
 */
class AdministrationHost extends Model
{
    protected $table = 'administration_hosts';

    protected $fillable = [
        'grant_key',
        'administration_user_id',
        'name',
        'email',
        'level',
        'administration_superintendence_id',
        'administration_church_id',
        'active',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** Sempre minúsculo e sem espaços: é por ele que o cadastro acha o anfitrião. */
    protected function email(): Attribute
    {
        return Attribute::set(fn (?string $value) => mb_strtolower(trim((string) $value)));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /** Registro ativo com esse e-mail (comparação sem caixa e sem espaços). */
    public static function activeForEmail(?string $email): ?self
    {
        $email = mb_strtolower(trim((string) $email));
        if ($email === '') {
            return null;
        }

        return static::active()->where('email', $email)->orderBy('id')->first();
    }
}
