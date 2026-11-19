<?php

use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\User;

test('assigned tab badge reflects outstanding not total distributions', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->checkedOutToUser()->create();
    $assignments = ConsumableAssignment::where('consumable_id', $consumable->id)->get();
    expect($assignments)->toHaveCount(2);

    $toConsume = $assignments->first();
    $toConsume->consumed_at = now();
    $toConsume->save();

    $html = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.show', $consumable))
        ->assertOk()
        ->getContent();

    // Badge should read 1 (the one still outstanding), not 2
    // (total ever distributed). Match the href + the digit so
    // we're reading the actual Assigned tab's count span.
    expect($html)->toMatch('/href="[^"]*#assigned"[^>]*>.*?<span[^>]*>1<\/span>/s', 'Assigned tab badge must reflect numOutstanding, not numCheckedOut.');
    $this->assertDoesNotMatchRegularExpression(
        '/href="[^"]*#assigned"[^>]*>.*?<span[^>]*>2<\/span>/s',
        $html,
        'Badge must not include consumed rows in its count.',
    );
});

test('edit view enables archive checkbox once all distributions consumed', function () {
    // Regression pin: the archive checkbox's :disabled used to
    // read $item->users()->count() (every pivot row ever), which
    // stayed > 0 even after admin bulk-consumed everything, so
    // the admin couldn't flip the box. It must track
    // numOutstanding() instead.
    $consumable = Consumable::factory()->checkedOutToUser()->create();
    ConsumableAssignment::query()
        ->where('consumable_id', $consumable->id)
        ->update(['consumed_at' => now()]);

    $html = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.edit', $consumable))
        ->assertOk()
        ->getContent();

    expect($html)->toMatch('/<input[^>]*name="archived"[^>]*(?<!disabled=")(?<!disabled)>/', 'Archived checkbox must be enabled when nothing is outstanding.');
});

test('edit view disables archive checkbox while distributions outstanding', function () {
    $consumable = Consumable::factory()->checkedOutToUser()->create();

    $html = $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.edit', $consumable))
        ->assertOk()
        ->getContent();

    expect($html)->toMatch('/<input[^>]*name="archived"[^>]*disabled/', 'Archived checkbox must be disabled while an outstanding distribution exists.');
});

test('show page loads for soft deleted consumable', function () {
    // Regression pin: the resource route needs withTrashed(['show'])
    // AND the show() method's internal re-fetch has to withTrashed()
    // too. Without both, the Deleted-index "View" link bounces back
    // with "That Consumable was not found" for an admin who can see
    // the deleted row in the first place.
    $consumable = Consumable::factory()->create();
    $consumable->delete();

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('consumables.show', $consumable->id))
        ->assertOk();
});
