<?php

namespace Tests\Feature\History\Api;

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
use Tests\TestCase;

class IndexHistoryTest extends TestCase
{
    /** Assets */
    public function test_viewing_asset_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.assets.history', Asset::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_asset_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewAssetHistory()->create())
            ->getJson(route('api.assets.history', Asset::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_asset_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.assets.history', Asset::factory()->create()))
            ->assertOk();
    }

    /** Users */
    public function test_viewing_user_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.users.history', User::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_user_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewUserHistory()->create())
            ->getJson(route('api.users.history', User::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_user_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.users.history', User::factory()->create()))
            ->assertOk();
    }

    /** Locations */
    public function test_viewing_location_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.locations.history', Location::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_location_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewLocationHistory()->create())
            ->getJson(route('api.locations.history', Location::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_location_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.locations.history', Location::factory()->create()))
            ->assertOk();
    }

    /** Accessories */
    public function test_viewing_accessory_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.accessories.history', Accessory::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_accessory_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewAccessoryHistory()->create())
            ->getJson(route('api.accessories.history', Accessory::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_accessory_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.accessories.history', Accessory::factory()->create()))
            ->assertOk();
    }

    /** Licenses */
    public function test_viewing_license_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.licenses.history', License::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_license_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewLicenseHistory()->create())
            ->getJson(route('api.licenses.history', License::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_license_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.licenses.history', License::factory()->create()))
            ->assertOk();
    }

    /** Components */
    public function test_viewing_component_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.components.history', Component::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_component_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewComponentHistory()->create())
            ->getJson(route('api.components.history', Component::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_component_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.components.history', Component::factory()->create()))
            ->assertOk();
    }

    /** Consumables */
    public function test_viewing_consumable_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.consumables.history', Consumable::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_consumable_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewConsumableHistory()->create())
            ->getJson(route('api.consumables.history', Consumable::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_consumable_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.consumables.history', Consumable::factory()->create()))
            ->assertOk();
    }

    /** Maintenances */
    public function test_viewing_maintenance_history_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
            ->assertForbidden();
    }

    public function test_viewing_maintenance_history_user_has_permission()
    {
        $this->actingAsForApi(User::factory()->viewAssetHistory()->create())
            ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
            ->assertOk();
    }

    public function test_viewing_maintenance_history_admin_has_permission()
    {
        $this->actingAsForApi(User::factory()->admin()->create())
            ->getJson(route('api.maintenances.history', Maintenance::factory()->create()))
            ->assertOk();
    }

    /** Deleted Models */
    public function test_viewing_user_history_for_deleted_user_still_returns_logs()
    {
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
    }

    public function test_viewing_user_history_can_order_by_created_at()
    {
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
    }

    public function test_viewing_user_history_can_order_by_action_date()
    {
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
    }

    public function test_viewing_user_history_can_order_by_created_by()
    {
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
    }

    public function test_viewing_user_history_respects_limit_and_keeps_full_total()
    {
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
    }

    public function test_viewing_user_history_respects_offset_and_limit_and_keeps_full_total()
    {
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
    }

    public function test_viewing_user_history_avoids_n_plus_one_queries_for_polymorphic_relations()
    {
        $subject = User::factory()->create();
        $actor = User::factory()->viewUserHistory()->create();
        $uniqueNote = 'history-polymorphic-n-plus-one-'.uniqid();

        $locations = Location::factory()->count(10)->create();
        $assets = Asset::factory()->count(10)->create();
        $users = User::factory()->count(10)->create();

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
        $this->assertLessThan(45, $queryCount, 'History endpoint query count regressed and may have reintroduced N+1 behavior.');
        $this->assertCount(30, $response->json('rows'));
    }
}
