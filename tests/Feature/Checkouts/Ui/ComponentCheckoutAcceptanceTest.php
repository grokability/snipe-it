<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Component;
use App\Models\User;

/**
 * Regression coverage for GH #19570: components with require_acceptance
 * on their category didn't produce a CheckoutAcceptance row when the
 * checkout target was an Asset. That left the recipient (the asset's
 * assigned user) with an email that had a broken accept link and no
 * pending row to accept from their profile.
 *
 * The fix routes any Asset target through the asset's assignedto to
 * pick the real acceptance target, and the mail template no longer
 * renders the "please accept" copy or button when no accept URL exists.
 */
test('component checkout to asset creates acceptance for the assets assigned user', function () {
    $assignedUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignedUser)->create();

    $category = Category::factory()->componentRamCategory()->create([
        'require_acceptance' => true,
    ]);
    $component = Component::factory()->for($category)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'assigned_qty' => '1',
            'redirect_option' => 'index',
        ]);

    $this->assertDatabaseHas('checkout_acceptances', [
        'checkoutable_type' => Component::class,
        'checkoutable_id' => $component->id,
        'assigned_to_id' => $assignedUser->id,
    ]);
});

test('component checkout to asset with no assigned user skips acceptance', function () {
    // Asset that's not checked out to anyone. No user to sign an
    // acceptance, so no row should land.
    $asset = Asset::factory()->create();

    $category = Category::factory()->componentRamCategory()->create([
        'require_acceptance' => true,
    ]);
    $component = Component::factory()->for($category)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'assigned_qty' => '1',
            'redirect_option' => 'index',
        ]);

    $this->assertDatabaseMissing('checkout_acceptances', [
        'checkoutable_type' => Component::class,
        'checkoutable_id' => $component->id,
    ]);
});

test('component checkout without required acceptance writes no row', function () {
    // Regression guard on the pre-existing branch: if the
    // category does not require acceptance, no row is written
    // even when the target chain resolves to a real user.
    $assignedUser = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($assignedUser)->create();

    $category = Category::factory()->componentRamCategory()->create([
        'require_acceptance' => false,
    ]);
    $component = Component::factory()->for($category)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.checkout.store', $component), [
            'asset_id' => $asset->id,
            'assigned_qty' => '1',
            'redirect_option' => 'index',
        ]);

    $this->assertDatabaseMissing('checkout_acceptances', [
        'checkoutable_type' => Component::class,
        'checkoutable_id' => $component->id,
    ]);
});
