<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Component;
use App\Models\LicenseSeat;
use App\Models\Maintenance;
use App\Models\User;
use Tests\TestCase;

class AssetCountsFilterTest extends TestCase
{
    public function test_index_response_includes_the_four_count_fields(): void
    {
        $asset = Asset::factory()->create();

        $this->attachLicenseSeats($asset, 2);
        $this->attachComponents($asset, 3);
        $this->attachMaintenances($asset, 4);
        $this->attachChildAssets($asset, 5);

        $row = $this->indexRowFor($asset);

        $this->assertSame(2, $row['licenses_count']);
        $this->assertSame(3, $row['components_count']);
        $this->assertSame(4, $row['maintenances_count']);
        $this->assertSame(5, $row['assigned_assets_count']);
    }

    public function test_index_sort_by_assigned_assets_count_descending(): void
    {
        [$few, $many] = Asset::factory()->count(2)->create();
        $this->attachChildAssets($few, 1);
        $this->attachChildAssets($many, 4);

        $ids = $this->indexSortedIds('assigned_assets_count', 'desc');

        $this->assertSame($many->id, $ids[0]);
        $this->assertSame($few->id, $ids[1]);
    }

    public function test_assigned_assets_count_filter_gt_narrows_the_result_set(): void
    {
        [$zero, $one, $three] = Asset::factory()->count(3)->create();
        $this->attachChildAssets($one, 1);
        $this->attachChildAssets($three, 3);

        $ids = $this->filteredIds(['assigned_assets_count' => 'gt:1']);

        $this->assertContains($three->id, $ids);
        $this->assertNotContains($one->id, $ids);
        $this->assertNotContains($zero->id, $ids);
    }

    public function test_assigned_assets_count_excludes_trashed_children(): void
    {
        /*
         * A child asset whose soft-delete column is set should NOT
         * count toward the parent's assigned_assets_count. The
         * withCount closure and the filter subquery both filter
         * trashed children.
         */
        $parent = Asset::factory()->create();
        $this->attachChildAssets($parent, 2);
        $trashedChild = Asset::factory()->create([
            'assigned_to' => $parent->id,
            'assigned_type' => Asset::class,
        ]);
        $trashedChild->delete();

        $row = $this->indexRowFor($parent);

        $this->assertSame(2, $row['assigned_assets_count']);
    }

    public function test_index_sort_by_licenses_count_descending(): void
    {
        [$few, $many] = Asset::factory()->count(2)->create();
        $this->attachLicenseSeats($few, 1);
        $this->attachLicenseSeats($many, 3);

        $ids = $this->indexSortedIds('licenses_count', 'desc');

        $this->assertSame($many->id, $ids[0]);
        $this->assertSame($few->id, $ids[1]);
    }

    public function test_index_sort_by_components_count_descending(): void
    {
        [$few, $many] = Asset::factory()->count(2)->create();
        $this->attachComponents($few, 1);
        $this->attachComponents($many, 4);

        $ids = $this->indexSortedIds('components_count', 'desc');

        $this->assertSame($many->id, $ids[0]);
        $this->assertSame($few->id, $ids[1]);
    }

    public function test_index_sort_by_maintenances_count_descending(): void
    {
        [$few, $many] = Asset::factory()->count(2)->create();
        $this->attachMaintenances($few, 1);
        $this->attachMaintenances($many, 5);

        $ids = $this->indexSortedIds('maintenances_count', 'desc');

        $this->assertSame($many->id, $ids[0]);
        $this->assertSame($few->id, $ids[1]);
    }

    public function test_licenses_count_filter_gt_narrows_the_result_set(): void
    {
        [$zero, $one, $three] = Asset::factory()->count(3)->create();
        $this->attachLicenseSeats($one, 1);
        $this->attachLicenseSeats($three, 3);

        $ids = $this->filteredIds(['licenses_count' => 'gt:1']);

        $this->assertContains($three->id, $ids);
        $this->assertNotContains($one->id, $ids);
        $this->assertNotContains($zero->id, $ids);
    }

