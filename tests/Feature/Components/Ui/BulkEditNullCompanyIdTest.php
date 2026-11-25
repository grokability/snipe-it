<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Tests\TestCase;

/**
 * GHSA-rhwq-w7gg-rwr6 / GHSA-3prj-5x7r-hx5r. See the sibling
 * BulkEditNullCompanyIdTest files for the full context.
 */
class BulkEditNullCompanyIdTest extends TestCase
{
    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_under_fmcs()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editComponents()->viewComponents()->make()
        );
        $component = Component::factory()->for($company)->create();

        $this->actingAs($actor)
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('components.index'));

        $this->assertEquals($company->id, $component->fresh()->company_id);
    }

    public function test_non_superuser_cannot_null_company_id_via_ui_bulk_edit_in_floater_mode()
    {
        $this->settings->enableFloaterMode();

        $company = Company::factory()->create();
        $actor = $company->users()->save(
            User::factory()->editComponents()->viewComponents()->make()
        );
        $component = Component::factory()->for($company)->create();

        $this->actingAs($actor)
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('components.index'));

        $this->assertEquals($company->id, $component->fresh()->company_id);
    }

    public function test_superuser_can_still_null_company_id_via_ui_bulk_edit()
    {
        $this->settings->enableMultipleFullCompanySupport();

        $company = Company::factory()->create();
        $component = Component::factory()->for($company)->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('components.bulkedit.store'), [
                'ids' => [$component->id],
                'null_company_id' => '1',
            ])
            ->assertRedirect(route('components.index'));

        $this->assertNull($component->fresh()->company_id);
    }
}
