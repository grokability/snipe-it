<?php

namespace App\Services\Documents;

use App\Models\Asset;
use App\Models\Document;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Checkout integration (spec §4): after a successful asset checkout to a
 * user, generate the matching document from the active template.
 *
 * Failure policy: a document problem never blocks or rolls back the
 * checkout itself. When require_checkout_document is on, the failure is
 * surfaced to the admin flash; when off, it is logged only.
 */
class CheckoutDocumentHook
{
    public function __construct(
        private DocumentService $documents = new DocumentService,
    ) {}

    /**
     * @return Document|null the generated document, or null when nothing
     *                       was generated (no active template configured)
     */
    public function afterCheckout(Asset $asset, User $target, ?User $admin, ?int $checkoutLogId = null): ?Document
    {
        $settings = Setting::getSettings();
        $version = $this->documents->latestVersionForType('checkout');

        if (! $version) {
            if ($settings->require_checkout_document) {
                throw new \RuntimeException('require_checkout_document is enabled but no active checkout template with a published version exists.');
            }

            return null;
        }

        $document = $this->documents->generate($version, $target, collect([$asset]), [
            'date' => now()->format('Y-m-d'),
            'expected_return' => (string) $asset->expected_checkin,
        ], $admin, $checkoutLogId);

        // Straight to awaiting signatures — the checkout already happened.
        $this->documents->markPendingSignature($document, $admin);

        return $document;
    }

    /**
     * Safe wrapper for controllers: never throws, never blocks.
     */
    public function afterCheckoutSafe(Asset $asset, User $target, ?User $admin, ?int $checkoutLogId = null): ?Document
    {
        try {
            return $this->afterCheckout($asset, $target, $admin, $checkoutLogId);
        } catch (\Throwable $e) {
            Log::error('Checkout document generation failed: '.$e->getMessage(), [
                'asset_id' => $asset->id,
                'target_id' => $target->id,
            ]);

            return null;
        }
    }
}
