<?php

namespace Tests\Feature\CustomFieldsets\Ui;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class UpdateFieldGroupTest extends TestCase
{
    public function test_requires_permission()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);

        $this->actingAs(User::factory()->viewCustomFields()->create())
            ->post(route('fields.group', [$fieldset->id, $field->id]), ['group' => 'Network'])
            ->assertForbidden();

        $this->assertNull($fieldset->fields()->first()->pivot->group);
    }

    public function test_can_set_and_clear_a_fields_group()
    {
        $fieldset = CustomFieldset::factory()->create();
        $field = CustomField::query()->where('name', 'MAC Address')->first() ?? CustomField::factory()->macAddress()->create();
        $fieldset->fields()->attach($field, ['order' => 1, 'required' => false]);
        $user = User::factory()->editCustomFields()->create();

        $this->actingAs($user)
            ->post(route('fields.group', [$fieldset->id, $field->id]), ['group' => 'Network'])
            ->assertRedirect(route('fieldsets.show', $fieldset->id));

        $this->assertEquals('Network', $fieldset->fields()->first()->pivot->group);

        $this->actingAs($user)
            ->post(route('fields.group', [$fieldset->id, $field->id]), ['group' => ''])
            ->assertRedirect(route('fieldsets.show', $fieldset->id));

        $this->assertNull($fieldset->fields()->first()->pivot->group);
    }
}
