<?php

namespace Tests\Feature\AssetModels\Api;

use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\User;
use Tests\TestCase;

/**
 * api.assetmodels.requestable — hydrates the models tab on
 * /account/requestable. Row shape has to carry the per-user
 * assigned_to_self flag and available_actions.request/cancel so the
 * assetmodelRequestableActionsFormatter JS helper can render the
 * request-vs-cancel button-swap without a second query.
 *
 * Mirrors the coverage other requestable endpoints have (accessories,
 * consumables, components, licenses) and closes the N+1 that the
 * server-rendered @foreach on the old page fired one query per row.
 */
class RequestableAssetModelsApiTest extends TestCase
{
    public function test_returns_only_requestable_models(): void
    {
        $requestable = AssetModel::factory()->create(['requestable' => 1]);
        $notRequestable = AssetModel::factory()->create(['requestable' => 0]);

        $rows = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows');

        $ids = collect($rows)->pluck('id')->all();
        $this->assertContains($requestable->id, $ids);
        $this->assertNotContains($notRequestable->id, $ids);
    }

    public function test_row_carries_assigned_to_self_true_when_user_has_open_request(): void
    {
        $user = User::factory()->viewRequestableAssets()->create();
        $model = AssetModel::factory()->create(['requestable' => 1]);
        CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $model->id,
            'requestable_type' => AssetModel::class,
        ]);

        $row = $this->actingAsForApi($user)
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertTrue($row['assigned_to_self']);
        // Button-swap contract: caller with an open request sees
        // cancel, not request.
        $this->assertFalse($row['available_actions']['request']);
        $this->assertTrue($row['available_actions']['cancel']);
    }

    public function test_row_carries_assigned_to_self_false_for_other_users_requests(): void
    {
        $me = User::factory()->viewRequestableAssets()->create();
        $someoneElse = User::factory()->create();
        $model = AssetModel::factory()->create(['requestable' => 1]);
        // Not my request.
        CheckoutRequest::factory()->create([
            'user_id' => $someoneElse->id,
            'requestable_id' => $model->id,
            'requestable_type' => AssetModel::class,
        ]);

        $row = $this->actingAsForApi($me)
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertFalse($row['assigned_to_self']);
        $this->assertTrue($row['available_actions']['request']);
        $this->assertFalse($row['available_actions']['cancel']);
    }

    public function test_canceled_requests_do_not_flip_assigned_to_self(): void
    {
        // Cancel semantics: a canceled row shouldn't leave the user
        // stuck seeing a cancel button they can't act on.
        $user = User::factory()->viewRequestableAssets()->create();
        $model = AssetModel::factory()->create(['requestable' => 1]);
        CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $model->id,
            'requestable_type' => AssetModel::class,
            'canceled_at' => now()->subMinute(),
        ]);

        $row = $this->actingAsForApi($user)
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertFalse($row['assigned_to_self']);
        $this->assertTrue($row['available_actions']['request']);
    }

    public function test_cross_type_open_requests_do_not_flip_assigned_to_self(): void
    {
        // Guard: an open request against an Accessory with the same
        // requestable_id must not bleed into the AssetModel row's
        // assigned_to_self flag. The transformer's contains-check
        // matches purely on user_id + canceled_at because the
        // eager-loaded requests collection is already scoped to this
        // model - but codify the isolation with a test so a future
        // refactor doesn't regress it.
        $user = User::factory()->viewRequestableAssets()->create();
        AssetModel::factory()->create(['requestable' => 1]);
        $accessory = Accessory::factory()->create();
        CheckoutRequest::factory()->create([
            'user_id' => $user->id,
            'requestable_id' => $accessory->id,
            'requestable_type' => Accessory::class,
        ]);

        $row = $this->actingAsForApi($user)
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertFalse($row['assigned_to_self']);
    }

    public function test_available_actions_view_reflects_caller_permission(): void
    {
        // The formatter uses available_actions.view to decide
        // whether to render the name as an <a> link or plain text.
        // A user with the view-assetmodel permission gets view=true;
        // a user without it gets view=false.
        AssetModel::factory()->create(['requestable' => 1]);

        $withView = User::factory()->viewRequestableAssets()->viewAssetModels()->create();
        $withoutView = User::factory()->viewRequestableAssets()->create();

        $rowWith = $this->actingAsForApi($withView)
            ->getJson(route('api.assetmodels.requestable'))
            ->json('rows.0');
        $rowWithout = $this->actingAsForApi($withoutView)
            ->getJson(route('api.assetmodels.requestable'))
            ->json('rows.0');

        $this->assertTrue($rowWith['available_actions']['view']);
        $this->assertFalse($rowWithout['available_actions']['view']);
    }

    public function test_remaining_reflects_deployable_asset_count(): void
    {
        $model = AssetModel::factory()->create(['requestable' => 1]);
        $rtd = \App\Models\Statuslabel::factory()->rtd()->create();

        // Three deployable + unassigned assets of this model, one already
        // assigned. `remaining` should count only the three unassigned.
        \App\Models\Asset::factory()->count(3)->create([
            'model_id' => $model->id,
            'status_id' => $rtd->id,
            'assigned_to' => null,
        ]);
        \App\Models\Asset::factory()->create([
            'model_id' => $model->id,
            'status_id' => $rtd->id,
            'assigned_to' => User::factory()->create()->id,
            'assigned_type' => User::class,
        ]);

        $row = $this->actingAsForApi(User::factory()->viewRequestableAssets()->create())
            ->getJson(route('api.assetmodels.requestable'))
            ->assertOk()
            ->json('rows.0');

        $this->assertSame(3, $row['remaining']);
    }
}
