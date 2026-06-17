<?php

namespace Tests\Unit\Models;

use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

class MaintenanceLogicTest extends TestCase
{
    public function test_get_improvement_options_is_array(): void
    {
        $this->assertIsArray(Maintenance::getImprovementOptions());
    }

    public function test_set_is_warranty_attribute_casts(): void
    {
        $m = new Maintenance;
        $m->is_warranty = '1';

        $this->assertEquals(1, $m->getAttributes()['is_warranty']);
    }

    public function test_set_cost_attribute_parses_currency(): void
    {
        $m = new Maintenance;
        $m->cost = '1,234.56';

        $this->assertIsNumeric($m->getAttributes()['cost']);
    }

    public function test_set_notes_attribute(): void
    {
        $m = new Maintenance;
        $m->notes = '  some notes  ';

        $this->assertArrayHasKey('notes', $m->getAttributes());
    }

    public function test_set_completion_date_attribute_handles_empty(): void
    {
        $m = new Maintenance;
        $m->completion_date = '';

        $this->assertNull($m->getAttributes()['completion_date']);
    }

    public function test_is_deletable_returns_bool(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $m = Maintenance::factory()->create();

        $this->assertIsBool($m->isDeletable());
    }

    public function test_display_name_attribute(): void
    {
        $m = Maintenance::factory()->create(['name' => 'Repair']);

        $this->assertEquals('Repair', $m->display_name);
    }
}
