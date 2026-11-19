<?php

use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('consumable with outstanding distribution is not bulk deleted', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$consumable->id],
        ])
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseHas('consumables', ['id' => $consumable->id, 'deleted_at' => null]);
});

test('deletable consumables are bulk deleted', function () {
    $one = Consumable::factory()->create();
    $two = Consumable::factory()->create();
    $three = Consumable::factory()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$one->id, $two->id, $three->id],
        ])
        ->assertRedirect(route('consumables.index'));

    $this->assertSoftDeleted('consumables', ['id' => $one->id]);
    $this->assertSoftDeleted('consumables', ['id' => $two->id]);
    $this->assertSoftDeleted('consumables', ['id' => $three->id]);
});

test('partial success deletes the clean ones and reports the rest', function () {
    $deletable = Consumable::factory()->create();
    $blocked = Consumable::factory()->checkedOutToUser()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$deletable->id, $blocked->id],
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('consumables', ['id' => $deletable->id]);
    $this->assertDatabaseHas('consumables', ['id' => $blocked->id, 'deleted_at' => null]);
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Consumable::factory()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('consumables', ['id' => $deletable->id]);
});

test('respects fmcs scoping for non superuser', function () {
    // Caller scoped to company A asking to bulk delete [A-owned,
    // B-owned] sees the B-owned row hidden by CompanyableScope
    // (Consumable::find() returns null), falling into the
    // does_not_exist bucket. A-owned deletes normally, so the
    // request is a partial success.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $consumableA = Consumable::factory()->for($companyA)->create();
    $consumableB = Consumable::factory()->for($companyB)->create();
    $userA = User::factory()->forCompany($companyA)->deleteConsumables()->create();

    $this->actingAs($userA)
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$consumableA->id, $consumableB->id],
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('consumables', ['id' => $consumableA->id]);
    $this->assertDatabaseHas('consumables', ['id' => $consumableB->id, 'deleted_at' => null]);
});

test('superuser bypasses fmcs and deletes consumables in any company', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $consumableA = Consumable::factory()->for($companyA)->create();
    $consumableB = Consumable::factory()->for($companyB)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$consumableA->id, $consumableB->id],
        ])
        ->assertRedirect(route('consumables.index'))
        ->assertSessionHas('success')
        ->assertSessionMissing('multi_error_messages');

    $this->assertSoftDeleted('consumables', ['id' => $consumableA->id]);
    $this->assertSoftDeleted('consumables', ['id' => $consumableB->id]);
});

test('bulk success message pluralizes by count', function () {
    $solo = Consumable::factory()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$solo->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/consumables/message.delete.bulk_success', 1, ['count' => 1]));

    $a = Consumable::factory()->create();
    $b = Consumable::factory()->create();
    $c = Consumable::factory()->create();

    $this->actingAs(User::factory()->deleteConsumables()->create())
        ->post(route('consumables.bulkdelete.store'), [
            'ids' => [$a->id, $b->id, $c->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/consumables/message.delete.bulk_success', 3, ['count' => 3]));
});
