<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('checking out component requires correct permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('components.checkout.store', [
            'componentID' => Component::factory()->checkedOutToAsset()->create()->id,
        ]))
        ->assertForbidden();
});

test('page renders', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('components.checkout.show', Component::factory()->create()->id))
        ->assertOk();
});

test('cannot checkout across companies when full company support enabled', function () {
    Event::fake([CheckoutableCheckedOut::class]);

    $this->settings->enableMultipleFullCompanySupport();

    [$assetCompany, $componentCompany] = Company::factory()->count(2)->create();

    $asset = Asset::factory()->for($assetCompany)->create();
    $component = Component::factory()->for($componentCompany)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'assigned_qty' => '1',
            'redirect_option' => 'index',
        ]);

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});

test('component checkout page post is redirected if redirect selection is index', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('components.index'))
        ->post(route('components.checkout.store', $component), [
            'asset_id' => Asset::factory()->create()->id,
            'redirect_option' => 'index',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('components.index'));
    $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
});

test('component checkout page post is redirected if redirect selection is item', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('components.index'))
        ->post(route('components.checkout.store', $component), [
            'asset_id' => Asset::factory()->create()->id,
            'redirect_option' => 'item',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('components.show', $component));
    $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
});

test('component checkout page post is redirected if redirect selection is target', function () {
    $asset = Asset::factory()->create();
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('components.index'))
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'redirect_option' => 'target',
            'assigned_qty' => 1,
        ])
        ->assertStatus(302)
        ->assertRedirect(route('hardware.show', $asset));
    $this->assertHasTheseActionLogs($component, ['create', 'checkout']);
});

test('quantity stored in action log', function () {
    $component = Component::factory()->create();
    $asset = Asset::factory()->create();

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('components.index'))
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'redirect_option' => 'index',
            'assigned_qty' => 2,
        ]);

    $this->assertDatabaseHas('action_logs', [
        'action_type' => 'checkout',
        'target_id' => $asset->id,
        'target_type' => Asset::class,
        'item_id' => $component->id,
        'item_type' => Component::class,
        'quantity' => 2,
        'created_by' => $admin->id,
    ]);
});
