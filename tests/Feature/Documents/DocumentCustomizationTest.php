<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Services\Documents\DocumentFonts;
use App\Services\Documents\DocumentLayout;
use App\Services\Documents\DocumentService;
use App\Services\Documents\TemplateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentCustomizationTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->superuser()->create();
        $this->actingAs($this->admin);
    }

    private function input(array $extra = []): array
    {
        return array_replace_recursive([
            'name' => 'Custom document', 'type' => 'handover',
            'eula_enabled' => 1, 'eula_title' => 'Agreement for {{user.name}}',
            'eula_body' => "## Responsibility\n\n**{{user.name}}** receives the equipment on {{document.date_formatted}}.\n\n{{#assets}}\n- {{asset.asset_tag}}: {{asset.name}}\n{{/assets}}",
            'eula_version' => '1', 'eula_format' => 'markdown',
            'header_config' => ['title' => 'Issued to {{user.name}}', 'date_calendar' => 'jalali', 'date_format' => 'd/m/Y', 'font_family' => 'dejavuserif', 'font_size' => 12, 'show_company' => 0, 'show_logo' => 0, 'show_provenance' => 0, 'left_text' => 'Ref: {{document.number}}', 'terms_position' => 'before_assets'],
            'footer_config' => ['text' => 'Return to {{company.name}}', 'show_pages' => 0, 'font_size' => 8, 'alignment' => 'center'],
            'signature_roles' => ['employee', 'manager'],
            'signature_config' => ['labels' => ['employee' => 'Equipment recipient', 'manager' => 'Department approver'], 'columns' => 1, 'show_date' => 0, 'show_name' => 1, 'show_line' => 0, 'space' => 15, 'heading' => 'Acknowledgement', 'instructions' => 'Please sign, {{user.name}}.'],
        ], $extra);
    }

    public function test_customizations_survive_save_publish_preview_and_browser_print(): void
    {
        $this->post(route('documents.templates.store'), $this->input())->assertSessionHasNoErrors();
        $template = DocumentTemplate::sole();
        $this->assertSame('Department approver', $template->signature_config['labels']['manager']);
        $this->assertSame('jalali', $template->header_config['date_calendar']);
        $edit = $this->get(route('documents.templates.edit', $template))->assertOk()->assertSee('Placeholder guide')->assertSee('signature_config[labels][employee]', false);
        $preview = $this->get(route('documents.templates.preview', ['template' => $template, 'web' => 1]))->assertOk()
            ->assertSee('Sample employee')->assertDontSee('{{user.name}}')->assertSee('Equipment recipient');
        $this->get(route('documents.templates.preview', $template))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $version = app(TemplateService::class)->publishVersion($template, [], $this->admin);
        $employee = User::factory()->create(['first_name' => 'Mary', 'last_name' => 'Example']);
        $document = app(DocumentService::class)->generate($version, $employee, collect([
            Asset::factory()->create(['name' => 'Laptop One', 'asset_tag' => 'TAG-ONE']),
            Asset::factory()->create(['name' => 'Monitor Two', 'asset_tag' => 'TAG-TWO']),
        ]), [], $this->admin);
        $layout = app(DocumentLayout::class);
        $html = $layout->body($document);
        $this->assertStringContainsString('<strong>Mary Example</strong>', $html);
        $this->assertStringContainsString('TAG-ONE: Laptop One', $html);
        $this->assertStringContainsString('TAG-TWO: Monitor Two', $html);
        $this->assertStringNotContainsString('{{', $html);
        $this->assertStringNotContainsString('________________', $layout->signatures($document));
        $this->assertStringNotContainsString('Date:', $layout->signatures($document));
        $this->assertStringContainsString('width="100%"', $layout->signatures($document));
        $web = $this->get(route('documents.web-print', $document))->assertOk()->assertSee('Mary Example')->assertSee('Print from browser');
        $this->get(route('documents.show', $document))->assertOk()->assertSee('Equipment recipient')->assertSee('<strong>Mary Example</strong>', false);
        $savedPdf = Storage::get($document->pdf_path);
        $this->post(route('documents.templates.update', $template), $this->input(['header_config' => ['font_size' => 20], 'signature_config' => ['labels' => ['employee' => 'Changed label']]]))->assertSessionHasNoErrors();
        $this->assertSame(12, $document->fresh()->templateVersion->snapshot['header_config']['font_size']);
        $this->get(route('documents.web-print', $document))->assertSee('Equipment recipient')->assertDontSee('Changed label');
        $this->assertSame($savedPdf, Storage::get($document->pdf_path));
        $this->actingAs(User::factory()->create())->get(route('documents.web-print', $document))->assertForbidden();
        $this->get(route('documents.templates.preview', ['template' => $template, 'web' => 1]))->assertForbidden();
        $this->actingAs($employee)->get(route('documents.web-print', $document))->assertOk();
        if ($directory = getenv('DOCUMENT_UI_ARTIFACTS')) {
            file_put_contents($directory.'/custom-edit.html', $edit->getContent());
            file_put_contents($directory.'/custom-print.html', $web->getContent());
            file_put_contents($directory.'/custom-preview.html', $preview->getContent());
            file_put_contents($directory.'/custom.pdf', $savedPdf);
        }
    }

    public function test_date_calendar_and_format_are_explicit_and_placeholder_values_cannot_inject_markup(): void
    {
        $layout = app(DocumentLayout::class);
        $this->assertSame('04/07/1405', $layout->date('2026-09-26', ['date_calendar' => 'jalali', 'date_format' => 'd/m/Y']));
        $this->assertSame('09/26/2026', $layout->date('2026-09-26', ['date_calendar' => 'gregorian', 'date_format' => 'm/d/Y']));
        $this->assertSame('2026-09-26 / 1405-07-04', $layout->date('2026-09-26', ['date_calendar' => 'both']));
        $html = app(TemplateService::class)->renderTerms('**{{user.name}}**', 'markdown', ['user.name' => '<script>alert(1)</script> **Admin** {{company.name}}']);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('**Admin**', $html);
        $this->assertStringContainsString('{{company.name}}', $html);
        $this->postJson(route('documents.templates.markdown-preview'), ['text' => '**{{user.name}}**', 'format' => 'markdown'])->assertOk()->assertJsonPath('html', '<p><strong>'.trans('documents.general.sample_employee').'</strong></p>');
    }

    public function test_invalid_layout_values_and_font_uploads_are_rejected(): void
    {
        $this->post(route('documents.templates.store'), $this->input([
            'header_config' => ['font_size' => 200, 'font_family' => '../../evil', 'date_calendar' => 'unknown', 'margin_top' => -1],
            'signature_config' => ['columns' => 0],
        ]))->assertSessionHasErrors(['header_config.font_size', 'header_config.font_family', 'header_config.date_calendar', 'header_config.margin_top', 'signature_config.columns']);
        $this->post(route('documents.templates.store'), $this->input(['header_config' => ['font_family' => 'custom']]))->assertSessionHasErrors('custom_font');
        $this->post(route('documents.templates.store'), $this->input(['custom_font' => UploadedFile::fake()->createWithContent('bad.ttf', '<?php die();')]))->assertSessionHasErrors('custom_font');
        $this->assertDatabaseCount('document_templates', 0);
    }

    public function test_sections_can_be_hidden_and_empty_role_selection_is_rejected(): void
    {
        $this->post(route('documents.templates.store'), $this->input([
            'header_config' => ['show_header' => 0, 'show_title' => 0, 'show_assignee' => 0, 'show_assets' => 0, 'show_notes' => 0, 'show_revision' => 0],
            'footer_config' => ['show_footer' => 0],
        ]))->assertSessionHasNoErrors();
        $template = DocumentTemplate::sole();
        $this->get(route('documents.templates.preview', ['template' => $template, 'web' => 1]))->assertOk()
            ->assertDontSee('Ref: PREVIEW')->assertDontSee('Issued to Sample employee')
            ->assertDontSee('Employee No.')->assertDontSee('Return to Example Company')
            ->assertSee('Agreement for Sample employee');
        $this->post(route('documents.templates.update', $template), array_merge($this->input(), [
            'layout_submitted' => 1, 'signature_roles' => [], 'asset_columns' => [],
        ]))->assertSessionHasErrors(['signature_roles', 'asset_columns']);
        $this->postJson(route('documents.templates.markdown-preview'), [
            'text' => '{{document.date_formatted}}', 'format' => 'plain',
            'header_config' => ['date_calendar' => 'jalali', 'date_format' => 'd/m/Y'],
        ])->assertOk()->assertJsonPath('html', \App\Support\Jalali\Jalali::format(now(), 'd/m/Y'));
    }

    public function test_uploaded_font_is_embedded_and_preserved_for_published_versions(): void
    {
        $font = UploadedFile::fake()->createWithContent('Company.ttf', gzuncompress(file_get_contents(base_path('vendor/tecnickcom/tcpdf/fonts/dejavusans.z'))));
        $this->post(route('documents.templates.store'), $this->input(['header_config' => ['font_family' => 'custom'], 'custom_font' => $font]))->assertSessionHasNoErrors();
        $template = DocumentTemplate::sole();
        $directory = app(DocumentFonts::class)->directory($template->header_config);
        $this->assertNotNull($directory);
        $version = app(TemplateService::class)->publishVersion($template, [], $this->admin);
        $fontPdf = $this->get(route('documents.templates.preview', $template))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('/FontFile2', $fontPdf->getContent());
        $web = $this->get(route('documents.templates.preview', ['template' => $template, 'web' => 1]))->assertOk()->assertSee('data:font/ttf;base64,', false);
        $this->post(route('documents.templates.update', $template), $this->input(['header_config' => ['font_family' => 'dejavusans', 'custom_font' => ['directory' => '/etc', 'name' => 'evil']]]))->assertSessionHasNoErrors();
        $this->assertSame($directory, $template->fresh()->header_config['custom_font']['directory']);
        $this->assertSame($directory, $version->fresh()->snapshot['header_config']['custom_font']['directory']);
        if ($output = getenv('DOCUMENT_UI_ARTIFACTS')) {
            file_put_contents($output.'/custom-font-print.html', $web->getContent());
        }
    }
}
