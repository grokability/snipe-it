<?php

namespace Tests\Feature\Components\Ui;

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\TestsFullMultipleCompaniesSupport;
use Tests\Concerns\TestsPermissionsRequirement;
use Tests\TestCase;

class DeleteComponentTest extends TestCase implements TestsFullMultipleCompaniesSupport, TestsPermissionsRequirement
{
    public function test_requires_permission()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertForbidden();
    }

    public function test_handles_non_existent_component()
    {
        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', 10000))
            ->assertSessionHas('error');
    }

    public function test_can_delete_component()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertSessionHas('success')
            ->assertRedirect(route('components.index'));

        $this->assertSoftDeleted($component);
    }

    public function test_cannot_delete_component_if_checked_out()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertSessionHas('error')
            ->assertRedirect(route('components.index'));
    }

    public function test_deleting_component_preserves_component_image()
    {
        // Soft-deleting a component preserves its image on disk so a
        // restored component still has one. The image is only removed
        // for good by `snipeit:purge` when the row is force-deleted.
        // Coverage for that permanent-removal path lives in
        // `tests/Feature/Console/Commands/PurgeTest.php`.
        Storage::fake('public');

        $component = Component::factory()->create(['image' => 'component-image.jpg']);

        Storage::disk('public')->put('components/component-image.jpg', 'content');

        Storage::disk('public')->assertExists('components/component-image.jpg');

        $this->actingAs(User::factory()->deleteComponents()->create())->delete(route('components.destroy', $component->id));

        Storage::disk('public')->assertExists('components/component-image.jpg');
    }

    public function test_deleting_component_is_logged()
    {
        $user = User::factory()->deleteComponents()->create();
        $component = Component::factory()->create();

        $this->actingAs($user)->delete(route('components.destroy', $component->id));

        $this->assertDatabaseHas('action_logs', [
            'created_by' => $user->id,
            'action_type' => 'delete',
            'item_type' => Component::class,
            'item_id' => $component->id,
        ]);
    }

    public function test_adheres_to_full_multiple_companies_support_scoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = User::factory()->forCompany($companyA)->create();
        $componentForCompanyB = Component::factory()->for($companyB)->create();

        $this->actingAs($userInCompanyA)
            ->delete(route('components.destroy', $componentForCompanyB->id))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($componentForCompanyB);
    }
}
