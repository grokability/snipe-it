<?php

use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

test('deletes acceptance when checkout target is non user', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create();
    $now = now();

    $badAcceptance = CheckoutAcceptance::factory()
        ->withoutActionLog()
        ->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $location->id,
            'created_at' => $now,
        ]);

    Actionlog::factory()->create([
        'action_type' => 'checkout',
        'target_id' => $location->id,
        'target_type' => Location::class,
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'created_at' => $now,
    ]);

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseMissing('checkout_acceptances', ['id' => $badAcceptance->id]);
});

test('preserves acceptance when checkout target is user', function () {
    // The factory creates a matching action_log with target_type = User::class by default
    $goodAcceptance = CheckoutAcceptance::factory()->create();

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseHas('checkout_acceptances', ['id' => $goodAcceptance->id]);
});

test('preserves acceptance when action log timestamp is too far apart', function () {
    $location = Location::factory()->create();
    $asset = Asset::factory()->create();
    $now = now();

    $acceptance = CheckoutAcceptance::factory()
        ->withoutActionLog()
        ->create([
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $asset->id,
            'assigned_to_id' => $location->id,
            'created_at' => $now,
        ]);

    // 10 seconds away — outside the ±5-second window
    Actionlog::factory()->create([
        'action_type' => 'checkout',
        'target_id' => $location->id,
        'target_type' => Location::class,
        'item_id' => $asset->id,
        'item_type' => Asset::class,
        'created_at' => $now->copy()->addSeconds(10),
    ]);

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseHas('checkout_acceptances', ['id' => $acceptance->id]);
});

test('skips acceptance with soft deleted checkoutable', function () {
    $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->create();
    Model::withoutEvents(fn () => $acceptance->checkoutable->delete());

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseHas('checkout_acceptances', ['id' => $acceptance->id]);
});

test('skips acceptance with null created at', function () {
    $acceptance = CheckoutAcceptance::factory()->withoutActionLog()->create(['created_at' => null]);

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseHas('checkout_acceptances', ['id' => $acceptance->id]);
});

test('deletes license seat acceptance when checkout target is non user', function () {
    $location = Location::factory()->create();
    $licenseSeat = LicenseSeat::factory()->create();
    $license = $licenseSeat->license;
    $now = now();

    $badAcceptance = CheckoutAcceptance::factory()
        ->withoutActionLog()
        ->create([
            'checkoutable_type' => LicenseSeat::class,
            'checkoutable_id' => $licenseSeat->id,
            'assigned_to_id' => $location->id,
            'created_at' => $now,
        ]);

    // Action log lives on the License, not the LicenseSeat
    Actionlog::factory()->create([
        'action_type' => 'checkout',
        'target_id' => $location->id,
        'target_type' => Location::class,
        'item_id' => $license->id,
        'item_type' => License::class,
        'created_at' => $now,
    ]);

    $this->artisan('snipeit:clean-checkout-acceptances')->assertExitCode(0);

    $this->assertDatabaseMissing('checkout_acceptances', ['id' => $badAcceptance->id]);
});
