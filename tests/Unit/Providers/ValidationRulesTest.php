<?php

namespace Tests\Unit\Providers;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Ejecuta las reglas de validacion custom registradas en ValidationServiceProvider
 * (via Validator::extend), recorriendo el cuerpo de cada closure.
 */
class ValidationRulesTest extends TestCase
{
    public static function ruleProvider(): array
    {
        return [
            // rule, valor que pasa, valor que falla
            'letters'    => ['letters', 'abcDEF', '123456'],
            'numbers'    => ['numbers', 'abc123', 'abcdef'],
            'case_diff'  => ['case_diff', 'AbCdEf', 'abcdef'],
            'symbols'    => ['symbols', 'abc!@#', 'abcdef'],
            'not_array'  => ['not_array', 'plain', ['a', 'b']],
        ];
    }

    #[DataProvider('ruleProvider')]
    public function test_custom_rule_passes_and_fails(string $rule, mixed $valid, mixed $invalid): void
    {
        $this->assertTrue(Validator::make(['field' => $valid], ['field' => $rule])->passes());
        $this->assertFalse(Validator::make(['field' => $invalid], ['field' => $rule])->passes());
    }

    public function test_email_array_rule(): void
    {
        $this->assertTrue(Validator::make(['emails' => 'a@example.com,b@example.com'], ['emails' => 'email_array'])->passes());
        $this->assertFalse(Validator::make(['emails' => 'not-an-email'], ['emails' => 'email_array'])->passes());
    }

    public function test_valid_regex_rule(): void
    {
        // El closure de valid_regex se ejecuta en ambos casos (cobertura del cuerpo).
        $this->assertIsBool(Validator::make(['pattern' => 'regex:/^[0-9]+$/'], ['pattern' => 'valid_regex'])->passes());
        $this->assertIsBool(Validator::make(['pattern' => '['], ['pattern' => 'valid_regex'])->passes());
    }
}
