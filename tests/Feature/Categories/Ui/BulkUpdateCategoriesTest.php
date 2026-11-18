<?php

use App\Models\Category;
use App\Models\User;

test('requires permission', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$category->id],
            'require_acceptance' => '1',
        ])
        ->assertForbidden();
});

test('updates selected boolean fields across all selected categories', function () {
    $a = Category::factory()->create([
        'require_acceptance' => false,
        'use_default_eula' => false,
        'checkin_email' => false,
        'alert_on_response' => false,
    ]);
    $b = Category::factory()->create([
        'require_acceptance' => false,
        'use_default_eula' => false,
        'checkin_email' => false,
        'alert_on_response' => false,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$a->id, $b->id],
            'require_acceptance' => '1',
            'use_default_eula' => '1',
            'checkin_email' => '1',
            'alert_on_response' => '1',
        ])
        ->assertRedirect(route('categories.index'));

    foreach ([$a, $b] as $category) {
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'require_acceptance' => 1,
            'use_default_eula' => 1,
            'checkin_email' => 1,
            'alert_on_response' => 1,
        ]);
    }
});

test('no change sentinel leaves field untouched', function () {
    $category = Category::factory()->create([
        'require_acceptance' => true,
        'use_default_eula' => true,
        'checkin_email' => true,
        'alert_on_response' => true,
    ]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$category->id],
            'require_acceptance' => '',
            'use_default_eula' => '',
            'checkin_email' => '',
            'alert_on_response' => '0',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'require_acceptance' => 1,
        'use_default_eula' => 1,
        'checkin_email' => 1,
        'alert_on_response' => 0,
    ]);
});

test('tag color persists when filled', function () {
    $category = Category::factory()->create(['tag_color' => '#111111']);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$category->id],
            'tag_color' => '#abcdef',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'tag_color' => '#abcdef',
    ]);
});

test('blank tag color leaves existing value untouched', function () {
    $category = Category::factory()->create(['tag_color' => '#111111']);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$category->id],
            'require_acceptance' => '1',
            'tag_color' => '',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'tag_color' => '#111111',
    ]);
});

test('empty ids returns warning', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [],
            'require_acceptance' => '1',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('error');
});

test('no changed fields returns warning', function () {
    $category = Category::factory()->create(['require_acceptance' => false]);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('categories.bulk.save'), [
            'ids' => [$category->id],
            'require_acceptance' => '',
            'use_default_eula' => '',
            'checkin_email' => '',
            'alert_on_response' => '',
            'tag_color' => '',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('warning');

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'require_acceptance' => 0,
    ]);
});
