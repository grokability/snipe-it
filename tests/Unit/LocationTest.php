<?php

use App\Models\Company;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

test('passes if not self parent', function () {
    $a = Location::factory()->make([
        'name' => 'Test Location',
        'id' => 1,
        'parent_id' => Location::factory()->create(['id' => 10])->id,
    ]);

    expect($a->isValid())->toBeTrue();
});

test('fails if self parent', function () {
    $a = Location::factory()->make([
        'name' => 'Test Location',
        'id' => 1,
        'parent_id' => 1,
    ]);

    expect($a->isValid())->toBeFalse();
    $this->assertStringContainsString(trans('validation.non_circular', ['attribute' => 'parent id']), $a->getErrors());
});

test('effective fmcs company id inherits from nearest ancestor with a company', function () {
    $company = Company::factory()->create();
    $grandparent = Location::factory()->create(['company_id' => $company->id]);
    $parent = Location::factory()->create(['parent_id' => $grandparent->id]);
    $child = Location::factory()->create(['parent_id' => $parent->id]);

    expect($child->effectiveFmcsCompanyId())->toBe($company->id);
});

test('effective fmcs company id terminates on a parent cycle', function () {
    $locationA = Location::factory()->create();
    $locationB = Location::factory()->create();

    DB::table('locations')->where('id', $locationA->id)->update(['parent_id' => $locationB->id]);
    DB::table('locations')->where('id', $locationB->id)->update(['parent_id' => $locationA->id]);

    expect($locationA->fresh()->effectiveFmcsCompanyId())->toBeNull();
});
