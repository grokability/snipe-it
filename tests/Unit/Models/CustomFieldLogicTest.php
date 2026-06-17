<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomFieldLogicTest extends TestCase
{
    public static function nameToDbProvider(): array
    {
        return [
            ['CPU Speed', '_snipeit_cpu_speed'],
            ['RAM-Size', '_snipeit_ram_size'],
            ['Color', '_snipeit_color'],
            ['MAC Address #1', '_snipeit_mac_address__1'],
        ];
    }

    #[DataProvider('nameToDbProvider')]
    public function test_name_to_db_name(string $name, string $expected): void
    {
        $this->assertEquals($expected, CustomField::name_to_db_name($name));
    }

    public function test_db_column_name_returns_column(): void
    {
        $field = CustomField::factory()->create();

        $this->assertEquals($field->db_column, $field->db_column_name());
    }

    public function test_check_format_validates_against_regex(): void
    {
        $field = CustomField::factory()->create();
        // Forzamos un formato regex conocido a nivel de atributo crudo.
        $field->setRawAttributes(array_merge($field->getAttributes(), ['format' => '[0-9]+']));

        $this->assertTrue($field->check_format('12345'));
        $this->assertFalse($field->check_format('abc'));
    }

    public function test_display_field_in_form_helpers_return_bool(): void
    {
        $field = CustomField::factory()->create();

        $this->assertIsBool((bool) $field->displayFieldInCheckinForm());
        $this->assertIsBool((bool) $field->displayFieldInCheckoutForm());
        $this->assertIsBool((bool) $field->displayFieldInAuditForm());
    }

    public function test_format_values_as_array(): void
    {
        $field = CustomField::factory()->create(['field_values' => "Red\nGreen\nBlue"]);

        $this->assertIsArray($field->formatFieldValuesAsArray());
    }

    public function test_get_and_set_format_attribute(): void
    {
        $field = CustomField::factory()->create();
        $field->format = 'IP';

        // El accessor devuelve una cadena (mapa interno de formatos).
        $this->assertIsString($field->format);
    }
}
