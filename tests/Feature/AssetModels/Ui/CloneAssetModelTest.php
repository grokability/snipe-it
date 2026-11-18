<?php

use App\Livewire\CustomFieldSetDefaultValuesForModel;
use App\Models\AssetModel;
use App\Models\CustomFieldset;
use App\Models\User;
use Livewire\Livewire;

test('clone page preselects source fieldset', function () {
    // Regression for #19286: cloning a model that has a fieldset was
    // rendering the clone form with the fieldset selector empty, because
    // getClone was passing model_id => null to the fieldset picker.
    $fieldset = CustomFieldset::factory()->create();
    $source = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

    $this->actingAs(User::factory()->superuser()->create())
        ->get(route('models.clone.create', $source))
        ->assertOk()
        // The fieldset id is rendered inside the fieldset selector's
        // <option selected> markup for the source fieldset.
        ->assertSee('value="'.$fieldset->id.'" selected', false);
});

test('livewire fieldset picker receives source model id on clone', function () {
    $fieldset = CustomFieldset::factory()->create();
    $source = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

    $this->actingAs(User::factory()->superuser()->create());

    Livewire::test(CustomFieldSetDefaultValuesForModel::class, ['model_id' => $source->id])
        ->assertSet('model_id', $source->id)
        ->assertSet('fieldset_id', $fieldset->id);
});

test('livewire fieldset picker leaves fieldset empty when no model id', function () {
    // Baseline: without a source model_id (i.e. plain create form), the
    // fieldset stays unset. Guards against a fix that would leak defaults
    // into an unrelated create flow.
    $this->actingAs(User::factory()->superuser()->create());

    Livewire::test(CustomFieldSetDefaultValuesForModel::class, ['model_id' => null])
        ->assertSet('model_id', null)
        ->assertSet('fieldset_id', null);
});

test('create permission alone is not enough to clone asset model', function () {
    // Cloning renders the source asset model's data (model_number,
    // notes, fieldset, image) into the create form, so a user with
    // only models.create (no models.view) must be blocked.
    $model = AssetModel::factory()->create();

    $this->actingAs(User::factory()->createAssetModels()->create())
        ->get(route('models.clone.create', $model))
        ->assertForbidden();
});
