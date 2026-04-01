<?php

namespace Tests\Unit\Services;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Location;
use App\Models\Printable;
use App\Models\Statuslabel;
use App\Models\User;
use App\Services\PrintableService;
use Tests\TestCase;

class PrintableServiceTest extends TestCase
{
    private PrintableService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PrintableService;
    }

    public function test_renders_asset_tag_variable(): void
    {
        $asset     = Asset::factory()->create(['asset_tag' => 'ASSET-001']);
        $printable = new Printable(['name' => 'Test', 'content' => 'Tag: {asset_tag}']);

        $result = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Tag: ASSET-001', $result);
    }

    public function test_renders_asset_name_variable(): void
    {
        $asset     = Asset::factory()->create(['name' => 'My Laptop']);
        $printable = new Printable(['name' => 'Test', 'content' => 'Name: {asset_name}']);

        $result = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Name: My Laptop', $result);
    }

    public function test_renders_serial_variable(): void
    {
        $asset     = Asset::factory()->create(['serial' => 'SN-XYZ-123']);
        $printable = new Printable(['name' => 'Test', 'content' => 'S/N: {serial}']);

        $result = $this->service->render($printable, $asset);

        $this->assertStringContainsString('S/N: SN-XYZ-123', $result);
    }

    public function test_renders_model_name_variable(): void
    {
        $model = AssetModel::factory()->create(['name' => 'ThinkPad X1']);
        $asset = Asset::factory()->create(['model_id' => $model->id]);

        $printable = new Printable(['name' => 'Test', 'content' => 'Model: {model_name}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Model: ThinkPad X1', $result);
    }

    public function test_renders_category_name_variable(): void
    {
        $category = Category::factory()->create(['category_type' => 'asset', 'name' => 'Laptops']);
        $model    = AssetModel::factory()->create(['category_id' => $category->id]);
        $asset    = Asset::factory()->create(['model_id' => $model->id]);

        $printable = new Printable(['name' => 'Test', 'content' => 'Category: {category_name}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Category: Laptops', $result);
    }

    public function test_renders_company_name_variable(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Corp']);
        $asset   = Asset::factory()->create(['company_id' => $company->id]);

        $printable = new Printable(['name' => 'Test', 'content' => 'Company: {company_name}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Company: Acme Corp', $result);
    }

    public function test_renders_location_name_variable(): void
    {
        $location = Location::factory()->create(['name' => 'Main Office']);
        $asset    = Asset::factory()->create(['location_id' => $location->id]);

        $printable = new Printable(['name' => 'Test', 'content' => 'Location: {location_name}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Location: Main Office', $result);
    }

    public function test_renders_assigned_to_variable(): void
    {
        $user  = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);
        $asset = Asset::factory()->assignedToUser($user)->create();

        $printable = new Printable(['name' => 'Test', 'content' => 'Assigned: {assigned_to}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Assigned: Jane Doe', $result);
    }

    public function test_renders_status_variable(): void
    {
        $status = Statuslabel::factory()->create(['name' => 'Ready to Deploy']);
        $asset  = Asset::factory()->create(['status_id' => $status->id]);

        $printable = new Printable(['name' => 'Test', 'content' => 'Status: {status}']);
        $result    = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Status: Ready to Deploy', $result);
    }

    public function test_renders_empty_string_for_null_relationships(): void
    {
        $asset     = Asset::factory()->create(['serial' => null]);
        $printable = new Printable(['name' => 'Test', 'content' => 'Serial: {serial}']);

        $result = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Serial: ', $result);
        $this->assertStringNotContainsString('{serial}', $result);
    }

    public function test_renders_multiple_variables(): void
    {
        $asset = Asset::factory()->create([
            'asset_tag' => 'TAG-999',
            'serial'    => 'SER-001',
        ]);

        $printable = new Printable([
            'name'    => 'Test',
            'content' => '<p>Tag: {asset_tag} | Serial: {serial}</p>',
        ]);

        $result = $this->service->render($printable, $asset);

        $this->assertStringContainsString('Tag: TAG-999', $result);
        $this->assertStringContainsString('Serial: SER-001', $result);
    }

    public function test_bulk_render_wraps_each_asset_in_a_div(): void
    {
        $assets = Asset::factory()->count(2)->create();

        $printable = new Printable([
            'name'    => 'Test',
            'content' => '<p>{asset_tag}</p>',
        ]);

        $result = $this->service->renderBulk($printable, $assets);

        $this->assertStringContainsString('class="printable-asset-page"', $result);
        $this->assertEquals(2, substr_count($result, 'printable-asset-page'));
    }

    public function test_available_variables_returns_at_least_the_core_variables(): void
    {
        $vars = PrintableService::availableVariables(collect());

        $this->assertArrayHasKey('{asset_tag}', $vars);
        $this->assertArrayHasKey('{model_name}', $vars);
        $this->assertArrayHasKey('{serial}', $vars);
        $this->assertArrayHasKey('{assigned_to}', $vars);
        $this->assertArrayHasKey('{company_name}', $vars);
    }
}
