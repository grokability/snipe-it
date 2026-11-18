<?php

use App\Mcp\Tools\CheckinAccessoryTool;
use App\Mcp\Tools\CheckoutAccessoryTool;
use App\Mcp\Tools\DeleteAccessoryTool;
use App\Mcp\Tools\UpdateAccessoryTool;
use App\Models\Accessory;
use App\Models\AccessoryCheckout;
use App\Models\Company;
use App\Models\User;
use Laravel\Mcp\Request;

beforeEach(function () {
    [$this->companyA, $this->companyB] = Company::factory()->count(2)->create();

    $this->accessoryA = Accessory::factory()->for($this->companyA)->create(['qty' => 5]);
    $this->accessoryB = Accessory::factory()->for($this->companyB)->create(['qty' => 5]);

    $this->userInCompanyA = $this->companyA->users()->save(
        User::factory()->editAccessories()->deleteAccessories()->checkoutAccessories()->checkinAccessories()->make()
    );
    $this->superUser = $this->companyA->users()->save(User::factory()->superuser()->make());

    $this->settings->enableMultipleFullCompanySupport();
});

test('update blocked for cross company accessory', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new UpdateAccessoryTool)->handle(new Request([
        'id' => $this->accessoryB->id,
        'qty' => 99,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('accessories', ['id' => $this->accessoryB->id, 'qty' => 99]);
});

test('update allowed for same company accessory', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new UpdateAccessoryTool)->handle(new Request([
        'id' => $this->accessoryA->id,
        'qty' => 10,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories', ['id' => $this->accessoryA->id, 'qty' => 10]);
});

test('delete blocked for cross company accessory', function () {
    $this->actingAs($this->userInCompanyA);

    $response = (new DeleteAccessoryTool)->handle(new Request([
        'id' => $this->accessoryB->id,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertNotSoftDeleted($this->accessoryB);
});

test('delete allowed for same company accessory', function () {
    $this->actingAs($this->userInCompanyA);

    $content = (new DeleteAccessoryTool)->handle(new Request([
        'id' => $this->accessoryA->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->accessoryA);
});

test('checkout blocked for cross company accessory', function () {
    $user = $this->companyA->users()->save(User::factory()->make());

    $this->actingAs($this->userInCompanyA);

    $response = (new CheckoutAccessoryTool)->handle(new Request([
        'id' => $this->accessoryB->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]));

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseMissing('accessories_checkout', ['accessory_id' => $this->accessoryB->id]);
});

test('checkout allowed for same company accessory', function () {
    $user = $this->companyA->users()->save(User::factory()->make());

    $this->actingAs($this->userInCompanyA);

    $content = (new CheckoutAccessoryTool)->handle(new Request([
        'id' => $this->accessoryA->id,
        'checkout_to_type' => 'user',
        'assigned_user' => $user->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', ['accessory_id' => $this->accessoryA->id]);
});

test('checkin blocked for cross company accessory', function () {
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

    expect($response->responses()->first()->isError())->toBeTrue();
    $this->assertDatabaseHas('accessories_checkout', ['id' => $checkoutId]);
});

test('superuser can update accessory in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new UpdateAccessoryTool)->handle(new Request([
        'id' => $this->accessoryB->id,
        'qty' => 20,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertDatabaseHas('accessories', ['id' => $this->accessoryB->id, 'qty' => 20]);
});

test('superuser can delete accessory in any company', function () {
    $this->actingAs($this->superUser);

    $content = (new DeleteAccessoryTool)->handle(new Request([
        'id' => $this->accessoryB->id,
    ]))->getStructuredContent();

    expect($content['success'])->toBeTrue();
    $this->assertSoftDeleted($this->accessoryB);
});
