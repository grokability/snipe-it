<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

test('setting location id to zero normalizes to null', function () {
    $asset = Asset::factory()->create();

    $asset->location_id = 0;
    expect($asset->location_id)->toBeNull();

    $asset->location_id = '0';
    expect($asset->location_id)->toBeNull();

    $asset->location_id = '';
    expect($asset->location_id)->toBeNull();
});

test('setting company id to zero normalizes to null', function () {
    $asset = Asset::factory()->create();

    $asset->company_id = 0;
    expect($asset->company_id)->toBeNull();

    $asset->company_id = '0';
    expect($asset->company_id)->toBeNull();

    $asset->company_id = '';
    expect($asset->company_id)->toBeNull();
});

test('legacy zero stored in db reads as null', function () {
    // Simulate a row from the pre-mutator era where 0 slipped in.
    // DB::update bypasses model events so we can write the invalid
    // value directly.
    $asset = Asset::factory()->create();
    DB::table('assets')->where('id', $asset->id)->update([
        'location_id' => 0,
        'company_id' => 0,
    ]);

    $reloaded = Asset::find($asset->id);

    expect($reloaded->location_id)->toBeNull();
    expect($reloaded->company_id)->toBeNull();
});

test('real ids still persist and read normally', function () {
    // Non-regression: normalization must not touch valid ids.
    $company = Company::factory()->create();
    $location = Location::factory()->create();
    $asset = Asset::factory()->create();

    $asset->company_id = $company->id;
    $asset->location_id = $location->id;
    $asset->save();

    $reloaded = Asset::find($asset->id);

    expect($reloaded->company_id)->toBe($company->id);
    expect($reloaded->location_id)->toBe($location->id);
});
