<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// Limpeza das pessoas duplicadas pelo sync da administração (mesmo administration_person_id).
class UnirPessoasDuplicadasTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;
    private Event $outro;

    protected function setUp(): void
    {
        parent::setUp();
        // estas pessoas só existem em produção antes da migration de unicidade
        Schema::table('persons', function ($table) {
            $table->dropUnique(['administration_person_id']);
            $table->index('administration_person_id');
        });
        DB::table('states')->insert(['id' => 1, 'code' => 'SP', 'name' => 'São Paulo']);
        DB::table('cities')->insert(['id' => 1, 'ibge_id' => 1, 'state_id' => 1, 'name' => 'São Paulo']);
        DB::table('event_sites')->insert(['id' => 1, 'name' => 'Sítio', 'state_id' => 1, 'city_id' => 1, 'address' => 'Rua']);
        $church = Church::create(['name' => 'Igreja', 'state_id' => 1, 'city_id' => 1]);
        $dados = ['scope' => 'nacional', 'start_date' => '2026-10-10', 'end_date' => '2026-10-12', 'church_id' => $church->id, 'event_site_id' => 1];
        $this->event = Event::create($dados + ['name' => 'Congresso']);
        $this->outro = Event::create($dados + ['name' => 'Retiro']);
    }

    private function pessoa(array $extra = []): Person
    {
        return Person::create($extra + ['name' => 'Ana', 'cpf' => '111.444.777-35', 'administration_person_id' => 7]);
    }

    public function test_so_lista_sem_aplicar(): void
    {
        $this->pessoa();
        $this->pessoa();

        $this->artisan('pessoas:unir-duplicadas')->assertSuccessful();

        $this->assertSame(2, Person::count());
    }

    public function test_fica_a_pessoa_com_inscricao_e_leva_os_vinculos_da_copia(): void
    {
        $antiga = $this->pessoa();
        $comInscricao = $this->pessoa(['phone' => '84999990000']);
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $comInscricao->id]);
        $copia = $this->pessoa(['email' => 'ana@ex.com']);
        EventParticipantAllocation::create(['event_id' => $this->outro->id, 'person_id' => $copia->id]);

        $this->artisan('pessoas:unir-duplicadas', ['--aplicar' => true])->assertSuccessful();

        $this->assertSame(1, Person::where('administration_person_id', 7)->count());
        $fica = Person::where('administration_person_id', 7)->firstOrFail();
        $this->assertSame(2, DB::table('events_participants_allocations')->where('person_id', $fica->id)->count());
        $this->assertSame('ana@ex.com', $fica->email);
        $this->assertSame('84999990000', $fica->phone);
        $this->assertSame($comInscricao->id, $fica->id);
        $this->assertDatabaseMissing('persons', ['id' => $antiga->id]);
        $this->assertDatabaseMissing('persons', ['id' => $copia->id]);
    }

    public function test_copia_inscrita_no_mesmo_evento_nao_e_mexida(): void
    {
        $a = $this->pessoa();
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $a->id]);
        $b = $this->pessoa();
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $b->id]);

        $this->artisan('pessoas:unir-duplicadas', ['--aplicar' => true])->assertSuccessful();

        $this->assertSame(2, Person::where('administration_person_id', 7)->count());
        $this->assertSame(2, DB::table('events_participants_allocations')->count());
    }

    public function test_migration_de_unicidade_recusa_enquanto_ha_duplicadas(): void
    {
        $this->pessoa();
        $this->pessoa();
        $migration = require database_path('migrations/2026_09_30_500000_make_administration_person_id_unique.php');

        $this->expectException(\RuntimeException::class);
        $migration->up();
    }
}
