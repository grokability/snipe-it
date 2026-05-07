<?php

namespace Tests\Feature\Reporting;

use App\Models\User;
use Tests\TestCase;

class UnacceptedAssetReportTest extends TestCase
{
    public function test_permission_required_to_view_unaccepted_asset_report()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports/unaccepted_assets'))
            ->assertForbidden();
    }

    public function test_user_can_list_unaccepted_assets()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('reports/unaccepted_assets'))
            ->assertOk();
    }
}
