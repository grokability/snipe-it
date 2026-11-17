<?php

namespace Tests\Feature\CustomFieldsets\Ui;

use App\Models\AssetModel;
use App\Models\CompanyableScope;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    public function test_cannot_bulk_delete_fieldset_assigned_to_soft_deleted_model(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $model->delete();

        $this->assertSoftDeleted($model);

        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => [$fieldset->id]])
            ->assertSessionHas('multi_error_messages')
            ->assertSessionMissing('success');

        $this->assertDatabaseHas('custom_fieldsets', ['id' => $fieldset->id]);
    }

    public function test_cannot_bulk_delete_fieldset_with_assignment_hidden_by_company_scope(): void
    {
        $fieldset = CustomFieldset::factory()->create();
        $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);
        $user = User::factory()->deleteCustomFieldsets()->create();
        $originalScopes = AssetModel::getAllGlobalScopes();

        try {
            AssetModel::addGlobalScope(CompanyableScope::class, function (Builder $query) use ($model): void {
                $query->whereKeyNot($model->id);
            });

            $this->assertFalse(AssetModel::query()->whereKey($model->id)->exists());

            $this->actingAs($user)
                ->post(route('fieldsets.bulk.delete'), ['ids' => [$fieldset->id]])
                ->assertSessionHas('multi_error_messages')
                ->assertSessionMissing('success');

            $this->assertDatabaseHas('custom_fieldsets', ['id' => $fieldset->id]);
            $this->assertFalse(AssetModel::query()->whereKey($model->id)->exists());
        } finally {
            AssetModel::setAllGlobalScopes($originalScopes);
        }
    }

    public function test_empty_ids_shows_error(): void
    {
        $this->actingAs(User::factory()->deleteCustomFieldsets()->create())
            ->post(route('fieldsets.bulk.delete'), ['ids' => []])
            ->assertRedirect(route('fields.index').'#fieldsets')
            ->assertSessionHas('error');
    }
}
