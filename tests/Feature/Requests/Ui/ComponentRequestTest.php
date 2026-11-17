<?php

namespace Tests\Feature\Requests\Ui;

use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

class ComponentRequestTest extends TestCase
{
    public function test_requestable_index_lists_requestable_components(): void
    {
        // API-backed. See AccessoryRequestTest for the rationale.
        $requestable = Component::factory()->create(['requestable' => true]);
        $nonRequestable = Component::factory()->create(['requestable' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('account.requestable'))
            ->assertOk()
            ->assertViewHas('counts', fn ($counts) => ($counts['components'] ?? 0) > 0);

        $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.components.requestable'))
            ->assertOk()
            ->json('rows');

        $ids = collect($rows)->pluck('id')->all();
        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($nonRequestable->id, $ids);
    }

    public function test_user_can_request_a_requestable_component(): void
    {
        $component = Component::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'component',
                'itemId' => $component->id,
            ]), ['request-quantity' => 4])
            ->assertRedirect();

        $request = CheckoutRequest::where('requestable_id', $component->id)
            ->where('requestable_type', Component::class)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($request);
        $this->assertEquals(4, $request->quantity);
        $this->assertNull($request->canceled_at);
    }

    public function test_user_cannot_request_a_component_that_is_not_requestable(): void
    {
        $component = Component::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'component',
                'itemId' => $component->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $component->id)
                ->where('requestable_type', Component::class)
                ->first()
        );
    }

    public function test_user_can_cancel_their_own_component_request(): void
    {
        $component = Component::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('account/request-item', [
                'itemType' => 'component',
                'itemId' => $component->id,
            ]))
            ->assertRedirect();

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $component->id)
                ->where('requestable_type', Component::class)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_fmcs_prevents_cross_company_component_request(): void
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();
        $userInA = $companyA->users()->save(User::factory()->make());
        $componentInB = Component::factory()->for($companyB)->create(['requestable' => true]);

        $this->actingAs($userInA)
            ->post(route('account/request-item', [
                'itemType' => 'component',
                'itemId' => $componentInB->id,
            ]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $componentInB->id)
                ->where('requestable_type', Component::class)
                ->first(),
            'Cross-company user must not be able to create a request against a company they cannot see.'
        );
    }

    public function test_api_endpoint_creates_a_component_request(): void
    {
        $component = Component::factory()->create(['requestable' => true]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.components.requests.store', ['component' => $component->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $component->id)
                ->where('requestable_type', Component::class)
                ->where('user_id', $user->id)
                ->whereNull('canceled_at')
                ->first()
        );
    }

    public function test_api_cancel_endpoint_cancels_the_users_own_request(): void
    {
        $component = Component::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.components.requests.destroy', ['component' => $component->id]))
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertNotNull(
            CheckoutRequest::where('requestable_id', $component->id)
                ->where('user_id', $user->id)
                ->whereNotNull('canceled_at')
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_non_requestable_component(): void
    {
        $component = Component::factory()->create(['requestable' => false]);
        $user = User::factory()->create();

        $this->actingAsForApi($user)
            ->postJson(route('api.components.requests.store', ['component' => $component->id]))
            ->assertStatus(403);

        $this->assertNull(
            CheckoutRequest::where('requestable_id', $component->id)
                ->where('requestable_type', Component::class)
                ->first()
        );
    }

    public function test_api_endpoint_rejects_a_duplicate_component_request(): void
    {
        $component = Component::factory()->create(['requestable' => true]);
        $user = User::factory()->create();
        CheckoutRequest::factory()->create([
            'requestable_id' => $component->id,
            'requestable_type' => Component::class,
            'user_id' => $user->id,
        ]);

        $this->actingAsForApi($user)
            ->postJson(route('api.components.requests.store', ['component' => $component->id]))
            ->assertStatus(409);
    }
}
