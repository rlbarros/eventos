<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Igreja da administração (réplica do data-sync); `id` é o `igrejas.id` de lá. */
class AdministrationChurch extends Model
{
    protected $table = 'administration_churches';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'superintendence_id',
        'superintendence_name',
        'active',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** "SUPERINTENDÊNCIA · Igreja", para as listas de escolha. */
    public function label(): string
    {
        return $this->superintendence_name ? "{$this->superintendence_name} · {$this->name}" : $this->name;
    }
}
