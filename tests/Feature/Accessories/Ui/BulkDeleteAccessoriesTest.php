<?php

use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('checked out accessory is not bulk deleted', function () {
    $accessory = Accessory::factory()->create();
    AccessoryCheckout::factory()->create(['accessory_id' => $accessory->id]);

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$accessory->id],
        ])
        ->assertSessionHas('multi_error_messages');

    $this->assertDatabaseHas('accessories', ['id' => $accessory->id, 'deleted_at' => null]);
});

test('deletable accessories are bulk deleted', function () {
    $accessory1 = Accessory::factory()->create();
    $accessory2 = Accessory::factory()->create();
    $accessory3 = Accessory::factory()->create();

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$accessory1->id, $accessory2->id, $accessory3->id],
        ])
        ->assertRedirect(route('accessories.index'));

    // Accessories are soft-deleted.
    $this->assertSoftDeleted('accessories', ['id' => $accessory1->id]);
    $this->assertSoftDeleted('accessories', ['id' => $accessory2->id]);
    $this->assertSoftDeleted('accessories', ['id' => $accessory3->id]);
});

test('partial success deletes the clean ones and reports the rest', function () {
    $deletable = Accessory::factory()->create();
    $blocked = Accessory::factory()->create();
    AccessoryCheckout::factory()->create(['accessory_id' => $blocked->id]);

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$deletable->id, $blocked->id],
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('accessories', ['id' => $deletable->id]);
    $this->assertDatabaseHas('accessories', ['id' => $blocked->id, 'deleted_at' => null]);
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Accessory::factory()->create();

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('accessories', ['id' => $deletable->id]);
});

test('respects fmcs scoping for non superuser', function () {
    // Caller scoped to company A asking to bulk delete [A-owned, B-owned]
    // sees the B-owned row hidden by CompanyableScope (Accessory::find()
    // returns null), which falls into the "does_not_exist" error bucket.
    // The A-owned row deletes normally, so the request is a partial success.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessoryA = Accessory::factory()->for($companyA)->create();
    $accessoryB = Accessory::factory()->for($companyB)->create();
    $userA = User::factory()->forCompany($companyA)->deleteAccessories()->create();

    $this->actingAs($userA)
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$accessoryA->id, $accessoryB->id],
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted('accessories', ['id' => $accessoryA->id]);
    $this->assertDatabaseHas('accessories', ['id' => $accessoryB->id, 'deleted_at' => null]);
});

test('superuser bypasses fmcs and deletes accessories in any company', function () {
    // FMCS on, but a superuser is exempt from CompanyableScope. A bulk
    // delete touching accessories in two different companies should
    // remove both without any "does_not_exist" errors.
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();
    $accessoryA = Accessory::factory()->for($companyA)->create();
    $accessoryB = Accessory::factory()->for($companyB)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$accessoryA->id, $accessoryB->id],
        ])
        ->assertRedirect(route('accessories.index'))
        ->assertSessionHas('success')
        ->assertSessionMissing('multi_error_messages');

    $this->assertSoftDeleted('accessories', ['id' => $accessoryA->id]);
    $this->assertSoftDeleted('accessories', ['id' => $accessoryB->id]);
});

test('bulk success message pluralizes by count', function () {
    $solo = Accessory::factory()->create();

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$solo->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/accessories/message.delete.bulk_success', 1, ['count' => 1]));

    $a = Accessory::factory()->create();
    $b = Accessory::factory()->create();
    $c = Accessory::factory()->create();

    $this->actingAs(User::factory()->deleteAccessories()->create())
        ->post(route('accessories.bulk.delete'), [
            'ids' => [$a->id, $b->id, $c->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/accessories/message.delete.bulk_success', 3, ['count' => 3]));
});
