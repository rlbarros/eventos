<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventBatch;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\EventParticipantPayment;
use App\Models\Person;
use App\Models\SyncDeletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Eventos ⇄ superapp (data-sync): lotes, preços, inscrições e pagamentos saem daqui com a pessoa
// só como HMAC do CPF; a inscrição feita no app entra por POST /superapp/inscricoes.
class SuperappSyncTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE = 'chave-hmac-de-teste';

    private Event $event;
    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-29 12:00:00');
        config(['services.superapp.cpf_hmac_chave' => self::CHAVE]);

        DB::table('states')->insert(['id' => 1, 'code' => 'SP', 'name' => 'São Paulo']);
        DB::table('cities')->insert(['id' => 1, 'ibge_id' => 1, 'state_id' => 1, 'name' => 'Atibaia']);
        DB::table('event_sites')->insert(['id' => 1, 'name' => 'Sítio Betel', 'state_id' => 1, 'city_id' => 1, 'address' => 'Rua']);
        DB::table('event_sites')->insert(['id' => 2, 'name' => 'Outro', 'state_id' => 1, 'city_id' => 1, 'address' => 'Rua']);
        DB::table('event_site_room_types')->insert(['id' => 1, 'event_site_id' => 1, 'name' => 'Apartamento casal', 'type' => 'Apartamento', 'beds' => 2]);
        DB::table('event_site_room_types')->insert(['id' => 2, 'event_site_id' => 2, 'name' => 'De outro sítio', 'type' => 'Hotel', 'beds' => 2]);

        $this->church = Church::create(['administration_system_id' => 7, 'name' => 'Igreja', 'state_id' => 1, 'city_id' => 1]);
        $this->event = Event::create([
            'name' => 'Congresso', 'scope' => 'nacional', 'start_date' => '2026-10-10', 'end_date' => '2026-10-12',
            'church_id' => $this->church->id, 'event_site_id' => 1, 'contact_name' => 'Irmão Paulo', 'contact_phone' => '11999990000',
            'children_age' => 10,
        ]);

        $user = User::factory()->create();
        config(['services.data_sync.email' => $user->email]);
        Sanctum::actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function hmac(string $cpf): string
    {
        return hash_hmac('sha256', preg_replace('/\D/', '', $cpf), self::CHAVE);
    }

    private function doApp(array $dados = [])
    {
        return $this->postJson('/api/superapp/inscricoes', $dados + [
            'id' => 3,
            'event_id' => $this->event->id,
            'cpf' => '529.982.247-25',
            'nome' => 'Irmã Marta',
            'data_nascimento' => '1980-05-06',
            'telefone' => '(11) 98888-7777',
            'email' => 'marta@exemplo.com.br',
            'igreja_id' => 7,
            'hospedagem_id' => 1,
            'alterado_em' => '2026-09-29T11:59:00+00:00',
        ]);
    }

    public function test_so_o_usuario_do_data_sync(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/superapp/inscricoes')->assertStatus(403);
        $this->doApp()->assertStatus(403);
    }

    public function test_evento_leva_local_contato_e_igreja_da_administracao(): void
    {
        $evento = $this->getJson('/api/superapp/eventos')->assertOk()->json('data.0');

        $this->assertSame('Sítio Betel', $evento['site_name']);
        $this->assertSame('Atibaia', $evento['site_city']);
        $this->assertSame('SP', $evento['site_state']);
        $this->assertSame('Irmão Paulo', $evento['contact_name']);
        $this->assertSame(7, $evento['administration_church_id']);
        $this->assertSame('2026-10-10', $evento['start_date']);
        $this->assertSame(10, $evento['children_age']);
    }

    public function test_lotes_e_precos_com_hospedagem_e_exclusao(): void
    {
        $lote = EventBatch::create(['event_id' => $this->event->id, 'batch' => 1, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30']);
        $preco = EventFee::create([
            'event_id' => $this->event->id, 'event_site_room_type_id' => 1, 'event_batch_id' => $lote->id,
            'category' => 'Integral', 'fee' => 350.5,
        ]);

        $this->assertSame(1, $this->getJson('/api/superapp/lotes')->json('data.0.batch'));
        $p = $this->getJson('/api/superapp/precos')->assertOk()->json('data.0');
        $this->assertSame([1, 'Apartamento casal', 'Apartamento', 'Integral', 350.5], [
            $p['batch'], $p['room_type_name'], $p['room_type_kind'], $p['category'], $p['fee'],
        ]);

        Carbon::setTestNow('2026-09-29 13:00:00');
        $preco->delete();
        $desde = urlencode('2026-09-29T12:30:00+00:00');
        $excluido = $this->getJson("/api/superapp/precos?desde={$desde}")->assertOk()->json('data');
        $this->assertCount(1, $excluido);
        $this->assertSame([$preco->id, false], [$excluido[0]['id'], $excluido[0]['active']]);
        $this->assertSame('2026-09-29T13:00:00+00:00', $this->getJson('/api/sync')->json('data.superapp_precos'));
    }

    public function test_inscricoes_e_pagamentos_levam_hmac_e_nao_o_cpf(): void
    {
        $person = Person::create(['name' => 'Irmã Marta', 'cpf' => '529.982.247-25', 'church_id' => $this->church->id]);
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $person->id, 'event_site_room_type_id' => 1]);
        $lote = EventBatch::create(['event_id' => $this->event->id, 'batch' => 1, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30']);
        $preco = EventFee::create(['event_id' => $this->event->id, 'event_site_room_type_id' => 1, 'event_batch_id' => $lote->id, 'category' => 'Integral', 'fee' => 300]);
        EventParticipantPayment::create(['event_id' => $this->event->id, 'event_fee_id' => $preco->id, 'person_id' => $person->id, 'payment_date' => '2026-09-20', 'amount' => 150]);

        $inscricao = $this->getJson('/api/superapp/inscricoes')->assertOk()->json('data.0');
        $this->assertSame($this->hmac('52998224725'), $inscricao['cpf_hmac']);
        $this->assertArrayNotHasKey('cpf', $inscricao);
        $this->assertSame('Apartamento casal', $inscricao['room_type_name']);

        $pagamento = $this->getJson('/api/superapp/pagamentos')->assertOk()->json('data.0');
        $this->assertSame([$this->hmac('52998224725'), $preco->id, 150, '2026-09-20'], [
            $pagamento['cpf_hmac'], $pagamento['fee_id'], $pagamento['amount'], $pagamento['payment_date'],
        ]);
    }

    public function test_sem_chave_hmac_falha_em_vez_de_mandar_sem_identificacao(): void
    {
        config(['services.superapp.cpf_hmac_chave' => '']);

        $this->getJson('/api/superapp/inscricoes')->assertStatus(500);
    }

    public function test_inscricao_do_app_cria_pessoa_e_participacao_com_hospedagem(): void
    {
        $this->doApp()->assertOk();

        $person = Person::where('cpf', '529.982.247-25')->firstOrFail();
        $this->assertSame(['Irmã Marta', 'marta@exemplo.com.br', $this->church->id], [$person->name, $person->email, $person->church_id]);
        $allocation = EventParticipantAllocation::where('person_id', $person->id)->firstOrFail();
        $this->assertSame(1, $allocation->event_site_room_type_id);
        $this->assertSame('2026-09-29T11:59:00+00:00', $this->getJson('/api/sync')->json('data.inscricoes_superapp'));
    }

    public function test_inscricao_do_app_ja_inscrito_so_completa_hospedagem_e_nao_muda_cadastro(): void
    {
        $person = Person::create(['name' => 'Marta da Silva', 'cpf' => '52998224725', 'email' => 'antigo@x.org']);
        EventParticipantAllocation::create(['event_id' => $this->event->id, 'person_id' => $person->id]);

        $this->doApp()->assertOk();

        $this->assertSame(1, EventParticipantAllocation::count());
        $this->assertSame(1, EventParticipantAllocation::first()->event_site_room_type_id);
        $this->assertSame(['Marta da Silva', 'antigo@x.org'], [$person->fresh()->name, $person->fresh()->email]);
    }

    public function test_hospedagem_de_outro_local_e_ignorada_e_evento_inexistente_nao_quebra(): void
    {
        $this->doApp(['hospedagem_id' => 2])->assertOk();
        $this->assertNull(EventParticipantAllocation::first()->event_site_room_type_id);

        $this->doApp(['event_id' => 999, 'cpf' => '111.444.777-35'])->assertOk();
        $this->assertSame(1, EventParticipantAllocation::count());
    }

    public function test_nao_recria_o_que_a_organizacao_removeu_depois(): void
    {
        SyncDeletion::create([
            'model' => SyncDeletion::PARTICIPANTS, 'record_id' => 50, 'event_id' => $this->event->id,
            'cpf' => '529.982.247-25', 'deleted_at' => '2026-09-29 12:00:00',
        ]);

        $this->doApp()->assertOk();

        $this->assertSame(0, EventParticipantAllocation::count());
    }
}
