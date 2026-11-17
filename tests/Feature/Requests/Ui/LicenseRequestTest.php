<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\License;
use App\Models\User;
use Tests\TestCase;

/**
 * License gained the Requestable trait + per-row `requestable`
 * boolean alongside Consumable / Component / Accessory. Coverage
 * matches ConsumableRequestTest / ComponentRequestTest to keep the
 * behavior symmetric across every requestable type.
 */
class LicenseRequestTest extends TestCase
{
    public function test_requestable_index_lists_requestable_licenses(): void
    {
        // API-backed. See AccessoryRequestTest for the rationale.
        $requestable = License::factory()->create(['requestable' => true]);
        $nonRequestable = License::factory()->create(['requestable' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('account.requestable'))
            ->assertOk()
            ->assertViewHas('counts', fn ($counts) => ($counts['licenses'] ?? 0) > 0);

        $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.licenses.requestable'))
            ->assertOk()
            ->json('rows');

        $ids = collect($rows)->pluck('id')->all();
        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($nonRequestable->id, $ids);
    }

    public function test_user_can_request_a_requestable_license(): void
    {
        $license = License::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'license',
                'itemId' => $license->id,
            ]), ['request-quantity' => 1])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $license->id)
            ->where('requestable_type', License::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertNull($request->canceled_at);
    }

    public function test_user_cannot_request_a_license_that_is_not_requestable(): void
    {
        $license = License::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'license',
                'itemId' => $license->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $license->id)
                ->where('requestable_type', License::class)
                ->first()
        );
    }

    public function test_user_can_cancel_their_own_license_request(): void
    {
        $license = License::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $license->id,
            'requestable_type' => License::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'license',
                'itemId' => $license->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $license->id)
                ->where('requestable_type', License::class)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_fmcs_prevents_cross_company_license_request(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInA = $companyA->users()->save(User::factory()->make());
        $licenseInB = License::factory()->for($companyB)->create(['requestable' => true]);

        $this->actingAs($userInA)
            ->post(route('account/request-item', [
                'itemType' => 'license',
                'itemId' => $licenseInB->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $licenseInB->id)
                ->where('requestable_type', License::class)
                ->first(),
            'Cross-company user must not be able to create a request against a license they cannot see.'
        );
    }

    public function test_api_endpoint_creates_a_license_request(): void
    {
        $license = License::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.licenses.requests.store', ['license' => $license->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $license->id)
                ->where('requestable_type', License::class)
                ->where('user_id', $user->id)
                ->whereNull('canceled_at')
                ->first()
        );
    }

    public function test_api_cancel_endpoint_cancels_the_users_own_license_request(): void
    {
        $license = License::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $license->id,
            'requestable_type' => License::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.licenses.requests.destroy', ['license' => $license->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $license->id)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_non_requestable_license(): void
    {
        $license = License::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.licenses.requests.store', ['license' => $license->id]))
            ->assertStatus(403);

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $license->id)
                ->where('requestable_type', License::class)
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_duplicate_license_request(): void
    {
        $license = License::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $license->id,
            'requestable_type' => License::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.licenses.requests.store', ['license' => $license->id]))
            ->assertStatus(409);
    }
}
