<?php

use App\Livewire\Importer;
use App\Models\User;
use Livewire\Livewire;

test('send welcome checkbox is not rendered on asset history type', function () {
    // AssetHistoryImporter's resolveTargetUser() only queries the
    // users table (see the private method's body). It never creates
    // users, so a "send welcome email to newly-created users"
    // checkbox is dead UI here. hasUserCheckoutMapping IS true for
    // assetHistory because its field_map contains full_name, so the
    // guard has to be on the typeOfImport itself.
    $this->actingAs(User::factory()->superuser()->create());

    $component = Livewire::test(Importer::class)
        ->set('typeOfImport', 'assetHistory')
        ->set('wizardStep', 1);

    $component->assertDontSee('name="send_welcome"', false);
});

test('send welcome checkbox is still rendered on user type', function () {
    // Positive-side regression guard: the exclusion added for
    // assetHistory must not leak into the user import type, where
    // the checkbox is load-bearing.
    $this->actingAs(User::factory()->superuser()->create());

    $component = Livewire::test(Importer::class)
        ->set('typeOfImport', 'user')
        ->set('wizardStep', 1);

    $component->assertSee('name="send_welcome"', false);
});
