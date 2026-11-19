<?php

use App\Events\CheckoutableCheckedIn;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function attachCheckout(Component $component, Asset $asset, int $qty = 1): int
{
    $component->assets()->attach($asset->id, [
        'assigned_qty' => $qty,
        'created_at' => now(),
        'created_by' => 1,
    ]);

    return (int) ComponentAssignment::where('component_id', $component->id)
        ->where('asset_id', $asset->id)
        ->latest('id')
        ->value('id');
}

test('requires checkin permission on show', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $pivotId = attachCheckout($component, $asset);

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.bulkcheckin.show'), ['ids' => [$pivotId]])
        ->assertForbidden();

    $this->assertDatabaseHas('components_assets', ['id' => $pivotId]);
});

test('requires checkin permission on store', function () {
    $component = Component::factory()->create(['qty' => 5]);
    $asset = Asset::factory()->create();
    $pivotId = attachCheckout($component, $asset);

    $this->actingAs(User::factory()->viewComponents()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivotId]])
        ->assertForbidden();

    $this->assertDatabaseHas('components_assets', ['id' => $pivotId]);
});

test('show renders interstitial with selected rows', function () {
    $component = Component::factory()->create(['qty' => 10]);
    $pivotA = attachCheckout($component, Asset::factory()->create(), 2);
    $pivotB = attachCheckout($component, Asset::factory()->create(), 3);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.show'), ['ids' => [$pivotA, $pivotB]])
        ->assertOk()
        ->assertViewIs('components.bulk-checkin')
        ->assertViewHas('ids', [$pivotA, $pivotB]);

    $this->assertDatabaseHas('components_assets', ['id' => $pivotA]);
    $this->assertDatabaseHas('components_assets', ['id' => $pivotB]);
});

test('show warns when no rows selected', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.show'), ['ids' => []])
        ->assertRedirect()
        ->assertSessionHas('warning');
});

test('store removes pivot rows and fires event', function () {
    Event::fake([CheckoutableCheckedIn::class]);

    $component = Component::factory()->create(['qty' => 10]);
    $assetA = Asset::factory()->create();
    $assetB = Asset::factory()->create();

    $pivotA = attachCheckout($component, $assetA, 2);
    $pivotB = attachCheckout($component, $assetB, 3);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivotA, $pivotB]])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('components_assets', ['id' => $pivotA]);
    $this->assertDatabaseMissing('components_assets', ['id' => $pivotB]);

    // Fully-returned pivots mean the component is now entirely
    // back in stock.
    expect((int) $component->fresh()->numCheckedOut(true))->toBe(0);

    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 2);
});

test('store warns when no rows selected', function () {
    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => []])
        ->assertRedirect()
        ->assertSessionHas('warning');
});

test('success message reports total units not just pivot rows', function () {
    // Regression: a pivot row can represent many units via
    // assigned_qty, so "3 rows checked in" might really mean
    // 25 units returned. The success message must surface the
    // units total, not just the row count. Pin so a refactor
    // can't quietly drop the per-row qty sum.
    $component = Component::factory()->create(['qty' => 50]);

    $pivotA = attachCheckout($component, Asset::factory()->create(), 4);
    $pivotB = attachCheckout($component, Asset::factory()->create(), 11);
    $pivotC = attachCheckout($component, Asset::factory()->create(), 7);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivotA, $pivotB, $pivotC]])
        ->assertRedirect()
        ->assertSessionHas('success', trans_choice(
            'admin/components/message.bulk.checkin_selected.success',
            3,
            ['count' => 3, 'unit_count' => 22],
        ));
});

test('success message handles single unit single row', function () {
    // Covers the singular side of the trans_choice branch for the
    // simplest checkin: one row, one unit.
    $component = Component::factory()->create(['qty' => 5]);
    $pivot = attachCheckout($component, Asset::factory()->create(), 1);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivot]])
        ->assertRedirect()
        ->assertSessionHas('success', trans_choice(
            'admin/components/message.bulk.checkin_selected.success',
            1,
            ['count' => 1, 'unit_count' => 1],
        ));
});

test('shared note lands on every action log', function () {
    // Regression test: the whole reason for the interstitial is to
    // capture a shared note. The posted `note` must thread into
    // every per-row CheckoutableCheckedIn event so each resulting
    // action_log row carries it.
    $component = Component::factory()->create(['qty' => 10]);
    $pivotA = attachCheckout($component, Asset::factory()->create(), 1);
    $pivotB = attachCheckout($component, Asset::factory()->create(), 1);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), [
            'ids' => [$pivotA, $pivotB],
            'note' => 'End-of-lease returns, batch 2026-Q4',
        ])
        ->assertRedirect();

    $logs = Actionlog::where('item_type', Component::class)
        ->where('item_id', $component->id)
        ->where('action_type', 'checkin from')
        ->get();

    expect($logs)->toHaveCount(2);
    foreach ($logs as $log) {
        expect($log->note)->toBe('End-of-lease returns, batch 2026-Q4');
    }
});

test('unknown pivot ids are silently skipped', function () {
    // Mirrors the LicenseCheckinController flow: stale pivot ids
    // posted from a dropdown-swap race just get skipped so the
    // real selections still check in cleanly.
    Event::fake([CheckoutableCheckedIn::class]);

    $component = Component::factory()->create(['qty' => 10]);
    $asset = Asset::factory()->create();
    $pivotId = attachCheckout($component, $asset);

    $this->actingAs(User::factory()->superuser()->create())
        ->post(route('components.bulkcheckin.store'), ['ids' => [$pivotId, 999999]])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('components_assets', ['id' => $pivotId]);
    Event::assertDispatchedTimes(CheckoutableCheckedIn::class, 1);
});
