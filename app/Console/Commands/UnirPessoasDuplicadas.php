<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Junta as pessoas gravadas em duplicidade pelo sync de pessoas da administração (mesmo
 * `administration_person_id`, o que só acontece se duas cargas rodaram ao mesmo tempo). Fica a
 * pessoa com mais vínculos (inscrições, viagens, consumos, pagamentos), ou a mais antiga; os
 * vínculos das outras passam para ela e a cópia é apagada. Por padrão só lista; `--aplicar` grava.
 *
 * Uma cópia que tem inscrição em um evento em que a pessoa que fica também está inscrita não é
 * mexida e vai para o relatório final: juntar isso é decisão de quem conhece o caso. As
 * inscrições são movidas direto no banco, sem disparar o registro de exclusão para a administração.
 */
class UnirPessoasDuplicadas extends Command
{
    protected $signature = 'pessoas:unir-duplicadas {--aplicar : grava a união}';

    protected $description = 'Junta pessoas duplicadas pelo sync da administração (mesmo administration_person_id)';

    /** Colunas que apontam para persons.id */
    private const REFERENCES = [
        ['events_participants_allocations', 'person_id'],
        ['events_participants_allocations', 'payer_person_id'],
        ['events_participants_payments', 'person_id'],
        ['events_services_participants_consumption', 'person_id'],
        ['events_services_participants_payments', 'person_id'],
        ['events_trips_participants', 'person_id'],
        ['persons', 'father_id'],
        ['persons', 'mother_id'],
        ['persons', 'spouse_id'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('aplicar');
        $groups = DB::table('persons')
            ->whereNotNull('administration_person_id')
            ->select('administration_person_id')
            ->groupBy('administration_person_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('administration_person_id');

        $merged = 0;
        $skipped = [];

        foreach ($groups as $administrationId) {
            $people = Person::where('administration_person_id', $administrationId)->orderBy('id')->get();
            $keeper = $people->sortByDesc(fn (Person $p) => $this->references($p->id))->first();

            foreach ($people->where('id', '!=', $keeper->id) as $copy) {
                if ($this->conflicts($keeper->id, $copy->id)) {
                    $skipped[] = "#{$copy->id} (mesma inscrição que #{$keeper->id}, administração #{$administrationId})";

                    continue;
                }

                $this->line(($apply ? 'unida' : 'seria unida') . ": #{$copy->id} {$copy->name} => #{$keeper->id}");
                if ($apply) {
                    DB::transaction(fn () => $this->merge($keeper, $copy));
                }
                $merged++;
            }
        }

        $this->info(($apply ? 'Unidas' : 'Seriam unidas') . ": {$merged}.");
        if ($skipped) {
            $this->warn('Não mexidas (resolver à mão): ' . implode('; ', $skipped));
        }
        if (! $apply) {
            $this->comment('Nada gravado. Use --aplicar para gravar.');
        }

        return self::SUCCESS;
    }

    private function references(int $personId): int
    {
        $total = 0;
        foreach (self::REFERENCES as [$table, $column]) {
            if (Schema::hasColumn($table, $column)) {
                $total += DB::table($table)->where($column, $personId)->count();
            }
        }

        return $total;
    }

    /** As duas estão inscritas no mesmo evento. */
    private function conflicts(int $keeperId, int $copyId): bool
    {
        $keeperEvents = DB::table('events_participants_allocations')->where('person_id', $keeperId)->pluck('event_id');

        return $keeperEvents->isNotEmpty()
            && DB::table('events_participants_allocations')->where('person_id', $copyId)->whereIn('event_id', $keeperEvents)->exists();
    }

    private function merge(Person $keeper, Person $copy): void
    {
        foreach (self::REFERENCES as [$table, $column]) {
            if (Schema::hasColumn($table, $column)) {
                DB::table($table)->where($column, $copy->id)->update([$column => $keeper->id]);
            }
        }

        // o que a pessoa que fica não tem, herda da cópia
        foreach (['phone', 'email', 'birth_date', 'church_id', 'function'] as $field) {
            if (empty($keeper->{$field}) && ! empty($copy->{$field})) {
                $keeper->{$field} = $copy->{$field};
            }
        }
        $keeper->save();

        DB::table('persons')->where('id', $copy->id)->delete();
    }
}
