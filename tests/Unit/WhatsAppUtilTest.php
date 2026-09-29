<?php

namespace Tests\Unit;

use App\Utils\WhatsAppUtil;
use PHPUnit\Framework\TestCase;

class WhatsAppUtilTest extends TestCase
{
    public function test_normaliza_celular_com_mascara(): void
    {
        $this->assertSame('5511987654321', WhatsAppUtil::normalizePhone('(11) 98765-4321'));
    }

    public function test_mantem_numero_ja_com_ddi(): void
    {
        $this->assertSame('5511987654321', WhatsAppUtil::normalizePhone('+55 11 98765-4321'));
    }

    public function test_aceita_fixo(): void
    {
        $this->assertSame('551133334444', WhatsAppUtil::normalizePhone('(11) 3333-4444'));
    }

    public function test_rejeita_vazio_e_invalido(): void
    {
        $this->assertNull(WhatsAppUtil::normalizePhone(null));
        $this->assertNull(WhatsAppUtil::normalizePhone(''));
        $this->assertNull(WhatsAppUtil::normalizePhone('12345'));
    }

    public function test_link_codifica_o_texto(): void
    {
        $this->assertSame(
            'https://wa.me/5511987654321?text=Ol%C3%A1%20mundo%0Alinha',
            WhatsAppUtil::link('(11) 98765-4321', "Olá mundo\nlinha")
        );
        $this->assertNull(WhatsAppUtil::link('abc', 'x'));
    }
}
