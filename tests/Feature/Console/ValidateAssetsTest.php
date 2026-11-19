<?php

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('it only outputs invalid assets by default', function () {
    [$validAsset, $invalidAsset] = seedValidAndInvalidAssets();

    Artisan::call('snipeit:validate-assets');
    $output = Artisan::output();

    $this->assertStringContainsString('Run this command with the --all option to see the full list in the console.', $output);
    $this->assertStringContainsString($invalidAsset->asset_tag, $output);
    $this->assertStringContainsString($invalidAsset->serial, $output);
    $this->assertStringNotContainsString($validAsset->asset_tag, $output);
    $this->assertStringNotContainsString($validAsset->serial, $output);
    $this->assertStringNotContainsString('MessageBag', $output);
    $this->assertStringContainsString('assigned type', strtolower($output));
});

test('it outputs all assets when all option is passed', function () {
    [$validAsset, $invalidAsset] = seedValidAndInvalidAssets();

    Artisan::call('snipeit:validate-assets', ['--all' => true]);
    $output = Artisan::output();

    $this->assertStringContainsString($invalidAsset->asset_tag, $output);
    $this->assertStringContainsString($invalidAsset->serial, $output);
    $this->assertStringContainsString($validAsset->asset_tag, $output);
    $this->assertStringContainsString($validAsset->serial, $output);
});

/**
 * @return array{0: Asset, 1: Asset}
 */
function seedValidAndInvalidAssets(): array
{
    $tagSuffix = (string) Str::uuid();

    $validAsset = Asset::factory()->create([
        'asset_tag' => 'GOOD-ASSET-'.$tagSuffix,
        'serial' => 'GOOD-SERIAL-'.$tagSuffix,
    ]);

    // assigned_to is not in $fillable, so we create the asset normally then
    // inject the inconsistent state directly via DB to simulate legacy data.
    $invalidAsset = Asset::factory()
        ->canBeInvalidUponCreation()
        ->create([
            'asset_tag' => 'BROKEN-ASSET-'.$tagSuffix,
            'serial' => 'BROKEN-SERIAL-'.$tagSuffix,
        ]);

    DB::table('assets')->where('id', $invalidAsset->id)->update([
        'assigned_to' => User::factory()->create()->id,
        'assigned_type' => null,
    ]);
    $invalidAsset->refresh();

    return [$validAsset, $invalidAsset];
}
