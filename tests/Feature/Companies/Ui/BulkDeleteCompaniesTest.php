<?php

namespace Tests\Feature\Companies\Ui;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class BulkDeleteCompaniesTest extends TestCase
{
    public function test_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->post(route('companies.bulk.delete'), [
                'ids' => [1, 2, 3],
            ])
            ->assertForbidden();
    }

    public function test_company_with_assets_is_not_bulk_deleted()
    {
        $company = Company::factory()->create();
        Asset::factory()->create(['company_id' => $company->id]);

        $this->actingAs(User::factory()->deleteCompanies()->create())
            ->post(route('companies.bulk.delete'), [
                'ids' => [$company->id],
            ]);

        $this->assertModelExists($company);
        $this->assertNotSoftDeleted($company);
    }

    public function test_company_with_users_is_not_bulk_deleted()
    {
        $company = Company::factory()->create();
        $company->users()->syncWithoutDetaching([User::factory()->create()->id]);

        $this->actingAs(User::factory()->deleteCompanies()->create())
            ->post(route('companies.bulk.delete'), [
                'ids' => [$company->id],
            ]);

        $this->assertModelExists($company);
        $this->assertNotSoftDeleted($company);
    }

    public function test_deletable_companies_are_bulk_deleted()
    {
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
    }

    public function test_partial_success_deletes_the_clean_ones_and_reports_the_rest()
    {
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
    }

    public function test_parent_company_with_children_is_not_bulk_deleted()
    {
        $parent = Company::factory()->create();
        Company::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs(User::factory()->deleteCompanies()->create())
            ->post(route('companies.bulk.delete'), [
                'ids' => [$parent->id],
            ])
            ->assertSessionHas('multi_error_messages');

        $this->assertModelExists($parent);
        $this->assertNotSoftDeleted($parent);
    }

    public function test_nonexistent_ids_are_reported_and_do_not_break_the_batch()
    {
        $deletable = Company::factory()->create();

        $this->actingAs(User::factory()->deleteCompanies()->create())
            ->post(route('companies.bulk.delete'), [
                'ids' => [$deletable->id, 999999],
            ])
            ->assertRedirect(route('companies.index'))
            ->assertSessionHas('multi_error_messages');

        $this->assertSoftDeleted($deletable);
    }

    public function test_bulk_success_message_pluralizes_by_count()
    {
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
    }
}
