<?php

use App\Models\Company;
use App\Models\Component;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('requires permission', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('components.destroy', $component->id))
        ->assertForbidden();
});

test('handles non existent component', function () {
    $this->actingAs(User::factory()->deleteComponents()->create())
        ->delete(route('components.destroy', 10000))
        ->assertSessionHas('error');
});

test('can delete component', function () {
    $component = Component::factory()->create();

    $this->actingAs(User::factory()->deleteComponents()->create())
        ->delete(route('components.destroy', $component->id))
        ->assertSessionHas('success')
        ->assertRedirect(route('components.index'));

    $this->assertSoftDeleted($component);
});

test('cannot delete component if checked out', function () {
    $component = Component::factory()->checkedOutToAsset()->create();

    $this->actingAs(User::factory()->deleteComponents()->create())
        ->delete(route('components.destroy', $component->id))
        ->assertSessionHas('error')
        ->assertRedirect(route('components.index'));
});

test('deleting component preserves component image', function () {
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
});

test('deleting component is logged', function () {
    $user = User::factory()->deleteComponents()->create();
    $component = Component::factory()->create();

    $this->actingAs($user)->delete(route('components.destroy', $component->id));

    $this->assertDatabaseHas('action_logs', [
        'created_by' => $user->id,
        'action_type' => 'delete',
        'item_type' => Component::class,
        'item_id' => $component->id,
    ]);
});

test('adheres to full multiple companies support scoping', function () {
    $this->settings->enableMultipleFullCompanySupport();

    [$companyA, $companyB] = Company::factory()->count(2)->create();

    $userInCompanyA = User::factory()->forCompany($companyA)->create();
    $componentForCompanyB = Component::factory()->for($companyB)->create();

    $this->actingAs($userInCompanyA)
        ->delete(route('components.destroy', $componentForCompanyB->id))
        ->assertSessionHas('error');

    $this->assertNotSoftDeleted($componentForCompanyB);
});
