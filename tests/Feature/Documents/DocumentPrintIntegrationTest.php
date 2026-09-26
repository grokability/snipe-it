<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentTemplateVersion;
use App\Models\ReportTemplate;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\TemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPrintIntegrationTest extends TestCase
{
    private User $admin;

    private User $employee;

    private DocumentTemplateVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->superuser()->create();
        $this->employee = User::factory()->create();
        $templates = app(TemplateService::class);
        $template = $templates->create([
            'name' => 'Inventory handover', 'type' => 'handover',
            'body' => '<p>{{user.name}} — {{document.date_jalali}} — {{checkout.notes}}</p>',
        ], $this->admin);
        $this->version = $templates->publishVersion($template, ['eula_enabled' => true, 'eula_body' => 'Sample terms', 'eula_version' => '1'], $this->admin);
    }

    private function printInput(array $overrides = []): array
    {
        return array_merge([
            'source_type' => 'user', 'source_id' => $this->employee->id,
            'document_template_version_id' => $this->version->id,
        ], $overrides);
    }

    public function test_existing_report_and_print_screens_offer_documents(): void
    {
        Asset::factory()->assignedToUser($this->employee)->create();
        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk()
            ->assertSee(route('documents.index'))->assertSee(route('documents.templates.index'));
        $this->get(route('reports/custom'))->assertOk()->assertSee('name="document_number"', false)
            ->assertSee('value="document_pdf"', false)->assertSee('Inventory handover');
        $this->get(route('users.print', $this->employee))->assertOk()
            ->assertSee('Inventory handover')->assertSee(route('documents.print'));
    }

    public function test_report_only_user_does_not_see_document_controls(): void
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->get(route('reports/custom'))->assertOk()
            ->assertDontSee('name="document_number"', false)
            ->assertDontSee('value="document_pdf"', false);
    }

    public function test_inventory_print_generates_only_assigned_assets_and_preserves_notes_and_jalali(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 9, 23, 12));
        $assets = Asset::factory()->count(2)->assignedToUser($this->employee)->create();
        Asset::factory()->assignedToUser(User::factory()->create())->create();
        $response = $this->actingAs($this->admin)->post(route('documents.print'), $this->printInput(['notes' => 'Print test notes']));
        $document = Document::sole();
        $response->assertRedirect(route('documents.pdf', ['document' => $document, 'inline' => 1]));
        $this->assertEqualsCanonicalizing($assets->modelKeys(), $document->items->pluck('item_id')->all());
        $this->assertSame('Print test notes', $document->notes);
        $this->assertSame('1405/07/01', $document->data['document.date_jalali']);
        $this->assertStringStartsWith('%PDF-', Storage::get($document->pdf_path));
        $hash = $document->pdf_sha256;
        $this->get($response->headers->get('Location'))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="'.$document->number.'.pdf"');
        $this->assertSame($hash, $document->fresh()->pdf_sha256);
        $this->get(route('users.print', $this->employee))->assertOk()->assertSee($document->number);
    }

    public function test_print_requires_document_permission(): void
    {
        $this->actingAs(User::factory()->viewUsers()->create())
            ->post(route('documents.print'), $this->printInput())->assertForbidden();
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_print_rejects_inactive_template_and_empty_inventory(): void
    {
        $this->actingAs($this->admin)->post(route('documents.print'), $this->printInput())
            ->assertSessionHasErrors('document_template_version_id');
        Asset::factory()->assignedToUser($this->employee)->create();
        $this->version->template->update(['active' => false]);
        $this->post(route('documents.print'), $this->printInput())->assertSessionHasErrors('document_template_version_id');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_print_rejects_other_company_user(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        [$a, $b] = Company::factory()->count(2)->create();
        $actor = User::factory()->forCompany($a)->create(['permissions' => '{"users.view":"1","documents.create":"1","documents.download":"1","assets.view":"1"}']);
        $other = User::factory()->forCompany($b)->create();
        $this->actingAs($actor)->post(route('documents.print'), $this->printInput(['source_id' => $other->id]))->assertRedirect();
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_selected_version_is_used_even_after_template_type_changes(): void
    {
        Asset::factory()->assignedToUser($this->employee)->create();
        $this->version->template->update(['type' => 'return']);
        $this->actingAs($this->admin)->post(route('documents.print'), $this->printInput())->assertRedirect();
        $this->assertSame('handover', Document::sole()->type);
    }

    public function test_company_scoped_employee_can_generate_and_print_with_permissions(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();
        $employee = User::factory()->forCompany($company)->create(['permissions' => '{"documents.create":"1","assets.view":"1"}']);
        Asset::factory()->assignedToUser($employee)->create(['company_id' => $company->id]);
        $response = $this->actingAs($employee)->post(route('documents.print'), $this->printInput(['source_id' => $employee->id]));
        $document = Document::sole();
        $this->assertSame($company->id, $document->company_id);
        $response->assertRedirect(route('documents.pdf', ['document' => $document, 'inline' => 1]));
        $this->get($response->headers->get('Location'))->assertOk();
    }

    public function test_acceptance_screen_offers_printing_without_changing_acceptance(): void
    {
        $acceptance = CheckoutAcceptance::factory()->pending()->create(['assigned_to_id' => $this->admin->id]);
        $this->actingAs($this->admin)->get(route('account.accept.item', $acceptance))
            ->assertOk()->assertSee('Inventory handover')->assertSee('name="asset_acceptance"', false);
        $this->post(route('documents.print'), $this->printInput(['source_type' => 'acceptance', 'source_id' => $acceptance->id]))
            ->assertRedirect();
        $document = Document::sole();
        $this->assertSame($acceptance->checkoutable_id, $document->items->sole()->item_id);
        $this->assertSame($acceptance->id, $document->data['checkout.acceptance_id']);
        $this->assertNull($acceptance->fresh()->accepted_at);
        $this->assertCount(0, $document->signatures);
    }

    public function test_acceptance_print_cannot_disclose_another_employee(): void
    {
        $acceptance = CheckoutAcceptance::factory()->create();
        $actor = User::factory()->create(['permissions' => '{"documents.create":"1","documents.download":"1","assets.view":"1"}']);
        $this->actingAs($actor)->post(route('documents.print'), $this->printInput(['source_type' => 'acceptance', 'source_id' => $acceptance->id]))
            ->assertForbidden();
    }

    public function test_custom_report_pdf_uses_existing_filters_and_employee(): void
    {
        $included = Asset::factory()->assignedToUser($this->employee)->create();
        Asset::factory()->assignedToUser($this->employee)->create();
        Asset::factory()->assignedToUser(User::factory()->create())->create(['model_id' => $included->model_id]);
        $response = $this->actingAs($this->admin)->post(route('reports.post-custom'), [
            'output_format' => 'document_pdf', 'document_assigned_to_id' => $this->employee->id,
            'document_template_version_id' => $this->version->id, 'by_model_id' => [$included->model_id],
        ]);
        $document = Document::sole();
        $response->assertRedirect(route('documents.pdf', ['document' => $document, 'inline' => 1]));
        $this->assertSame($included->id, $document->items->sole()->item_id);
    }

    public function test_custom_report_pdf_requires_employee_and_document_permission(): void
    {
        $this->actingAs($this->admin)->post(route('reports.post-custom'), ['output_format' => 'document_pdf'])
            ->assertSessionHasErrors(['document_assigned_to_id', 'document_template_version_id']);
        $this->actingAs(User::factory()->canViewReports()->create())->post(route('reports.post-custom'), [
            'output_format' => 'document_pdf', 'document_assigned_to_id' => $this->employee->id,
            'document_template_version_id' => $this->version->id,
        ])->assertForbidden();
    }

    public function test_csv_exports_historical_template_and_document_metadata(): void
    {
        $asset = Asset::factory()->create();
        $document = app(DocumentService::class)->generate($this->version, $this->employee, collect([$asset]), [], $this->admin);
        $this->version->template->update(['name' => 'Changed working copy']);
        $this->actingAs($this->admin)->post(route('reports.post-custom'), [
            'asset_tag' => 1, 'document_number' => 1, 'document_status' => 1,
            'document_template' => 1, 'document_template_version' => 1,
        ])->assertOk()->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertSeeTextInStreamedResponse([$asset->asset_tag, $document->number, 'Inventory handover', 'generated']);
    }

    public function test_csv_document_metadata_requires_documents_permission(): void
    {
        $this->actingAs(User::factory()->canViewReports()->create())
            ->post(route('reports.post-custom'), ['asset_tag' => 1, 'document_number' => 1])->assertForbidden();
    }

    public function test_saved_report_preserves_selected_published_version(): void
    {
        $this->actingAs($this->admin);
        $report = ReportTemplate::create([
            'name' => 'Saved print', 'type' => 'asset', 'created_by' => $this->admin->id,
            'options' => ['output_format' => 'document_pdf', 'document_template_version_id' => $this->version->id, 'document_assigned_to_id' => $this->employee->id],
        ]);
        app(TemplateService::class)->publishVersion($this->version->template, [], $this->admin);
        $this->get(route('report-templates.show', $report))->assertOk()
            ->assertSee('value="'.$this->version->id.'" selected>Inventory handover', false);
    }
}
