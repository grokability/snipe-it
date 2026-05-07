<?php

namespace Tests\Feature\StatusLabels\Api;

use App\Models\Statuslabel;
use App\Models\User;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteStatusLabelsTest extends TestCase implements TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $statusLabel = Statuslabel::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertForbidden();

        $this->assertNotSoftDeleted($statusLabel);
    }

    public function test_cannot_delete_status_label_while_still_associated_to_assets()
    {
        $statusLabel = Statuslabel::factory()->hasAssets()->create();

        $this->assertGreaterThan(0, $statusLabel->assets->count(), 'Precondition failed: StatusLabel has no assets');

        $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertStatusMessageIs('error');

        $this->assertNotSoftDeleted($statusLabel);
    }

    public function test_can_delete_status_label()
    {
        $statusLabel = Statuslabel::factory()->create();

        $this->actingAsForApi(User::factory()->deleteStatusLabels()->create())
            ->deleteJson(route('api.statuslabels.destroy', $statusLabel))
            ->assertOk()
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($statusLabel);
    }
}
