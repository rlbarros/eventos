<?php

namespace Tests\Unit;

use App\Services\Pricing\OccupancyPricing;
use PHPUnit\Framework\TestCase;

class OccupancyPricingTest extends TestCase
{
    private function fee(string $category, ?int $min, ?int $max, float $value): object
    {
        return (object) ['category' => $category, 'min_occupants' => $min, 'max_occupants' => $max, 'fee' => $value];
    }

    /** Tabela do hotel do Rodrigo (2026-09-30), por pessoa. */
    private function hotel(): array
    {
        return [
            $this->fee('Integral', 1, 1, 1698),
            $this->fee('Integral', 2, 2, 1199),
            $this->fee('Integral', 3, 3, 1055),
            $this->fee('Integral', 4, 4, 987),
            $this->fee('Integral', 5, 5, 978),
        ];
    }

    /** Tabela das casas A, B e C: adulto e criança a partir de 3 pessoas. */
    private function casas(): array
    {
        return [
            $this->fee('Integral', 2, 2, 1160),
            $this->fee('Integral', 3, 3, 994),
            $this->fee('Infantil', 3, 3, 782),
            $this->fee('Integral', 4, 5, 918),
            $this->fee('Infantil', 4, 5, 706),
        ];
    }

    public function test_preco_pela_ocupacao_do_quarto(): void
    {
        $pricing = new OccupancyPricing();

        $this->assertSame(1698.0, $pricing->pick($this->hotel(), 'Integral', 1)->fee);
        $this->assertSame(1199.0, $pricing->pick($this->hotel(), 'Integral', 2)->fee);
        $this->assertSame(978.0, $pricing->pick($this->hotel(), 'Integral', 5)->fee);
        $this->assertNull($pricing->pick($this->hotel(), 'Integral', 6));
    }

    public function test_crianca_paga_infantil_quando_existe_e_integral_quando_nao(): void
    {
        $pricing = new OccupancyPricing();

        $this->assertSame(782.0, $pricing->pick($this->casas(), 'Infantil', 3)->fee);
        $this->assertSame(706.0, $pricing->pick($this->casas(), 'Infantil', 5)->fee);
        $this->assertSame(1160.0, $pricing->pick($this->casas(), 'Infantil', 2)->fee);
        $this->assertSame(994.0, $pricing->pick($this->casas(), 'Integral', 3)->fee);
    }

    public function test_taxa_sem_faixa_vale_para_qualquer_ocupacao_mas_perde_para_a_especifica(): void
    {
        $pricing = new OccupancyPricing();
        $fees = [$this->fee('Integral', null, null, 500), $this->fee('Integral', 2, 3, 400)];

        $this->assertSame(500.0, $pricing->pick($fees, 'Integral', 1)->fee);
        $this->assertSame(400.0, $pricing->pick($fees, 'Integral', 2)->fee);
        $this->assertSame(500.0, $pricing->pick($fees, 'Integral', 4)->fee);
    }

    public function test_faixa_mais_estreita_vence(): void
    {
        $pricing = new OccupancyPricing();
        $fees = [$this->fee('Integral', 1, 5, 900), $this->fee('Integral', 2, 2, 800)];

        $this->assertSame(800.0, $pricing->pick($fees, 'Integral', 2)->fee);
        $this->assertSame(900.0, $pricing->pick($fees, 'Integral', 3)->fee);
    }
}
