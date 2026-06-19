<?php

namespace Tests\Unit\Presenters;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\User;
use App\Presenters\DepreciationReportPresenter;
use Tests\TestCase;

/**
 * Cubre los metodos accesores de DepreciationReportPresenter (antes ~70%):
 * urls, imagenes, nombres, eol/warranty y los textos de status.
 */
class DepreciationReportPresenterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    private function presenterFor(Asset $asset): DepreciationReportPresenter
    {
        return new DepreciationReportPresenter($asset);
    }

    public function test_urls_and_names(): void
    {
        $model = AssetModel::factory()->create(['eol' => 24, 'image' => 'model.png']);
        $asset = Asset::factory()->for($model, 'model')->create([
            'image' => 'asset.png',
            'purchase_date' => '2024-01-01',
            'warranty_months' => 12,
        ]);
        $p = $this->presenterFor($asset);

        $this->assertStringContainsString('<a href', $p->nameUrl());
        $this->assertIsString($p->modelUrl());
        $this->assertStringContainsString('<img', $p->imageUrl());
        $this->assertIsString($p->imageSrc());
        $this->assertIsString($p->name());
        $this->assertIsString($p->fullName());
        $this->assertStringContainsString('hardware', $p->viewUrl());
        $this->assertStringContainsString('icon', $p->glyph());
    }

    public function test_name_url_for_non_authorized_user(): void
    {
        $asset = Asset::factory()->create();
        // Usuario sin permisos -> rama else (texto plano sin enlace).
        $this->actingAs(User::factory()->create());

        $result = $this->presenterFor($asset)->nameUrl();

        $this->assertStringNotContainsString('<a href', $result);
    }

    public function test_eol_and_warranty_dates(): void
    {
        $model = AssetModel::factory()->create(['eol' => 24]);
        $asset = Asset::factory()->for($model, 'model')->create([
            'purchase_date' => '2024-01-01',
            'warranty_months' => 12,
        ]);
        $p = $this->presenterFor($asset);

        $this->assertSame('2026-01-01', $p->eol_date());
        $this->assertSame('2025-01-01', $p->warranty_expires());
        // months_until_eol devuelve DateInterval o null segun fecha.
        $eol = $p->months_until_eol();
        $this->assertTrue($eol === null || $eol instanceof \DateInterval);
    }

    public function test_warranty_false_without_data(): void
    {
        $asset = Asset::factory()->create(['purchase_date' => null, 'warranty_months' => null]);

        $this->assertFalse($this->presenterFor($asset)->warranty_expires());
    }

    public function test_status_texts_when_unassigned(): void
    {
        $asset = Asset::factory()->create();
        $p = $this->presenterFor($asset);

        $this->assertIsString($p->statusMeta());
        $this->assertIsString($p->statusText());
        $this->assertIsString($p->fullStatusText());
    }

    public function test_status_texts_when_assigned(): void
    {
        $asset = Asset::factory()->assignedToUser()->create();
        $asset->load('assignedTo');
        $p = $this->presenterFor($asset);

        $this->assertIsString($p->statusMeta());
        $this->assertIsString($p->statusText());
        $this->assertIsString($p->fullStatusText());
    }
}
