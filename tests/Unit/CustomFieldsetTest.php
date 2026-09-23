<?php

namespace Tests\Unit;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Tests\TestCase;

class CustomFieldsetTest extends TestCase
{
    public function test_grouped_fields_returns_all_fields_ungrouped_by_default()
    {
        $fieldset = CustomFieldset::factory()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $ram = CustomField::query()->where('name', 'RAM')->first() ?? CustomField::factory()->ram()->create();
        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false]);
        $fieldset->fields()->attach($ram, ['order' => 2, 'required' => false]);

        $grouped = $fieldset->groupedFields();

        $this->assertCount(1, $grouped);
        $this->assertSame('', $grouped->keys()->first());
        $this->assertEquals(['MAC Address', 'RAM'], $grouped->first()->pluck('name')->all());
    }

    public function test_grouped_fields_groups_by_pivot_group_preserving_order()
    {
        $fieldset = CustomFieldset::factory()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $ram = CustomField::query()->where('name', 'RAM')->first() ?? CustomField::factory()->ram()->create();
        $cpu = CustomField::query()->where('name', 'CPU')->first() ?? CustomField::factory()->cpu()->create();

        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false, 'group' => 'Network']);
        $fieldset->fields()->attach($ram, ['order' => 2, 'required' => false, 'group' => 'Resources']);
        $fieldset->fields()->attach($cpu, ['order' => 3, 'required' => false, 'group' => 'Resources']);

        $grouped = $fieldset->groupedFields();

        $this->assertEquals(['Network', 'Resources'], $grouped->keys()->all());
        $this->assertEquals(['MAC Address'], $grouped->get('Network')->pluck('name')->all());
        $this->assertEquals(['RAM', 'CPU'], $grouped->get('Resources')->pluck('name')->all());
    }

    public function test_grouped_fields_treats_empty_string_group_as_ungrouped()
    {
        $fieldset = CustomFieldset::factory()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false, 'group' => '']);

        $grouped = $fieldset->groupedFields();

        $this->assertSame('', $grouped->keys()->first());
    }

    public function test_ungrouped_fields_always_render_before_named_groups()
    {
        $fieldset = CustomFieldset::factory()->create();
        $ram = CustomField::query()->where('name', 'RAM')->first() ?? CustomField::factory()->ram()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $cpu = CustomField::query()->where('name', 'CPU')->first() ?? CustomField::factory()->cpu()->create();

        // Ungrouped field comes LAST in pivot_order, after the named group.
        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false, 'group' => 'Network']);
        $fieldset->fields()->attach($cpu, ['order' => 2, 'required' => false, 'group' => 'Network']);
        $fieldset->fields()->attach($ram, ['order' => 3, 'required' => false]);

        $grouped = $fieldset->groupedFields();

        $this->assertSame(['', 'Network'], $grouped->keys()->all());
        $this->assertEquals(['MAC Address', 'CPU'], $grouped->get('Network')->pluck('name')->all());
        $this->assertEquals(['RAM'], $grouped->get('')->pluck('name')->all());
    }

    public function test_ungrouped_fields_render_first_even_with_multiple_named_groups()
    {
        $fieldset = CustomFieldset::factory()->create();
        $ram = CustomField::query()->where('name', 'RAM')->first() ?? CustomField::factory()->ram()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $cpu = CustomField::query()->where('name', 'CPU')->first() ?? CustomField::factory()->cpu()->create();

        // Ungrouped field sits between the two named groups in pivot_order.
        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false, 'group' => 'Network']);
        $fieldset->fields()->attach($ram, ['order' => 2, 'required' => false]);
        $fieldset->fields()->attach($cpu, ['order' => 3, 'required' => false, 'group' => 'Resources']);

        $grouped = $fieldset->groupedFields();

        $this->assertSame(['', 'Network', 'Resources'], $grouped->keys()->all());
        $this->assertEquals(['RAM'], $grouped->get('')->pluck('name')->all());
    }
}
