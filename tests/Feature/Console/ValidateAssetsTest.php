<?php

namespace Tests\Feature\Console;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class ValidateAssetsTest extends TestCase
{
    public function test_it_only_outputs_invalid_assets_by_default(): void
    {
        [$validAsset, $invalidAsset] = $this->seedValidAndInvalidAssets();

        Artisan::call('snipeit:validate-assets');
        $output = Artisan::output();

        $this->assertStringContainsString('Run this command with the --all option to see the full list in the console.', $output);
        $this->assertStringContainsString($invalidAsset->asset_tag, $output);
        $this->assertStringContainsString($invalidAsset->serial, $output);
        $this->assertStringNotContainsString($validAsset->asset_tag, $output);
        $this->assertStringNotContainsString($validAsset->serial, $output);
        $this->assertStringNotContainsString('MessageBag', $output);
        $this->assertStringContainsString('assigned type', strtolower($output));
    }

    public function test_it_outputs_all_assets_when_all_option_is_passed(): void
    {
        [$validAsset, $invalidAsset] = $this->seedValidAndInvalidAssets();

        Artisan::call('snipeit:validate-assets', ['--all' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString($invalidAsset->asset_tag, $output);
        $this->assertStringContainsString($invalidAsset->serial, $output);
        $this->assertStringContainsString($validAsset->asset_tag, $output);
        $this->assertStringContainsString($validAsset->serial, $output);
    }

    /**
     * @return array{0: Asset, 1: Asset}
     */
    private function seedValidAndInvalidAssets(): array
    {
        $tagSuffix = (string) Str::uuid();

        $validAsset = Asset::factory()->create([
            'asset_tag' => 'GOOD-ASSET-'.$tagSuffix,
            'serial' => 'GOOD-SERIAL-'.$tagSuffix,
        ]);

        $invalidAsset = Asset::factory()
            ->canBeInvalidUponCreation()
            ->create([
                'asset_tag' => 'BROKEN-ASSET-'.$tagSuffix,
                'serial' => 'BROKEN-SERIAL-'.$tagSuffix,
                'assigned_to' => User::factory()->create()->id,
                'assigned_type' => null,
            ]);

        return [$validAsset, $invalidAsset];
    }
}
