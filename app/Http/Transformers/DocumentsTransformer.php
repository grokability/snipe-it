<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Document;
use Illuminate\Database\Eloquent\Collection;

class DocumentsTransformer
{
    public function transformDocuments(Collection $documents, $total)
    {
        $array = [];
        foreach ($documents as $document) {
            $array[] = $this->transformDocument($document);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformDocument(?Document $document = null)
    {
        if ($document) {
            $progress = $document->signatureProgress();

            $array = [
                'id' => (int) $document->id,
                'number' => e($document->number),
                'type' => e($document->type),
                'status' => e($document->status),
                'assigned_to' => ($document->assignedTo) ? [
                    'id' => (int) $document->assignedTo->id,
                    'name' => e($document->assignedTo->present()->fullName),
                ] : null,
                'template' => ($document->templateVersion) ? [
                    'id' => (int) $document->templateVersion->template->id,
                    'name' => e($document->templateVersion->snapshotField('name')),
                    'version' => (int) $document->templateVersion->version,
                ] : null,
                'assets_count' => (int) $document->items()->count(),
                'signatures_required' => $progress['required'],
                'signatures_obtained' => $progress['obtained'],
                'signed_at' => ($document->signed_at) ? $document->signed_at->format('Y-m-d H:i:s') : null,
                'cancelled_at' => ($document->cancelled_at) ? $document->cancelled_at->format('Y-m-d H:i:s') : null,
                'cancel_reason' => ($document->cancel_reason) ? e($document->cancel_reason) : null,
                'sha256' => $document->pdf_sha256,
                'notes' => ($document->notes) ? e($document->notes) : null,
                'created_at' => Helper::getFormattedDateObject($document->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($document->updated_at, 'datetime'),
            ];

            return $array;
        }

        return null;
    }
}
