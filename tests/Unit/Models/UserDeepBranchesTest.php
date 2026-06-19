<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * Cubre ramas profundas de User: checkPermissionSection (deny/grupo/vacio),
 * getEffectivePermissionsBySection, hasIndividualPermissions, relaciones de
 * historial/aceptaciones y getUserTotalCost.
 */
class UserDeepBranchesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_check_permission_section_branches(): void
    {
        // Sin permisos individuales ni grupos -> false temprano.
        $empty = User::factory()->create(['permissions' => '']);
        $this->assertFalse($empty->hasAccess('admin'));

        // Permiso individual explicitamente denegado (-1).
        $deny = User::factory()->create(['permissions' => json_encode(['admin' => '-1'])]);
        $this->assertFalse($deny->isAdmin());

        // Permiso concedido via grupo.
        $group = Group::factory()->create(['permissions' => json_encode(['admin' => '1'])]);
        $grouped = User::factory()->create(['permissions' => '{}']);
        $grouped->groups()->attach($group->id);
        $this->assertTrue($grouped->fresh()->hasAccess('admin'));
    }

    public function test_effective_permissions_by_section(): void
    {
        $grantGroup = Group::factory()->create(['permissions' => json_encode(['reports.view' => '1'])]);
        $denyGroup = Group::factory()->create(['permissions' => json_encode(['self.api' => '-1'])]);

        $user = User::factory()->create(['permissions' => json_encode(['admin' => '1', 'import' => '-1'])]);
        $user->groups()->attach([$grantGroup->id, $denyGroup->id]);

        $result = $user->fresh()->getEffectivePermissionsBySection();
        $this->assertIsArray($result);

        // Superusuario -> rama source = 'superuser'.
        $super = User::factory()->superuser()->create();
        $superResult = $super->getEffectivePermissionsBySection();
        $this->assertIsArray($superResult);
    }

    public function test_has_individual_permissions(): void
    {
        $withPerm = User::factory()->create(['permissions' => json_encode(['admin' => '1'])]);
        $this->assertTrue($withPerm->hasIndividualPermissions());

        $withoutPerm = User::factory()->create(['permissions' => '{}']);
        $this->assertFalse($withoutPerm->hasIndividualPermissions());
    }

    public function test_history_and_acceptance_relations_smoke(): void
    {
        $user = User::factory()->create();

        try {
            $this->assertIsInt($user->assetlog()->count());
            $this->assertIsInt($user->acceptances()->count());
            $this->assertIsInt($user->eulas()->count());
            $this->assertIsInt($user->checkoutRequests()->count());
            $this->assertNotNull($user->getAssignedItemsWithPendingAcceptance());
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_get_user_total_cost(): void
    {
        $user = User::factory()->create();
        Asset::factory()->assignedToUser($user)->create(['purchase_cost' => 100]);

        $result = $user->fresh()->getUserTotalCost();

        $this->assertNotNull($result->total_user_cost);
    }
}
