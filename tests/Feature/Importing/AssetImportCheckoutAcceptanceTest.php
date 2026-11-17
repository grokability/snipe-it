<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Import;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

/**
 * Pin the "CSV import creates a checkout_acceptance row when the imported
 * asset requires acceptance and is being checked out to a user" behavior.
 *
 * The import path routes through Asset::checkOut which fires
 * CheckoutableCheckedOut. CheckoutableListener::handleAcceptance responds
 * by calling CreateCheckoutAcceptanceAction::run when the target is a
 * User and the checkoutable's category requires acceptance. This is the
 * same code path the UI checkout uses. No importer-specific gate exists.
 * Without this regression test a future refactor of the listener or the
 * importer could silently drop acceptance creation on imported checkouts,
 * leaving imported assets in an unaccepted-but-checked-out state that
 * would only surface later in the acceptance-alerting cron.
 */
class AssetImportCheckoutAcceptanceTest extends TestCase
{
    use CleansUpImportFiles;

    #[Test]
    public function importing_a_user_checkout_creates_an_acceptance_record_when_category_requires_it(): void
    {
        $category = Category::factory()->create([
            'category_type' => 'asset',
            'require_acceptance' => true,
        ]);
        Location::factory()->create();
        $statusLabel = Statuslabel::factory()->create();
        Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create(['name' => 'Acceptance Test Model']);

        $target = User::factory()->create(['username' => 'accepting_user']);
        $importer = User::factory()->canImport()->create();

        Asset::factory()
            ->for($assetModel, 'model')
            ->for($statusLabel, 'status')
            ->create(['asset_tag' => 'ACCEPT-001', 'assigned_to' => null]);

        $csv = "asset tag,checkout user\n";
        $csv .= "ACCEPT-001,{$target->username}\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('acceptance.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'checkout user' => 'checkout_user',
                ],
            ])
            ->assertSuccessful();

        $imported = Asset::where('asset_tag', 'ACCEPT-001')->firstOrFail();
        $this->assertSame($target->id, $imported->assigned_to);

        $this->assertDatabaseHas('checkout_acceptances', [
            'checkoutable_type' => Asset::class,
            'checkoutable_id' => $imported->id,
            'assigned_to_id' => $target->id,
            'accepted_at' => null,
            'declined_at' => null,
        ]);
    }

    #[Test]
    public function importing_a_location_checkout_does_not_create_an_acceptance_record(): void
    {
        // Locations can't "accept" anything; the listener guards on
        // target instanceof User. Pinning this so a future change to the
        // guard doesn't accidentally start creating orphan acceptance
        // rows for location checkouts.
        $category = Category::factory()->create([
            'category_type' => 'asset',
            'require_acceptance' => true,
        ]);
        $statusLabel = Statuslabel::factory()->create();
        Company::factory()->create();
        $assetModel = AssetModel::factory()->for($category, 'category')->create();
        $destination = Location::factory()->create(['name' => 'Storage Room B']);

        $importer = User::factory()->canImport()->create();

        Asset::factory()
            ->for($assetModel, 'model')
            ->for($statusLabel, 'status')
            ->create(['asset_tag' => 'ACCEPT-002', 'assigned_to' => null]);

        $csv = "asset tag,checkout location\n";
        $csv .= "ACCEPT-002,{$destination->name}\n";

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('acceptance-location.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), [
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => [
                    'asset tag' => 'asset_tag',
                    'checkout location' => 'checkout_location',
                ],
            ])
            ->assertSuccessful();

        $imported = Asset::where('asset_tag', 'ACCEPT-002')->firstOrFail();
        $this->assertSame($destination->id, $imported->assigned_to);
        $this->assertSame(Location::class, $imported->assigned_type);

        $this->assertSame(
            0,
            CheckoutAcceptance::where('checkoutable_type', Asset::class)
                ->where('checkoutable_id', $imported->id)
                ->count(),
            'A location checkout must not create an acceptance record.',
        );
    }
}
