<?php

namespace Tests\Feature\Users\Ui;

use App\Models\Company;
use App\Models\User;
use Tests\TestCase;

class CloneUserTest extends TestCase
{
    public function test_page_renders()
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.clone.show', User::factory()->create()))
            ->assertOk();
    }

    public function test_clone_prepopulates_all_companies_for_multi_company_user()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $user = User::factory()->create(['company_id' => $companyA->id]);
        $user->companies()->sync([$companyA->id, $companyB->id]);

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('users.clone.show', $user))
            ->assertOk();

        // Both company IDs should be pre-selected in the form.
        $response->assertSee('value="'.$companyA->id.'"', false);
        $response->assertSee('value="'.$companyB->id.'"', false);
    }
}
