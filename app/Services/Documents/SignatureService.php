<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Signature capture + storage (spec §7, §21).
 *
 * Digital captures arrive as base64 PNG data URLs from the SignaturePad
 * component. They are strictly validated, stored under private_uploads,
 * and never logged.
 */
class SignatureService
{
    private const MAX_BYTES = 200_000; // ~200 KB decoded

    private const MAX_DIMENSIONS = [1200, 500];

    public function storeDigital(Document $document, string $role, User $signer, string $dataUrl, Request $request): DocumentSignature
    {
        if ($document->isImmutable()) {
            throw new RuntimeException('This document is closed to signatures.');
        }

        $filename = $this->storeImage($dataUrl, $document->id);

        try {
            return $this->record($document, $role, $signer, [
                'method' => DocumentSignature::METHOD_DIGITAL,
                'signature_filename' => $filename,
                'request' => $request,
            ]);
        } catch (\Throwable $e) {
            Storage::delete($filename);
            throw $e;
        }
    }

    /**
     * Record a wet-ink signature (signed on paper; the PDF block was used).
     */
    public function recordPrinted(Document $document, string $role, User $signer, Request $request): DocumentSignature
    {
        if ($document->isImmutable()) {
            throw new RuntimeException('This document is closed to signatures.');
        }

        return $this->record($document, $role, $signer, [
            'method' => DocumentSignature::METHOD_PRINTED,
            'request' => $request,
        ]);
    }

    public function absSignaturePath(string $filename): string
    {
        return Storage::path($filename);
    }

    private function record(Document $document, string $role, User $signer, array $opts): DocumentSignature
    {
        return DB::transaction(function () use ($document, $role, $signer, $opts) {
            $document = Document::whereKey($document->id)->lockForUpdate()->firstOrFail();
            if ($document->isImmutable()) {
                throw new RuntimeException('This document is closed to signatures.');
            }
            Gate::forUser($signer)->authorize('signRole', [$document, $role, $opts['method']]);
            if (! in_array($role, $document->signatureProgress()['required'], true)) {
                throw new RuntimeException(trans('documents.general.unknown_role'));
            }
            $signature = DocumentSignature::firstOrNew([
                'document_id' => $document->id,
                'role' => $role,
            ]);

            if ($signature->signed_at) {
                throw new RuntimeException("The '{$role}' signature already exists on this document.");
            }

            $signature->fill([
                'signer_user_id' => $role === 'employee' && $opts['method'] === DocumentSignature::METHOD_PRINTED ? $document->assigned_to_id : $signer->id,
                'signer_name' => $role === 'employee' && $opts['method'] === DocumentSignature::METHOD_PRINTED
                    ? ($document->data['user.name'] ?? trim($signer->first_name.' '.$signer->last_name))
                    : trim($signer->first_name.' '.$signer->last_name),
                'metadata' => ['recorded_by' => $signer->id, 'eula_version' => $document->templateVersion->eula_version, 'terms_accepted' => $opts['request']->boolean('agree_terms')],
                'method' => $opts['method'],
                'signature_filename' => $opts['signature_filename'] ?? null,
                'signed_at' => now(),
                'remote_ip' => $opts['request']?->ip(),
                'user_agent' => substr((string) $opts['request']?->userAgent(), 0, 255),
            ]);
            $signature->save();

            app(DocumentService::class)->onSignatureRecorded($document, $signer);

            return $signature;
        });
    }

    /**
     * Validate and persist a base64 PNG data URL. Returns the storage path.
     */
    private function storeImage(string $dataUrl, int $documentId): string
    {
        if (! preg_match('#^data:image/(png);base64,([A-Za-z0-9+/=]+)$#', trim($dataUrl), $m)) {
            throw new RuntimeException('Signature must be a base64-encoded PNG data URL.');
        }

        $binary = base64_decode($m[2], true);
        if ($binary === false || strlen($binary) > self::MAX_BYTES) {
            throw new RuntimeException('Signature image rejected (invalid or larger than 200 KB).');
        }

        // finfo MIME sniff — trust content, not the data URL label
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary);
        if (! in_array($mime, ['image/png'])) {
            throw new RuntimeException('Signature content is not a PNG image.');
        }

        if (! ($info = getimagesizefromstring($binary)) || $info[0] > self::MAX_DIMENSIONS[0] || $info[1] > self::MAX_DIMENSIONS[1]) {
            throw new RuntimeException('Signature image dimensions out of range.');
        }

        $dir = 'private_uploads/signatures';
        Storage::makeDirectory($dir, 775);

        $filename = $dir.'/doc-'.$documentId.'-'.bin2hex(random_bytes(16)).'.png';
        if (! Storage::put($filename, $binary)) {
            throw new RuntimeException('Could not save signature image.');
        }

        return $filename;
    }
}
