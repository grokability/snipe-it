<?php

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use App\Models\User;

test('edit form masks encrypted custom field for user without view encrypted', function (array $fieldAttributes, string $secret) {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $asset = assetWithEncryptedField($fieldAttributes, $secret);

    $actor = User::factory()->editAssets()->create();

    $this->actingAs($actor)
        ->get(route('hardware.edit', $asset))
        ->assertOk()
        ->assertDontSee($secret, false)
        ->assertSee(strtoupper(trans('admin/custom_fields/general.encrypted')), false);
})->with('elementTypeProvider');

test('edit form shows encrypted custom field plaintext for superuser', function (array $fieldAttributes, string $secret) {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $asset = assetWithEncryptedField($fieldAttributes, $secret);

    $actor = User::factory()->superuser()->create();

    $this->actingAs($actor)
        ->get(route('hardware.edit', $asset))
        ->assertOk()
        ->assertSee($secret, false);
})->with('elementTypeProvider');

test('checkin form masks encrypted custom field for user without view encrypted', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    // Checkin flow requires the asset to be checked out first, otherwise the
    // controller redirects to hardware.index with "already checked in".
    $asset = assetWithEncryptedField(['element' => 'textarea', 'display_checkin' => 1], 'CHECKIN-SECRET-SHOULD-NOT-LEAK', fn ($factory) => $factory->assignedToUser());

    $actor = User::factory()->checkinAssets()->create();

    $response = $this->actingAs($actor)->get(route('hardware.checkin.create', $asset))->assertOk();

    $response->assertDontSee('CHECKIN-SECRET-SHOULD-NOT-LEAK', false);
    $response->assertSee(strtoupper(trans('admin/custom_fields/general.encrypted')), false);
});

test('checkout form masks encrypted custom field for user without view encrypted', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $asset = assetWithEncryptedField(['element' => 'textarea', 'display_checkout' => 1], 'CHECKOUT-SECRET-SHOULD-NOT-LEAK');

    $actor = User::factory()->checkoutAssets()->create();

    $response = $this->actingAs($actor)->get(route('hardware.checkout.create', $asset))->assertOk();

    $response->assertDontSee('CHECKOUT-SECRET-SHOULD-NOT-LEAK', false);
    $response->assertSee(strtoupper(trans('admin/custom_fields/general.encrypted')), false);
});

test('audit form masks encrypted custom field for user without view encrypted', function () {
    $this->markIncompleteIfMySQL('Custom Fields tests do not work on MySQL');

    $asset = assetWithEncryptedField(['element' => 'textarea', 'display_audit' => 1], 'AUDIT-SECRET-SHOULD-NOT-LEAK');

    $actor = User::factory()->auditAssets()->create();

    $response = $this->actingAs($actor)->get(route('asset.audit.create', $asset))->assertOk();

    $response->assertDontSee('AUDIT-SECRET-SHOULD-NOT-LEAK', false);
    $response->assertSee(strtoupper(trans('admin/custom_fields/general.encrypted')), false);
});

/**
 * Each row exercises one of the seven Blade branches that used to leak
 * plaintext when the caller lacked view.encrypted_custom_fields.
 */
dataset('elementTypeProvider', function () {
    return [
        // Note: element='listbox' is intentionally excluded from this
        // feature-level provider. The <x-input.select> component only
        // renders options that appear in field_values, so both the
        // plaintext (pre-fix) and the mask (post-fix) collapse into
        // "no option marked selected" when they do not match one of
        // the configured options. There is no substring assertion that
        // cleanly distinguishes the two states without regex parsing.
        // The listbox branch calls Helper::customFieldFormValue verbatim
        // (identical to the four branches below), and HelperTest covers
        // the helper's return values directly.
        'textarea' => [
            ['element' => 'textarea'],
            'SECRET-INSIDE-TEXTAREA',
        ],
        'markdown-textarea' => [
            ['element' => 'markdown-textarea'],
            'SECRET-INSIDE-MARKDOWN-TEXTAREA',
        ],
        'date_picker' => [
            // Intentionally-artificial dates that won't collide with
            // real timestamps rendered elsewhere on the page (footer,
            // recent-activity strings, "created N days ago" text,
            // etc.). Any real date is at risk of matching an
            // incidental page render on the day the suite runs.
            ['element' => 'date_picker'],
            '1899-06-15',
        ],
        'datetime_picker' => [
            ['element' => 'datetime_picker'],
            '1899-06-15 03:14:15',
        ],
        // Note: element='text' with format='DATE' or 'DATETIME' also render
        // decrypt chains in the template, but CustomField::canEncryptFor()
        // rejects field_encrypted='1' for those formats at validation time
        // (native date columns can't hold ciphertext), so those branches
        // can never actually render an encrypted value. Not vulnerable.
    ];
});

/**
 * Build an asset whose model uses a fieldset containing a single encrypted
 * custom field with the given attributes and stored plaintext $secret.
 *
 * Custom fields materialize their `db_column` and add a real database
 * column via an afterCreating observer, so we always re-fetch the field
 * from the DB after the factory returns rather than trusting the in-memory
 * factory instance (which will not know its own db_column yet).
 *
 * Optionally accepts a $factoryState closure to layer additional Asset
 * factory states (e.g. assignedToUser() for the checkin test).
 */
function assetWithEncryptedField(array $fieldAttributes, string $secret, ?callable $factoryState = null): Asset
{
    $uniqueName = 'Encrypted Field '.uniqid();

    CustomField::factory()->create(array_merge([
        'name' => $uniqueName,
        'field_encrypted' => '1',
    ], $fieldAttributes));

    $field = CustomField::where('name', $uniqueName)->firstOrFail();

    $fieldset = CustomFieldset::factory()->create();
    $fieldset->fields()->attach($field, ['order' => '1', 'required' => false]);

    $model = AssetModel::factory()->create(['fieldset_id' => $fieldset->id]);

    $assetFactory = Asset::factory()->state([
        'model_id' => $model->id,
        $field->db_column => $secret,
    ]);

    if ($factoryState) {
        $assetFactory = $factoryState($assetFactory);
    }

    return $assetFactory->create();
}
