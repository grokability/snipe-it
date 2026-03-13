<?php

namespace Tests\Feature\PredefinedKits\Api;

use App\Models\PredefinedKit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeletePredefinedKitsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $predefinedKit = PredefinedKit::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.kits.destroy', $predefinedKit))
            ->assertForbidden();

        $this->assertDatabaseHas('kits', ['id' => $predefinedKit->id]);
    }

    public function testCanDeletePredefinedKits()
    {
        $predefinedKit = PredefinedKit::factory()->create();

        $this->actingAsForApi(User::factory()->deletePredefinedKits()->create())
            ->deleteJson(route('api.kits.destroy', $predefinedKit))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('kits', ['id' => $predefinedKit->id]);
    }

    public function testAssociatedDataDetachedWhenPredefinedKitDeleted()
    {
        $predefinedKit = PredefinedKit::factory()
            ->hasAccessories()
            ->hasConsumables()
            ->hasLicenses()
            ->hasModels()
            ->create();

        $this->assertGreaterThan(0, $predefinedKit->accessories->count(), 'Precondition failed: PredefinedKit has no accessories');
        $this->assertGreaterThan(0, $predefinedKit->consumables->count(), 'Precondition failed: PredefinedKit has no consumables');
        $this->assertGreaterThan(0, $predefinedKit->licenses->count(), 'Precondition failed: PredefinedKit has no licenses');
        $this->assertGreaterThan(0, $predefinedKit->models->count(), 'Precondition failed: PredefinedKit has no models');

        $this->actingAsForApi(User::factory()->deletePredefinedKits()->create())
            ->deleteJson(route('api.kits.destroy', $predefinedKit))
            ->assertStatusMessageIs('success');

        $this->assertEquals(0, DB::table('kits_accessories')->where('kit_id', $predefinedKit->id)->count());
        $this->assertEquals(0, DB::table('kits_consumables')->where('kit_id', $predefinedKit->id)->count());
        $this->assertEquals(0, DB::table('kits_licenses')->where('kit_id', $predefinedKit->id)->count());
        $this->assertEquals(0, DB::table('kits_models')->where('kit_id', $predefinedKit->id)->count());
    }
}
