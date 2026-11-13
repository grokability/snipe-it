<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\CheckinAccessoryTool;
use App\Mcp\Tools\CheckoutAccessoryTool;
use App\Mcp\Tools\DeleteAccessoryTool;
use App\Mcp\Tools\UpdateAccessoryTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;
use Tests\TestCase;

class FmcsAccessoryScopingTest extends TestCase
{
    private Company $companyA;

    private Company $companyB;

    private Accessory $accessoryA;

    private Accessory $accessoryB;

    private User $userInCompanyA;

    private User $superUser;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

        $this->accessoryA = Accessory::factory()->for($this->companyA)->create(['qty' => 5]);
        $this->accessoryB = Accessory::factory()->for($this->companyB)->create(['qty' => 5]);

        $this->userInCompanyA = $this->companyA->users()->save(
            User::factory()->editAccessories()->deleteAccessories()->checkoutAccessories()->checkinAccessories()->make()
        );
        $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

        $this->settings->enableMultipleFullCompanySupport();
    }

    // --- UpdateAccessoryTool ---

    public function test_update_blocked_for_cross_company_accessory()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new UpdateAccessoryTool)->handle(new Request([
            'id' => $this->accessoryB->id,
            'qty' => 99,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('accessories', ['id' => $this->accessoryB->id, 'qty' => 99]);
    }

    public function test_update_allowed_for_same_company_accessory()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new UpdateAccessoryTool)->handle(new Request([
            'id' => $this->accessoryA->id,
            'qty' => 10,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories', ['id' => $this->accessoryA->id, 'qty' => 10]);
    }

    // --- DeleteAccessoryTool ---

    public function test_delete_blocked_for_cross_company_accessory()
    {
        $this->actingAs($this->userInCompanyA);

        $response = (new DeleteAccessoryTool)->handle(new Request([
            'id' => $this->accessoryB->id,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertNotSoftDeleted($this->accessoryB);
    }

    public function test_delete_allowed_for_same_company_accessory()
    {
        $this->actingAs($this->userInCompanyA);

        $content = (new DeleteAccessoryTool)->handle(new Request([
            'id' => $this->accessoryA->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->accessoryA);
    }

    // --- CheckoutAccessoryTool ---

    public function test_checkout_blocked_for_cross_company_accessory()
    {
        $user = $this->companyA->users()->save(User::factory()->make());

        $this->actingAs($this->userInCompanyA);

        $response = (new CheckoutAccessoryTool)->handle(new Request([
            'id' => $this->accessoryB->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseMissing('accessories_checkout', ['accessory_id' => $this->accessoryB->id]);
    }

    public function test_checkout_allowed_for_same_company_accessory()
    {
        $user = $this->companyA->users()->save(User::factory()->make());

        $this->actingAs($this->userInCompanyA);

        $content = (new CheckoutAccessoryTool)->handle(new Request([
            'id' => $this->accessoryA->id,
            'checkout_to_type' => 'user',
            'assigned_user' => $user->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories_checkout', ['accessory_id' => $this->accessoryA->id]);
    }

    // --- CheckinAccessoryTool ---

    public function test_checkin_blocked_for_cross_company_accessory()
    {
        $user = $this->companyB->users()->save(User::factory()->make());
        $this->actingAs($this->userInCompanyA);

        // Checkout accessory B as superuser (different company), then try to check it in as userInCompanyA
        $checkoutId = AccessoryCheckout::create([
            'accessory_id' => $this->accessoryB->id,
            'assigned_to' => $user->id,
            'assigned_type' => User::class,
            'created_by' => $this->superUser->id,
        ])->id;

        $response = (new CheckinAccessoryTool)->handle(new Request([
            'checkout_id' => $checkoutId,
        ]));

        $this->assertTrue($response->responses()->first()->isError());
        $this->assertDatabaseHas('accessories_checkout', ['id' => $checkoutId]);
    }

    // --- Superuser bypass ---

    public function test_superuser_can_update_accessory_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new UpdateAccessoryTool)->handle(new Request([
            'id' => $this->accessoryB->id,
            'qty' => 20,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertDatabaseHas('accessories', ['id' => $this->accessoryB->id, 'qty' => 20]);
    }

    public function test_superuser_can_delete_accessory_in_any_company()
    {
        $this->actingAs($this->superUser);

        $content = (new DeleteAccessoryTool)->handle(new Request([
            'id' => $this->accessoryB->id,
        ]))->getStructuredContent();

        $this->assertTrue($content['success']);
        $this->assertSoftDeleted($this->accessoryB);
    }
}
