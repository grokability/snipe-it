<?php

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('viewing asset history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.assets.history', Asset::factory()->create()))
        ->assertForbidden();
});

test('viewing asset history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewAssetHistory()->create())
        ->getJson(route('api.assets.history', Asset::factory()->create()))
        ->assertOk();
});

test('viewing asset history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.assets.history', Asset::factory()->create()))
        ->assertOk();
});

test('viewing user history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.users.history', User::factory()->create()))
        ->assertForbidden();
});

test('viewing user history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewUserHistory()->create())
        ->getJson(route('api.users.history', User::factory()->create()))
        ->assertOk();
});

test('viewing user history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.users.history', User::factory()->create()))
        ->assertOk();
});

test('viewing location history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.locations.history', Location::factory()->create()))
        ->assertForbidden();
});

test('viewing location history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewLocationHistory()->create())
        ->getJson(route('api.locations.history', Location::factory()->create()))
        ->assertOk();
});

test('viewing location history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.locations.history', Location::factory()->create()))
        ->assertOk();
});

test('viewing accessory history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.accessories.history', Accessory::factory()->create()))
        ->assertForbidden();
});

test('viewing accessory history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewAccessoryHistory()->create())
        ->getJson(route('api.accessories.history', Accessory::factory()->create()))
        ->assertOk();
});

test('viewing accessory history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.accessories.history', Accessory::factory()->create()))
        ->assertOk();
});

test('viewing license history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.licenses.history', License::factory()->create()))
        ->assertForbidden();
});

test('viewing license history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewLicenseHistory()->create())
        ->getJson(route('api.licenses.history', License::factory()->create()))
        ->assertOk();
});

test('viewing license history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.licenses.history', License::factory()->create()))
        ->assertOk();
});

test('viewing component history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.components.history', Component::factory()->create()))
        ->assertForbidden();
});

test('viewing component history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewComponentHistory()->create())
        ->getJson(route('api.components.history', Component::factory()->create()))
        ->assertOk();
});

test('viewing component history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.components.history', Component::factory()->create()))
        ->assertOk();
});

test('viewing consumable history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.consumables.history', Consumable::factory()->create()))
        ->assertForbidden();
});

test('viewing consumable history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewConsumableHistory()->create())
        ->getJson(route('api.consumables.history', Consumable::factory()->create()))
        ->assertOk();
});

test('viewing consumable history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.consumables.history', Consumable::factory()->create()))
        ->assertOk();
});

test('viewing maintenance history index requires permission', function () {
    $this->actingAsForApi(User::factory()->create())
        ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
        ->assertForbidden();
});

test('viewing maintenance history user has permission', function () {
    $this->actingAsForApi(User::factory()->viewAssetHistory()->create())
        ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
        ->assertOk();
});

test('viewing maintenance history admin has permission', function () {
    $this->actingAsForApi(User::factory()->admin()->create())
        ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
        ->assertOk();
});

test('viewing user history for deleted user still returns logs', function () {
    $deletedUser = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();
    $uniqueNote = 'history-for-deleted-user-'.uniqid();

    $log = Actionlog::factory()->create([
        'item_id' => $deletedUser->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    $deletedUser->delete();

    $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $deletedUser,
            'search' => $uniqueNote,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('rows.0.id', $log->id)
        ->assertJsonPath('rows.0.item.id', $deletedUser->id);
});

test('viewing user history can order by created at', function () {
    $subject = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();

    $older = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    $newer = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'created_at' => '2026-01-02 00:00:00',
        'action_date' => '2026-01-02 00:00:00',
    ]);

    $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'sort' => 'created_at',
            'order' => 'asc',
        ]))
        ->assertOk()
        ->assertJsonPath('rows.0.id', $older->id)
        ->assertJsonPath('rows.1.id', $newer->id);
});

test('viewing user history can order by action date', function () {
    $subject = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();

    $olderActionDate = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'created_at' => '2026-01-02 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    $newerActionDate = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-02 00:00:00',
    ]);

    $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'sort' => 'action_date',
            'order' => 'asc',
        ]))
        ->assertOk()
        ->assertJsonPath('rows.0.id', $olderActionDate->id)
        ->assertJsonPath('rows.1.id', $newerActionDate->id);
});

