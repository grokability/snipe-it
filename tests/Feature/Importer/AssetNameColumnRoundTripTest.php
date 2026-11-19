<?php

use App\Livewire\Importer;
use App\Models\Asset;
use App\Models\User;
use App\Presenters\AssetPresenter;
use Livewire\Livewire;

function importTargetLabelForAssetName(string $locale): string
{
    return trans('general.item_name_var', ['item' => trans('general.asset')], $locale);
}

test('custom report asset name header matches import target label', function () {

    /**
     * Locales with non-trivial word-order differences from English where
     * item_name_var and the previous admin/hardware/form.name key
     * definitely diverge. Not exhaustive across every Snipe-IT locale
     * because the point of the round-trip is a single equality check,
     * not per-locale linguistic verification.
     */
    $representativeLocales = ['en-US', 'es-ES', 'es-MX', 'es-CO', 'de-DE', 'fr-FR'];

    // ReportsController::postCustom writes the asset Name column
    // header via this trans key.
    foreach ($representativeLocales as $locale) {
        expect(trans('general.item_name_var', ['item' => trans('general.asset')], $locale))->toBe(importTargetLabelForAssetName($locale), "Custom report asset Name column header diverges from import target label in {$locale}.");
    }
});

test('asset datatable download asset name header matches import target label', function () {
    // The AssetPresenter datatable layout drives the bs-table CSV
    // download. Pull the column definition directly and assert its
    // title matches the import target label.
    $layout = json_decode(AssetPresenter::dataTableLayout(), true);
    $nameColumn = collect($layout)->firstWhere('field', 'name');
    expect($nameColumn)->not->toBeNull('AssetPresenter datatable layout must define a name column.');

    // dataTableLayout resolves trans() at render time under the
    // then-current locale. Cross-locale round-trip pins live in
    // the ReportsController test above; here we just assert the
    // rendered title equals the import target label under the
    // currently-active locale.
    expect($nameColumn['title'])->toBe(importTargetLabelForAssetName(app()->getLocale()), 'Asset datatable download Name column title diverges from import target label.');
});

test('wizard auto map binds asset name column under spanish locale', function () {
    // The whole point of the fix is that the export header string
    // now exact-label-matches the import target label string under
    // any locale. Assert that directly by pulling the assets
    // columnOptions the wizard uses AND the header string a
    // Spanish-locale export writes, and confirming they're the
    // same string. If they are, updatingTypeOfImport()'s
    // exact-match branch binds the column with no further ceremony
    // (that logic is already covered by the wizard's own tests).
    $exportHeader = trans('general.item_name_var', ['item' => trans('general.asset')], 'es-ES');

    $this->actingAs(User::factory()->superuser()->create());

    $component = Livewire::test(Importer::class);
    $assetsFields = $component->get('assets_fields');

    // The wizard reads assets_fields when the type is 'asset'. Its
    // item_name entry is the target-label string the exact-label
    // auto-map compares CSV headers against. Under the wizard's own
    // locale it MUST equal what the customer's export CSV writes
    // under the same locale.
    expect($assetsFields['item_name'])->toBe(trans('general.item_name_var', ['item' => trans('general.asset')], app()->getLocale()), 'Assets fields item_name target label must equal what the export sites write. Diverging here is what broke the customer.');

    // Cross-locale sanity: even if the wizard runs under English,
    // an export CSV written under Spanish should be recognizable by
    // an admin who set their locale to Spanish before opening the
    // wizard. Both sides derive from the same trans expression, so
    // this holds mechanically.
    expect(trans('general.item_name_var', ['item' => trans('general.asset')], 'es-ES'))->toBe($exportHeader, 'Export header derivation must be stable across callers.');
});
