<?php

namespace Tests\Feature\Users\Api;

use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for GitHub #19334: /users/{id}/assets, /accessories,
 * /licenses, and /eulas emitted pagination metadata but returned every row,
 * ignoring ?limit and ?page. Each test seeds N rows for the target user and a
 * few extra rows for a bystander user (so we also catch a "return everything
 * across all users" regression), then asserts total, rows count, per_page,
 * current_page, and next/prev URL against a specific ?limit/?page query.
 */
class UserItemsPaginationTest extends TestCase
{
    public function test_assets_endpoint_paginates_rows_and_reports_accurate_metadata(): void
    {
        $user = User::factory()->create();
        $bystander = User::factory()->create();

        Asset::factory()->count(7)->assignedToUser($user)->create();
        Asset::factory()->count(3)->assignedToUser($bystander)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewAssets()->create())
            ->getJson(route('api.users.assetlist', ['user' => $user->id, 'limit' => 3, 'page' => 2]))
            ->assertOk()
            ->json();

        $this->assertSame(7, $response['total'], 'total should reflect the users assigned assets only');
        $this->assertCount(3, $response['rows'], 'rows should be paginated to per_page');
        $this->assertSame(3, $response['per_page']);
        $this->assertSame(2, $response['current_page']);
        $this->assertSame(3, $response['total_pages']);
        $this->assertNotNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_assets_endpoint_last_page_returns_remainder_and_no_next_url(): void
    {
        $user = User::factory()->create();
        Asset::factory()->count(7)->assignedToUser($user)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewAssets()->create())
            ->getJson(route('api.users.assetlist', ['user' => $user->id, 'limit' => 3, 'page' => 3]))
            ->assertOk()
            ->json();

        $this->assertSame(7, $response['total']);
        $this->assertCount(1, $response['rows']);
        $this->assertSame(3, $response['current_page']);
        $this->assertNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_accessories_endpoint_paginates_rows_and_reports_accurate_metadata(): void
    {
        $user = User::factory()->create();
        $bystander = User::factory()->create();

        Accessory::factory()->count(5)->checkedOutToUser($user)->create();
        Accessory::factory()->count(2)->checkedOutToUser($bystander)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewAccessories()->create())
            ->getJson(route('api.users.accessorieslist', ['user' => $user->id, 'limit' => 2, 'page' => 2]))
            ->assertOk()
            ->json();

        $this->assertSame(5, $response['total']);
        $this->assertCount(2, $response['rows']);
        $this->assertSame(2, $response['per_page']);
        $this->assertSame(2, $response['current_page']);
        $this->assertSame(3, $response['total_pages']);
        $this->assertNotNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_licenses_endpoint_paginates_rows_and_reports_accurate_metadata(): void
    {
        $user = User::factory()->create();
        $bystander = User::factory()->create();

        $userLicenses = License::factory()->count(6)->create();
        foreach ($userLicenses as $license) {
            LicenseSeat::factory()->for($license)->assignedToUser($user)->create();
        }

        $bystanderLicense = License::factory()->create();
        LicenseSeat::factory()->for($bystanderLicense)->assignedToUser($bystander)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewLicenses()->create())
            ->getJson(route('api.users.licenselist', ['user' => $user->id, 'limit' => 2, 'page' => 2]))
            ->assertOk()
            ->json();

        $this->assertSame(6, $response['total']);
        $this->assertCount(2, $response['rows']);
        $this->assertSame(2, $response['per_page']);
        $this->assertSame(2, $response['current_page']);
        $this->assertSame(3, $response['total_pages']);
        $this->assertNotNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_eulas_endpoint_paginates_rows_and_reports_accurate_metadata(): void
    {
        $user = User::factory()->create();
        $bystander = User::factory()->create();

        // eulas() filters on target_type=User, action_type=accepted, and
        // NOT NULL filename + accept_signature. Bystander rows and rows
        // missing either field must be excluded from the total.
        Actionlog::factory()->count(4)->create([
            'target_type' => User::class,
            'target_id' => $user->id,
            'item_type' => Asset::class,
            'action_type' => 'accepted',
            'filename' => 'eula.pdf',
            'accept_signature' => 'sig',
        ]);
        Actionlog::factory()->create([
            'target_type' => User::class,
            'target_id' => $bystander->id,
            'item_type' => Asset::class,
            'action_type' => 'accepted',
            'filename' => 'eula.pdf',
            'accept_signature' => 'sig',
        ]);

        $response = $this->actingAsForApi(User::factory()->viewUsers()->create())
            ->getJson(route('api.user.eulas', ['user' => $user->id, 'limit' => 2, 'page' => 2]))
            ->assertOk()
            ->json();

        $this->assertSame(4, $response['total']);
        $this->assertCount(2, $response['rows']);
        $this->assertSame(2, $response['per_page']);
        $this->assertSame(2, $response['current_page']);
        $this->assertSame(2, $response['total_pages']);
        $this->assertNull($response['next_page_url']);
        $this->assertNotNull($response['prev_page_url']);
    }

    public function test_out_of_range_page_returns_empty_rows_but_keeps_total(): void
    {
        $user = User::factory()->create();
        Asset::factory()->count(2)->assignedToUser($user)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewAssets()->create())
            ->getJson(route('api.users.assetlist', ['user' => $user->id, 'limit' => 10, 'page' => 99]))
            ->assertOk()
            ->json();

        $this->assertSame(2, $response['total']);
        $this->assertCount(0, $response['rows']);
    }

    public function test_default_no_pagination_params_still_returns_paged_response(): void
    {
        // Without ?limit or ?page, the middleware falls back to config
        // app.max_results (150 in this app). The rows should include every
        // matching item up to that ceiling, with current_page=1 and no prev
        // URL. Guards against a regression where "no page param" bypassed
        // the skip/take entirely.
        $user = User::factory()->create();
        Asset::factory()->count(4)->assignedToUser($user)->create();

        $response = $this->actingAsForApi(User::factory()->viewUsers()->viewAssets()->create())
            ->getJson(route('api.users.assetlist', ['user' => $user->id]))
            ->assertOk()
            ->json();

        $this->assertSame(4, $response['total']);
        $this->assertCount(4, $response['rows']);
        $this->assertSame(1, $response['current_page']);
        $this->assertNull($response['prev_page_url']);
        $this->assertNull($response['next_page_url']);
    }
}
