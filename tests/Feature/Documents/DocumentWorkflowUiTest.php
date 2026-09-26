<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\PdfService;
use App\Services\Documents\SignatureService;
use App\Services\Documents\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentWorkflowUiTest extends TestCase
{
    private User $admin;

    private User $employee;

    private DocumentTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->superuser()->create();
        $this->employee = User::factory()->create();
        $this->template = app(TemplateService::class)->create([
            'name' => 'Employee handover', 'type' => 'handover',
            'body' => '<p>Equipment issued to {{user.name}}</p>',
            'signature_config' => ['roles' => ['employee', 'it_representative', 'manager', 'custom']],
        ], $this->admin);
    }

    private function document(): Document
    {
        $version = app(TemplateService::class)->publishVersion($this->template, [
            'eula_enabled' => true, 'eula_title' => 'Equipment terms',
            'eula_body' => 'Please return all equipment.', 'eula_version' => '1',
        ], $this->admin);

        return app(DocumentService::class)->generate($version, $this->employee,
            collect([Asset::factory()->assignedToUser($this->employee)->create()]), [], $this->admin);
    }

    public function test_creation_handles_missing_templates_and_offers_assigned_hardware(): void
    {
        $this->actingAs($this->admin)->get(route('documents.create'))->assertOk()
            ->assertSee(trans('documents.general.template_setup_help'))
            ->assertDontSee('name="asset_ids[]"', false);
        app(TemplateService::class)->publishVersion($this->template, [], $this->admin);
        $this->get(route('documents.create'))->assertOk()
            ->assertSee(trans('documents.general.all_assigned'))->assertSee(route('documents.print'));
        $this->actingAs($this->employee)->get(route('documents.create'))->assertForbidden();
    }

    public function test_manual_creation_preserves_notes_and_rejects_inactive_templates(): void
    {
        app(TemplateService::class)->publishVersion($this->template, [], $this->admin);
        $asset = Asset::factory()->assignedToUser($this->employee)->create();
        $input = ['document_template_id' => $this->template->id, 'assigned_to_id' => $this->employee->id,
            'asset_ids' => [$asset->id], 'notes' => 'Include charger and bag.'];
        $this->actingAs($this->admin)->post(route('documents.store'), $input)->assertRedirect();
        $document = Document::sole();
        $this->assertSame($input['notes'], $document->notes);
        $this->get(route('documents.show', $document))->assertOk()->assertSee($input['notes']);
        $this->template->update(['active' => false]);
        $this->post(route('documents.store'), $input)->assertSessionHas('error');
        $this->assertDatabaseCount('documents', 1);
    }

    public function test_template_preview_is_authorized_and_does_not_create_records(): void
    {
        $this->actingAs($this->employee)->get(route('documents.templates.preview', $this->template))->assertForbidden();
        $response = $this->actingAs($this->admin)->get(route('documents.templates.preview', $this->template))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_template_versions', 0);
        $this->assertSame([], Storage::allFiles());
    }

    public function test_template_editor_exposes_layout_and_create_only_submit(): void
    {
        $creator = User::factory()->create(['permissions' => '{"document_templates.create":"1"}']);
        $this->actingAs($creator)->get(route('documents.templates.create'))->assertOk()
            ->assertSee('type="submit"', false)->assertSee('name="asset_columns[]"', false)
            ->assertSee('name="footer_config[text]"', false);
    }

    public function test_publishing_preserves_terms_and_validates_version_length(): void
    {
        $this->document();
        $this->actingAs($this->admin)->get(route('documents.templates.show', $this->template))->assertOk()
            ->assertSee('Please return all equipment.')->assertSee(route('documents.templates.preview', $this->template));
        $this->post(route('documents.templates.publish', $this->template), ['eula_version' => str_repeat('x', 41)])
            ->assertSessionHasErrors('eula_version');
    }

    public function test_template_deletion_handles_unused_and_referenced_templates(): void
    {
        $this->actingAs($this->admin)->delete(route('documents.templates.destroy', $this->template))->assertRedirect();
        $this->assertSoftDeleted('document_templates', ['id' => $this->template->id]);
        $this->template->restore();
        $this->document();
        $this->delete(route('documents.templates.destroy', $this->template))->assertSessionHas('error');
        $this->assertFalse($this->template->fresh()->trashed());
    }

    public function test_employee_cannot_sign_it_role_and_can_see_frozen_terms(): void
    {
        $document = $this->document();
        $this->actingAs($this->employee)->get(route('documents.show', $document))->assertOk()
            ->assertSee('Please return all equipment.')
            ->assertSee('value="employee"', false)->assertDontSee('value="it_representative"', false);
        $this->post(route('documents.sign', $document), ['method' => 'printed', 'role' => 'it_representative'])
            ->assertForbidden();
        $this->assertDatabaseCount('document_signatures', 0);
    }

    public function test_digital_signing_requires_terms_acceptance(): void
    {
        $document = $this->document();
        $this->actingAs($this->employee)->post(route('documents.sign', $document), [
            'method' => 'digital', 'role' => 'employee', 'signature' => 'missing image',
        ])->assertSessionHasErrors('agree_terms');
        $this->assertDatabaseCount('document_signatures', 0);
    }

    public function test_four_signers_complete_and_final_records_are_immutable(): void
    {
        $document = $this->document();
        $initialPath = $document->pdf_path;
        $initialBytes = Storage::get($initialPath);
        foreach (['employee', 'it_representative', 'manager', 'custom'] as $role) {
            app(SignatureService::class)->recordPrinted($document, $role,
                $role === 'employee' ? $this->employee : $this->admin, Request::create('/'));
        }
        $document->refresh();
        $this->assertSame('signed', $document->status);
        $this->assertCount(4, $document->signatures);
        $this->assertSame($initialBytes, Storage::get($initialPath));
        $this->assertSame(hash('sha256', Storage::get($document->pdf_path)), $document->pdf_sha256);
        $document->notes = 'Changed';
        $this->assertFalse($document->save());
        $this->assertFalse($document->delete());
        $item = $document->items->first();
        $item->name = 'Changed';
        $this->assertFalse($item->save());
        $signature = $document->signatures->first();
        $signature->signer_name = 'Changed';
        $this->assertFalse($signature->save());
        $this->assertDatabaseHas('action_logs', ['item_type' => Document::class,
            'item_id' => $document->id, 'action_type' => 'document signed', 'created_by' => $this->admin->id]);
    }

    public function test_final_pdf_failure_rolls_back_signature_and_status(): void
    {
        $document = $this->document();
        foreach (['employee', 'it_representative', 'manager'] as $role) {
            app(SignatureService::class)->recordPrinted($document, $role, $this->admin, Request::create('/'));
        }
        $document->refresh();
        $beforeHash = $document->pdf_sha256;
        $pdf = \Mockery::mock(PdfService::class);
        $pdf->shouldReceive('generate')->once()->andThrow(new \RuntimeException('PDF failed'));
        $this->app->instance(DocumentService::class, new DocumentService(pdfs: $pdf));
        try {
            app(SignatureService::class)->recordPrinted($document, 'custom', $this->admin, Request::create('/'));
            $this->fail('Expected a PDF failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('PDF failed', $e->getMessage());
        }
        $document->refresh();
        $this->assertSame('partially_signed', $document->status);
        $this->assertNull($document->signed_at);
        $this->assertCount(3, $document->signatures);
        $this->assertSame($beforeHash, hash('sha256', Storage::get($document->pdf_path)));
    }

    public function test_template_html_keeps_formatting_but_removes_external_resources(): void
    {
        $html = app(TemplateService::class)->sanitizeHtml('<p style="background:url(http://invalid)">Hello <b>{{user.name}}</b></p><img src="http://invalid"><script>alert(1)</script><tcpdf method="Image" params="bad"></tcpdf>');
        $this->assertSame('<p>Hello <b>{{user.name}}</b></p>', $html);
    }

    public function test_document_pages_render_for_browser_review(): void
    {
        $document = $this->document();
        $this->actingAs($this->admin);
        $pages = [
            'create' => route('documents.create'),
            'index' => route('documents.index'),
            'template-edit' => route('documents.templates.edit', $this->template),
            'template-show' => route('documents.templates.show', $this->template),
            'show' => route('documents.show', $document),
        ];
        foreach ($pages as $name => $url) {
            $response = $this->get($url)->assertOk();
            if ($directory = getenv('DOCUMENT_UI_ARTIFACTS')) {
                file_put_contents($directory.'/'.$name.'.html', $response->getContent());
            }
        }
        if ($directory = getenv('DOCUMENT_UI_ARTIFACTS')) {
            file_put_contents($directory.'/document.pdf', Storage::get($document->pdf_path));
        }
    }

    public function test_history_filters_and_audit_actor(): void
    {
        $document = $this->document();
        $this->actingAs($this->admin)->get(route('documents.index', ['search' => $document->number, 'status' => 'generated']))
            ->assertOk()->assertSee($document->number);
        $this->get(route('documents.index', ['status' => 'signed']))->assertOk()
            ->assertDontSee($document->number)->assertSee(trans('documents.general.no_matches'));
        $this->get(route('documents.show', $document))->assertOk()->assertSee($this->admin->present()->fullName);
    }

    public function test_generation_requires_access_to_source_records(): void
    {
        app(TemplateService::class)->publishVersion($this->template, [], $this->admin);
        $actor = User::factory()->create(['permissions' => '{"documents.create":"1"}']);
        $input = ['document_template_id' => $this->template->id, 'assigned_to_id' => $this->employee->id,
            'asset_ids' => [Asset::factory()->create()->id]];
        $this->actingAs($actor)->post(route('documents.store'), $input)->assertForbidden();
        $this->actingAsForApi($actor)->postJson(route('api.documents.store'), $input)->assertForbidden();
        $this->assertDatabaseCount('documents', 0);
    }
}
