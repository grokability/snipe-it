<?php

namespace Tests\Feature\Documents;

use App\Models\Asset;
use App\Models\Document;
use App\Models\User;
use App\Services\Documents\DocumentService;
use App\Services\Documents\SignatureService;
use App\Services\Documents\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentLifecycleTest extends TestCase
{
    // 1x1 transparent PNG — the smallest payload that passes MIME + dimension checks
    private const PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private DocumentService $documents;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 9, 23, 12)); // 1405/07/01 — keep Jalali assertions clock-independent
        Storage::fake('local');
        $this->documents = app(DocumentService::class);
    }

    private function publishedVersion(array $templateOverrides = []): \App\Models\DocumentTemplateVersion
    {
        $templates = app(TemplateService::class);
        $template = $templates->create(array_merge([
            'name' => 'Checkout v1 '.uniqid(),
            'type' => 'checkout',
            'language' => 'en',
            'body' => 'Issued to {{user.name}} on {{document.date_jalali}}.',
        ], $templateOverrides), User::factory()->superuser()->create());

        return $templates->publishVersion($template, ['eula_enabled' => true, 'eula_title' => 'Terms', 'eula_body' => 'Be nice.', 'eula_version' => '1.0'], User::factory()->superuser()->create());
    }

    public function test_generate_creates_numbered_document_with_snapshot_and_pdf()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create(['first_name' => 'Sara', 'last_name' => 'Ahmadi']);
        $asset = Asset::factory()->create(['asset_tag' => 'LT-9001']);

        $document = $this->documents->generate($version, $employee, collect([$asset]), [], $employee);

        $this->assertMatchesRegularExpression('/^CO-\d{4}-\d{6}$/', $document->number);
        $this->assertSame(Document::STATUS_GENERATED, $document->status);
        $this->assertSame('checkout', $document->type);

        // Frozen item snapshot (spec §11)
        $item = $document->items()->first();
        $this->assertNotNull($item);
        $this->assertSame('LT-9001', $item->asset_tag);
        $this->assertSame($asset->id, $item->item_id);

        // Placeholder context stored on the document
        $this->assertSame('Sara Ahmadi', $document->data['user.name']);
        $this->assertSame($document->number, $document->data['document.number']);
        $this->assertSame('1405/07/01', $document->data['document.date_jalali']);

        // Immutable PDF + hash (spec §10)
        $this->assertNotNull($document->pdf_path);
        Storage::disk('local')->assertExists($document->pdf_path);
        $this->assertSame(64, strlen($document->pdf_sha256));
        $this->assertSame(
            hash('sha256', Storage::disk('local')->get($document->pdf_path)),
            $document->pdf_sha256,
        );
    }

    public function test_full_signature_flow_moves_document_to_signed()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create();
        $itRep = User::factory()->create(['first_name' => 'Admin', 'last_name' => 'User', 'permissions' => '{"documents.sign":"1"}']);
        $asset = Asset::factory()->create();

        $document = $this->documents->generate($version, $employee, collect([$asset]), [], $itRep);
        $this->documents->markPendingSignature($document, $itRep);
        $this->assertSame(Document::STATUS_PENDING_SIGNATURE, $document->refresh()->status);

        $signatures = app(SignatureService::class);
        $request = Request::create('/');

        // First signature → partially signed
        $signatures->storeDigital($document, 'employee', $employee, self::PNG_DATA_URL, $request);
        $document = $document->refresh();
        $this->assertSame(Document::STATUS_PARTIALLY_SIGNED, $document->status);
        $this->assertNull($document->signed_at);

        $firstHash = $document->pdf_sha256;

        // Second (final) signature → signed + re-rendered PDF with embedded images
        $signatures->storeDigital($document, 'it_representative', $itRep, self::PNG_DATA_URL, $request);
        $document = $document->refresh();

        $this->assertSame(Document::STATUS_SIGNED, $document->status);
        $this->assertNotNull($document->signed_at);

        // Final render embeds signature images → different bytes than the unsigned render
        $this->assertNotSame($firstHash, $document->pdf_sha256);
        $this->assertSame(
            hash('sha256', Storage::disk('local')->get($document->pdf_path)),
            $document->pdf_sha256,
        );
    }

    public function test_signed_document_rejects_further_signatures_and_cancellation()
    {
        $version = $this->publishedVersion(['signature_config' => ['roles' => ['employee']]]);
        $employee = User::factory()->create();

        $document = $this->documents->generate($version, $employee, collect([Asset::factory()->create()]), [], $employee);

        app(SignatureService::class)->storeDigital($document, 'employee', $employee, self::PNG_DATA_URL, Request::create('/'));
        $document = $document->refresh();
        $this->assertSame(Document::STATUS_SIGNED, $document->status);

        $this->expectException(\RuntimeException::class);
        $this->documents->cancel($document, $employee, 'oops');
    }

    public function test_duplicate_role_signature_is_rejected()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create();
        $document = $this->documents->generate($version, $employee, collect([Asset::factory()->create()]), [], $employee);

        $signatures = app(SignatureService::class);
        $signatures->storeDigital($document, 'employee', $employee, self::PNG_DATA_URL, Request::create('/'));

        $this->expectException(\RuntimeException::class);
        $signatures->storeDigital($document, 'employee', $employee, self::PNG_DATA_URL, Request::create('/'));
    }

    public function test_invalid_signature_payloads_are_rejected()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create();
        $document = $this->documents->generate($version, $employee, collect([Asset::factory()->create()]), [], $employee);

        $signatures = app(SignatureService::class);

        foreach ([
            'not a data url',
            'data:image/jpeg;base64,'.base64_encode('jpeg bytes'),
            'data:image/png;base64,'.base64_encode(random_bytes(32)), // valid b64, not a PNG
        ] as $payload) {
            try {
                $signatures->storeDigital($document, 'employee', $employee, $payload, Request::create('/'));
                $this->fail("Payload should have been rejected: {$payload}");
            } catch (\RuntimeException $e) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame(0, $document->signatures()->count());
    }

    public function test_cancelled_document_is_immutable()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create();
        $document = $this->documents->generate($version, $employee, collect([Asset::factory()->create()]), [], $employee);

        $this->documents->cancel($document, User::factory()->superuser()->create(), 'asset returned early');
        $document = $document->refresh();

        $this->assertSame(Document::STATUS_CANCELLED, $document->status);
        $this->assertNotNull($document->cancelled_at);
        $this->assertSame('asset returned early', $document->cancel_reason);

        $this->expectException(\RuntimeException::class);
        app(SignatureService::class)->storeDigital($document, 'employee', $employee, self::PNG_DATA_URL, Request::create('/'));
    }

    public function test_audit_trail_records_lifecycle_events()
    {
        $version = $this->publishedVersion();
        $employee = User::factory()->create();
        $asset = Asset::factory()->create();

        $document = $this->documents->generate($version, $employee, collect([$asset]), [], $employee);

        $this->assertDatabaseHas('action_logs', [
            'item_type' => Document::class,
            'item_id' => $document->id,
            'action_type' => 'document generated',
        ]);

        // The asset mirror row (spec §12) lands on the covered asset too
        $this->assertDatabaseHas('action_logs', [
            'item_type' => Asset::class,
            'item_id' => $asset->id,
            'action_type' => 'document generated',
        ]);
    }
}
