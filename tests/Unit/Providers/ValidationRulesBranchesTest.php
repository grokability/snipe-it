<?php

namespace Tests\Unit\Providers;

use App\Models\CustomField;
use App\Models\Location;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Cubre las ramas de error / poco recorridas de las reglas custom registradas
 * en App\Providers\ValidationServiceProvider (non_circular, valid_regex,
 * is_unique_across_company_and_location, checkboxes, etc.).
 */
class ValidationRulesBranchesTest extends TestCase
{
    public function test_non_circular_throws_without_enough_params(): void
    {
        $v = Validator::make(['parent_id' => 1], ['parent_id' => 'non_circular:locations']);

        $threw = false;
        try {
            $v->passes();
        } catch (\Throwable $e) {
            $threw = true;
        }

        $this->assertTrue($threw);
    }

    public function test_non_circular_fails_when_depth_exceeded(): void
    {
        $a = Location::factory()->create();
        $b = Location::factory()->create();

        // depth 0 fuerza el guard de bucle infinito (--depth < 0) sin igualdad de ids
        $v = Validator::make(
            ['id' => $a->id, 'parent_id' => $b->id],
            ['parent_id' => 'non_circular:locations,id,0']
        );

        $this->assertFalse($v->passes());
    }

    public function test_non_circular_fails_on_self_reference(): void
    {
        $a = Location::factory()->create();

        $v = Validator::make(
            ['id' => $a->id, 'parent_id' => $a->id],
            ['parent_id' => 'non_circular:locations,id']
        );

        $this->assertFalse($v->passes());
    }

    public function test_valid_regex_catches_invalid_pattern(): void
    {
        // patrón mal formado -> preg_match dispara warning -> ErrorException -> catch -> false
        $v = Validator::make(['x' => 'regex:/(/'], ['x' => 'valid_regex']);

        $this->assertFalse($v->passes());
    }

    public function test_valid_regex_rejects_non_regex_prefixed(): void
    {
        $v = Validator::make(['x' => 'not-a-regex'], ['x' => 'valid_regex']);

        $this->assertFalse($v->passes());
    }

    public function test_valid_regex_accepts_valid_pattern(): void
    {
        $v = Validator::make(['x' => 'regex:/[a-z]+/'], ['x' => 'valid_regex']);

        $this->assertTrue($v->passes());
    }

    public function test_is_unique_across_company_and_location_with_id(): void
    {
        // 'id' presente y no nulo entra a la rama where('id','!=',...)
        $v = Validator::make(
            ['id' => 999999, 'name' => 'Departamento Inexistente XYZ'],
            ['name' => 'is_unique_across_company_and_location:departments']
        );

        $this->assertTrue($v->passes());
    }

    public function test_checkboxes_rejects_invalid_array_option(): void
    {
        $cf = CustomField::factory()->testCheckbox()->create();
        $col = $cf->db_column;

        $v = Validator::make([$col => ['Bogus']], [$col => 'checkboxes']);

        $this->assertFalse($v->passes());
    }

    public function test_checkboxes_accepts_valid_array_option(): void
    {
        $cf = CustomField::factory()->testCheckbox()->create();
        $col = $cf->db_column;

        $v = Validator::make([$col => ['One']], [$col => 'checkboxes']);

        $this->assertTrue($v->passes());
    }

    public function test_disallow_same_pwd_as_user_fields(): void
    {
        $fail = Validator::make(
            ['username' => 'sameval', 'password' => 'sameval'],
            ['password' => 'disallow_same_pwd_as_user_fields']
        );
        $this->assertFalse($fail->passes());

        $ok = Validator::make(
            ['username' => 'alice', 'email' => 'a@b.com', 'first_name' => 'Al', 'last_name' => 'Ice', 'password' => 'distinto'],
            ['password' => 'disallow_same_pwd_as_user_fields']
        );
        $this->assertTrue($ok->passes());
    }

    public function test_letters_numbers_symbols_case_diff(): void
    {
        $this->assertTrue(Validator::make(['p' => 'abc'], ['p' => 'letters'])->passes());
        $this->assertFalse(Validator::make(['p' => '123'], ['p' => 'letters'])->passes());

        $this->assertTrue(Validator::make(['p' => 'a1c'], ['p' => 'numbers'])->passes());
        $this->assertFalse(Validator::make(['p' => 'abc'], ['p' => 'numbers'])->passes());

        $this->assertTrue(Validator::make(['p' => 'ab!c'], ['p' => 'symbols'])->passes());
        $this->assertFalse(Validator::make(['p' => 'abc'], ['p' => 'symbols'])->passes());

        $this->assertTrue(Validator::make(['p' => 'aB'], ['p' => 'case_diff'])->passes());
        $this->assertFalse(Validator::make(['p' => 'ab'], ['p' => 'case_diff'])->passes());
    }

    public function test_fmcs_location_passes_when_disabled(): void
    {
        // Sin FMCS activo, la regla pasa siempre (rama por defecto)
        $loc = Location::factory()->create();
        $v = Validator::make(
            ['company_id' => 1, 'location_id' => $loc->id],
            ['location_id' => 'fmcs_location']
        );

        $this->assertTrue($v->passes());
    }
}
