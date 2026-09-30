<?php

namespace App\Services;

use App\Models\AdministrationChurch;
use App\Models\Church;
use App\Models\State;
use App\Models\Person;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Grava as pessoas que o data-sync traz da administração (ADR-008). A pessoa é achada pelo id de
 * lá (quando já foi sincronizada) ou pelo CPF, e criada se não existir. Nome, nascimento, igreja
 * e função são da administração e vencem; telefone e e-mail só preenchem o que está vazio aqui.
 * Pessoa inativa na administração não é criada nem alterada.
 */
class AdministrationPeople
{
    /** Funções que o cadastro daqui aceita (enum de `persons.function`). */
    private const FUNCTIONS = ['Membro', 'Pastor', 'Convidado', 'Obreiro', 'Diácono', 'Pregador de Conferência', 'Presbítero', 'Evangelista', 'Bispo'];

    private const UFS = 'AC|AL|AP|AM|BA|CE|DF|ES|GO|MA|MT|MS|MG|PA|PB|PR|PE|PI|RJ|RN|RS|RO|RR|SC|SP|SE|TO';

    private const WATERMARK_SOURCE = 'administracao';

    private const WATERMARK_MODEL = 'pessoas';

    /**
     * @param array{id:int, cpf:string, nome:string, data_nascimento:?string, email:?string, telefone:?string, igreja_id:?int, funcao:?string, ativo:bool, sincronizado_em:?string} $data
     * @return array{action:string, person_id:?int}
     */
    public function save(array $data): array
    {
        try {
            return $this->saveOnce($data);
        } catch (UniqueConstraintViolationException) {
            // duas gravações da mesma pessoa ao mesmo tempo: a outra criou primeiro, então atualiza
            return $this->saveOnce($data);
        }
    }

    private function saveOnce(array $data): array
    {
        $result = DB::transaction(function () use ($data) {
            $cpf = $this->formatCpf($data['cpf']);
            $person = Person::where('administration_person_id', $data['id'])->orderBy('id')->first()
                ?? Person::whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)])->orderBy('id')->first();

            if (! $data['ativo']) {
                return ['action' => 'ignored', 'person_id' => $person?->id];
            }

            $attributes = array_filter([
                'administration_person_id' => $data['id'],
                'name' => $data['nome'],
                'birth_date' => $data['data_nascimento'] ?? null,
                'church_id' => empty($data['igreja_id']) ? null : $this->resolveChurch((int) $data['igreja_id']),
                'function' => in_array($data['funcao'] ?? null, self::FUNCTIONS, true) ? $data['funcao'] : null,
            ], fn ($v) => $v !== null && $v !== '');

            if (! $person) {
                $person = Person::create($attributes + [
                    'cpf' => $cpf,
                    'phone' => $this->phone($data['telefone'] ?? null),
                    'email' => $data['email'] ?? null,
                ]);

                return ['action' => 'created', 'person_id' => $person->id];
            }

            // CPF corrigido na administração: vale o de lá, se nenhuma outra pessoa daqui já o usa
            if ($person->administration_person_id === $data['id']
                && ! in_array($person->cpf, [$cpf, preg_replace('/\D/', '', $cpf)], true)
                && ! Person::whereKeyNot($person->id)->whereIn('cpf', [$cpf, preg_replace('/\D/', '', $cpf)])->exists()) {
                $attributes['cpf'] = $cpf;
            }
            if (empty($person->phone) && ! empty($data['telefone'])) {
                $attributes['phone'] = $this->phone($data['telefone']);
            }
            if (empty($person->email) && ! empty($data['email'])) {
                $attributes['email'] = $data['email'];
            }

            $person->fill($attributes);
            $action = $person->isDirty() ? 'updated' : 'unchanged';
            $person->save();

