<?php

use App\Models\Company;

test('two child companies with the same name under different parents can coexist', function () {
    $alpha = Company::factory()->create(['name' => 'AlphaCorp']);
    $bravo = Company::factory()->create(['name' => 'BravoCorp']);

    Company::factory()->create(['name' => 'West', 'parent_id' => $alpha->id]);
    $westUnderBravo = Company::factory()->create(['name' => 'West', 'parent_id' => $bravo->id]);

    expect($westUnderBravo->exists)->toBeTrue();
    $this->assertDatabaseCount('companies', 4);
});

test('two child companies with the same name under the same parent still collide', function () {
    $alpha = Company::factory()->create(['name' => 'AlphaCorp']);
    Company::factory()->create(['name' => 'West', 'parent_id' => $alpha->id]);

    $duplicate = new Company(['name' => 'West', 'parent_id' => $alpha->id]);

    expect($duplicate->save())->toBeFalse('sibling company name collision should still be rejected');
    expect($duplicate->getErrors()->get('name'))->not->toBeEmpty();
});

test('two top level companies with the same name still collide', function () {
    Company::factory()->create(['name' => 'AcmeGlobal', 'parent_id' => null]);

    $duplicate = new Company(['name' => 'AcmeGlobal', 'parent_id' => null]);

    expect($duplicate->save())->toBeFalse('two top-level companies sharing a name should still be rejected');
});

test('top level and child can share a name', function () {
    $parent = Company::factory()->create(['name' => 'ParentCorp']);
    $topLevel = Company::factory()->create(['name' => 'Sub', 'parent_id' => null]);
    $child = Company::factory()->create(['name' => 'Sub', 'parent_id' => $parent->id]);

    expect($topLevel->exists)->toBeTrue();
    expect($child->exists)->toBeTrue();
});
