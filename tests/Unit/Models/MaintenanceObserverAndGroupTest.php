<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Group;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class MaintenanceObserverAndGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    public function test_maintenance_creating_copies_checkout_target_from_asset(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['assigned_to' => $user->id, 'assigned_type' => User::class]);

        $maintenance = Maintenance::factory()->create(['asset_id' => $asset->id]);

        // El observer creating() copia el destino de checkout del activo.
        $this->assertEquals($user->id, $maintenance->fresh()->checked_out_to_id);
    }

    public function test_maintenance_updating(): void
    {
        $maintenance = Maintenance::factory()->create();
        $maintenance->update(['notes' => 'updated', 'cost' => '199.99']);

        $this->assertEquals('updated', $maintenance->fresh()->notes);
    }

    public function test_group_members_and_users(): void
    {
        $group = Group::factory()->create();
        $user = User::factory()->create();
        $user->groups()->attach($group->id);

        $this->assertInstanceOf(Collection::class, $group->users()->get());
        $this->assertInstanceOf(Collection::class, $group->members()->get());
        $this->assertGreaterThanOrEqual(1, $group->users()->count());
    }
}
