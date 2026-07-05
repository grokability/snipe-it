<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\AssetModel;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteDepreciationsTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_depreciation_with_asset_models_is_not_bulk_deleted()
    {
        $depreciation = Depreciation::factory()->create();
        AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$depreciation->id],
            ])
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
    }

    public function test_depreciation_with_licenses_is_not_bulk_deleted()
    {
        $depreciation = Depreciation::factory()->create();
        License::factory()->create(['depreciation_id' => $depreciation->id]);

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$depreciation->id],
            ])
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
    }

    public function test_deletable_depreciations_are_bulk_deleted()
    {
        $depreciation1 = Depreciation::factory()->create();
        $depreciation2 = Depreciation::factory()->create();
        $depreciation3 = Depreciation::factory()->create();

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$depreciation1->id, $depreciation2->id, $depreciation3->id],
            ])
            ->assertRedirect(route('depreciations.index'));

        // Depreciations are hard-deleted, not soft-deleted.
        $this->assertDatabaseMissing('depreciations', ['id' => $depreciation1->id]);
        $this->assertDatabaseMissing('depreciations', ['id' => $depreciation2->id]);
        $this->assertDatabaseMissing('depreciations', ['id' => $depreciation3->id]);
    }

    public function test_partial_success_deletes_the_clean_ones_and_reports_the_rest()
    {
        $deletable = Depreciation::factory()->create();
        $blocked = Depreciation::factory()->create();
        AssetModel::factory()->create(['depreciation_id' => $blocked->id]);

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$deletable->id, $blocked->id],
            ])
            ->assertRedirect(route('depreciations.index'))
            ->assertSessionHas('success')
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseMissing('depreciations', ['id' => $deletable->id]);
        $this->assertDatabaseHas('depreciations', ['id' => $blocked->id]);
    }

    public function test_nonexistent_ids_are_reported_and_do_not_break_the_batch()
    {
        $deletable = Depreciation::factory()->create();

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$deletable->id, 999999],
            ])
            ->assertRedirect(route('depreciations.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertDatabaseMissing('depreciations', ['id' => $deletable->id]);
    }

    public function test_bulk_success_message_pluralizes_by_count()
    {
        $solo = Depreciation::factory()->create();

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$solo->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/depreciations/message.delete.bulk_success', 1, ['count' => 1]));

        $a = Depreciation::factory()->create();
        $b = Depreciation::factory()->create();
        $c = Depreciation::factory()->create();

        $this->actingAs(User::factory()->deleteDepreciations()->create())
            ->post(route('depreciations.bulk.delete'), [
                'ids' => [$a->id, $b->id, $c->id],
            ])
            ->assertSessionHas('success', trans_choice('admin/depreciations/message.delete.bulk_success', 3, ['count' => 3]));
    }
}
