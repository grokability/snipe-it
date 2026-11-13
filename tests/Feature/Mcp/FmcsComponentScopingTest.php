<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckinComponentTool;
use App\Mcp\Tools\CheckoutComponentTool;
use App\Mcp\Tools\DeleteComponentTool;
use App\Mcp\Tools\UpdateComponentTool;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Tests\TestCase;

class FmcsComponentScopingTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private Component $componentA;

    private Component $componentB;

    private User $userInCompanyA;

    private User $superUser;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

        $this->componentA = Component::factory()->for($this->companyA)->create(['qty' => 5]);
        $this->componentB = Component::factory()->for($this->companyB)->create(['qty' => 5]);

        $this->userInCompanyA = $this->companyA->users()->save(
            User::factory()->editComponents()->deleteComponents()->checkoutComponents()->checkinComponents()->make()
        );
        $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

        $this->settings->enableMultipleFullCompanySupport();
    }

    // --- UpdateComponentTool ---

    public function test_update_blocked_for_cross_company_component()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new UpdateComponentTool)->handle(new Request([
            'id' => $this->componentB->id,
            'qty' => 99,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('components', ['id' => $this->componentB->id, 'qty' => 99]);
    }

    public function test_update_allowed_for_same_company_component()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new UpdateComponentTool)->handle(new Request([
            'id' => $this->componentA->id,
            'qty' => 10,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components', ['id' => $this->componentA->id, 'qty' => 10]);
    }

    // --- DeleteComponentTool ---

    public function test_delete_blocked_for_cross_company_component()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new DeleteComponentTool)->handle(new Request([
            'id' => $this->componentB->id,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted($this->componentB);
    }

    public function test_delete_allowed_for_same_company_component()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new DeleteComponentTool)->handle(new Request([
            'id' => $this->componentA->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->componentA);
    }

    // --- CheckoutComponentTool ---

    public function test_checkout_blocked_for_cross_company_component()
    {
        $asset = Asset::factory()->for($this->companyA)->create();

        $this->actingAs($this->userInCompanyA);

        $response = (new CheckoutComponentTool)->handle(new Request([
            'id' => $this->componentB->id,
            'asset_id' => $asset->id,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('components_assets', ['component_id' => $this->componentB->id]);
    }

    public function test_checkout_allowed_for_same_company_component()
    {
        $asset = Asset::factory()->for($this->companyA)->create();

        $this->actingAs($this->userInCompanyA);

        $content = (new CheckoutComponentTool)->handle(new Request([
            'id' => $this->componentA->id,
            'asset_id' => $asset->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components_assets', ['component_id' => $this->componentA->id, 'asset_id' => $asset->id]);
    }

    // --- CheckinComponentTool ---

    public function test_checkin_blocked_for_cross_company_component()
    {
        $asset = Asset::factory()->for($this->companyB)->create();

        // Checkout component B to asset B as superuser
        $componentAssetId = DB::table('components_assets')->insertGetId([
            'component_id' => $this->componentB->id,
            'asset_id' => $asset->id,
            'assigned_qty' => 1,
            'created_by' => $this->superUser->id,
            'created_at' => now(),
        ]);

        $this->actingAs($this->userInCompanyA);

        $response = (new CheckinComponentTool)->handle(new Request([
            'component_asset_id' => $componentAssetId,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('components_assets', ['id' => $componentAssetId]);
    }

    // --- Superuser bypass ---

    public function test_superuser_can_update_component_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new UpdateComponentTool)->handle(new Request([
            'id' => $this->componentB->id,
            'qty' => 20,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('components', ['id' => $this->componentB->id, 'qty' => 20]);
    }

    public function test_superuser_can_delete_component_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new DeleteComponentTool)->handle(new Request([
            'id' => $this->componentB->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->componentB);
    }
}
