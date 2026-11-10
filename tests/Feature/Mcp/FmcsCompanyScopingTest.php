<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\AuditAssetTool;
use App\Mcp\Tools\CheckinAssetTool;
use App\Mcp\Tools\CheckoutAssetTool;
use App\Mcp\Tools\DeleteAssetTool;
use App\Mcp\Tools\UpdateAssetTool;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Tests\TestCase;

class FmcsCompanyScopingTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private Asset $assetA;

    private Asset $assetB;

    private User $userInCompanyA;

    private User $superUser;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

        $this->assetA = Asset::factory()->for($this->companyA)->create();
        $this->assetB = Asset::factory()->for($this->companyB)->create();

        $this->userInCompanyA = $this->companyA->users()->save(User::factory()->make());
        $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

        $this->settings->enableMultipleFullCompanySupport();
    }

    // --- UpdateAssetTool ---

    public function test_update_blocked_for_cross_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new UpdateAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
            'name' => 'Should Not Apply',
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('assets', ['id' => $this->assetB->id, 'name' => 'Should Not Apply']);
    }

    public function test_update_allowed_for_same_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new UpdateAssetTool)->handle(new Request([
            'asset_tag' => $this->assetA->asset_tag,
            'name' => 'Updated Name',
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $this->assetA->id, 'name' => 'Updated Name']);
    }

    // --- DeleteAssetTool ---

    public function test_delete_blocked_for_cross_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new DeleteAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted($this->assetB);
    }

    public function test_delete_allowed_for_same_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new DeleteAssetTool)->handle(new Request([
            'asset_tag' => $this->assetA->asset_tag,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->assetA);
    }

    // --- AuditAssetTool ---

    public function test_audit_blocked_for_cross_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        (new AuditAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
        ]));

        $this->assertNull($this->assetB->fresh()->last_audit_date);
    }

    public function test_audit_allowed_for_same_company_asset()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new AuditAssetTool)->handle(new Request([
            'asset_tag' => $this->assetA->asset_tag,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertNotNull($this->assetA->fresh()->last_audit_date);
    }

    // --- CheckinAssetTool ---

    public function test_checkin_blocked_for_cross_company_asset()
    {
        $user = $this->companyB->users()->save(User::factory()->make());
        $checkedOutAsset = Asset::factory()->for($this->companyB)->assignedToUser($user)->create();

        $this->actingAs($this->userInCompanyA);

        $response = (new CheckinAssetTool)->handle(new Request([
            'asset_tag' => $checkedOutAsset->asset_tag,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('assets', ['id' => $checkedOutAsset->id, 'assigned_to' => $user->id]);
    }

    public function test_checkin_allowed_for_same_company_asset()
    {
        $user = $this->companyA->users()->save(User::factory()->make());
        $asset = Asset::factory()->for($this->companyA)->assignedToUser($user)->create();

        $this->actingAs($this->userInCompanyA);

        $content = (new CheckinAssetTool)->handle(new Request([
            'asset_tag' => $asset->asset_tag,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'assigned_to' => null]);
    }

    // --- CheckoutAssetTool ---

    public function test_checkout_blocked_for_cross_company_asset()
    {
        $user = $this->companyA->users()->save(User::factory()->make());

        $this->actingAs($this->userInCompanyA);

        $response = (new CheckoutAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('assets', ['id' => $this->assetB->id, 'assigned_to' => null]);
    }

    public function test_checkout_allowed_for_same_company_asset()
    {
        $user = $this->companyA->users()->save(User::factory()->make());

        $this->actingAs($this->userInCompanyA);

        $content = (new CheckoutAssetTool)->handle(new Request([
            'asset_tag' => $this->assetA->asset_tag,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $this->assetA->id, 'assigned_to' => $user->id]);
    }

    // --- Superuser bypass ---

    public function test_superuser_can_update_asset_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new UpdateAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
            'name' => 'Superuser Override',
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('assets', ['id' => $this->assetB->id, 'name' => 'Superuser Override']);
    }

    public function test_superuser_can_delete_asset_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new DeleteAssetTool)->handle(new Request([
            'asset_tag' => $this->assetB->asset_tag,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->assetB);
    }
}
