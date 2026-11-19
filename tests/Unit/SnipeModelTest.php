<?php

use App\Models\SnipeModel;

test('sets purchase dates appropriately', function () {
    $c = new SnipeModel;
    $c->purchase_date = '';
    expect($c->purchase_date)->toBeNull();
    $c->purchase_date = null;
    expect($c->purchase_date)->toBeNull();
    $c->purchase_date = '2016-03-25';
    expect($c->purchase_date === '2016-03-25')->toBeTrue();
    expect($c->purchase_date)->toEqual('2016-03-25');
});

test('sets purchase costs appropriately', function () {
    $c = new SnipeModel;
    $c->purchase_cost = '';
    expect($c->purchase_cost)->toBeNull();
    $c->purchase_cost = null;
    expect($c->purchase_cost)->toBeNull();
    $c->purchase_cost = '0.00';
    expect((float) $c->purchase_cost)->toBe(0.0);
    $c->purchase_cost = '9.54';
    expect((float) $c->purchase_cost)->toBe(9.54);
    $c->purchase_cost = 12.34; // already a float — no ParseCurrency needed
    expect((float) $c->purchase_cost)->toBe(12.34);
});

test('sets purchase cost from us format with thousands', function () {
    $this->settings->set(['digit_separator' => '1,234.56']);
    $c = new SnipeModel;

    $c->purchase_cost = '8,888.00';
    expect((float) $c->purchase_cost)->toBe(8888.0);

    $c->purchase_cost = '8888.00';
    expect((float) $c->purchase_cost)->toBe(8888.0);
});

test('sets purchase cost from eu format with thousands', function () {
    $this->settings->set(['digit_separator' => '1.234,56']);
    $c = new SnipeModel;

    $c->purchase_cost = '8.888,00';
    expect((float) $c->purchase_cost)->toBe(8888.0);

    $c->purchase_cost = '8888,00';
    expect((float) $c->purchase_cost)->toBe(8888.0);

    $c->purchase_cost = '12,34';
    expect((float) $c->purchase_cost)->toBe(12.34);
});

test('nulls blank location ids but not others', function () {
    $c = new SnipeModel;
    $c->location_id = '';
    expect($c->location_id === null)->toBeTrue();
    $c->location_id = '5';
    expect($c->location_id == 5)->toBeTrue();
});

test('nulls blank categories but not others', function () {
    $c = new SnipeModel;
    $c->category_id = '';
    expect($c->category_id === null)->toBeTrue();
    $c->category_id = '1';
    expect($c->category_id == 1)->toBeTrue();
});

test('nulls blank suppliers but not others', function () {
    $c = new SnipeModel;
    $c->supplier_id = '';
    expect($c->supplier_id === null)->toBeTrue();
    $c->supplier_id = '4';
    expect($c->supplier_id == 4)->toBeTrue();
});

test('nulls blank depreciations but not others', function () {
    $c = new SnipeModel;
    $c->depreciation_id = '';
    expect($c->depreciation_id === null)->toBeTrue();
    $c->depreciation_id = '4';
    expect($c->depreciation_id == 4)->toBeTrue();
});

test('nulls blank manufacturers but not others', function () {
    $c = new SnipeModel;
    $c->manufacturer_id = '';
    expect($c->manufacturer_id === null)->toBeTrue();
    $c->manufacturer_id = '4';
    expect($c->manufacturer_id == 4)->toBeTrue();
});
