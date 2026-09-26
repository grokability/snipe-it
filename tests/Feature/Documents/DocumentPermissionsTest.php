<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\TemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPermissionsTest extends TestCase
{
    private Document $document;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->employee = User::factory()->create(['first_name' => 'Plain', 'last_name' => 'Employee']);
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'Checkout '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => 'x'], $admin);
        $version = $templates->publishVersion($template, [], $admin);

        $this->document = app(DocumentService::class)->generate($version, $this->employee, collect([Asset::factory()->create()]), [], $admin);
    }

    public function test_documents_index_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('documents.index'))
            ->assertForbidden();
    }

    public function test_documents_index_renders_for_granted_user()
    {
        $this->actingAs(User::factory()->create(['permissions' => '{"documents.view":"1"}']))
            ->get(route('documents.index'))
            ->assertOk()
            ->assertViewIs('documents.index');
    }

    public function test_document_show_renders_for_granted_user()
    {
        $this->actingAs(User::factory()->create(['permissions' => '{"documents.view":"1"}']))
            ->get(route('documents.show', $this->document))
            ->assertOk()
            ->assertSee($this->document->number);
    }

    public function test_assignee_can_view_their_own_document_without_permission()
    {
        $this->actingAs($this->employee)
            ->get(route('documents.show', $this->document))
            ->assertOk();
    }

    public function test_document_show_is_forbidden_for_other_plain_users()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('documents.show', $this->document))
            ->assertForbidden();
    }

    public function test_templates_index_requires_permission()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('documents.templates.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['permissions' => '{"document_templates.view":"1"}']))
            ->get(route('documents.templates.index'))
            ->assertOk();
    }

    public function test_templates_create_is_gated_on_create_permission()
    {
        $this->actingAs(User::factory()->create(['permissions' => '{"document_templates.view":"1"}']))
            ->get(route('documents.templates.create'))
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['permissions' => '{"document_templates.create":"1"}']))
            ->get(route('documents.templates.create'))
            ->assertOk();
    }

    public function test_templates_show_and_edit_render(): void
    {
        $editor = User::factory()->create(['permissions' => '{"document_templates.view":"1","document_templates.edit":"1"}']);
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'Render smoke '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => '{{user.name}}'], $admin);

        $this->actingAs($editor)
            ->get(route('documents.templates.show', $template))
            ->assertOk()
            ->assertSee($template->name);

        $this->actingAs($editor)
            ->get(route('documents.templates.edit', $template))
            ->assertOk();
    }

    public function test_documents_create_page_renders(): void
    {
        $templates = app(TemplateService::class);
        $admin = User::factory()->superuser()->create();
        $template = $templates->create(['name' => 'Create smoke '.uniqid(), 'type' => 'checkout', 'language' => 'en', 'body' => 'x'], $admin);

        $this->actingAs(User::factory()->create(['permissions' => '{"documents.create":"1"}']))
            ->get(route('documents.create'))
            ->assertOk();
    }

    public function test_api_index_requires_documents_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.documents.index'))
            ->assertForbidden();

        $response = $this->actingAsForApi(User::factory()->create(['permissions' => '{"documents.view":"1"}']))
            ->getJson(route('api.documents.index'))
            ->assertOk()
            ->assertJsonStructure(['total', 'rows']);

        $this->assertGreaterThanOrEqual(1, $response->json('total'));
    }

    public function test_api_show_returns_transformed_document()
    {
        $this->actingAsForApi(User::factory()->create(['permissions' => '{"documents.view":"1"}']))
            ->getJson(route('api.documents.show', $this->document))
            ->assertOk()
            ->assertJsonPath('payload.number', $this->document->number)
            ->assertJsonPath('payload.status', 'generated');
    }

    public function test_pdf_download_is_gated_and_logged()
    {
        $viewer = User::factory()->create(['permissions' => '{"documents.view":"1"}']);

        $this->actingAs($viewer)
            ->get(route('documents.pdf', $this->document))
            ->assertForbidden();

        $downloader = User::factory()->create(['permissions' => '{"documents.download":"1"}']);
        $this->actingAs($downloader)
            ->get(route('documents.pdf', $this->document))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Document::class,
            'item_id' => $this->document->id,
            'created_by' => $downloader->id,
            'action_type' => 'document downloaded',
        ]);
    }
}
