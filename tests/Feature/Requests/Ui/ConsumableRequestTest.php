<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

/**
 * Consumable is a first-class checkoutable, so it now participates in
 * the Requestable trait's user-facing flow the same way Accessory does.
 * Coverage mirrors AccessoryRequestTest and adds cross-company FMCS
 * negative cases per the adversarial-tests rule.
 */
class ConsumableRequestTest extends TestCase
{
    public function test_requestable_index_lists_requestable_consumables(): void
    {
        // The consumables tab on /account/requestable is now
        // API-backed via api.consumables.requestable. See the sibling
        // AccessoryRequestTest for the shell-page + API-shape rationale.
        $requestable = Consumable::factory()->create(['requestable' => true]);
        $nonRequestable = Consumable::factory()->create(['requestable' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('account.requestable'))
            ->assertOk()
            ->assertViewHas('counts', fn ($counts) => ($counts['consumables'] ?? 0) > 0);

        $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.consumables.requestable'))
            ->assertOk()
            ->json('rows');

        $ids = collect($rows)->pluck('id')->all();
        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($nonRequestable->id, $ids);
    }

    public function test_user_can_request_a_requestable_consumable(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'consumable',
                'itemId' => $consumable->id,
            ]), ['request-quantity' => 2])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $consumable->id)
            ->where('requestable_type', Consumable::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals(2, $request->quantity);
        $this->assertNull($request->canceled_at);
    }

    public function test_user_cannot_request_a_consumable_that_is_not_requestable(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'consumable',
                'itemId' => $consumable->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $consumable->id)
                ->where('requestable_type', Consumable::class)
                ->first()
        );
    }

    public function test_user_can_cancel_their_own_consumable_request(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'consumable',
                'itemId' => $consumable->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $consumable->id)
                ->where('requestable_type', Consumable::class)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_fmcs_prevents_cross_company_consumable_request(): void
    {
        // A user in company A must not be able to request a consumable
        // that belongs to company B. The requestable-flag gate loads
        // through Consumable::Requestable which respects
        // the CompanyableTrait global scope, so the item never
        // resolves for the cross-company caller and the request is
        // refused with the standard "not requestable" error.
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInA = $companyA->users()->save(User::factory()->make());
        $consumableInB = Consumable::factory()->for($companyB)->create(['requestable' => true]);

        $this->actingAs($userInA)
            ->post(route('account/request-item', [
                'itemType' => 'consumable',
                'itemId' => $consumableInB->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $consumableInB->id)
                ->where('requestable_type', Consumable::class)
                ->first(),
            'Cross-company user must not be able to create a request against a company they cannot see.'
        );
    }

    public function test_api_endpoint_creates_a_consumable_request(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.consumables.requests.store', ['consumable' => $consumable->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $consumable->id)
                ->where('requestable_type', Consumable::class)
                ->where('user_id', $user->id)
                ->whereNull('canceled_at')
                ->first()
        );
    }

    public function test_api_cancel_endpoint_cancels_the_users_own_request(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.consumables.requests.destroy', ['consumable' => $consumable->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $consumable->id)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_non_requestable_consumable(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.consumables.requests.store', ['consumable' => $consumable->id]))
            ->assertStatus(403);

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $consumable->id)
                ->where('requestable_type', Consumable::class)
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_duplicate_consumable_request(): void
    {
        $consumable = Consumable::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $consumable->id,
            'requestable_type' => Consumable::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.consumables.requests.store', ['consumable' => $consumable->id]))
            ->assertStatus(409);
    }
}
