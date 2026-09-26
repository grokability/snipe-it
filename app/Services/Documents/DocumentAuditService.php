<?php

namespace App\Services\Documents;

use App\Enums\ActionType;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\User;

/**
 * Document audit trail (spec §12): every lifecycle event lands in the shared
 * action_logs table against the document AND each affected asset, so the
 * existing activity-report infrastructure picks it up for free.
 */
class DocumentAuditService
{
    public function logGenerated(Document $document, ?User $actor): void
    {
        $this->log($document, ActionType::DocumentGenerated, $actor,
            "Document {$document->number} generated ({$document->type}).");
    }

    public function logSignature(Document $document, DocumentSignature $signature, ?User $actor): void
    {
        $this->log($document, ActionType::DocumentSigned, $actor,
            "Document {$document->number}: '{$signature->role}' signature recorded ({$signature->method}) by {$signature->signer_name}.");
    }

    public function logCancelled(Document $document, ?User $actor, string $reason): void
    {
        $this->log($document, ActionType::DocumentCancelled, $actor,
            "Document {$document->number} cancelled: {$reason}");
    }

    public function logDownloaded(Document $document, ?User $actor): void
    {
        $this->log($document, ActionType::DocumentDownloaded, $actor,
            "Document {$document->number} PDF downloaded.");
    }

    private function log(Document $document, ActionType $type, ?User $actor, string $note): void
    {
        $entry = new Actionlog;
        $entry->item_type = Document::class;
        $entry->item_id = $document->id;
        $entry->created_by = $actor?->id;
        $entry->note = $note;
        $entry->logaction($type);

        // Mirror the event onto each covered asset so hardware history shows it
        if (in_array($type, [ActionType::DocumentGenerated, ActionType::DocumentSigned, ActionType::DocumentCancelled])) {
            foreach ($document->items as $item) {
                if ($asset = Asset::find($item->item_id)) {
                    $assetEntry = new Actionlog;
                    $assetEntry->item_type = Asset::class;
                    $assetEntry->item_id = $asset->id;
                    $assetEntry->created_by = $actor?->id;
                    $assetEntry->note = $note;
                    $assetEntry->logaction($type);
                }
            }
        }
    }
}
