<?php

namespace App\Services\Documents;

use App\Events\Documents\DocumentCancelled;
use App\Events\Documents\DocumentGenerated;
use App\Events\Documents\DocumentSigned;
use App\Models\Asset;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\DocumentTemplateVersion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Document lifecycle orchestrator (spec §8): draft/generate → pending →
 * partially signed → signed (+ cancelled). Signed or cancelled documents are
 * immutable — the stored PDF and its SHA-256 are the legal record.
 */
class DocumentService
{
    public function __construct(
        private DocumentNumberService $numbers = new DocumentNumberService,
        private PdfService $pdfs = new PdfService,
        private DocumentAuditService $audit = new DocumentAuditService,
    ) {}

    /**
     * Generate a document from a published template version.
     *
     * @param  Collection<int,Asset>  $assets
     */
    public function generate(
        DocumentTemplateVersion $version,
        User $assignee,
        Collection $assets,
        array $checkout = [],
        ?User $actor = null,
        ?int $checkoutLogId = null,
    ): Document {
        if ($assets->isEmpty()) {
            throw new RuntimeException('A document needs at least one asset.');
        }

        $template = $version->template;
        $type = $version->snapshotField('type', $template->type);

        return DB::transaction(function () use ($version, $type, $assignee, $assets, $checkout, $actor, $checkoutLogId) {
            $number = $this->numbers->next($type);

            $document = Document::create([
                'number' => $number,
                'type' => $type,
                'status' => Document::STATUS_GENERATED,
                'document_template_version_id' => $version->id,
                'assigned_to_id' => $assignee->id,
                'company_id' => $assignee->legacy_company_id,
                'checkout_log_id' => $checkoutLogId,
                'notes' => $checkout['notes'] ?? null,
                'data' => PlaceholderRenderer::buildContext($this->payload($type, $number, $assignee, $assets, $checkout, $actor)),
                'created_by' => $actor?->id,
            ]);

            foreach ($assets as $asset) {
                $this->snapshotAsset($document, $asset);
            }

            $document->setRelation('items', $document->items()->get());
            $this->renderAndStore($document);

            $this->audit->logGenerated($document, $actor);
            DocumentGenerated::dispatch($document, $actor);

            return $document;
        });
    }

    /**
     * Convenience for the checkout hook: latest published version of the
     * active template for the type, or null when none exists.
     */
    public function latestVersionForType(string $type): ?DocumentTemplateVersion
    {
        $template = \App\Models\DocumentTemplate::active()->ofType($type)
            ->with('currentVersion')
            ->first();

        return $template?->currentVersion;
    }

    /**
     * Called by SignatureService after each signature row is written.
     * Moves the document to partially_signed / signed and finalizes the PDF.
     */
    public function onSignatureRecorded(Document $document, ?User $actor = null): Document
    {
        $document->refresh();
        $progress = $document->signatureProgress();
        $complete = empty(array_diff($progress['required'], $progress['obtained']));

        if ($complete) {
            $this->transition($document, Document::STATUS_SIGNED);
            $document->signed_at = now();

            // Final render embeds the captured signature images
            $document->setRelation('items', $document->items()->get());
            $this->renderAndStore($document);

            $latest = $document->signatures()->latest('signed_at')->first();
            $this->audit->logSignature($document, $latest, $actor);
            DocumentSigned::dispatch($document->refresh(), $latest);
        } else {
            $this->transition($document, Document::STATUS_PARTIALLY_SIGNED);
            $document->save();

            $latest = $document->signatures()->latest('signed_at')->first();
            $this->audit->logSignature($document, $latest, $actor);
        }

        return $document->refresh();
    }

    /**
     * Present the document for signatures (generated → pending_signature).
     */
    public function markPendingSignature(Document $document, ?User $actor = null): Document
    {
        $this->transition($document, Document::STATUS_PENDING_SIGNATURE);
        $document->save();

        return $document;
    }

    public function cancel(Document $document, User $actor, string $reason): Document
    {
        $this->transition($document, Document::STATUS_CANCELLED);
        $document->cancelled_at = now();
        $document->cancel_reason = $reason;
        $document->save();

        $this->audit->logCancelled($document, $actor, $reason);
        DocumentCancelled::dispatch($document, $actor);

        return $document;
    }

    public function logDownloaded(Document $document, ?User $actor): void
    {
        $this->audit->logDownloaded($document, $actor);
    }

    /**
     * Enforce the legal state machine (spec §8).
     */
    public function transition(Document $document, string $to): void
    {
        $allowed = Document::TRANSITIONS[$document->status] ?? [];

        if (! in_array($to, $allowed)) {
            throw new RuntimeException("Illegal transition {$document->status} → {$to} on {$document->number}.");
        }

        $document->status = $to;
    }

    /**
     * Render + persist the PDF with its SHA-256 (spec §10 immutability).
     */
    private function renderAndStore(Document $document): void
    {
        $bytes = $this->pdfs->generate($document);
        if (! preg_match('/\A[A-Za-z0-9_-]+\z/', $document->number)) {
            throw new RuntimeException('Document numbers may contain only letters, numbers, hyphens and underscores.');
        }
        $path = 'private_uploads/documents/'.$document->number.'-'.hash('sha256', $bytes).'.pdf';

        if (Storage::put($path, $bytes) === false) {
            throw new RuntimeException("Could not store PDF for {$document->number}.");
        }

        $document->pdf_path = $path;
        $document->pdf_sha256 = hash('sha256', $bytes);
        $document->save();
    }

    /**
     * Freeze the asset into document_items — the document never changes when
     * the live asset record later changes (spec §11 snapshots).
     */
    private function snapshotAsset(Document $document, Asset $asset): void
    {
        DocumentItem::create([
            'document_id' => $document->id,
            'item_id' => $asset->id,
            'asset_tag' => $asset->asset_tag,
            'name' => $asset->name,
            'model_name' => $asset->model?->name,
            'manufacturer_name' => $asset->model?->manufacturer?->name,
            'serial' => $asset->serial,
            'status_name' => $asset->assetstatus?->name,
            'snapshot' => [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'model' => $asset->model?->name,
                'manufacturer' => $asset->model?->manufacturer?->name,
                'serial' => $asset->serial,
                'location' => $asset->location?->name,
                'purchase_date' => (string) $asset->purchase_date,
                'warranty_expiration' => ($asset->warranty_months && $asset->purchase_date) ? (string) $asset->warranty_expires : '',
                'status' => $asset->assetstatus?->name,
            ],
        ]);
    }

    /**
     * Build the placeholder payload for the renderer.
     *
     * @param  Collection<int,Asset>  $assets
     */
    private function payload(string $type, string $number, User $assignee, Collection $assets, array $checkout, ?User $actor): array
    {
        return [
            'company' => $assignee->company ?? null,
            'user' => $assignee,
            'checkout' => $checkout,
            'assets' => $assets->map(fn ($asset) => [
                'asset_tag' => $asset->asset_tag,
                'name' => $asset->name,
                'model' => $asset->model?->name,
                'manufacturer' => $asset->model?->manufacturer?->name,
                'serial' => $asset->serial,
                'location' => $asset->location?->name,
                'purchase_date' => (string) $asset->purchase_date,
                'warranty_expiration' => ($asset->warranty_months && $asset->purchase_date) ? (string) $asset->warranty_expires : '',
                'status' => $asset->assetstatus?->name,
            ])->all(),
            'current_user' => $actor,
            'document' => [
                'number' => $number,
                'type' => $type,
                'date' => now()->format('Y-m-d'),
            ],
        ];
    }
}
