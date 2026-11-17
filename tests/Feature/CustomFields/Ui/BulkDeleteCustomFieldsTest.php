<?php

namespace Tests\Feature\CustomFields\Ui;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteCustomFieldsTest extends TestCase
{
    public function test_permission_needed_to_bulk_delete_fields(): void
    {
        $field = CustomField::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('fields.bulk.delete'), ['ids' => [$field->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('custom_fields', ['id' => $field->id]);
    }

    public function test_bulk_deletes_unattached_fields(): void
    {
        $one = CustomField::factory()->create();
        $two = CustomField::factory()->create();

        $this->actingAs(User::factory()->deleteCustomFields()->create())
            ->post(route('fields.bulk.delete'), ['ids' => [$one->id, $two->id]])
            ->assertRedirectToRoute('fields.index')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_fields', ['id' => $one->id]);
        $this->assertDatabaseMissing('custom_fields', ['id' => $two->id]);
    }

    public function test_skips_attached_fields_and_returns_error(): void
    {
        $attached = CustomField::factory()->create();
        $fieldset = CustomFieldset::factory()->create();
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('fieldsets.associate', $fieldset), ['field_id' => $attached->id]);

        $this->actingAs(User::factory()->deleteCustomFields()->create())
            ->post(route('fields.bulk.delete'), ['ids' => [$attached->id]])
            ->assertRedirectToRoute('fields.index')
            ->assertSessionHas('multi_error_messages')
            ->assertSessionMissing('success');

        $this->assertDatabaseHas('custom_fields', ['id' => $attached->id]);
    }

    public function test_mixed_batch_reports_partial_success(): void
    {
        $deletable = CustomField::factory()->create();
        $attached = CustomField::factory()->create();
        $fieldset = CustomFieldset::factory()->create();
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('fieldsets.associate', $fieldset), ['field_id' => $attached->id]);

        $this->actingAs(User::factory()->deleteCustomFields()->create())
            ->post(route('fields.bulk.delete'), ['ids' => [$deletable->id, $attached->id]])
            ->assertRedirectToRoute('fields.index')
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseMissing('custom_fields', ['id' => $deletable->id]);
        $this->assertDatabaseHas('custom_fields', ['id' => $attached->id]);
    }

    public function test_empty_ids_shows_error(): void
    {
        $this->actingAs(User::factory()->deleteCustomFields()->create())
            ->post(route('fields.bulk.delete'), ['ids' => []])
            ->assertRedirectToRoute('fields.index')
            ->assertSessionHas('error');
    }
}
