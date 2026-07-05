<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Company;
use Tests\TestCase;

class PurgeCompaniesTest extends TestCase
{
    public function test_soft_deleted_company_is_force_deleted_by_purge()
    {
        $company = Company::factory()->create();
        $company->delete();

        $this->assertSoftDeleted($company);

        $this->artisan('snipeit:purge', ['--force' => 'true'])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_live_company_is_untouched_by_purge()
    {
        $company = Company::factory()->create();

        $this->artisan('snipeit:purge', ['--force' => 'true'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }
}
