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
    public function testRequiresPermission()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertForbidden();
    }

    public function testHandlesNonExistentComponent()
    {
        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', 10000))
            ->assertSessionHas('error');
    }

    public function testCanDeleteComponent()
    {
        $component = Component::factory()->create();

        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertSessionHas('success')
            ->assertRedirect(route('components.index'));

        $this->assertSoftDeleted($component);
    }

    public function testCannotDeleteComponentIfCheckedOut()
    {
        $component = Component::factory()->checkedOutToAsset()->create();

        $this->actingAs(User::factory()->deleteComponents()->create())
            ->delete(route('components.destroy', $component->id))
            ->assertSessionHas('error')
            ->assertRedirect(route('components.index'));
    }

    public function testDeletingComponentRemovesComponentImage()
    {
        Storage::fake('public');

        $component = Component::factory()->create(['image' => 'component-image.jpg']);

        Storage::disk('public')->put('components/component-image.jpg', 'content');

        Storage::disk('public')->assertExists('components/component-image.jpg');

        $this->actingAs(User::factory()->deleteComponents()->create())->delete(route('components.destroy', $component->id));

        Storage::disk('public')->assertMissing('components/component-image.jpg');
    }

    public function testDeletingComponentIsLogged()
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

    public function testAdheresToFullMultipleCompaniesSupportScoping()
    {
        $this->settings->enableMultipleFullCompanySupport();

        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $userInCompanyA = User::factory()->for($companyA)->create();
        $componentForCompanyB = Component::factory()->for($companyB)->create();

        $this->actingAs($userInCompanyA)
            ->delete(route('components.destroy', $componentForCompanyB->id))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($componentForCompanyB);
    }
}
