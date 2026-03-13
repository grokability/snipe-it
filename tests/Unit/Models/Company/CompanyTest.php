<?php
namespace Tests\Unit\Models\Company;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    public function testACompanyCanHaveUsers()
    {
        $company = Company::factory()->create();
        $user = User::factory()
                ->create(
                    [
                        'company_id'=> $company->id
                    ]
        );

        $this->assertCount(1, $company->users);
    }
}
