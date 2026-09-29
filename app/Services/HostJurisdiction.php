<?php

namespace App\Services;

use App\Models\AdministrationChurch;
use App\Models\AdministrationHost;
use App\Models\Church;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Jurisdição do anfitrião (perfis AnfitriaoNacional/Superintendencia/Igreja da administração):
 * - nacional: eventos de qualquer abrangência, em qualquer igreja;
 * - superintendência: eventos de abrangência superintendência ou igreja, nas igrejas da
 *   superintendência (pelo catálogo `administration_churches`);
 * - igreja: eventos de abrangência igreja, na própria igreja.
 *
 * A igreja do evento é ligada à da administração por `churches.administration_system_id`:
 * igreja sem esse vínculo fica fora de qualquer jurisdição que não a nacional.
 *
 * Contas antigas (sem `administration_user_id`) não são de anfitrião: continuam como antes,
 * vendo os eventos de que são donas ou para os quais foram liberadas, sem restrição de
 * abrangência, para ninguém ficar trancado para fora.
 */
class HostJurisdiction
{
    public const SCOPES = ['nacional', 'superintendencia', 'igreja'];

    public const OUT_OF_JURISDICTION = 'Esta igreja e abrangência estão fora da sua jurisdição de anfitrião.';

    private ?Collection $regionalChurchIds = null;

    /** @param Collection<int, AdministrationHost>|null $grants null = conta antiga, sem vínculo */
    private function __construct(private ?Collection $grants)
    {
    }

    public static function for(?User $user): self
    {
        if (! $user || ! $user->isAdministrationHost()) {
            return new self(null);
        }

        return new self(AdministrationHost::active()
            ->where('administration_user_id', $user->administration_user_id)
            ->get());
    }

    /** Conta antiga, sem vínculo com a administração: nenhuma regra de anfitrião se aplica. */
    public function isLegacy(): bool
    {
        return $this->grants === null;
    }

    /** Conta de anfitrião com ao menos uma jurisdição ativa (ou conta antiga). */
    public function hasAccess(): bool
    {
        return $this->isLegacy() || $this->grants->isNotEmpty();
    }

    public function isNational(): bool
    {
        return ! $this->isLegacy() && $this->grants->contains('level', 'nacional');
    }

    /** Abrangências que o usuário pode dar a um evento. */
    public function allowedScopes(): array
    {
        if ($this->isLegacy() || $this->isNational()) {
            return self::SCOPES;
        }
        if ($this->grants->contains('level', 'superintendencia')) {
            return ['superintendencia', 'igreja'];
        }

        return $this->grants->isNotEmpty() ? ['igreja'] : [];
    }

    /** Pode criar/editar um evento com essa abrangência nessa igreja (id local de `churches`)? */
    public function canManage(?string $scope, ?int $churchId): bool
    {
        if ($this->isLegacy() || $this->isNational()) {
            return true;
        }

        $administrationChurchId = $churchId ? Church::whereKey($churchId)->value('administration_system_id') : null;
        if ($administrationChurchId === null) {
            return false;
        }

        if (in_array($scope, ['superintendencia', 'igreja'], true)
            && $this->regionalChurchIds()->contains((int) $administrationChurchId)) {
            return true;
        }

        return $scope === 'igreja' && $this->localChurchIds()->contains((int) $administrationChurchId);
    }

    /**
     * Igrejas da administração em que o anfitrião pode criar eventos; null = todas (nacional ou
     * conta antiga).
     */
    public function manageableAdministrationChurchIds(): ?Collection
    {
        if ($this->isLegacy() || $this->isNational()) {
            return null;
        }

        return $this->regionalChurchIds()->merge($this->localChurchIds())->unique()->values();
    }

    /**
     * Restringe uma query de eventos aos que a jurisdição alcança. Usado dentro de um orWhere
     * (o dono e os liberados continuam vendo o evento por fora daqui).
     */
    public function constrainEvents(Builder $query): Builder
    {
        if ($this->isNational()) {
            return $query->whereRaw('1 = 1');
        }
        if ($this->isLegacy() || $this->grants->isEmpty()) {
            return $query->whereRaw('0 = 1');
        }

        $regional = $this->regionalChurchIds()->all();
        $local = $this->localChurchIds()->all();

        return $query->where(function (Builder $query) use ($regional, $local) {
            $query->where(fn (Builder $q) => $q
                ->whereIn('scope', ['superintendencia', 'igreja'])
                ->whereHas('church', fn (Builder $c) => $c->whereIn('administration_system_id', $regional ?: [0])))
                ->orWhere(fn (Builder $q) => $q
                    ->where('scope', 'igreja')
                    ->whereHas('church', fn (Builder $c) => $c->whereIn('administration_system_id', $local ?: [0])));
        });
    }

    /** Igrejas das superintendências do anfitrião de superintendência. */
    private function regionalChurchIds(): Collection
    {
        if ($this->regionalChurchIds === null) {
            $superintendences = $this->grants
                ->where('level', 'superintendencia')
                ->pluck('administration_superintendence_id')->filter()->unique();

            $this->regionalChurchIds = $superintendences->isEmpty()
                ? collect()
                : AdministrationChurch::whereIn('superintendence_id', $superintendences)
                    ->pluck('id')->map(fn ($id) => (int) $id);
        }

        return $this->regionalChurchIds;
    }

    private function localChurchIds(): Collection
    {
        return $this->grants
            ->where('level', 'igreja')
            ->pluck('administration_church_id')->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }
}
