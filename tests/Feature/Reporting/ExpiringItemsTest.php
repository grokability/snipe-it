<?php

namespace Feature\Reporting;

use App\Models\User;
use Tests\TestCase;

class ExpiringItemsTest extends TestCase
{
    public function test_user_without_reports_view_cannot_access_expiring_assets_api()
    {
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.expiring-assets'))
            ->assertForbidden();
    }

    public function test_user_with_reports_view_can_access_expiring_assets_api()
    {
        $user = User::factory()->canViewReports()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.expiring-assets'))
            ->assertOk();
    }

    public function test_user_without_reports_view_cannot_access_expiring_licenses_api()
    {
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.expiring-licenses'))
            ->assertForbidden();
    }

    public function test_user_with_reports_view_can_access_expiring_licenses_api()
    {
        $user = User::factory()->canViewReports()->create();

        $this->actingAsForApi($user)
            ->getJson(route('api.expiring-licenses'))
            ->assertOk();
    }
}