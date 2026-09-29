<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidationLangTest extends TestCase
{
    public function test_traducoes_cobrem_regras_usadas_nos_formularios(): void
    {
        $lang = require __DIR__ . '/../../lang/pt-BR/validation.php';

        foreach (['after', 'after_or_equal', 'required', 'date', 'unique', 'exists', 'email'] as $rule) {
            $this->assertArrayHasKey($rule, $lang);
        }
        $this->assertSame('data de fim', $lang['attributes']['form.end_date']);
    }
}
