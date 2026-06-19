<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Segunda tanda de metodos de User: permisos efectivos, subordinados/manager
 * y los query scopes (group/admins/order/search/inventory).
 */
class UserDeepMethodsMoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_get_effective_permissions_by_section(): void
    {
        $user = User::factory()->create([
            'permissions' => json_encode(['superuser' => '1', 'admin' => '0']),
        ]);

        $result = $user->getEffectivePermissionsBySection();

        $this->assertIsArray($result);
    }

    public function test_decode_permissions(): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['admin' => '1'])]);

        $decoded = $user->decodePermissions();

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('admin', $decoded);
    }

    public function test_subordinates_and_is_manager_of(): void
    {
        $manager = User::factory()->create();
        $sub1 = User::factory()->create(['manager_id' => $manager->id]);
        $sub2 = User::factory()->create(['manager_id' => $sub1->id]); // nieto

        $all = $manager->getAllSubordinates();
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $all);
        $this->assertGreaterThanOrEqual(2, $all->count());

        $withSelf = $manager->getAllSubordinatesIncludingSelf();
        $this->assertTrue($withSelf->contains('id', $manager->id));

        $this->assertTrue($manager->isManagerOf($sub2));
        $this->assertFalse($sub2->isManagerOf($manager));
    }

    public function test_created_by_relation_builds(): void
    {
        $user = User::factory()->create();
        $user->createdBy()->getQuery();
        $this->assertTrue(true);
    }

    public function test_group_and_admin_scopes(): void
    {
        $this->assertInstanceOf(Collection::class, User::byGroup(1)->get());
        $this->assertInstanceOf(Collection::class, User::onlySuperAdmins()->get());
        $this->assertInstanceOf(Collection::class, User::onlyAdminsAndSuperAdmins()->get());
    }

    public function test_order_scopes(): void
    {
        User::factory()->count(2)->create();

        $this->assertInstanceOf(Collection::class, User::OrderManager('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderLocation('desc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderDepartment('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderByCreatedBy('asc')->get());
        $this->assertInstanceOf(Collection::class, User::OrderCompany('desc')->get());
    }

    public function test_search_and_location_scopes(): void
    {
        $location = Location::factory()->create();
        User::factory()->create(['location_id' => $location->id]);

        $this->assertInstanceOf(Collection::class, User::SimpleNameSearch('a')->get());

        // scopeUserLocation y advancedTextSearch via builder.
        $byLoc = User::query()->where(function ($q) use ($location) {
            (new User)->scopeUserLocation($q, $location->id, 'test');
        })->get();
        $this->assertInstanceOf(Collection::class, $byLoc);
    }

    public function test_with_inventory_relations_scope(): void
    {
        $user = User::factory()->create();

        $result = User::withInventoryRelations($user->id)->get();

        $this->assertInstanceOf(Collection::class, $result);
    }
}
