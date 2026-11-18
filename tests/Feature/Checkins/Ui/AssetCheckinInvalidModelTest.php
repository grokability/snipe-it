<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('checkin get flashes specific errors when asset fails model validation', function () {
    $asset = Asset::factory()->assignedToUser()->create();

    // Bypass model events + validation to write an invalid value
    // directly. purchase_cost has a `gte:0` rule (Asset::$rules),
    // so a negative value trips isInvalid() at checkin time.
    // Using DB::update rather than $asset->forceSave() keeps us
    // out of the observer stack entirely.
    DB::table('assets')->where('id', $asset->id)->update(['purchase_cost' => -1]);

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.checkin.create', $asset));

    $response->assertStatus(302);
    $response->assertRedirect(route('hardware.edit', $asset));

    // The generic errors bag is still populated so inline
    // .has-error styling fires on any visible fields.
    $response->assertSessionHasErrors('purchase_cost');

    // AND the specific message is flashed so notifications.blade.php
    // renders it in the top alert, catching the off-form case.
    $response->assertSessionHas('multi_error_messages', function ($messages) {
        return is_array($messages)
            && count($messages) > 0
            && collect($messages)->contains(fn ($msg) => str_contains(strtolower($msg), 'purchase cost'));
    });
});

test('multi error messages alert hides show more when three or fewer', function () {
    // Companion regression: notifications.blade.php used to render
    // an empty <details><summary>Show all</summary> block for any
    // multi_error_messages payload, even when there were 3 or
    // fewer items and nothing to disclose.
    session()->flash('multi_error_messages', ['one', 'two', 'three']);

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.index'));

    $content = $response->getContent();
    $this->assertStringContainsString('<li>one</li>', $content);
    $this->assertStringContainsString('<li>three</li>', $content);
    $this->assertStringNotContainsString('<summary>', $content, 'Show-more disclosure must not render for 3 or fewer messages.');
});

test('multi error messages alert shows show more when more than three', function () {
    session()->flash('multi_error_messages', ['one', 'two', 'three', 'four']);

    $response = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('hardware.index'));

    $content = $response->getContent();
    $this->assertStringContainsString('<summary>', $content);
    $this->assertStringContainsString('<li>four</li>', $content);
});