    public function test_components_count_filter_lt_narrows_the_result_set(): void
    {
        [$zero, $one, $three] = Asset::factory()->count(3)->create();
        $this->attachComponents($one, 1);
        $this->attachComponents($three, 3);

        $ids = $this->filteredIds(['components_count' => 'lt:2']);

        $this->assertContains($zero->id, $ids);
        $this->assertContains($one->id, $ids);
        $this->assertNotContains($three->id, $ids);
    }

    public function test_maintenances_count_filter_gt_and_lt_bounds_the_result_set(): void
    {
        $zero = Asset::factory()->create();
        $two = Asset::factory()->create();
        $five = Asset::factory()->create();
        $this->attachMaintenances($two, 2);
        $this->attachMaintenances($five, 5);

        $ids = $this->filteredIds(['maintenances_count' => 'gt:1,lt:4']);

        $this->assertContains($two->id, $ids);
        $this->assertNotContains($zero->id, $ids);
        $this->assertNotContains($five->id, $ids);
    }

    public function test_maintenances_count_respects_morph_type_predicate(): void
    {
        /*
         * A Maintenance row whose morph target is NOT an Asset
         * (item_type on a Component, say) must not inflate any
         * asset's maintenances_count. Guard against someone later
         * dropping the item_type predicate from the subquery and
         * silently catching unrelated rows.
         */
        $asset = Asset::factory()->create();
        $foreignOwner = Component::factory()->create();

        $this->attachMaintenances($asset, 1);
        Maintenance::factory()->create([
            'item_id' => $foreignOwner->id,
            'item_type' => Component::class,
        ]);

        $row = $this->indexRowFor($asset);

        $this->assertSame(1, $row['maintenances_count']);
    }

    private function indexRowFor(Asset $asset): array
    {
        $rows = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', ['limit' => 100]))
            ->assertOk()
            ->json('rows');

        foreach ($rows as $row) {
            if ((int) $row['id'] === $asset->id) {
                return $row;
            }
        }

        $this->fail('Asset '.$asset->id.' did not appear in the API index response');
    }

    /** @return array<int, int> */
    private function indexSortedIds(string $sort, string $order): array
    {
        return collect(
            $this->actingAsForApi(User::factory()->superuser()->create())
                ->getJson(route('api.assets.index', [
                    'sort' => $sort,
                    'order' => $order,
                    'limit' => 100,
                ]))
                ->assertOk()
                ->json('rows'),
        )->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  array<string, string>  $filter
     * @return array<int, int>
     */
    private function filteredIds(array $filter): array
    {
        return collect(
            $this->actingAsForApi(User::factory()->superuser()->create())
                ->getJson(route('api.assets.index', [
                    'filter' => $filter,
                    'limit' => 100,
                ]))
                ->assertOk()
                ->json('rows'),
        )->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function attachLicenseSeats(Asset $asset, int $count): void
    {
        LicenseSeat::factory()->count($count)->assignedToAsset($asset)->create();
    }

    private function attachComponents(Asset $asset, int $count): void
    {
        $creator = User::factory()->create();
        for ($i = 0; $i < $count; $i++) {
            $component = Component::factory()->create();
            $asset->components()->attach($component->id, [
                'assigned_qty' => 1,
                'created_by' => $creator->id,
                'created_at' => now(),
            ]);
        }
    }

    private function attachMaintenances(Asset $asset, int $count): void
    {
        Maintenance::factory()->count($count)->create([
            'item_id' => $asset->id,
            'item_type' => Asset::class,
        ]);
    }

    private function attachChildAssets(Asset $parent, int $count): void
    {
        Asset::factory()->count($count)->create([
            'assigned_to' => $parent->id,
            'assigned_type' => Asset::class,
        ]);
    }
}