test('viewing user history can order by created by', function () {
    $subject = User::factory()->create();
    $requestUser = User::factory()->viewUserHistory()->create();
    $uniqueNote = 'history-created-by-sort-'.uniqid();

    $alphaCreator = User::factory()->create([
        'first_name' => 'Aaron',
        'last_name' => 'Alpha',
        'username' => 'aaron-alpha-'.uniqid(),
    ]);

    $omegaCreator = User::factory()->create([
        'first_name' => 'Zelda',
        'last_name' => 'Omega',
        'username' => 'zelda-omega-'.uniqid(),
    ]);

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $omegaCreator->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $alphaCreator->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    $this->actingAsForApi($requestUser)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'search' => $uniqueNote,
            'sort' => 'created_by',
            'order' => 'asc',
        ]))
        ->assertOk()
        ->assertJsonPath('rows.0.created_by.id', $alphaCreator->id)
        ->assertJsonPath('rows.1.created_by.id', $omegaCreator->id);
});

test('viewing user history respects limit and keeps full total', function () {
    $subject = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();
    $uniqueNote = 'history-pagination-limit-'.uniqid();

    $first = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-01 00:00:00',
        'action_date' => '2026-01-01 00:00:00',
    ]);

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-02 00:00:00',
        'action_date' => '2026-01-02 00:00:00',
    ]);

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-01-03 00:00:00',
        'action_date' => '2026-01-03 00:00:00',
    ]);

    $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'search' => $uniqueNote,
            'sort' => 'created_at',
            'order' => 'asc',
            'offset' => 0,
            'limit' => 1,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonCount(1, 'rows')
        ->assertJsonPath('rows.0.id', $first->id);
});

test('viewing user history respects offset and limit and keeps full total', function () {
    $subject = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();
    $uniqueNote = 'history-pagination-offset-'.uniqid();

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-02-01 00:00:00',
        'action_date' => '2026-02-01 00:00:00',
    ]);

    $second = Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-02-02 00:00:00',
        'action_date' => '2026-02-02 00:00:00',
    ]);

    Actionlog::factory()->create([
        'item_id' => $subject->id,
        'item_type' => User::class,
        'created_by' => $actor->id,
        'action_type' => 'update',
        'note' => $uniqueNote,
        'created_at' => '2026-02-03 00:00:00',
        'action_date' => '2026-02-03 00:00:00',
    ]);

    $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'search' => $uniqueNote,
            'sort' => 'created_at',
            'order' => 'asc',
            'offset' => 1,
            'limit' => 1,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonCount(1, 'rows')
        ->assertJsonPath('rows.0.id', $second->id);
});

test('viewing user history avoids n plus one queries for polymorphic relations', function () {
    $subject = User::factory()->create();
    $actor = User::factory()->viewUserHistory()->create();
    $uniqueNote = 'history-polymorphic-n-plus-one-'.uniqid();

    // Deterministic per-row names / usernames sidestep faker's
    // bounded dictionary. Collisions on Location.name (unique in
    // parent_id/company_id) or User.username silently no-save
    // under ValidatingTrait, and downstream `$item->id` reads null.
    $locations = Location::factory()
        ->count(10)
        ->sequence(fn ($seq) => ['name' => 'history-test-loc-'.$seq->index])
        ->create();
    $assets = Asset::factory()->count(10)->create();
    $users = User::factory()
        ->count(10)
        ->sequence(fn ($seq) => ['username' => 'history-test-user-'.$seq->index])
        ->create();

    for ($index = 0; $index < 30; $index++) {
        $itemType = $index % 3;

        if ($itemType === 0) {
            $item = $assets[$index % $assets->count()];
            $itemTypeClass = Asset::class;
        } elseif ($itemType === 1) {
            $item = $locations[$index % $locations->count()];
            $itemTypeClass = Location::class;
        } else {
            $item = $users[$index % $users->count()];
            $itemTypeClass = User::class;
        }

        Actionlog::factory()->create([
            'item_id' => $item->id,
            'item_type' => $itemTypeClass,
            'target_id' => $subject->id,
            'target_type' => User::class,
            'location_id' => $locations[$index % $locations->count()]->id,
            'created_by' => $actor->id,
            'action_type' => 'update',
            'note' => $uniqueNote,
        ]);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->actingAsForApi($actor)
        ->getJson(route('api.users.history', [
            'user' => $subject,
            'limit' => 30,
            'offset' => 0,
            'search' => $uniqueNote,
        ]))
        ->assertOk()
        ->assertJsonPath('total', 30);

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // This threshold is intentionally generous but prevents N+1 regressions.
    expect($queryCount)->toBeLessThan(45, 'History endpoint query count regressed and may have reintroduced N+1 behavior.');
    expect($response->json('rows'))->toHaveCount(30);
});
