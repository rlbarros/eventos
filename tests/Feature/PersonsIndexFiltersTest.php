<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

// Filtros adicionais do componente genérico de lista (aqui: igreja na tela de Pessoas).
class PersonsIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    private Church $igrejaA;
    private Church $igrejaB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $stateId = DB::table('states')->insertGetId(['code' => 'RN', 'name' => 'Rio Grande do Norte']);
        $cityId = DB::table('cities')->insertGetId(['ibge_id' => 1, 'state_id' => $stateId, 'name' => 'Natal']);
        $nova = fn ($nome) => Church::create(['name' => $nome, 'state_id' => $stateId, 'city_id' => $cityId]);
        $this->igrejaA = $nova('Igreja A');
        $this->igrejaB = $nova('Igreja B');

        Person::create(['church_id' => $this->igrejaA->id, 'name' => 'Ana da A', 'function' => 'Membro']);
        Person::create(['church_id' => $this->igrejaB->id, 'name' => 'Bruno da B', 'function' => 'Membro']);

        $this->actingAs(User::factory()->create());
    }

    public function test_sem_filtro_lista_todas_as_pessoas(): void
    {
        Livewire::test('pages::forms.persons.persons-index')
            ->assertSee('Todas as igrejas')
            ->assertSee('Ana da A')
            ->assertSee('Bruno da B');
    }

    public function test_filtra_pessoas_pela_igreja(): void
    {
        Livewire::test('pages::forms.persons.persons-index')
            ->set('filters.church_id', (string) $this->igrejaA->id)
            ->assertSee('Ana da A')
            ->assertDontSee('Bruno da B');
    }

    public function test_opcao_todas_as_igrejas_remove_o_filtro(): void
    {
        $tela = Livewire::test('pages::forms.persons.persons-index')
            ->set('filters.church_id', (string) $this->igrejaA->id);
        $this->assertSame(['Ana da A'], $tela->instance()->index()->pluck('name')->all());

        $tela->set('filters.church_id', '');
        $this->assertEqualsCanonicalizing(['Ana da A', 'Bruno da B'], $tela->instance()->index()->pluck('name')->all());
    }
}
