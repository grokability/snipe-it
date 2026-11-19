<?php

use App\Events\CheckoutableCheckedOut;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([CheckoutableCheckedOut::class]);
});

test('second checkout of already assigned asset does not increment counter', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create(['checkout_counter' => 1]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertRedirect();

    $asset->refresh();
    expect((int) $asset->checkout_counter)->toBe(1, 'checkout_counter must not increment when checkout is refused');
    expect((int) $asset->assigned_to)->toBe($firstTarget->id, 'existing assignment must remain intact');
});

test('second checkout of already assigned asset does not fire checkout event', function () {
    $firstTarget = User::factory()->create();
    $asset = Asset::factory()->assignedToUser($firstTarget)->create();

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('hardware.checkout.store', $asset), [
            'checkout_to_type' => 'user',
            'assigned_user' => User::factory()->create()->id,
        ])
        ->assertRedirect();

    Event::assertNotDispatched(CheckoutableCheckedOut::class);
});
