<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;

test('requires permission', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [1, 2, 3],
        ])
        ->assertForbidden();
});

test('company with assets is not bulk deleted', function () {
    $company = Company::factory()->create();
    Asset::factory()->create(['company_id' => $company->id]);

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$company->id],
        ]);

    $this->assertModelExists($company);
    $this->assertNotSoftDeleted($company);
});

test('company with users is not bulk deleted', function () {
    $company = Company::factory()->create();
    $company->users()->syncWithoutDetaching([User::factory()->create()->id]);

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$company->id],
        ]);

    $this->assertModelExists($company);
    $this->assertNotSoftDeleted($company);
});

test('deletable companies are bulk deleted', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $company3 = Company::factory()->create();

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$company1->id, $company2->id, $company3->id],
        ])
        ->assertRedirect(route('companies.index'));

    $this->assertSoftDeleted($company1);
    $this->assertSoftDeleted($company2);
    $this->assertSoftDeleted($company3);
});

test('partial success deletes the clean ones and reports the rest', function () {
    // One clean company, one with an asset — the clean one should be
    // deleted, the other should be skipped and reported to the flash bag.
    $deletable = Company::factory()->create();
    $blocked = Company::factory()->create();
    Asset::factory()->create(['company_id' => $blocked->id]);

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$deletable->id, $blocked->id],
        ])
        ->assertRedirect(route('companies.index'))
        ->assertSessionHas('success')
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted($deletable);
    $this->assertNotSoftDeleted($blocked);
});

test('parent company with children is not bulk deleted', function () {
    $parent = Company::factory()->create();
    Company::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$parent->id],
        ])
        ->assertSessionHas('multi_error_messages');

    $this->assertModelExists($parent);
    $this->assertNotSoftDeleted($parent);
});

test('nonexistent ids are reported and do not break the batch', function () {
    $deletable = Company::factory()->create();

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$deletable->id, 999999],
        ])
        ->assertRedirect(route('companies.index'))
        ->assertSessionHas('multi_error_messages');

    $this->assertSoftDeleted($deletable);
});

test('bulk success message pluralizes by count', function () {
    // Single-item batch → singular success message.
    $solo = Company::factory()->create();

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$solo->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/companies/message.delete.bulk_success', 1, ['count' => 1]));

    $a = Company::factory()->create();
    $b = Company::factory()->create();
    $c = Company::factory()->create();

    $this->actingAs(User::factory()->deleteCompanies()->create())
        ->post(route('companies.bulk.delete'), [
            'ids' => [$a->id, $b->id, $c->id],
        ])
        ->assertSessionHas('success', trans_choice('admin/companies/message.delete.bulk_success', 3, ['count' => 3]));
});
