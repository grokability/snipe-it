<?php

use App\Models\Accessory;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;

test('an accessory belongs to a company', function () {
    $accessory = Accessory::factory()
        ->create(
            [
                'company_id' => Company::factory()->create()->id]);
    expect($accessory->company)->toBeInstanceOf(Company::class);
});

test('an accessory has a location', function () {
    $accessory = Accessory::factory()
        ->create(
            [
                'location_id' => Location::factory()->create()->id,
            ]);
    expect($accessory->location)->toBeInstanceOf(Location::class);
});

test('an accessory belongs to a category', function () {
    $accessory = Accessory::factory()->appleBtKeyboard()
        ->create(
            [
                'category_id' => Category::factory()->create(
                    [
                        'category_type' => 'accessory',
                    ]
                )->id]);
    expect($accessory->category)->toBeInstanceOf(Category::class);
    expect($accessory->category->category_type)->toEqual('accessory');
});

test('an accessory has a manufacturer', function () {
    $accessory = Accessory::factory()->appleBtKeyboard()->create(
        [
            'category_id' => Category::factory()->create(),
            'manufacturer_id' => Manufacturer::factory()->apple()->create(),
        ]);
    expect($accessory->manufacturer)->toBeInstanceOf(Manufacturer::class);
});

test('percent remaining returns one hundred when nothing is checked out', function () {
    $accessory = new Accessory([
        'qty' => 10,
    ]);
    $accessory->checkouts_count = 0;

    expect($accessory->percentRemaining())->toEqual(100);
});

test('percent remaining returns expected value when partially checked out', function () {
    $accessory = new Accessory([
        'qty' => 10,
    ]);
    $accessory->checkouts_count = 3;

    expect($accessory->percentRemaining())->toEqual(70.0);
});

test('percent remaining can go negative when checked out exceeds quantity', function () {
    $accessory = new Accessory([
        'qty' => 2,
    ]);
    $accessory->checkouts_count = 3;

    expect($accessory->percentRemaining())->toEqual(-50.0);
});
