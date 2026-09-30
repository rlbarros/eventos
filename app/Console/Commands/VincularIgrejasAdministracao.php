<?php

namespace App\Console\Commands;

use App\Models\AdministrationChurch;
use App\Models\Church;
use App\Services\AdministrationPeople;
use Illuminate\Console\Command;

/**
 * Liga as igrejas daqui (`churches`) às da administração (`administration_system_id`), que o
 * sync de pessoas usa para pôr cada pessoa na igreja certa. Por padrão só lista; `--aplicar`
 * grava os casamentos por nome que têm uma única candidata; `--manual=ID_AQUI:ID_ADMIN` liga
 * uma igreja à mão (ex.: `--manual=34:12`).
 */
class VincularIgrejasAdministracao extends Command
{
    protected $signature = 'igrejas:vincular-administracao {--aplicar : grava os vínculos por nome} {--manual=* : ID_AQUI:ID_ADMINISTRACAO}';

    protected $description = 'Liga as igrejas do eventos às da administração (administration_system_id)';

    public function handle(AdministrationPeople $people): int
    {
        foreach ($this->option('manual') as $par) {
            [$aqui, $admin] = array_pad(explode(':', $par, 2), 2, null);
            $church = Church::find((int) $aqui);
            $administrationChurch = AdministrationChurch::find((int) $admin);
            if (! $church || ! $administrationChurch) {
                $this->error("Vínculo {$par} ignorado: igreja não encontrada.");

                continue;
            }
            if (Church::where('administration_system_id', $administrationChurch->id)->whereKeyNot($church->id)->exists()) {
                $this->error("Vínculo {$par} ignorado: a igreja {$administrationChurch->id} da administração já está ligada a outra.");

                continue;
            }
            $church->update(['administration_system_id' => $administrationChurch->id]);
            $this->info("#{$church->id} {$church->name} => administração #{$administrationChurch->id} {$administrationChurch->name}");
        }

        $linked = Church::whereNotNull('administration_system_id')->pluck('administration_system_id')->all();
        $pending = 0;

        AdministrationChurch::where('active', true)->orderBy('name')->get()
            ->reject(fn (AdministrationChurch $a) => in_array($a->id, $linked))
            ->each(function (AdministrationChurch $a) use ($people, &$pending) {
                $candidates = $people->unlinkedChurchesNamed($a->name);
                if ($candidates->count() === 1) {
                    $church = $candidates->first();
                    if ($this->option('aplicar')) {
                        $church->update(['administration_system_id' => $a->id]);
                    }
                    $this->line(($this->option('aplicar') ? 'ligada' : 'candidata') . ": #{$church->id} {$church->name} => administração #{$a->id} {$a->name}");
                } else {
                    $pending++;
                    $this->warn("sem par único ({$candidates->count()}): administração #{$a->id} {$a->name}");
                }
            });

        if (! $this->option('aplicar')) {
            $this->comment('Nada gravado. Use --aplicar para ligar as candidatas.');
        }
        if ($pending > 0) {
            $this->comment("{$pending} igreja(s) da administração sem par: use --manual=ID_AQUI:ID_ADMINISTRACAO.");
        }

        return self::SUCCESS;
    }
}
