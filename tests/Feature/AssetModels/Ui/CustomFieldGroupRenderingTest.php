<?php

namespace Tests\Feature\AssetModels\Ui;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class CustomFieldGroupRenderingTest extends TestCase
{
    public function test_grouped_field_renders_a_group_heading_on_the_custom_fields_form(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false, 'group' => 'Network']);
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $this->actingAs(User::factory()->viewAssetModels()->create())
            ->get(route('custom_fields/model', $model->id))
            ->assertOk()
            ->assertSee('Network');
    }

    public function test_ungrouped_field_still_renders_normally(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $this->actingAs(User::factory()->viewAssetModels()->create())
            ->get(route('custom_fields/model', $model->id))
            ->assertOk()
            ->assertSee('MAC Address');
    }

    public function test_ungrouped_field_between_named_groups_renders_before_all_groups(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $mac = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $ram = CustomField::query()->where('name', 'RAM')->first() ?? CustomField::factory()->ram()->create();
        $cpu = CustomField::query()->where('name', 'CPU')->first() ?? CustomField::factory()->cpu()->create();

        // RAM is ungrouped and sits BETWEEN the two Network fields in pivot_order.
        $fieldset->fields()->attach($mac, ['order' => 1, 'required' => false, 'group' => 'Network']);
        $fieldset->fields()->attach($ram, ['order' => 2, 'required' => false]);
        $fieldset->fields()->attach($cpu, ['order' => 3, 'required' => false, 'group' => 'Network']);
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

        $this->actingAs(User::factory()->viewAssetModels()->create())
            ->get(route('custom_fields/model', $model->id))
            ->assertOk()
            ->assertSeeInOrder(['RAM', 'Network', 'MAC Address', 'CPU']);
    }
}
