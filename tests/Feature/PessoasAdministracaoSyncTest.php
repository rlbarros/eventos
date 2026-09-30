<?php

namespace Tests\Feature;

use App\Models\AdministrationChurch;
use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use App\Services\AdministrationPeople;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Pessoas vindas da administração (data-sync): upsert pelo CPF, administração vence nos campos
// dela, telefone/e-mail locais ficam, igreja ligada pelo administration_system_id (ou pelo nome).
class PessoasAdministracaoSyncTest extends TestCase
{
    use RefreshDatabase;

    private Church $igreja;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('states')->insert(['id' => 24, 'code' => 'RN', 'name' => 'Rio Grande do Norte']);
        DB::table('cities')->insert(['id' => 1, 'ibge_id' => 1, 'state_id' => 24, 'name' => 'Natal']);
        $this->igreja = Church::create(['administration_system_id' => 34, 'name' => 'IEA - LOTEAMENTO BRASIL - RN', 'state_id' => 24, 'city_id' => 1]);

        $user = User::factory()->create();
        config(['services.data_sync.email' => $user->email]);
        Sanctum::actingAs($user);
    }

    private function envia(array $dados = [])
    {
        return $this->postJson('/api/pessoas-sync', $dados + [
            'id' => 500,
            'cpf' => '09016762418',
            'nome' => 'Josenildo da Silva',
            'data_nascimento' => '1992-01-01',
            'email' => null,
            'telefone' => null,
            'igreja_id' => 34,
            'funcao' => 'Obreiro',
            'ativo' => true,
            'sincronizado_em' => '2026-09-30 10:00:00',
        ]);
    }

    public function test_cria_a_pessoa_nova_com_cpf_formatado_e_igreja(): void
    {
        $this->envia(['email' => 'jo@ex.com', 'telefone' => '84999990000'])->assertOk()->assertJsonPath('data.action', 'created');

        $p = Person::where('administration_person_id', 500)->firstOrFail();
        $this->assertSame('090.167.624-18', $p->cpf);
        $this->assertSame($this->igreja->id, $p->church_id);
        $this->assertSame('Obreiro', $p->function);
        $this->assertSame('jo@ex.com', $p->email);
    }

    public function test_acha_pelo_cpf_mascarado_e_a_administracao_vence_no_que_e_dela(): void
    {
        $local = Person::create(['cpf' => '090.167.624-18', 'name' => 'JOSENILDO', 'church_id' => null,
            'phone' => '84111111111', 'email' => 'local@ex.com', 'function' => 'Membro']);

        $this->envia(['telefone' => '84222222222', 'email' => 'adm@ex.com'])->assertOk()->assertJsonPath('data.action', 'updated');

        $this->assertSame(1, Person::count());
        $local->refresh();
        $this->assertSame('Josenildo da Silva', $local->name);
        $this->assertSame('Obreiro', $local->function);
        $this->assertSame($this->igreja->id, $local->church_id);
        $this->assertSame(500, $local->administration_person_id);
        // telefone e e-mail daqui não são trocados
        $this->assertSame('84111111111', $local->phone);
        $this->assertSame('local@ex.com', $local->email);
    }

    public function test_preenche_telefone_e_email_que_estavam_vazios(): void
    {
        Person::create(['cpf' => '090.167.624-18', 'name' => 'J']);

        $this->envia(['telefone' => '84222222222', 'email' => 'adm@ex.com'])->assertOk();

        $p = Person::first();
        $this->assertSame('84222222222', $p->phone);
        $this->assertSame('adm@ex.com', $p->email);
    }

    public function test_sem_dados_da_administracao_mantem_o_que_ja_existe(): void
    {
        Person::create(['cpf' => '090.167.624-18', 'name' => 'J', 'birth_date' => '1990-05-05', 'church_id' => $this->igreja->id, 'function' => 'Diácono']);

        $this->envia(['data_nascimento' => null, 'igreja_id' => null, 'funcao' => 'Cargo Desconhecido'])->assertOk();

        $p = Person::first();
        $this->assertSame('1990-05-05', substr((string) $p->birth_date, 0, 10));
        $this->assertSame($this->igreja->id, $p->church_id);
        $this->assertSame('Diácono', $p->function);
    }

    public function test_inativa_nao_cria_nem_altera(): void
    {
        $this->envia(['ativo' => false])->assertOk()->assertJsonPath('data.action', 'ignored');
        $this->assertSame(0, Person::count());

        Person::create(['cpf' => '090.167.624-18', 'name' => 'Antigo']);
        $this->envia(['ativo' => false])->assertOk();
        $this->assertSame('Antigo', Person::first()->name);
    }

    public function test_cpf_corrigido_na_administracao_troca_o_cpf_daqui(): void
    {
        Person::create(['cpf' => '111.111.111-11', 'name' => 'J', 'administration_person_id' => 500]);

        $this->envia()->assertOk();

        $this->assertSame(1, Person::count());
        $this->assertSame('090.167.624-18', Person::first()->cpf);
    }

    public function test_liga_a_igreja_pelo_nome_quando_ainda_nao_ha_vinculo(): void
    {
        $this->igreja->update(['administration_system_id' => null]);
        AdministrationChurch::create(['id' => 34, 'name' => 'Loteamento Brasil', 'active' => true]);

        $this->envia()->assertOk();

        $this->assertSame(34, (int) $this->igreja->fresh()->administration_system_id);
        $this->assertSame($this->igreja->id, Person::first()->church_id);
    }

    public function test_nome_ambiguo_nao_liga_igreja_nenhuma(): void
    {
        $this->igreja->update(['administration_system_id' => null]);
        Church::create(['name' => 'Loteamento Brasil', 'state_id' => 24, 'city_id' => 1]);
        AdministrationChurch::create(['id' => 34, 'name' => 'Loteamento Brasil', 'active' => true]);

        $this->envia()->assertOk();

        $this->assertNull(Person::first()->church_id);
        $this->assertSame(0, Church::whereNotNull('administration_system_id')->count());
    }

    public function test_marca_d_agua_anda_para_frente_mesmo_para_inativa(): void
    {
        $this->envia(['sincronizado_em' => '2026-09-30 10:00:00'])->assertOk();
        $this->envia(['id' => 501, 'cpf' => '81229062404', 'ativo' => false, 'sincronizado_em' => '2026-09-30 10:05:00'])->assertOk();
        $this->envia(['sincronizado_em' => '2026-09-30 09:00:00'])->assertOk();

        $this->assertSame('2026-09-30 10:05:00', app(\App\Services\AdministrationPeople::class)->lastSyncedAt());
    }

    public function test_so_o_usuario_do_data_sync_grava(): void
    {
        config(['services.data_sync.email' => 'outro@ex.com']);

        $this->envia()->assertForbidden();
    }

    public function test_comando_liga_por_nome_so_com_aplicar(): void
    {
        $this->igreja->update(['administration_system_id' => null]);
        AdministrationChurch::create(['id' => 34, 'name' => 'IEA - Loteamento Brasil', 'active' => true]);

        $this->artisan('igrejas:vincular-administracao')->assertSuccessful();
        $this->assertNull($this->igreja->fresh()->administration_system_id);

        $this->artisan('igrejas:vincular-administracao', ['--aplicar' => true])->assertSuccessful();
        $this->assertSame(34, (int) $this->igreja->fresh()->administration_system_id);
    }

    public function test_comando_cria_so_as_sem_par_e_so_com_aplicar(): void
    {
        AdministrationChurch::create(['id' => 34, 'name' => 'IEA - LOTEAMENTO BRASIL - RN', 'active' => true]);
        AdministrationChurch::create(['id' => 60, 'name' => 'IEA - NOVA FLORESTA - RN', 'active' => true]);
        AdministrationChurch::create(['id' => 61, 'name' => 'IEA - SEM UF', 'active' => true]);
        $antes = Church::count();

        $this->artisan('igrejas:vincular-administracao', ['--criar' => true])->assertSuccessful();
        $this->assertSame($antes, Church::count());

        $this->artisan('igrejas:vincular-administracao', ['--criar' => true, '--aplicar' => true])->assertSuccessful();

        $this->assertSame($antes + 2, Church::count());
        $nova = Church::where('administration_system_id', 60)->firstOrFail();
        $this->assertSame('IEA - NOVA FLORESTA - RN', $nova->name);
        $this->assertSame(24, $nova->state_id);
        $this->assertNull($nova->city_id);
        $this->assertNull(Church::where('administration_system_id', 61)->firstOrFail()->state_id);
        // a 34 já estava ligada: não duplica
        $this->assertSame(1, Church::where('administration_system_id', 34)->count());
    }

    public function test_comando_nao_cria_quando_ha_candidata_por_nome(): void
    {
        $this->igreja->update(['administration_system_id' => null]);
        AdministrationChurch::create(['id' => 34, 'name' => 'Loteamento Brasil', 'active' => true]);
        $antes = Church::count();

        $this->artisan('igrejas:vincular-administracao', ['--criar' => true, '--aplicar' => true])->assertSuccessful();

        $this->assertSame($antes, Church::count());
        $this->assertSame(34, (int) $this->igreja->fresh()->administration_system_id);
    }

    public function test_uf_do_nome_da_igreja(): void
    {
        $this->assertSame('RN', AdministrationPeople::stateCodeFromName('IEA - ROSA DOS VENTOS - RN'));
        $this->assertSame('RJ', AdministrationPeople::stateCodeFromName('IEA - Anchieta - RJ-A'));
        $this->assertSame('RJ', AdministrationPeople::stateCodeFromName('IEA - Santa Cruz-RJ-B'));
        $this->assertNull(AdministrationPeople::stateCodeFromName('IEA - CAMPINAS - SEDE NACIONAL'));
    }
}