            return ['action' => $action, 'person_id' => $person->id];
        });

        $this->recordWatermark($data['sincronizado_em'] ?? null);

        return $result;
    }

    /** Último carimbo recebido (relógio da administração, como veio): é o `desde` do próximo delta. */
    public function lastSyncedAt(): ?string
    {
        return DB::table('sync_watermarks')
            ->where('source', self::WATERMARK_SOURCE)->where('model', self::WATERMARK_MODEL)
            ->value('last_changed_at');
    }

    /**
     * A marca anda até para pessoas ignoradas (inativas), senão o data-sync as receberia de novo a
     * cada rodada. O valor fica como a administração mandou, sem mudar de fuso.
     */
    private function recordWatermark(?string $syncedAt): void
    {
        if (empty($syncedAt)) {
            return;
        }

        $value = preg_replace('/^(\d{4}-\d{2}-\d{2})T/', '$1 ', substr($syncedAt, 0, 19));
        $current = $this->lastSyncedAt();

        if ($current === null) {
            DB::table('sync_watermarks')->insertOrIgnore([
                'source' => self::WATERMARK_SOURCE,
                'model' => self::WATERMARK_MODEL,
                'last_changed_at' => $value,
            ]);
        } elseif ($value > $current) {
            DB::table('sync_watermarks')
                ->where('source', self::WATERMARK_SOURCE)->where('model', self::WATERMARK_MODEL)
                ->update(['last_changed_at' => $value]);
        }
    }

    /**
     * Igreja daqui ligada à da administração. Se ainda não há vínculo (`administration_system_id`
     * vazio), tenta ligar pelo nome, só quando há uma única candidata. Sem vínculo devolve null e a
     * pessoa mantém a igreja que já tinha: nunca vai parar numa igreja errada.
     */
    public function resolveChurch(int $administrationId): ?int
    {
        $church = Church::where('administration_system_id', $administrationId)->value('id');
        if ($church) {
            return $church;
        }

        $administrationChurch = AdministrationChurch::find($administrationId);
        $candidates = $administrationChurch ? $this->unlinkedChurchesNamed($administrationChurch->name) : collect();
        if ($candidates->count() !== 1) {
            return null;
        }

        $candidate = $candidates->first();
        $candidate->update(['administration_system_id' => $administrationId]);

        return $candidate->id;
    }

    /** Igrejas daqui sem vínculo com a administração cujo nome (normalizado) é igual ao dado. */
    public function unlinkedChurchesNamed(string $name)
    {
        $key = self::nameKey($name);

        return Church::whereNull('administration_system_id')->get()
            ->filter(fn (Church $c) => $key !== '' && self::nameKey($c->name) === $key)
            ->values();
    }

    /**
     * Nome sem acento, em maiúsculas, sem o prefixo "IEA" nem a UF no fim ("IEA - LOTEAMENTO
     * BRASIL - RN" e "Loteamento Brasil" dão a mesma chave).
     */
    public static function nameKey(string $name): string
    {
        $key = Str::upper(Str::ascii($name));
        $key = preg_replace('/[^A-Z0-9]+/', ' ', $key);
        $key = trim((string) $key);
        $key = preg_replace('/^IEA\b\s*/', '', $key);
        $key = preg_replace('/\s+(?:' . self::UFS . ')$/', '', $key);

        return trim((string) $key);
    }

    /** UF no fim do nome ("IEA - ROSA DOS VENTOS - RN", "IEA - Anchieta - RJ-A"), ou null. */
    public static function stateCodeFromName(string $name): ?string
    {
        return preg_match('/(?:^|[\s-])(' . self::UFS . ')(?:[\s-][A-Z])?\s*$/', $name, $m) ? $m[1] : null;
    }

    /** Cria a igreja daqui para uma da administração sem par. Estado pela UF do nome; cidade fica vazia. */
    public function createChurch(AdministrationChurch $administrationChurch): Church
    {
        $code = self::stateCodeFromName($administrationChurch->name);

        return Church::create([
            'administration_system_id' => $administrationChurch->id,
            'name' => $administrationChurch->name,
            'state_id' => $code ? State::where('code', $code)->value('id') : null,
            'city_id' => null,
        ]);
    }

    private function formatCpf(string $value): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', preg_replace('/\D/', '', $value));
    }

    private function phone(?string $value): ?string
    {
        return $value === null || $value === '' ? null : mb_substr($value, 0, 20);
    }
}
