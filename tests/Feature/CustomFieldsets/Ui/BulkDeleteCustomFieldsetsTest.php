<?php

namespace Tests\Feature\CustomFieldsets\Ui;

use App\Models\AssetModel;
use App\Models\CustomFieldset;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteCustomFieldsetsTest extends TestCase
{
    public function test_permission_needed_to_bulk_delete_fieldsets(): void
    {
        $fieldset = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => [$fieldset->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('custom_fieldsets', ['id' => $fieldset->id]);
    }

    public function test_bulk_deletes_unattached_fieldsets(): void
    {
        $one = CustomFieldset::factory()->create();
        $two = CustomFieldset::factory()->create();

        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => [$one->id, $two->id]])
            ->assertRedirect(route('fields.index').'#fieldsets')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('custom_fieldsets', ['id' => $one->id]);
        $this->assertDatabaseMissing('custom_fieldsets', ['id' => $two->id]);
    }

    public function test_skips_fieldsets_attached_to_models_and_returns_error(): void
    {
        $attached = CustomFieldset::factory()->create();
        AssetModel::factory()->create(['fieldset_id' => $attached->id]);

        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => [$attached->id]])
            ->assertRedirect(route('fields.index').'#fieldsets')
            ->assertSessionHas('multi_error_messages')
            ->assertSessionMissing('success');

        $this->assertDatabaseHas('custom_fieldsets', ['id' => $attached->id]);
    }

    public function test_mixed_batch_reports_partial_success(): void
    {
        $deletable = CustomFieldset::factory()->create();
        $attached = CustomFieldset::factory()->create();
        AssetModel::factory()->create(['fieldset_id' => $attached->id]);

        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => [$deletable->id, $attached->id]])
            ->assertRedirect(route('fields.index').'#fieldsets')
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseMissing('custom_fieldsets', ['id' => $deletable->id]);
        $this->assertDatabaseHas('custom_fieldsets', ['id' => $attached->id]);
    }

    public function test_empty_ids_shows_error(): void
    {
        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => []])
            ->assertRedirect(route('fields.index').'#fieldsets')
            ->assertSessionHas('error');
    }
}
