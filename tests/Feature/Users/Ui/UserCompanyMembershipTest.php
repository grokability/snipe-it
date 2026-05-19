<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class UserCompanyMembershipTest extends TestCase
{
    public function test_updating_user_via_ui_syncs_company_pivot()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id]);

        $actor = User::factory()->superuser()->create();

        $this->actingAs($actor)
            ->put(route('users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'activated' => $user->activated,
                'company_ids' => [$companyA->id, $companyB->id],
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertCount(2, $user->companies, 'Pivot should hold both companies after UI update');
        $this->assertTrue($user->companies->contains($companyA));
        $this->assertTrue($user->companies->contains($companyB));
    }

    public function test_bulk_edit_assigns_companies_via_pivot()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $users = User::factory()->count(3)->create(['company_id' => null]);

        $actor = User::factory()->superuser()->create();

        $ids = $users->pluck('id')->mapWithKeys(fn ($id) => [$id => $id])->all();

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), array_merge(
                ['ids' => $ids],
                ['company_ids' => [$companyA->id, $companyB->id]],
            ))
            ->assertRedirect();

        foreach ($users as $user) {
            $user->refresh();
            $this->assertCount(2, $user->companies, "User {$user->id} should belong to two companies after bulk edit");
            $this->assertTrue($user->companies->contains($companyA));
            $this->assertTrue($user->companies->contains($companyB));
        }
    }

    public function test_bulk_edit_clears_company_pivot_when_null_flag_set()
    {
        $company = Company::factory()->create();

        $users = User::factory()->count(2)->create(['company_id' => $company->id]);
        foreach ($users as $user) {
            $user->companies()->sync([$company->id]);
        }

        $actor = User::factory()->superuser()->create();

        $ids = $users->pluck('id')->mapWithKeys(fn ($id) => [$id => $id])->all();

        $this->actingAs($actor)
            ->post(route('users/bulkeditsave'), [
                'ids' => $ids,
                'null_company_ids' => '1',
            ])
            ->assertRedirect();

        foreach ($users as $user) {
            $user->refresh();
            $this->assertCount(0, $user->companies, "User {$user->id} should have no companies after null flag");
            $this->assertNull($user->company_id);
        }
    }
}
