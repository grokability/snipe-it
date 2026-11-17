<?php

namespace Tests\Feature\Importing;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Import;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Importing\CleansUpImportFiles;
use Tests\TestCase;

/**
 * Regression coverage for the reporter case where a CSV with a single
 * "checked out to: Location" column mapped to checkout_location did not
 * actually check the asset out to the location. The prior code required
 * BOTH a checkout_class column (with value "Location") AND a
 * checkout_location column; missing the first silently fell through to
 * user lookup, produced no target, and skipped the checkout with no
 * error surfaced. Location intent is now inferred when checkout_location
 * has a value and no explicit checkout_class contradicts it. Same shape
 * applies to the new checkout_asset and checkout_user shortcuts.
 */
class AssetImportCheckoutTargetsTest extends TestCase
{
    use CleansUpImportFiles;

    private function importCsv(string $csv, array $mappings, array $extraPayload = []): void
    {
        Category::factory()->create(['category_type' => 'asset']);
        Location::factory()->create();
        Statuslabel::factory()->create();
        Company::factory()->create();
        AssetModel::factory()->create();

        $importer = User::factory()->canImport()->create();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.store'), [
                'files' => [
                    $this->tempUploadedFile('checkout-targets.csv', $csv),
                ],
            ])
            ->assertSuccessful();

        $import = Import::latest()->first();

        $this->actingAsForApi($importer)
            ->postJson(route('api.imports.importFile', $import->id), array_merge([
                'import-type' => 'asset',
                'import-update' => true,
                'column-mappings' => $mappings,
            ], $extraPayload))
            ->assertSuccessful();
    }

    #[Test]
    public function checkout_location_column_alone_checks_asset_out_to_the_named_location(): void
    {
        $destination = Location::factory()->create(['name' => 'BIC_ALS Office_AD']);
        Asset::factory()->create(['asset_tag' => 'CHKLOC-001', 'assigned_to' => null]);

        $csv = "asset tag,checked out to: Location\n";
        $csv .= "CHKLOC-001,{$destination->name}\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checked out to: Location' => 'checkout_location',
        ]);

        $imported = Asset::where('asset_tag', 'CHKLOC-001')->firstOrFail();
        $this->assertSame($destination->id, $imported->assigned_to);
        $this->assertSame(Location::class, $imported->assigned_type);
    }

    #[Test]
    public function checkout_class_location_plus_checkout_location_still_works(): void
    {
        // Backward-compat pin for the two-column shape.
        $destination = Location::factory()->create(['name' => 'Legacy Two Column Room']);
        Asset::factory()->create(['asset_tag' => 'CHKLOC-002', 'assigned_to' => null]);

        $csv = "asset tag,checkout type,checkout location\n";
        $csv .= "CHKLOC-002,Location,{$destination->name}\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout type' => 'checkout_class',
            'checkout location' => 'checkout_location',
        ]);

        $imported = Asset::where('asset_tag', 'CHKLOC-002')->firstOrFail();
        $this->assertSame($destination->id, $imported->assigned_to);
        $this->assertSame(Location::class, $imported->assigned_type);
    }

    #[Test]
    public function checkout_asset_column_alone_checks_asset_out_to_the_named_parent_asset(): void
    {
        $parent = Asset::factory()->create(['asset_tag' => 'PARENT-001']);
        Asset::factory()->create(['asset_tag' => 'CHILD-001', 'assigned_to' => null]);

        $csv = "asset tag,checkout asset\n";
        $csv .= "CHILD-001,{$parent->asset_tag}\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout asset' => 'checkout_asset',
        ]);

        $child = Asset::where('asset_tag', 'CHILD-001')->firstOrFail();
        $this->assertSame($parent->id, $child->assigned_to);
        $this->assertSame(Asset::class, $child->assigned_type);
    }

    #[Test]
    public function checkout_asset_with_unknown_tag_skips_checkout_and_does_not_assign(): void
    {
        // Assets are never auto-created as checkout targets; a typo in
        // the tag column produces a skipped checkout, not a phantom asset.
        Asset::factory()->create(['asset_tag' => 'CHILD-002', 'assigned_to' => null]);

        $csv = "asset tag,checkout asset\n";
        $csv .= "CHILD-002,DOES-NOT-EXIST\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout asset' => 'checkout_asset',
        ]);

        $child = Asset::where('asset_tag', 'CHILD-002')->firstOrFail();
        $this->assertNull($child->assigned_to);
    }

    #[Test]
    public function checkout_user_column_alone_checks_asset_out_to_the_matching_user(): void
    {
        $targetUser = User::factory()->create(['username' => 'checkout_user_target']);
        Asset::factory()->create(['asset_tag' => 'CHKUSR-001', 'assigned_to' => null]);

        $csv = "asset tag,checkout user\n";
        $csv .= "CHKUSR-001,{$targetUser->username}\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout user' => 'checkout_user',
        ]);

        $imported = Asset::where('asset_tag', 'CHKUSR-001')->firstOrFail();
        $this->assertSame($targetUser->id, $imported->assigned_to);
        $this->assertSame(User::class, $imported->assigned_type);
    }

    #[Test]
    public function checkout_user_creates_new_user_when_row_has_enough_identity_data(): void
    {
        // Preserves the existing "create user on the fly" behavior when
        // the row carries additional identity columns alongside a
        // checkout_user username that does not yet exist.
        Asset::factory()->create(['asset_tag' => 'CHKUSR-002', 'assigned_to' => null]);

        $csv = "asset tag,checkout user,first name,last name,email\n";
        $csv .= "CHKUSR-002,newhire,New,Hire,new.hire@example.org\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout user' => 'checkout_user',
            'first name' => 'first_name',
            'last name' => 'last_name',
            'email' => 'email',
        ]);

        $newUser = User::where('username', 'newhire')->firstOrFail();
        $imported = Asset::where('asset_tag', 'CHKUSR-002')->firstOrFail();
        $this->assertSame($newUser->id, $imported->assigned_to);
        $this->assertSame(User::class, $imported->assigned_type);
    }

    #[Test]
    public function explicit_checkout_class_user_overrides_present_checkout_location(): void
    {
        // Disambiguation escape hatch: explicit checkout_class=user beats
        // a populated checkout_location.
        Location::factory()->create(['name' => 'Not Used']);
        $targetUser = User::factory()->create(['username' => 'checkout_target_user']);
        Asset::factory()->create(['asset_tag' => 'CHKLOC-003', 'assigned_to' => null]);

        $csv = "asset tag,checkout type,checkout location,username\n";
        $csv .= "CHKLOC-003,User,Not Used,{$targetUser->username}\n";

        $this->importCsv($csv, [
            'asset tag' => 'asset_tag',
            'checkout type' => 'checkout_class',
            'checkout location' => 'checkout_location',
            'username' => 'username',
        ]);

        $imported = Asset::where('asset_tag', 'CHKLOC-003')->firstOrFail();
        $this->assertSame($targetUser->id, $imported->assigned_to);
        $this->assertSame(User::class, $imported->assigned_type);
    }
}
