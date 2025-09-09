<?php

namespace Tests\Feature\CustomFields\Ui;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class DeleteCustomFieldsTest extends TestCase
{
    public function testPermissionNeededToDeleteField()
    {
        $this->actingAs(User::factory()->create())
            ->delete(route('fields.destroy', CustomField::factory()->create()))
            ->assertForbidden();
    }


    public function testCanDeleteCustomField()
    {
        $field = CustomField::factory()->create();
        $this->assertDatabaseHas('custom_fields', ['id' => $field->id]);

        $this->actingAs(User::factory()->deleteCustomFields()->create())
            ->delete(route('fields.destroy', $field))
            ->assertRedirectToRoute('fields.index')
            ->assertStatus(302)
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_fields', ['id' => $field->id]);
    }

    public function testCannotDeleteCustomFieldThatDoesNotExist()
    {

        $response = $this->actingAs(User::factory()->viewCustomFields()->deleteCustomFields()->create())
            ->delete(route('fields.destroy', '49857589'))
            ->assertRedirect(route('fields.index'))
            ->assertSessionHas('error');

        $temp = $this->followRedirects($response);
        $temp->assertSee(trans('general.error'))->assertSee(trans('general.generic_model_not_found', ['model' => 'custom field']));

    }

    public function testCannotDeleteFieldThatIsAssociatedWithFieldsets()
    {
        $field = CustomField::factory()->create();
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('fieldsets.associate', $fieldset), [
                'field_id' => $field->id,
            ]);

        $response = $this->actingAs(User::factory()->viewCustomFields()->deleteCustomFields()->create())
            ->from(route('fields.index'))
            ->delete(route('fields.destroy', $field))
            ->assertStatus(302)
            ->assertRedirect(route('fields.index'))
            ->assertSessionHas('error');

        $this->followRedirects($response)->assertSee(trans('general.error'))->assertSee(trans('admin/custom_fields/message.field.delete.in_use'));

        // Ensure the field is still in the database
        $this->assertDatabaseHas('custom_fields', ['id' => $field->id]);
    }
}
