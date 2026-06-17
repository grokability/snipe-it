<?php

namespace Tests\Unit\Presenters;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\User;
use Tests\TestCase;

/**
 * Cubre los metodos compartidos de la clase base App\Presenters\Presenter
 * (displayAddress, categoryUrl, locationUrl, companyUrl, manufacturerUrl).
 */
class PresenterBaseTest extends TestCase
{
    public function test_display_address_concatenates_address_fields(): void
    {
        $location = Location::factory()->create([
            'address' => '123 Main St',
            'address2' => 'Suite 4',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62704',
            'country' => 'US',
        ]);

        $address = $location->present()->displayAddress();

        $this->assertStringContainsString('123 Main St', $address);
        $this->assertStringContainsString('Springfield', $address);
        $this->assertStringContainsString('62704', $address);
    }

    public function test_location_company_manufacturer_category_urls_for_asset(): void
    {
        $this->actingAs(User::factory()->superuser()->create());

        $manufacturer = Manufacturer::factory()->create();
        $model = AssetModel::factory()->for($manufacturer)->create();
        $location = Location::factory()->create();
        $company = Company::factory()->create();

        $asset = Asset::factory()
            ->for($model, 'model')
            ->create([
                'location_id' => $location->id,
                'company_id' => $company->id,
            ]);

        $this->assertIsString($asset->present()->locationUrl());
        $this->assertIsString($asset->present()->companyUrl());
        $this->assertStringContainsString('<a href=', $asset->present()->manufacturerUrl());
        $this->assertStringContainsString('<a href=', $asset->present()->categoryUrl());
    }

    public function test_urls_return_empty_string_when_relations_missing(): void
    {
        $this->actingAs(User::factory()->superuser()->create());

        $model = AssetModel::factory()->create();
        $asset = Asset::factory()->for($model, 'model')->create([
            'location_id' => null,
            'company_id' => null,
        ]);

        $this->assertSame('', $asset->present()->locationUrl());
        $this->assertSame('', $asset->present()->companyUrl());
    }
}
