<?php

namespace Tests\Feature\Accessories\Ui;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class CheckoutAccessoryTest extends TestCase
{
    public function test_checkout_to_user_in_same_company_succeeds_with_fmcs_enabled()
    {
        [$companyA] = Company::factory()->count(1)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $user = User::factory()->for($companyA)->create();
        $user->companies()->sync([$companyA->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_checkout_to_user_in_different_company_is_blocked_with_fmcs_enabled()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $user = User::factory()->for($companyB)->create();
        $user->companies()->sync([$companyB->id]);

        $this->settings->enableMultipleFullCompanySupport();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_checkout_to_user_is_blocked_when_accessory_has_no_company_with_fmcs_enabled_without_floater()
    {
        $accessory = Accessory::factory()->create(['qty' => 5, 'company_id' => null]);
        [$companyA] = Company::factory()->count(1)->create();
        $user = User::factory()->for($companyA)->create();
        $user->companies()->sync([$companyA->id]);

        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->disableFloaterMode();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_checkout_to_user_succeeds_when_accessory_has_no_company_with_floater_enabled()
    {
        $accessory = Accessory::factory()->create(['qty' => 5, 'company_id' => null]);
        [$companyA] = Company::factory()->count(1)->create();
        $user = User::factory()->for($companyA)->create();
        $user->companies()->sync([$companyA->id]);

        $this->settings->enableFloaterMode();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $user->id,
        ]);
    }

    public function test_checkout_to_asset_does_not_throw_when_fmcs_enabled()
    {
        [$companyA] = Company::factory()->count(1)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $asset = Asset::factory()->for($companyA)->create();

        $this->settings->enableMultipleFullCompanySupport();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'asset',
                'assigned_asset' => $asset->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $asset->id,
        ]);
    }

    public function test_checkout_to_null_company_user_blocked_in_strict_mode()
    {
        [$companyA] = Company::factory()->count(1)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableMultipleFullCompanySupport();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $nullCompanyUser->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $nullCompanyUser->id,
        ]);
    }

    public function test_checkout_to_null_company_user_succeeds_in_floater_mode()
    {
        [$companyA] = Company::factory()->count(1)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $nullCompanyUser = User::factory()->create(['company_id' => null]);

        $this->settings->enableFloaterMode();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'user',
                'assigned_user' => $nullCompanyUser->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $nullCompanyUser->id,
        ]);
    }

    public function test_checkout_to_location_does_not_throw_when_fmcs_enabled()
    {
        [$companyA] = Company::factory()->count(1)->create();
        $accessory = Accessory::factory()->for($companyA)->create(['qty' => 5]);
        $location = Location::factory()->create();

        $this->settings->enableFloaterMode();

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->post(route('accessories.checkout.store', $accessory), [
                'checkout_to_type' => 'location',
                'assigned_location' => $location->id,
                'checkout_qty' => 1,
                'redirect_option' => 'index',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('accessories_checkout', [
            'accessory_id' => $accessory->id,
            'assigned_to' => $location->id,
        ]);
    }
}
