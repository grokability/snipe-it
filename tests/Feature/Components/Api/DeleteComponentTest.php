<?php

namespace Tests\Feature\Components\Api;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteComponentTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function testRequiresPermission()
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->create())
            ->deleteJson(route('api.components.destroy', $component))
            ->assertForbidden();

        $this->assertNotSoftDeleted($component);
    }

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $componentA = Component::factory()->for($companyA)->create();
        $componentB = Component::factory()->for($companyB)->create();
        $componentC = Component::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->deleteComponents()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->deleteComponents()->make());

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($userInCompanyA)
            ->deleteJson(route('api.components.destroy', $componentB))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($userInCompanyB)
            ->deleteJson(route('api.components.destroy', $componentA))
            ->assertStatusMessageIs('error');

        $this->actingAsForApi($superUser)
            ->deleteJson(route('api.components.destroy', $componentC))
            ->assertStatusMessageIs('success');

        $this->assertNotSoftDeleted($componentA);
        $this->assertNotSoftDeleted($componentB);
        $this->assertSoftDeleted($componentC);
    }

    public function testCanDeleteComponents()
    {
        $component = Component::factory()->create();

        $this->actingAsForApi(User::factory()->deleteComponents()->create())
            ->deleteJson(route('api.components.destroy', $component))
            ->assertStatusMessageIs('success');

        $this->assertSoftDeleted($component);
    }

    public function testCannotDeleteComponentIfCheckedOut()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $this->actingAsForApi(User::factory()->deleteComponents()->create())
            ->deleteJson(route('api.components.destroy', $component))
            ->assertStatusMessageIs('error');
    }
}
