<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use App\Models\SyncDeletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Participações nos dois sentidos com a administração (data-sync): exclusões saem como
// active = false e o que a administração manda (POST /participants-admin-sync) entra aqui.
class ParticipantSyncTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-29 12:00:00');

        DB::table('states')->insert(['id' => 1, 'code' => 35, 'name' => 'São Paulo']);
        DB::table('cities')->insert(['id' => 1, 'ibge_id' => 1, 'state_id' => 1, 'name' => 'São Paulo']);
        DB::table('event_sites')->insert(['id' => 1, 'name' => 'Sítio', 'state_id' => 1, 'city_id' => 1, 'address' => 'Rua']);

        $this->church = Church::create(['administration_system_id' => 7, 'name' => 'Igreja', 'state_id' => 1, 'city_id' => 1]);
        $this->event = Event::create([
            'name' => 'Congresso', 'scope' => 'nacional', 'start_date' => '2026-10-10', 'end_date' => '2026-10-12',
            'church_id' => $this->church->id, 'event_site_id' => 1,
        ]);

        Sanctum::actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function daAdministracao(array $dados)
    {
        return $this->postJson('/api/participants-admin-sync', $dados + [
            'id' => 10,
            'event_id' => $this->event->id,
            'cpf' => '222.222.222-22',
            'nome' => 'Bruno',
            'data_nascimento' => '1990-02-03',
            'telefone' => '11988880000',
            'email' => 'bruno@x.com',
            'igreja_id' => 7,
            'ativo' => true,
            'alterado_em' => '2026-09-29T11:59:00+00:00',
        ]);
    }

    public function test_participacao_da_administracao_cria_pessoa_e_alocacao(): void
    {
        $this->daAdministracao([])->assertOk();

        $person = Person::where('cpf', '222.222.222-22')->firstOrFail();
        $this->assertSame('Bruno', $person->name);
        $this->assertSame($this->church->id, $person->church_id);
        $this->assertTrue(EventParticipantAllocation::where('event_id', $this->event->id)->where('person_id', $person->id)->exists());

        // repetir não duplica
        $this->daAdministracao([])->assertOk();
        $this->assertSame(1, EventParticipantAllocation::count());

        $this->getJson('/api/sync')->assertJsonPath('data.participants_admin', '2026-09-29T11:59:00+00:00');
    }

    public function test_pessoa_existente_e_achada_pelo_cpf_sem_mascara(): void
    {
        $person = Person::create(['name' => 'Bruno Local', 'cpf' => '22222222222']);

        $this->daAdministracao([])->assertOk();

        $this->assertSame(1, Person::count());
        $this->assertSame($person->id, EventParticipantAllocation::firstOrFail()->person_id);
    }

    public function test_exclusao_da_administracao_remove_se_for_mais_recente(): void
    {
        $person = Person::create(['name' => 'Bruno', 'cpf' => '222.222.222-22']);
        $allocation = EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $person->id]);

        // alterada aqui depois da exclusão de lá: fica
        $this->daAdministracao(['ativo' => false, 'participants_system_id' => $allocation->id, 'alterado_em' => '2026-09-29T11:00:00+00:00'])->assertOk();
        $this->assertNotNull($allocation->fresh());

        $this->daAdministracao(['ativo' => false, 'participants_system_id' => $allocation->id, 'alterado_em' => '2026-09-29T12:00:01+00:00'])->assertOk();
        $this->assertNull($allocation->fresh());
        $this->assertTrue(SyncDeletion::where('model', SyncDeletion::PARTICIPANTS)->where('record_id', $allocation->id)->exists());
    }

    public function test_nao_recria_participacao_removida_aqui_depois(): void
    {
        $person = Person::create(['name' => 'Bruno', 'cpf' => '222.222.222-22']);
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $person->id])->delete();

        $this->daAdministracao(['alterado_em' => '2026-09-29T11:00:00+00:00'])->assertOk();
        $this->assertSame(0, EventParticipantAllocation::count());

        $this->daAdministracao(['alterado_em' => '2026-09-29T12:30:00+00:00'])->assertOk();
        $this->assertSame(1, EventParticipantAllocation::count());
    }

    public function test_exclusao_sai_no_participants_sync_como_inativa(): void
    {
        $person = Person::create(['name' => 'Ana', 'cpf' => '111.111.111-11']);
        $allocation = EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $person->id]);

        Carbon::setTestNow('2026-09-29 12:10:00');
        $allocation->delete();

        $registros = $this->getJson('/api/participants-sync?desde=' . urlencode('2026-09-29T12:05:00+00:00'))->assertOk()->json('data');

        $this->assertCount(1, $registros);
        $this->assertSame($allocation->id, $registros[0]['id']);
        $this->assertFalse($registros[0]['active']);
        $this->assertSame('111.111.111-11', $registros[0]['cpf']);
        $this->assertSame('2026-09-29T12:10:00+00:00', $this->getJson('/api/sync')->json('data.participants'));
    }

    public function test_evento_excluido_sai_no_events_sync_como_inativo(): void
    {
        $this->event->delete();

        $registros = collect($this->getJson('/api/events-sync')->assertOk()->json('data'));

        $this->assertFalse($registros->firstWhere('id', $this->event->id)['active']);
    }
}
