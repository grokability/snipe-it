<?php

use App\Models\AssetModel;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('depreciations.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('depreciation with asset models is not bulk deleted', function () {
    $depreciation = Depreciation::factory()->create();
    AssetModel::factory()->create(['depreciation_id' => $depreciation->id]);

    $this->actingAs(User::factory()->deleteDepreciations()->create())
        ->post(route('depreciations.bulk.delete'), [
            'ids' => [$depreciation->id],
        ])
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
});

test('depreciation with licenses is not bulk deleted', function () {
    $depreciation = Depreciation::factory()->create();
    License::factory()->create(['depreciation_id' => $depreciation->id]);

    $this->actingAs(User::factory()->deleteDepreciations()->create())
        ->post(route('depreciations.bulk.delete'), [
            'ids' => [$depreciation->id],
        ])
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
});

test('deletable depreciations are bulk deleted', function () {
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
});

test('partial success deletes the clean ones and reports the rest', function () {
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
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Depreciation::factory()->create();

    $this->actingAs(User::factory()->deleteDepreciations()->create())
        ->post(route('depreciations.bulk.delete'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('depreciations.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseMissing('depreciations', ['id' => $deletable->id]);
});

test('bulk success message pluralizes by count', function () {
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
});
