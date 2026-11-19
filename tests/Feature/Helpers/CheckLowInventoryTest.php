<?php

use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('consumable at min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $consumable = Consumable::factory()->create(['qty' => 1, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->not->toContain($consumable->id);
});

test('consumable below min amt is flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $consumable = Consumable::factory()->create(['qty' => 0, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->toContain($consumable->id);
});

test('consumable above min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $consumable = Consumable::factory()->create(['qty' => 5, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->not->toContain($consumable->id);
});

test('consumable with null min amt is never flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $consumable = Consumable::factory()->create(['qty' => 0, 'min_amt' => null]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->not->toContain($consumable->id);
});

test('consumable at top of threshold buffer is flagged', function () {
    $this->settings->set(['alert_threshold' => 3]);

    $consumable = Consumable::factory()->create(['qty' => 7, 'min_amt' => 5]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->toContain($consumable->id);
});

test('consumable just outside threshold buffer is not flagged', function () {
    $this->settings->set(['alert_threshold' => 3]);

    $consumable = Consumable::factory()->create(['qty' => 8, 'min_amt' => 5]);

    expect(idsForType(Helper::checkLowInventory(), 'consumables'))->not->toContain($consumable->id);
});

test('accessory at min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $accessory = Accessory::factory()->create(['qty' => 1, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'accessories'))->not->toContain($accessory->id);
});

test('accessory below min amt is flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $accessory = Accessory::factory()->create(['qty' => 0, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'accessories'))->toContain($accessory->id);
});

test('component at min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $component = Component::factory()->create(['qty' => 1, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'components'))->not->toContain($component->id);
});

test('component below min amt is flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // Component's validation rule is qty >= 1, so drive "below min" via
    // a higher min_amt rather than a zero qty.
    $component = Component::factory()->create(['qty' => 1, 'min_amt' => 2]);

    expect(idsForType(Helper::checkLowInventory(), 'components'))->toContain($component->id);
});

test('component fully assigned in one pivot row reports correct remaining', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $component = Component::factory()->create(['qty' => 10, 'min_amt' => 2]);
    $asset = Asset::factory()->create();

    $component->assets()->attach($asset->id, [
        'assigned_qty' => 10,
        'created_at' => now(),
        'created_by' => User::factory()->create()->id,
    ]);

    $rows = collect(Helper::checkLowInventory())->where('type', 'components')->keyBy('id');

    expect($rows->has($component->id))->toBeTrue();
    expect((int) $rows[$component->id]['remaining'])->toBe(0);
});

test('archived component is excluded even when below min amt', function () {
    // Archived components are retired from active inventory and
    // should not drive low-stock alerts even when they fall below
    // their min_amt threshold.
    $this->settings->set(['alert_threshold' => 0]);

    $component = Component::factory()->archived()->create(['qty' => 1, 'min_amt' => 5]);

    expect(idsForType(Helper::checkLowInventory(), 'components'))->not->toContain($component->id);
});

test('component partially assigned across pivots sums assigned qty', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $component = Component::factory()->create(['qty' => 10, 'min_amt' => 5]);
    $assetA = Asset::factory()->create();
    $assetB = Asset::factory()->create();
    $actor = User::factory()->create();

    $component->assets()->attach($assetA->id, [
        'assigned_qty' => 4,
        'created_at' => now(),
        'created_by' => $actor->id,
    ]);
    $component->assets()->attach($assetB->id, [
        'assigned_qty' => 3,
        'created_at' => now(),
        'created_by' => $actor->id,
    ]);

    $rows = collect(Helper::checkLowInventory())->where('type', 'components')->keyBy('id');

    expect($rows->has($component->id))->toBeTrue();
    expect((int) $rows[$component->id]['remaining'])->toBe(3);
});

test('asset model at min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $model = AssetModel::factory()->create(['min_amt' => 1]);
    Asset::factory()->create(['model_id' => $model->id]);

    expect(idsForType(Helper::checkLowInventory(), 'models'))->not->toContain($model->id);
});

test('asset model below min amt is flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // min_amt=1 with zero RTD assets = below the floor.
    $model = AssetModel::factory()->create(['min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'models'))->toContain($model->id);
});

test('license at min amt is not flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $license = License::factory()->create(['seats' => 1, 'min_amt' => 1]);

    expect(idsForType(Helper::checkLowInventory(), 'licenses'))->not->toContain($license->id);
});

test('license below min amt is flagged as low', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // seats=1 with min_amt=2 means only 1 seat available where 2 is the floor.
    $license = License::factory()->create(['seats' => 1, 'min_amt' => 2]);

    expect(idsForType(Helper::checkLowInventory(), 'licenses'))->toContain($license->id);
});

test('accessory with checkouts below min amt is flagged', function () {
    $this->settings->set(['alert_threshold' => 0]);

    $accessory = Accessory::factory()->create(['qty' => 5, 'min_amt' => 3]);
    $user = User::factory()->create();

    $accessory->checkouts()->createMany([
        ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
        ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
        ['assigned_to' => $user->id, 'assigned_type' => User::class, 'created_by' => 1],
    ]);

    expect(idsForType(Helper::checkLowInventory(), 'accessories'))->toContain($accessory->id);
});

test('accessory with checkouts still above min amt is not flagged', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // 5 total, 1 checked out → 4 remaining, min is 3 → above the floor.
    $accessory = Accessory::factory()->create(['qty' => 5, 'min_amt' => 3]);
    $user = User::factory()->create();
    $accessory->checkouts()->create([
        'assigned_to' => $user->id,
        'assigned_type' => User::class,
        'created_by' => 1,
    ]);

    expect(idsForType(Helper::checkLowInventory(), 'accessories'))->not->toContain($accessory->id);
});

test('license with assigned seats below min amt is flagged', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // 5 seats, min 3 → healthy at rest. Assign 3 → 2 available → below min.
    $license = License::factory()->create(['seats' => 5, 'min_amt' => 3]);
    $seats = $license->licenseseats()->orderBy('id')->take(3)->get();
    foreach ($seats as $seat) {
        $seat->update(['assigned_to' => User::factory()->create()->id]);
    }

    expect(idsForType(Helper::checkLowInventory(), 'licenses'))->toContain($license->id);
});

test('check low inventory query count stays bounded', function () {
    $this->settings->set(['alert_threshold' => 0]);

    // Populate each category with several low-inventory rows so the
    // foreach paths that used to trigger extra per-row queries actually
    // execute.
    Consumable::factory()->count(3)->create(['qty' => 0, 'min_amt' => 1]);
    Accessory::factory()->count(3)->create(['qty' => 0, 'min_amt' => 1]);
    Component::factory()->count(3)->create(['qty' => 1, 'min_amt' => 5]);
    AssetModel::factory()->count(3)->create(['min_amt' => 5]);
    License::factory()->count(3)->create(['seats' => 1, 'min_amt' => 5]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    Helper::checkLowInventory();

    // Ceiling covers: 1 settings + 5 category queries + 1 status_labels
    // lookup for the RTD() scope in the AssetModel branch = 7. Held to
    // 10 to leave a little headroom for a future settings-related
    // memoization change without loosening the N+1 guard.
    $count = count(DB::getQueryLog());
    expect($count)->toBeLessThanOrEqual(10, "checkLowInventory fired {$count} queries — expected <=10. Something is doing per-row DB access again.");
});

function idsForType(array $items, string $type): array
{
    return collect($items)
        ->where('type', $type)
        ->pluck('id')
        ->all();
}
