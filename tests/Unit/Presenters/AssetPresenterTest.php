<?php

namespace Tests\Unit\Presenters;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\User;
use Tests\TestCase;

class AssetPresenterTest extends TestCase
{
    private function presentableAsset(array $attributes = [], array $modelAttributes = []): Asset
    {
        $model = AssetModel::factory()->create(array_merge([
            'name' => 'Macbook Pro',
            'model_number' => 'MBP-16',
            'eol' => 12,
        ], $modelAttributes));

        return Asset::factory()
            ->for($model, 'model')
            ->create(array_merge([
                'purchase_date' => '2020-01-01',
                'warranty_months' => 24,
            ], $attributes));
    }

    public function test_name_url_renders_link_for_authorized_user(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $html = $asset->present()->nameUrl();

        $this->assertStringContainsString('<a href=', $html);
        $this->assertStringContainsString('hardware/'.$asset->id, $html);
    }

    public function test_formatted_name_link_returns_anchor(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $this->assertStringContainsString('<a href=', $asset->present()->formattedNameLink());
    }

    public function test_view_url_points_to_hardware_show(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $this->assertEquals(route('hardware.show', $asset->id), $asset->present()->viewUrl());
    }

    public function test_model_url_delegates_to_model_presenter(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $this->assertIsString($asset->present()->modelUrl());
    }

    public function test_full_name_and_name_are_strings(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $this->assertIsString($asset->present()->fullName());
        $this->assertIsString($asset->present()->name());
    }

    public function test_eol_date_is_purchase_date_plus_model_eol_months(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset(['purchase_date' => '2020-01-01'], ['eol' => 12]);

        $this->assertEquals('2021-01-01', $asset->present()->eol_date());
    }

    public function test_warranty_expires_is_purchase_date_plus_warranty_months(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset(['purchase_date' => '2020-01-01', 'warranty_months' => 24]);

        $this->assertEquals('2022-01-01', $asset->present()->warranty_expires());
    }

    public function test_warranty_expires_returns_false_without_warranty(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset(['warranty_months' => null]);

        $this->assertFalse($asset->present()->warranty_expires());
    }

    public function test_status_text_returns_status_name_when_not_assigned(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $status = Statuslabel::factory()->create(['name' => 'In Storage']);
        $asset = $this->presentableAsset(['status_id' => $status->id, 'assigned_to' => null]);

        $this->assertEquals('In Storage', $asset->present()->statusText());
        $this->assertEquals('In Storage', $asset->present()->fullStatusText());
    }

    public function test_status_meta_returns_label_type_when_not_assigned(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset(['assigned_to' => null]);

        $this->assertIsString($asset->present()->statusMeta());
    }

    public function test_image_url_and_src_are_empty_without_image(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset([], ['image' => null]);

        $this->assertSame('', $asset->present()->imageUrl());
        $this->assertSame('', $asset->present()->imageSrc());
    }

    public function test_image_url_renders_img_tag_with_asset_image(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset(['image' => 'asset.png']);

        $this->assertStringContainsString('<img', $asset->present()->imageUrl());
        $this->assertStringContainsString('asset.png', $asset->present()->imageSrc());
    }

    public function test_glyph_returns_icon_markup(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $asset = $this->presentableAsset();

        $this->assertStringContainsString('icon', $asset->present()->glyph());
    }

    public function test_dynamic_url()
    {
        $this->settings->set(['locale' => 'en-US']);

        $assetModel = AssetModel::factory()->create([
            'model_number' => 'MN-123',
            'name' => 'Macbook',
        ]);

        $asset = Asset::factory()
            ->for($assetModel, 'model')
            ->create(['serial' => 'SN-123']);

        $this->assertEquals(
            'https://example.com/en-US/SN-123/MN-123/Macbook',
            $asset->present()->dynamicUrl('https://example.com/{LOCALE}/{SERIAL}/{MODEL_NUMBER}/{MODEL_NAME}')
        );
    }
}
