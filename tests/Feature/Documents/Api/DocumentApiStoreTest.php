<?php

namespace Tests\Feature\Documents\Api;

use App\Models\Asset;
use App\Models\User;
use App\Services\Documents\TemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApiStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_api_can_generate_a_document()
    {
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'API Checkout '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => 'api body'], $admin);
        $templates->publishVersion($template, [], $admin);

        $employee = User::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAsForApi(User::factory()->create(['permissions' => '{"documents.create":"1","users.view":"1","assets.view":"1"}']))
            ->postJson(route('api.documents.store'), [
                'document_template_id' => $template->id,
                'assigned_to_id' => $employee->id,
                'asset_ids' => [$asset->id],
                'notes' => 'API handover notes',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('payload.assigned_to.id', $employee->id);

        $this->assertDatabaseHas('documents', ['assigned_to_id' => $employee->id, 'notes' => 'API handover notes']);
    }

    public function test_api_rejects_generation_without_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.documents.store'), [
                'document_template_id' => 1,
                'assigned_to_id' => 1,
                'asset_ids' => [1],
            ])
            ->assertForbidden();
    }

    public function test_api_rejects_unpublished_template()
    {
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'Unpub '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => 'x'], $admin);
        // no publish

        $this->actingAsForApi(User::factory()->create(['permissions' => '{"documents.create":"1"}']))
            ->postJson(route('api.documents.store'), [
                'document_template_id' => $template->id,
                'assigned_to_id' => User::factory()->create()->id,
                'asset_ids' => [Asset::factory()->create()->id],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'error');
    }

    public function test_asset_documents_endpoint_lists_covering_documents()
    {
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'Asset docs '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => 'x'], $admin);
        $version = $templates->publishVersion($template, [], $admin);

        $employee = User::factory()->create();
        $asset = Asset::factory()->create();

        app(\App\Services\Documents\DocumentService::class)->generate($version, $employee, collect([$asset]), [], $admin);

        $this->actingAsForApi(User::factory()->create(['permissions' => '{"documents.view":"1"}']))
            ->getJson(route('api.asset.documents', $asset))
            ->assertOk()
            ->assertJsonPath('total', 1);
    }
}
