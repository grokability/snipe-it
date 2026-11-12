<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\ListAccessoriesTool;
use App\Mcp\Tools\OffboardUserTool;
use App\Mcp\Tools\TransferAssetsTool;
use App\Mcp\Tools\UploadAssetImageTool;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;
use Tests\TestCase;

/**
 * Adversarial cross-company coverage for the tools added alongside the
 * atomic-workflow + rich-filter pass. One happy path, one cross-company
 * rejection path per tool, repeated across FMCS strict mode, floater
 * mode, and scoped-locations mode.
 */
class FmcsNewToolsScopingTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private User $userInA;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();
        // Non-superuser with the perms the tools need. Superusers bypass
        // FMCS so would hide the scope enforcement we are trying to verify.
        $this->userInA = $this->companyA->users()->save(
            User::factory()
                ->editUsers()
                ->editAssets()
                ->checkinAssets()
                ->checkoutAssets()
                ->viewAccessories()
                ->make()
        );
    }

    // --- OffboardUserTool ---

    public function test_strict_offboard_blocks_cross_company_target()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

        $this->actingAs($this->userInA);
        $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNull($targetInB->fresh()->deleted_at);
    }

    public function test_floater_offboard_blocks_cross_company_target()
    {
        $this->settings->enableFloaterMode();
        $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

        $this->actingAs($this->userInA);
        $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNull($targetInB->fresh()->deleted_at);
    }

    public function test_scoped_locations_offboard_blocks_cross_company_target()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'leaver']));

        $this->actingAs($this->userInA);
        $response = (new OffboardUserTool)->handle(new Request(['username' => 'leaver']));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNull($targetInB->fresh()->deleted_at);
    }

    public function test_strict_offboard_allows_same_company_target()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));

        $this->actingAs($this->userInA);
        $content = (new OffboardUserTool)->handle(new Request([
            'username' => 'leaver',
            'dry_run' => false,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotNull(User::withTrashed()->find($targetInA->id)->deleted_at);
    }

    public function test_strict_offboard_blocks_target_with_unreachable_pivot_company()
    {
        // Multi-company user case: target belongs to BOTH the actor's
        // company AND a company the actor cannot reach. The scoped
        // User::where query DOES find them (shared Company A pivot
        // row), and the Gate::allows('update') check passes, but
        // offboarding would check in only the actor-visible items via
        // FMCS-scoped queries and then soft-delete the whole user,
        // orphaning the Company B assignments. The tool must refuse.
        $this->settings->enableMultipleFullCompanySupport();
        // Use ->save(make()) to avoid the User factory's auto-attach a
        // fresh company behavior in create(), which would add a third
        // unreachable pivot row and muddy the "both companies, nothing
        // extra" setup this test is pinning down.
        $targetBoth = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));
        $this->companyB->users()->attach($targetBoth);

        $this->actingAs($this->userInA);
        $response = (new OffboardUserTool)->handle(new Request([
            'username' => 'leaver',
            'dry_run' => false,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNull($targetBoth->fresh()->deleted_at);
    }

    public function test_superuser_offboard_allows_target_with_unreachable_pivot_company()
    {
        // Superusers bypass the cross-company guard: they can reach
        // every company, so there is no "unreachable pivot row" case
        // for them to protect against.
        $this->settings->enableMultipleFullCompanySupport();
        $targetBoth = $this->companyA->users()->save(User::factory()->make(['username' => 'leaver']));
        $this->companyB->users()->attach($targetBoth);

        $this->actingAs(User::factory()->superuser()->create());
        $content = (new OffboardUserTool)->handle(new Request([
            'username' => 'leaver',
            'dry_run' => false,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotNull(User::withTrashed()->find($targetBoth->id)->deleted_at);
    }

    // --- TransferAssetsTool ---

    public function test_strict_transfer_blocks_cross_company_source()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $sourceInB = $this->companyB->users()->save(User::factory()->make(['username' => 'source']));
        $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'target']));

        $this->actingAs($this->userInA);
        $response = (new TransferAssetsTool)->handle(new Request([
            'from_username' => 'source',
            'to_username' => 'target',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_floater_transfer_blocks_cross_company_source()
    {
        $this->settings->enableFloaterMode();
        $sourceInB = $this->companyB->users()->save(User::factory()->make(['username' => 'source']));
        $targetInA = $this->companyA->users()->save(User::factory()->make(['username' => 'target']));

        $this->actingAs($this->userInA);
        $response = (new TransferAssetsTool)->handle(new Request([
            'from_username' => 'source',
            'to_username' => 'target',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_scoped_locations_transfer_blocks_cross_company_target()
    {
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $sourceInA = $this->companyA->users()->save(User::factory()->make(['username' => 'source']));
        $targetInB = $this->companyB->users()->save(User::factory()->make(['username' => 'target']));

        $this->actingAs($this->userInA);
        $response = (new TransferAssetsTool)->handle(new Request([
            'from_username' => 'source',
            'to_username' => 'target',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
    }

    // --- UploadAssetImageTool ---

    public function test_strict_upload_blocks_cross_company_asset()
    {
        Storage::fake('public');
        $this->settings->enableMultipleFullCompanySupport();
        $assetB = Asset::factory()->for($this->companyB)->create();

        $this->actingAs($this->userInA);
        $response = (new UploadAssetImageTool)->handle(new Request([
            'asset_tag' => $assetB->asset_tag,
            'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'mime_type' => 'image/png',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNull($assetB->fresh()->image);
    }

    public function test_floater_upload_blocks_cross_company_asset()
    {
        Storage::fake('public');
        $this->settings->enableFloaterMode();
        $assetB = Asset::factory()->for($this->companyB)->create();

        $this->actingAs($this->userInA);
        $response = (new UploadAssetImageTool)->handle(new Request([
            'asset_tag' => $assetB->asset_tag,
            'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'mime_type' => 'image/png',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
    }

    public function test_scoped_locations_upload_blocks_cross_company_asset()
    {
        Storage::fake('public');
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        $assetB = Asset::factory()->for($this->companyB)->create();

        $this->actingAs($this->userInA);
        $response = (new UploadAssetImageTool)->handle(new Request([
            'asset_tag' => $assetB->asset_tag,
            'image_base64' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI7wAAAABJRU5ErkJggg==',
            'mime_type' => 'image/png',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
    }

    // --- ListAccessoriesTool ---

    public function test_strict_list_accessories_excludes_other_company()
    {
        $this->settings->enableMultipleFullCompanySupport();
        $mine = Accessory::factory()->for($this->companyA)->create(['name' => 'OursA']);
        $theirs = Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

        $this->actingAs($this->userInA);
        $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
        $names = array_column($content['accessories'], 'name');

        $this->assertContains('OursA', $names);
        $this->assertNotContains('TheirsB', $names);
    }

    public function test_floater_list_accessories_includes_null_company_accessory()
    {
        $this->settings->enableFloaterMode();
        $mine = Accessory::factory()->for($this->companyA)->create(['name' => 'OursA']);
        $floater = Accessory::factory()->create(['name' => 'Floater', 'company_id' => null]);
        $theirs = Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

        $this->actingAs($this->userInA);
        $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
        $names = array_column($content['accessories'], 'name');

        $this->assertContains('OursA', $names);
        $this->assertContains('Floater', $names);
        $this->assertNotContains('TheirsB', $names);
    }

    public function test_scoped_locations_list_accessories_excludes_other_company()
    {
        // In scoped-locations mode, visibility also depends on
        // user-location matching, so we only assert the cross-company
        // negative here. "Can see own company" is covered by the strict
        // and floater variants above.
        $this->settings->enableScopedLocationsWithFullMultipleCompanySupport();
        Accessory::factory()->for($this->companyB)->create(['name' => 'TheirsB']);

        $this->actingAs($this->userInA);
        $content = (new ListAccessoriesTool)->handle(new Request(['limit' => 500]))->getStructuredContent();
        $names = array_column($content['accessories'], 'name');

        $this->assertNotContains('TheirsB', $names);
    }
}
