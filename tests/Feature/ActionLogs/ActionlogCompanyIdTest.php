<?php

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;

test('asset audit log stores the assets company id', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $company->id]);
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.audit', $asset), ['note' => 'audit test'])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'audit',
        'company_id' => $company->id,
    ]);
});

test('asset checkout to user log stores the assets company id', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'status_id' => $asset->status_id,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('asset checkout to location log stores the assets company id', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->create(['company_id' => $company->id]);
    $location = Location::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'location',
            'assigned_location' => $location->id,
            'status_id' => $asset->status_id,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('asset checkin log stores the assets company id', function () {
    $company = Company::factory()->create();
    $asset = Asset::factory()->assignedToUser()->create(['company_id' => $company->id]);
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.checkin', $asset))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkin from',
        'company_id' => $company->id,
    ]);
});

test('asset create log stores the assets company id', function () {
    $company = Company::factory()->create();
    $admin = User::factory()->superuser()->create();
    $model = AssetModel::factory()->create();
    $status = Statuslabel::factory()->readyToDeploy()->create();
    $tag = 'COMPANY-ID-TEST-'.uniqid();

    $this->actingAsForApi($admin)
        ->postJson(route('api.assets.store'), [
            'asset_tag' => $tag,
            'model_id' => $model->id,
            'status_id' => $status->id,
            'company_id' => $company->id,
        ])
        ->assertStatusMessageIs('success');

    $asset = Asset::where('asset_tag', $tag)->firstOrFail();

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'create',
        'company_id' => $company->id,
    ]);
});

test('accessory checkout log stores the accessorys company id', function () {
    $company = Company::factory()->create();
    $accessory = Accessory::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.accessories.checkout', $accessory), [
            'assigned_user' => $user->id,
            'checkout_to_type' => 'user',
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('accessory checkin log stores the accessorys company id', function () {
    $company = Company::factory()->create();
    $accessory = Accessory::factory()->checkedOutToUser()->create(['company_id' => $company->id]);
    $admin = User::factory()->superuser()->create();

    $checkoutRecord = $accessory->checkouts->first();

    $this->actingAsForApi($admin)
        ->postJson(route('api.accessories.checkin', $checkoutRecord))
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Accessory::class,
        'item_id' => $accessory->id,
        'action_type' => 'checkin from',
        'company_id' => $company->id,
    ]);
});

test('consumable checkout log stores the consumables company id', function () {
    $company = Company::factory()->create();
    $consumable = Consumable::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.consumables.checkout', $consumable), [
            'assigned_to' => $user->id,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Consumable::class,
        'item_id' => $consumable->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('component checkout log stores the components company id', function () {
    $company = Company::factory()->create();
    $component = Component::factory()->create(['company_id' => $company->id]);
    $asset = Asset::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.components.checkout', $component->id), [
            'assigned_to' => $asset->id,
            'assigned_qty' => 1,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Component::class,
        'item_id' => $component->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('component checkin log stores the components company id', function () {
    $company = Company::factory()->create();
    $component = Component::factory()->create(['company_id' => $company->id]);
    $asset = Asset::factory()->create();
    $admin = User::factory()->superuser()->create();

    // Check out first
    $this->actingAsForApi($admin)
        ->postJson(route('api.components.checkout', $component->id), [
            'assigned_to' => $asset->id,
            'assigned_qty' => 1,
        ])
        ->assertStatusMessageIs('success');

    $pivotId = $component->assets()->first()->pivot->id;

    $this->actingAsForApi($admin)
        ->postJson(route('api.components.checkin', $pivotId), [
            'checkin_qty' => 1,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Component::class,
        'item_id' => $component->id,
        'action_type' => 'checkin from',
        'company_id' => $company->id,
    ]);
});

test('license checkout log stores the licenses company id', function () {
    $company = Company::factory()->create();
    $license = License::factory()->create(['company_id' => $company->id]);
    $seat = $license->freeSeats()->first();
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->patchJson(route('api.licenses.seats.update', [$license->id, $seat->id]), [
            'assigned_to' => $user->id,
        ])
        ->assertStatusMessageIs('success');

    // The log is stored against the License (item_type), not the LicenseSeat
    $this->assertDatabaseHas('action_logs', [
        'item_type' => License::class,
        'item_id' => $license->id,
        'action_type' => 'checkout',
        'company_id' => $company->id,
    ]);
});

test('license checkin log stores the licenses company id', function () {
    $company = Company::factory()->create();
    $license = License::factory()->create(['company_id' => $company->id]);
    $seat = $license->freeSeats()->first();
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    // Check out first
    $seat->assigned_to = $user->id;
    $seat->save();

    $this->actingAsForApi($admin)
        ->patchJson(route('api.licenses.seats.update', [$license->id, $seat->id]), [
            'assigned_to' => null,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => License::class,
        'item_id' => $license->id,
        'action_type' => 'checkin from',
        'company_id' => $company->id,
    ]);
});

test('asset audit log company id is null when asset has no company', function () {
    $asset = Asset::factory()->create(['company_id' => null]);
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.audit', $asset), ['note' => 'no company'])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'audit',
        'company_id' => null,
    ]);
});

test('asset checkout log company id is null when asset has no company', function () {
    $asset = Asset::factory()->create(['company_id' => null]);
    $user = User::factory()->create();
    $admin = User::factory()->superuser()->create();

    $this->actingAsForApi($admin)
        ->postJson(route('api.asset.checkout', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
            'status_id' => $asset->status_id,
        ])
        ->assertStatusMessageIs('success');

    $this->assertDatabaseHas('action_logs', [
        'item_type' => Asset::class,
        'item_id' => $asset->id,
        'action_type' => 'checkout',
        'company_id' => null,
    ]);
});
