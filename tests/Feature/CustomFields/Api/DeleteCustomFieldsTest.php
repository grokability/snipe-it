<?php

namespace Tests\Feature\CustomFields\Api;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteCustomFieldsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $customField = CustomField::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.customfields.destroy', $customField))
            ->assertForbidden();

        $this->assertDatabaseHas('custom_fields', ['id' => $customField->id]);
    }

    public function testCustomFieldsCannotBeDeletedIfTheyHaveAssociatedFieldsets()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $customField = CustomField::factory()->create();
        $customFieldset = CustomFieldset::factory()->create();

        $customField->fieldset()->attach($customFieldset, ['order' => 1, 'required' => 'false']);

        $this->actingAsForApi(User::factory()->deleteCustomFields()->create())
            ->deleteJson(route('api.customfields.destroy', $customField))
            ->assertStatusMessageIs('error');

        $this->assertDatabaseHas('custom_fields', ['id' => $customField->id]);
    }

    public function testCustomFieldsCanBeDeleted()
    {
        $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

        $customField = CustomField::factory()->create();

        $this->actingAsForApi(User::factory()->deleteCustomFields()->create())
            ->deleteJson(route('api.customfields.destroy', $customField))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('custom_fields', ['id' => $customField->id]);
    }
}
