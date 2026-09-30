<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Event;
use App\Models\EventBatch;
use App\Models\EventFee;
use App\Models\EventParticipantAllocation;
use App\Models\Person;
use App\Services\Pricing\OccupancyPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Reserva com pagador: cada pessoa paga a taxa da sua categoria na ocupação do quarto, o pagador
// responde pelo total.
class ReservationPricingTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;
    private EventBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('states')->insert(['id' => 1, 'code' => 'SP', 'name' => 'São Paulo']);
        DB::table('cities')->insert(['id' => 1, 'ibge_id' => 1, 'state_id' => 1, 'name' => 'Sumaré']);
        DB::table('event_sites')->insert(['id' => 1, 'name' => 'Estância Árvore da Vida', 'state_id' => 1, 'city_id' => 1, 'address' => 'Rua']);
        DB::table('event_site_room_types')->insert(['id' => 1, 'event_site_id' => 1, 'name' => 'Casas A,B,C e Brasília', 'type' => 'Apartamento', 'beds' => 5, 'amenities' => 'Incluso roupa de cama e banho']);

        $church = Church::create(['administration_system_id' => 7, 'name' => 'Igreja', 'state_id' => 1, 'city_id' => 1]);
        $this->event = Event::create([
            'name' => 'Congresso', 'scope' => 'nacional', 'start_date' => '2026-10-10', 'end_date' => '2026-10-12',
            'church_id' => $church->id, 'event_site_id' => 1, 'children_age' => 10,
        ]);
        $this->batch = EventBatch::create(['event_id' => $this->event->id, 'batch' => 1, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30']);

        foreach ([
            ['Integral', 2, 2, 1160], ['Integral', 3, 3, 994], ['Infantil', 3, 3, 782],
            ['Integral', 4, 5, 918], ['Infantil', 4, 5, 706], ['Integral', 1, 1, 1698],
        ] as [$category, $min, $max, $value]) {
            EventFee::create([
                'event_id' => $this->event->id, 'event_site_room_type_id' => 1, 'event_batch_id' => $this->batch->id,
                'category' => $category, 'min_occupants' => $min, 'max_occupants' => $max, 'fee' => $value,
            ]);
        }
    }

    private function join(string $name, string $birth, ?Person $payer = null): EventParticipantAllocation
    {
        $person = Person::create(['name' => $name, 'birth_date' => $birth, 'church_id' => Church::first()->id]);

        return EventParticipantAllocation::create([
            'event_id' => $this->event->id,
            'person_id' => $person->id,
            'payer_person_id' => $payer?->id,
            'event_site_room_type_id' => 1,
        ]);
    }

    public function test_pessoa_sozinha_paga_a_taxa_de_uma_pessoa(): void
    {
        $payer = $this->join('Maria', '1980-01-01');
        $pricing = new OccupancyPricing();

        $this->assertSame(1, $pricing->occupancy($payer));
        $this->assertSame(1698.0, $pricing->reservationTotal($payer, EventFee::all(), $this->event));
    }

    public function test_familia_de_tres_com_uma_crianca_paga_adulto_e_infantil(): void
    {
        $payer = $this->join('Maria', '1980-01-01');
        $spouse = $this->join('João', '1979-01-01', $payer->person);
        $child = $this->join('Ana', '2020-01-01', $payer->person);
        $pricing = new OccupancyPricing();

        $this->assertSame(3, $pricing->occupancy($child));
        $this->assertTrue($pricing->payerAllocation($child)->is($payer));
        $this->assertTrue($pricing->isPayer($payer));
        $this->assertFalse($pricing->isPayer($spouse));
        // 2 adultos a 994 + 1 criança a 782
        $this->assertSame(2 * 994.0 + 782.0, $pricing->reservationTotal($payer, EventFee::all(), $this->event));
    }

    public function test_cinco_pessoas_usam_a_faixa_de_quatro_a_cinco(): void
    {
        $payer = $this->join('Maria', '1980-01-01');
        foreach (['B', 'C', 'D'] as $name) {
            $this->join($name, '1985-01-01', $payer->person);
        }
        $this->join('Criança', '2019-01-01', $payer->person);

        $this->assertSame(5, (new OccupancyPricing())->occupancy($payer));
        $this->assertSame(4 * 918.0 + 706.0, (new OccupancyPricing())->reservationTotal($payer, EventFee::all(), $this->event));
    }

    public function test_excluir_o_pagador_devolve_cada_um_para_a_propria_taxa(): void
    {
        $payer = $this->join('Maria', '1980-01-01');
        $member = $this->join('João', '1979-01-01', $payer->person);

        $payer->delete();

        $this->assertNull($member->fresh()->payer_person_id);
        $this->assertSame(1, (new OccupancyPricing())->occupancy($member->fresh()));
    }
}
