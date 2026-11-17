<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\OffboardUserTool;
use App\Models\Asset;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\ResponseFactory;
use Tests\TestCase;

class OffboardUserToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function handle(array $args): ResponseFactory
    {
        return resolve(OffboardUserTool::class)->handle(new Request($args));
    }

    public function test_dry_run_returns_preview_without_writing()
    {
        $user = User::factory()->create(['username' => 'leaver']);
        $asset = Asset::factory()->assignedToUser($user)->create();

        $content = $this->handle(['username' => 'leaver'])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertCount(1, $content['preview']['assets_to_checkin']);
        // Nothing should have changed in the DB
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $user->id]);
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_dry_run_is_default_when_not_specified()
    {
        $user = User::factory()->create(['username' => 'leaver']);

        $content = $this->handle(['username' => 'leaver'])->getStructuredContent();

        $this->assertTrue($content['dry_run']);
    }

    public function test_execute_checks_in_all_assigned_assets()
    {
        $user = User::factory()->create(['username' => 'leaver']);
        $assetA = Asset::factory()->assignedToUser($user)->create();
        $assetB = Asset::factory()->assignedToUser($user)->create();

        $this->handle(['username' => 'leaver', 'dry_run' => false]);

        $this->assertNull($assetA->fresh()->assigned_to);
        $this->assertNull($assetB->fresh()->assigned_to);
    }

    public function test_execute_releases_assigned_license_seats()
    {
        $user = User::factory()->create(['username' => 'leaver']);
        $license = License::factory()->create();
        $seat = LicenseSeat::factory()->create([
            'license_id' => $license->id,
            'assigned_to' => $user->id,
        ]);

        $this->handle(['username' => 'leaver', 'dry_run' => false]);

        $this->assertNull($seat->fresh()->assigned_to);
    }

    public function test_execute_nulls_manager_id_on_dependent_users()
    {
        $manager = User::factory()->create(['username' => 'themanager']);
        $report = User::factory()->create(['manager_id' => $manager->id]);

        $this->handle(['username' => 'themanager', 'dry_run' => false]);

        $this->assertNull($report->fresh()->manager_id);
    }

    public function test_execute_nulls_manager_id_on_dependent_locations()
    {
        $manager = User::factory()->create(['username' => 'themanager']);
        $location = Location::factory()->create(['manager_id' => $manager->id]);

        $this->handle(['username' => 'themanager', 'dry_run' => false]);

        $this->assertNull($location->fresh()->manager_id);
    }

    public function test_execute_soft_deletes_the_user()
    {
        $user = User::factory()->create(['username' => 'leaver']);

        $this->handle(['username' => 'leaver', 'dry_run' => false]);

        $this->assertNotNull(User::withTrashed()->find($user->id)->deleted_at);
    }

    public function test_execute_does_not_touch_activated_flag_directly()
    {
        // Many orgs never set `activated` on real users, so the tool must
        // use soft-delete as the exit signal and not just flip activated=0.
        $user = User::factory()->create(['username' => 'leaver', 'activated' => 0]);

        $this->handle(['username' => 'leaver', 'dry_run' => false]);

        $this->assertNotNull(User::withTrashed()->find($user->id)->deleted_at);
    }

    public function test_resolves_user_by_first_and_last_name()
    {
        $user = User::factory()->create([
            'first_name' => 'Taylor',
            'last_name' => 'Otwell',
            'username' => 'taylor',
        ]);

        $content = $this->handle(['first_name' => 'Taylor', 'last_name' => 'Otwell'])
            ->getStructuredContent();

        $this->assertTrue($content['dry_run']);
        $this->assertSame($user->id, $content['preview']['user']['id']);
    }

    public function test_returns_ambiguous_match_set_when_name_matches_multiple_users()
    {
        User::factory()->count(2)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $content = $this->handle(['first_name' => 'John', 'last_name' => 'Doe'])
            ->getStructuredContent();

        $this->assertFalse($content['success']);
        $this->assertSame('ambiguous_user', $content['error']);
        $this->assertCount(2, $content['matches']);
    }

    public function test_returns_error_when_no_identifier_provided()
    {
        $this->assertTrue($this->handle(['dry_run' => false])->responses()->first()->isError());
    }

    public function test_returns_error_when_user_not_found()
    {
        $this->assertTrue($this->handle(['username' => 'nobody'])->responses()->first()->isError());
    }

    public function test_returns_error_when_acting_user_lacks_permission()
    {
        $this->actingAs(User::factory()->create());

        $user = User::factory()->create(['username' => 'leaver']);
        $asset = Asset::factory()->assignedToUser($user)->create();

        $this->assertTrue($this->handle(['username' => 'leaver'])->responses()->first()->isError());
        // No changes made.
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => $user->id]);
        $this->assertNull($user->fresh()->deleted_at);
    }

    public function test_preview_includes_managed_users_and_locations()
    {
        $manager = User::factory()->create(['username' => 'themanager']);
        $report = User::factory()->create(['manager_id' => $manager->id]);
        $location = Location::factory()->create(['manager_id' => $manager->id]);

        $content = $this->handle(['username' => 'themanager'])->getStructuredContent();

        $managedUserIds = array_column($content['preview']['managed_users_to_unparent'], 'id');
        $managedLocationIds = array_column($content['preview']['managed_locations_to_unparent'], 'id');

        $this->assertContains($report->id, $managedUserIds);
        $this->assertContains($location->id, $managedLocationIds);
    }
}
