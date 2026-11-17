<?php

namespace Tests\Feature\Reporting;

use App\Models\User;
use Tests\TestCase;

/**
 * Page-access + endpoint-target + breadcrumb tests for the
 * reports/activity page. The report page is reachable by two
 * viewer classes with different UI:
 *   - reports.view holders get the full page with the general
 *     search / filter / sort endpoint.
 *   - Scoped viewers (canViewUsersAndCheckoutables, no reports.view)
 *     arrive through the dashboard Recent Activity widget's View-all
 *     button, land on the same template with admin-shaped UI stripped,
 *     and read from the narrow api.dashboard.activity endpoint.
 *
 * CSV export path lives in ActivityReportCsvExportTest so this file
 * stays under PHPMD's per-class method threshold.
 */
class ActivityReportPageAuthTest extends TestCase
{
    public function test_permissionless_user_is_forbidden()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports.activity'))
            ->assertForbidden();
    }

    public function test_reports_view_holder_reaches_page()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.activity'))
            ->assertOk();
    }

    public function test_scoped_viewer_reaches_page()
    {
        $this->actingAs(User::factory()->viewComponents()->create())
            ->get(route('reports.activity'))
            ->assertOk();
    }

    public function test_scoped_viewer_page_points_at_narrow_dashboard_endpoint()
    {
        $this->actingAs(User::factory()->viewComponents()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertSee(route('api.dashboard.activity'))
            ->assertDontSee(route('api.activity.index'));
    }

    public function test_reports_view_holder_page_points_at_general_endpoint()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertSee(route('api.activity.index'));
    }

    public function test_scoped_viewer_breadcrumb_does_not_link_to_reports_index()
    {
        // reports.index is admin-shaped. A scoped viewer's activity
        // report breadcrumb must not include a "Reports" crumb whose
        // link would 403 on click. Uses the URL with the closing "
        // so plain substring assertions don't collide with the
        // reports.activity URL (which contains reports.index's URL
        // as a prefix).
        $this->actingAs(User::factory()->viewComponents()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertDontSee(route('reports.index').'"', false);
    }

    public function test_reports_view_holder_breadcrumb_links_to_reports_index()
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports.activity'))
            ->assertOk()
            ->assertSee(route('reports.index').'"', false);
    }
}
