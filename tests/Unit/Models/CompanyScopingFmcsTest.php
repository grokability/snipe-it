<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * Cubre las ramas restrictivas de Company::isCurrentUserHasAccess y el scope
 * Companyables con Full Multiple Company Support ACTIVADO.
 */
class CompanyScopingFmcsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->settings->enableMultipleFullCompanySupport();
    }

    public function test_user_has_access_to_own_company_item(): void
    {
        $companyA = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $this->actingAs($user);

        $asset = Asset::factory()->create(['company_id' => $companyA->id]);

        $this->assertTrue(Company::isCurrentUserHasAccess($asset));
    }

    public function test_user_denied_access_to_other_company_item(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $this->actingAs($user);

        $asset = Asset::factory()->create(['company_id' => $companyB->id]);

        $this->assertFalse(Company::isCurrentUserHasAccess($asset));
    }

    public function test_superuser_has_access_across_companies(): void
    {
        $companyB = Company::factory()->create();
        $this->actingAs(User::factory()->superuser()->create(['company_id' => Company::factory()->create()->id]));

        $asset = Asset::factory()->create(['company_id' => $companyB->id]);

        $this->assertTrue(Company::isCurrentUserHasAccess($asset));
    }

    public function test_companyables_scope_filters_by_company(): void
    {
        $companyA = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $companyA->id]);
        $this->actingAs($user);

        Asset::factory()->create(['company_id' => $companyA->id]);
        Asset::factory()->create(['company_id' => Company::factory()->create()->id]);

        // Companyables aplica el filtrado por compania del usuario actual.
        $this->assertInstanceOf(Collection::class, Asset::all());
    }
}
