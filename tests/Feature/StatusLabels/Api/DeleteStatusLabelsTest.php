<?php

namespace Tests\Feature\StatusLabels\Api;

use App\Models\Statuslabel;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteStatusLabelsTest extends TestCase implements TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $statusLabel = Statuslabel::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertForbidden();

        $this->assertNotSoftDeleted($statusLabel);
    }

    public function testCannotDeleteStatusLabelWhileStillAssociatedToAssets()
    {
        $statusLabel = Statuslabel::factory()->hasAssets()->create();

        $this->assertGreaterThan(0, $statusLabel->assets->count(), 'Precondition failed: StatusLabel has no assets');

        $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($statusLabel);
    }

    public function testCanDeleteStatusLabel()
    {
        $statusLabel = Statuslabel::factory()->create();

        $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($statusLabel);
    }
}
