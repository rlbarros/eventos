<?php

namespace Tests\Unit;

use App\Services\Allocation\RoomDistributor;
use PHPUnit\Framework\TestCase;

class RoomDistributorTest extends TestCase
{
    private function participant(int $id, int $church, int $type = 1, array $relatives = []): array
    {
        return ['id' => $id, 'person_id' => 100 + $id, 'room_type_id' => $type, 'church_id' => $church, 'relatives' => $relatives];
    }

    private function room(int $id, int $free, int $type = 1, array $personIds = [], array $churchIds = []): array
    {
        return ['id' => $id, 'room_type_id' => $type, 'free' => $free, 'person_ids' => $personIds, 'church_ids' => $churchIds];
    }

    private function distribute(array $participants, array $rooms): array
    {
        return (new RoomDistributor())->distribute($participants, $rooms);
    }

    public function test_nunca_passa_da_capacidade_e_sobra_quem_nao_cabe(): void
    {
        $participants = array_map(fn($i) => $this->participant($i, 1), range(1, 5));
        $result = $this->distribute($participants, [$this->room(10, 2), $this->room(11, 2)]);

        $this->assertCount(4, $result['assignments']);
        $this->assertSame([10 => 2, 11 => 2], array_count_values($result['assignments']));
        $this->assertCount(1, $result['unplaced']);
    }

    public function test_respeita_o_tipo_de_quarto(): void
    {
        $result = $this->distribute(
            [$this->participant(1, 1, type: 1), $this->participant(2, 1, type: 2)],
            [$this->room(10, 4, type: 1), $this->room(20, 4, type: 2)]
        );

        $this->assertSame([1 => 10, 2 => 20], $result['assignments']);
    }

    public function test_sem_quarto_do_tipo_fica_de_fora(): void
    {
        $result = $this->distribute([$this->participant(1, 1, type: 3)], [$this->room(10, 4, type: 1)]);

        $this->assertSame([], $result['assignments']);
        $this->assertSame([1], $result['unplaced']);
    }

    public function test_mesma_igreja_fica_junta(): void
    {
        // intercalados para garantir que a ordem de entrada não decide
        $participants = [
            $this->participant(1, 1), $this->participant(2, 2), $this->participant(3, 1),
            $this->participant(4, 2), $this->participant(5, 1), $this->participant(6, 2),
        ];
        $result = $this->distribute($participants, [$this->room(10, 3), $this->room(11, 3)]);

        $a = $result['assignments'];
        $this->assertSame($a[1], $a[3]);
        $this->assertSame($a[1], $a[5]);
        $this->assertSame($a[2], $a[4]);
        $this->assertSame($a[2], $a[6]);
        $this->assertNotSame($a[1], $a[2]);
    }

    public function test_familia_fica_junta_mesmo_entre_igrejas_diferentes(): void
    {
        // 1 é casado com 2 (pessoa 102), que é de outra igreja; 3 é filho de 1
        $participants = [
            $this->participant(1, 1, relatives: [102]),
            $this->participant(2, 2),
            $this->participant(3, 1, relatives: [101]),
            $this->participant(4, 1),
        ];
        $result = $this->distribute($participants, [$this->room(10, 3), $this->room(11, 3)]);

        $a = $result['assignments'];
        $this->assertSame($a[1], $a[2]);
        $this->assertSame($a[1], $a[3]);
    }

    public function test_prefere_quarto_onde_ja_esta_um_parente(): void
    {
        // o cônjuge (pessoa 500) já está no quarto 11
        $result = $this->distribute(
            [$this->participant(1, 1, relatives: [500])],
            [$this->room(10, 4), $this->room(11, 3, personIds: [500], churchIds: [9])]
        );

        $this->assertSame([1 => 11], $result['assignments']);
    }

    public function test_completa_quarto_da_mesma_igreja_antes_de_abrir_outro(): void
    {
        $result = $this->distribute(
            [$this->participant(1, 7)],
            [$this->room(10, 4), $this->room(11, 2, personIds: [900, 901], churchIds: [7, 7])]
        );

        $this->assertSame([1 => 11], $result['assignments']);
    }

    public function test_nao_usa_quarto_cheio(): void
    {
        $result = $this->distribute(
            [$this->participant(1, 7)],
            [$this->room(10, 0, personIds: [900], churchIds: [7]), $this->room(11, 2)]
        );

        $this->assertSame([1 => 11], $result['assignments']);
    }

    public function test_familia_maior_que_o_quarto_e_dividida_no_minimo_de_quartos(): void
    {
        // família de 5 ligada pelo pai (pessoa 101) em quartos de 3
        $participants = [$this->participant(1, 1)];
        foreach (range(2, 5) as $i) {
            $participants[] = $this->participant($i, 1, relatives: [101]);
        }
        $result = $this->distribute($participants, [$this->room(10, 3), $this->room(11, 3), $this->room(12, 3)]);

        $this->assertCount(5, $result['assignments']);
        $this->assertCount(2, array_unique($result['assignments']));
    }

    public function test_prefere_familia_inteira_em_quarto_vazio_a_dividir(): void
    {
        // casal não cabe no quarto 10 (1 leito, mesma igreja), mas cabe inteiro no 11
        $result = $this->distribute(
            [$this->participant(1, 1, relatives: [102]), $this->participant(2, 1)],
            [$this->room(10, 1, personIds: [900], churchIds: [1]), $this->room(11, 2)]
        );

        $this->assertSame([1 => 11, 2 => 11], $result['assignments']);
    }

    public function test_resultado_e_deterministico(): void
    {
        $participants = [];
        foreach (range(1, 20) as $i) {
            $participants[] = $this->participant($i, $i % 3 + 1, relatives: $i % 5 === 0 ? [100 + $i - 1] : []);
        }
        $rooms = [$this->room(10, 4), $this->room(11, 4), $this->room(12, 4), $this->room(13, 4), $this->room(14, 3)];

        $first = $this->distribute($participants, $rooms);
        $second = $this->distribute(array_reverse($participants), array_reverse($rooms));

        ksort($first['assignments']);
        ksort($second['assignments']);
        $this->assertSame($first, $second);
    }
}
