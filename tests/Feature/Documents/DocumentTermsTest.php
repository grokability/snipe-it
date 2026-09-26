<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVersion;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\TemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTermsTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->superuser()->create();
        $this->actingAs($this->admin);
    }

    private function input(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Equipment agreement', 'type' => 'handover',
            'eula_enabled' => '1', 'eula_title' => 'Care and return agreement',
            'eula_body' => "## Care\n\n- Keep equipment **secure**.\n- Report damage.\n\n| Item | Responsibility |\n| --- | --- |\n| Laptop | Return with charger |",
            'eula_version' => '1.0', 'eula_format' => 'markdown',
        ], $overrides);
    }

    public function test_eula_only_template_saves_without_body_and_publishes_saved_draft(): void
    {
        $this->post(route('documents.templates.store'), $this->input())->assertSessionHasNoErrors()->assertRedirect();
        $template = DocumentTemplate::sole();
        if ($directory = getenv('DOCUMENT_UI_ARTIFACTS')) {
            file_put_contents($directory.'/terms-edit.html', $this->get(route('documents.templates.edit', $template))->assertOk()->getContent());
            file_put_contents($directory.'/terms-show.html', $this->get(route('documents.templates.show', $template))->assertOk()->getContent());
            file_put_contents($directory.'/terms-preview.json', $this->postJson(route('documents.templates.markdown-preview'), ['text' => $this->input()['eula_body'], 'format' => 'markdown'])->assertOk()->getContent());
            file_put_contents($directory.'/terms.pdf', $this->get(route('documents.templates.preview', $template))->assertOk()->getContent());
        }
        $this->assertSame('', $template->body);
        $this->assertSame($this->input()['eula_body'], $template->eula_config['eula_body']);
        $this->assertDatabaseCount('document_template_versions', 0);
        $this->get(route('documents.templates.show', $template))->assertOk()->assertSee('<h2>Care</h2>', false);
        $this->get(route('documents.templates.preview', $template))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertDatabaseCount('document_template_versions', 0);
        $this->post(route('documents.templates.publish', $template))->assertSessionHasNoErrors()->assertRedirect();
        $version = DocumentTemplateVersion::sole();
        $this->assertTrue($version->eula_enabled);
        $this->assertSame('markdown', $version->snapshotField('eula_format'));
        $this->assertSame($this->input()['eula_body'], $version->eula_body);
        $document = app(DocumentService::class)->generate($version, $this->admin, collect([Asset::factory()->create()]), [], $this->admin);
        $this->get(route('documents.show', $document))->assertOk()->assertSee('<strong>secure</strong>', false)->assertSee('<table>', false);
        $this->assertStringStartsWith('%PDF-', Storage::get($document->pdf_path));
    }

    public function test_draft_edits_do_not_change_published_terms_or_existing_pdf(): void
    {
        $this->post(route('documents.templates.store'), $this->input())->assertSessionHasNoErrors();
        $template = DocumentTemplate::sole();
        $this->post(route('documents.templates.publish', $template))->assertSessionHasNoErrors();
        $first = DocumentTemplateVersion::sole();
        $document = app(DocumentService::class)->generate($first, $this->admin, collect([Asset::factory()->create()]), [], $this->admin);
        $bytes = Storage::get($document->pdf_path);
        $this->post(route('documents.templates.update', $template), $this->input(['eula_body' => '## Revised terms', 'eula_version' => '2.0']))->assertSessionHasNoErrors();
        $this->assertSame($this->input()['eula_body'], $first->fresh()->eula_body);
        $this->assertSame($bytes, Storage::get($document->fresh()->pdf_path));
        $this->post(route('documents.templates.publish', $template))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('document_template_versions', ['version' => 2, 'eula_body' => '## Revised terms', 'eula_version' => '2.0']);
    }

    public function test_enabled_terms_require_title_content_and_revision_but_introduction_is_optional(): void
    {
        $this->post(route('documents.templates.store'), $this->input(['eula_title' => '', 'eula_body' => '', 'eula_version' => '']))
            ->assertSessionHasErrors(['eula_title', 'eula_body', 'eula_version']);
        $this->assertDatabaseCount('document_templates', 0);
        $this->post(route('documents.templates.store'), $this->input([
            'eula_enabled' => '0', 'eula_title' => '', 'eula_body' => '', 'eula_version' => '',
        ]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('document_templates', 1);
    }

    public function test_markdown_preview_renders_supported_formatting_without_active_content(): void
    {
        $markdown = "## Care\n\n**Bold** and *italic*.\n\n1. Return equipment\n\n> Notice\n\n<script>alert(1)</script>\n\n![Image](https://example.com/pixel.png)\n\n[Link](javascript:alert(1))";
        $response = $this->postJson(route('documents.templates.markdown-preview'), ['text' => $markdown, 'format' => 'markdown'])->assertOk();
        $html = $response->json('html');
        foreach (['<h2>Care</h2>', '<strong>Bold</strong>', '<em>italic</em>', '<ol>', '<blockquote>'] as $fragment) {
            $this->assertStringContainsString($fragment, $html);
        }
        foreach (['<script', '<img', 'href=', 'src=', '<iframe', '<tcpdf'] as $fragment) {
            $this->assertStringNotContainsString($fragment, $html);
        }
        $this->assertDatabaseCount('document_templates', 0);
    }

    public function test_markdown_preview_requires_template_editor_permission_and_validates_size(): void
    {
        $this->postJson(route('documents.templates.markdown-preview'), ['text' => str_repeat('x', 60001), 'format' => 'markdown'])->assertOk()->assertJsonPath('status', 'error')->assertJsonStructure(['messages' => ['text']]);
        $this->actingAs(User::factory()->create())->postJson(route('documents.templates.markdown-preview'), ['text' => 'x', 'format' => 'markdown'])->assertForbidden();
        $this->actingAs(User::factory()->create(['permissions' => '{"document_templates.create":"1"}']))
            ->postJson(route('documents.templates.markdown-preview'), ['text' => '**Care**', 'format' => 'markdown'])
            ->assertOk()->assertJsonPath('html', '<p><strong>Care</strong></p>');
    }

    public function test_legacy_published_terms_remain_plain_text_and_populate_the_editor(): void
    {
        $service = app(TemplateService::class);
        $template = $service->create(['name' => 'Legacy', 'type' => 'handover', 'body' => '<p>Existing cover note.</p>'], $this->admin);
        $version = DocumentTemplateVersion::create([
            'document_template_id' => $template->id, 'version' => 1,
            'snapshot' => ['name' => 'Legacy', 'type' => 'handover', 'body' => '<p>Existing cover note.</p>'],
            'eula_enabled' => true, 'eula_title' => 'Legacy terms', 'eula_body' => "**Literal asterisks**\nSecond line", 'eula_version' => 'old',
        ]);
        $terms = $service->terms($template);
        $this->assertSame('plain', $terms['eula_format']);
        $this->assertSame($version->eula_body, $terms['eula_body']);
        $this->assertSame("**Literal asterisks**<br />\nSecond line", $service->renderTerms($version->eula_body, $version->snapshotField('eula_format', 'plain')));
        $this->get(route('documents.templates.edit', $template))->assertOk()->assertSee('**Literal asterisks**')->assertSee('Existing cover note.');
        $this->assertNull($template->fresh()->eula_config);
    }
}
