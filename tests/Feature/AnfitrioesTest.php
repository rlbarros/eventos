<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Middleware\EnsureHostAccess;
use App\Models\AdministrationChurch;
use App\Models\AdministrationHost;
use App\Models\Church;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// Anfitriões vindos da administração (data-sync): só eles criam conta, e a jurisdição limita
// os eventos que manuseiam.
class AnfitrioesTest extends TestCase
{
    use RefreshDatabase;

    private const SYNC_EMAIL = 'data-sync@eventos.test';

    private int $siteId;
    private Church $igrejaA;   // superintendência 10, igreja 100 na administração
    private Church $igrejaB;   // superintendência 10, igreja 101
    private Church $igrejaC;   // superintendência 20, igreja 200
    private Church $semVinculo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['services.data_sync.email' => self::SYNC_EMAIL]);

        $stateId = DB::table('states')->insertGetId(['code' => 'RN', 'name' => 'Rio Grande do Norte']);
        $cityId = DB::table('cities')->insertGetId(['ibge_id' => 1, 'state_id' => $stateId, 'name' => 'Natal']);
        $this->siteId = DB::table('event_sites')->insertGetId([
            'name' => 'Sítio', 'state_id' => $stateId, 'city_id' => $cityId, 'address' => 'Rua A',
        ]);

        foreach ([[100, 10], [101, 10], [200, 20]] as [$id, $super]) {
            AdministrationChurch::create(['id' => $id, 'name' => "Igreja {$id}", 'superintendence_id' => $super, 'superintendence_name' => "S{$super}", 'active' => true]);
        }
        $nova = fn ($nome, $adm) => Church::create(['name' => $nome, 'state_id' => $stateId, 'city_id' => $cityId, 'administration_system_id' => $adm]);
        $this->igrejaA = $nova('A', 100);
        $this->igrejaB = $nova('B', 101);
        $this->igrejaC = $nova('C', 200);
        $this->semVinculo = $nova('Sem vínculo', null);
    }

    private function anfitriao(int $usuarioId, string $nivel, ?int $super = null, ?int $igreja = null, ?string $email = null, bool $ativo = true): AdministrationHost
    {
        return AdministrationHost::create([
            'grant_key' => "{$usuarioId}:{$nivel}:" . ($igreja ?? $super ?? ''),
            'administration_user_id' => $usuarioId,
            'name' => "Anfitrião {$usuarioId}",
            'email' => $email ?? "anfitriao{$usuarioId}@iea.test",
            'level' => $nivel,
            'administration_superintendence_id' => $super,
            'administration_church_id' => $igreja,
            'active' => $ativo,
        ]);
    }

    private function conta(int $usuarioId): User
    {
        return User::factory()->create(['administration_user_id' => $usuarioId, 'email' => "anfitriao{$usuarioId}@iea.test"]);
    }

    private function evento(Church $igreja, string $scope, ?User $dono = null): Event
    {
        return Event::create([
            'name' => "{$scope} em {$igreja->name} " . uniqid(), 'scope' => $scope,
            'start_date' => '2026-10-01', 'end_date' => '2026-10-02',
            'church_id' => $igreja->id, 'event_site_id' => $this->siteId,
            'owner_id' => ($dono ?? User::factory()->create())->id,
        ]);
    }

    private function visiveis(User $user): array
    {
        return Event::visibleTo($user)->orderBy('id')->pluck('id')->all();
    }

    public function test_cadastro_so_para_anfitriao_ativo(): void
    {
        $this->anfitriao(7, 'igreja', 10, 100, 'Pastor@IEA.test');
        $this->anfitriao(8, 'igreja', 10, 100, 'revogado@iea.test', ativo: false);
        $criar = fn ($email) => app(CreateNewUser::class)->create([
            'name' => 'Fulano', 'email' => $email, 'password' => 'Senha-forte-123', 'password_confirmation' => 'Senha-forte-123',
        ]);

        foreach (['estranho@iea.test', 'revogado@iea.test'] as $email) {
            try {
                $criar($email);
                $this->fail("{$email} não devia criar conta");
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertSame([CreateNewUser::NOT_A_HOST], $e->errors()['email'] ?? $e->errors());
            }
        }

        $user = $criar('pastor@iea.test');
        $this->assertSame(7, $user->administration_user_id);
        $this->assertSame('pastor@iea.test', $user->email);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $criar('PASTOR@iea.test');
    }

    public function test_so_o_usuario_do_data_sync_grava_a_replica(): void
    {
        $payload = [
            'id' => '5:igreja:100', 'usuario_id' => 5, 'nome' => 'Fulano', 'email' => 'Fulano@IEA.test',
            'nivel' => 'igreja', 'superintendencia_id' => 10, 'igreja_id' => 100, 'ativo' => true,
            'sincronizado_em' => '2026-09-29 10:00:00',
        ];

        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/anfitrioes-sync', $payload)->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['email' => self::SYNC_EMAIL]));
        $this->postJson('/api/anfitrioes-sync', $payload)->assertOk();
        $this->postJson('/api/igrejas-sync', [
            'id' => 300, 'nome' => 'Nova', 'superintendencia_id' => 30, 'superintendencia' => 'S30', 'ativo' => true,
            'sincronizado_em' => '2026-09-29 10:00:01',
        ])->assertOk();

        $host = AdministrationHost::where('grant_key', '5:igreja:100')->first();
        $this->assertSame('fulano@iea.test', $host->email);
        $this->assertSame(100, (int) $host->administration_church_id);
        $this->assertSame('S30', AdministrationChurch::find(300)->superintendence_name);

        $this->getJson('/api/sync')->assertOk()
            ->assertJsonPath('data.anfitrioes', '2026-09-29 10:00:00')
            ->assertJsonPath('data.igrejas', '2026-09-29 10:00:01');
    }

    public function test_email_trocado_na_administracao_troca_o_login_e_conta_antiga_e_vinculada(): void
    {
        Sanctum::actingAs(User::factory()->create(['email' => self::SYNC_EMAIL]));
        $antiga = User::factory()->create(['email' => 'Antigo@iea.test']);
        $payload = [
            'id' => '9:nacional', 'usuario_id' => 9, 'nome' => 'Fulano', 'email' => 'antigo@iea.test',
            'nivel' => 'nacional', 'superintendencia_id' => null, 'igreja_id' => null, 'ativo' => true,
            'sincronizado_em' => '2026-09-29 10:00:00',
        ];

        $this->postJson('/api/anfitrioes-sync', $payload)->assertOk();
        $this->assertSame(9, $antiga->fresh()->administration_user_id);

        $this->postJson('/api/anfitrioes-sync', ['email' => 'novo@iea.test'] + $payload)->assertOk();
        $this->assertSame('novo@iea.test', $antiga->fresh()->email);

        // e-mail já usado por outra conta: não troca (senão quebraria o unique)
        User::factory()->create(['email' => 'ocupado@iea.test']);
        $this->postJson('/api/anfitrioes-sync', ['email' => 'ocupado@iea.test'] + $payload)->assertOk();
        $this->assertSame('novo@iea.test', $antiga->fresh()->email);
    }

    public function test_jurisdicao_de_igreja_superintendencia_e_nacional(): void
    {
        $localA = $this->evento($this->igrejaA, 'igreja');
        $regionalA = $this->evento($this->igrejaA, 'superintendencia');
        $nacionalA = $this->evento($this->igrejaA, 'nacional');
        $localB = $this->evento($this->igrejaB, 'igreja');
        $localC = $this->evento($this->igrejaC, 'igreja');
        $localSemVinculo = $this->evento($this->semVinculo, 'igreja');

        $this->anfitriao(1, 'igreja', 10, 100);
        $igreja = $this->conta(1);
        $this->assertSame([$localA->id], $this->visiveis($igreja));
        $this->assertSame(['igreja'], $igreja->hostJurisdiction()->allowedScopes());
        $this->assertTrue($localA->isAllowedUser($igreja));
        $this->assertFalse($regionalA->isAllowedUser($igreja));

        $this->anfitriao(2, 'superintendencia', 10);
        $regional = $this->conta(2);
        $this->assertSame([$localA->id, $regionalA->id, $localB->id], $this->visiveis($regional));
        $this->assertSame(['superintendencia', 'igreja'], $regional->hostJurisdiction()->allowedScopes());
        $this->assertFalse($regional->hostJurisdiction()->canManage('nacional', $this->igrejaA->id));
        $this->assertFalse($regional->hostJurisdiction()->canManage('igreja', $this->igrejaC->id));

        $this->anfitriao(3, 'nacional');
        $nacional = $this->conta(3);
        $this->assertSame(
            [$localA->id, $regionalA->id, $nacionalA->id, $localB->id, $localC->id, $localSemVinculo->id],
            $this->visiveis($nacional),
        );
    }

    public function test_dono_continua_vendo_o_proprio_evento_e_conta_antiga_nao_muda(): void
    {
        $this->anfitriao(1, 'igreja', 10, 100);
        $igreja = $this->conta(1);
        $proprioFora = $this->evento($this->igrejaC, 'igreja', $igreja);
        $this->assertSame([$proprioFora->id], $this->visiveis($igreja));

        $antiga = User::factory()->create();
        $dela = $this->evento($this->igrejaC, 'nacional', $antiga);
        $this->evento($this->igrejaA, 'igreja');
        $this->assertSame([$dela->id], $this->visiveis($antiga));
        $this->assertTrue($antiga->hostJurisdiction()->canManage('nacional', $this->igrejaC->id));
    }

    public function test_anfitriao_revogado_perde_o_acesso(): void
    {
        $this->anfitriao(4, 'igreja', 10, 100, ativo: false);
        $revogado = $this->conta(4);

        $this->actingAs($revogado)->get('/dashboard')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => EnsureHostAccess::MESSAGE]);
        $this->assertGuest();

        // conta sem nenhum evento visível abre o painel (antes quebrava com eventId vazio)
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertSee('Nenhum evento por aqui ainda');
    }

    public function test_telas_de_eventos_e_igrejas_abrem_para_anfitriao_de_igreja(): void
    {
        $this->anfitriao(1, 'igreja', 10, 100);
        $igreja = $this->conta(1);
        $this->evento($this->igrejaA, 'igreja');

        $this->actingAs($igreja)->get('/events')->assertOk();
        $this->actingAs($igreja)->get('/forms/churches')->assertOk();
        $this->actingAs($igreja)->get('/dashboard')->assertOk();
    }

    public function test_formulario_de_evento_barra_fora_da_jurisdicao(): void
    {
        $this->anfitriao(1, 'igreja', 10, 100);
        $this->actingAs($this->conta(1));

        $form = \Livewire\Livewire::test('pages::events.event-form')->call('handleCreatingRequest');
        $this->assertSame(['igreja' => 'Igreja'], $form->instance()->scopeOptions());

        $form->set('form.name', 'Culto regional')
            ->set('form.scope', 'superintendencia')
            ->set('form.start_date', '2026-10-01')->set('form.end_date', '2026-10-01')
            ->set('form.church_id', $this->igrejaA->id)->set('form.event_site_id', $this->siteId)
            ->call('save');
        $this->assertFalse(Event::where('name', 'Culto regional')->exists());

        $form->set('form.scope', 'igreja')->call('save');
        $this->assertTrue(Event::where('name', 'Culto regional')->exists());
    }
}
