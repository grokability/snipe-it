<?php

namespace Tests\Feature\Depreciations\Api;

use App\Models\Depreciation;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteDepreciationsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $depreciation = Depreciation::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.depreciations.destroy', $depreciation))
            ->assertForbidden();

        $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
    }

    public function testCannotDeleteDepreciationThatHasAssociatedModels()
    {
        $depreciation = Depreciation::factory()->hasModels()->create();

        $this->actingAsForApi(User::factory()->deleteDepreciations()->create())
            ->deleteJson(route('api.depreciations.destroy', $depreciation))
            ->assertStatusMessageIs('error');

        $this->assertDatabaseHas('depreciations', ['id' => $depreciation->id]);
    }

    public function testCanDeleteDepreciation()
    {
        $depreciation = Depreciation::factory()->create();

        $this->actingAsForApi(User::factory()->deleteDepreciations()->create())
            ->deleteJson(route('api.depreciations.destroy', $depreciation))
            ->assertStatusMessageIs('success');

        $this->assertDatabaseMissing('depreciations', ['id' => $depreciation->id]);
    }
}
