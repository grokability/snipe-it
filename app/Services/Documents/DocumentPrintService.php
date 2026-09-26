<?php

namespace App\Services\Documents;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVersion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DocumentPrintService
{
    public const REPORT_FIELDS = [
        'document_number' => 'number',
        'document_type' => 'type',
        'document_status' => 'status',
        'document_template' => 'template',
        'document_template_version' => 'version',
        'document_signed_at' => 'signed_at',
    ];

    public function templates(): Collection
    {
        return DocumentTemplate::active()->whereHas('currentVersion')
            ->with('currentVersion')->orderBy('name')->get();
    }

    public function authorizeUser(User $user): void
    {
        abort_unless(Company::isCurrentUserHasAccess($user), 403);
        if ((int) $user->id !== (int) auth()->id()) {
            Gate::authorize('view', $user);
        }
    }

    public function generate(int $versionId, User $user, Collection $assets, array $checkout = []): Document
    {
        Gate::authorize('create', Document::class);
        $this->authorizeUser($user);
        Gate::authorize('download', new Document(['assigned_to_id' => $user->id, 'company_id' => $user->legacy_company_id]));
        $version = DocumentTemplateVersion::with('template')->findOrFail($versionId);
        if (! $version->template?->active) {
            throw ValidationException::withMessages(['document_template_version_id' => trans('documents.general.template_unavailable')]);
        }
        if ($assets->isEmpty() || $assets->count() > 100) {
            throw ValidationException::withMessages(['document_template_version_id' => trans('documents.general.print_asset_limit')]);
        }
        foreach ($assets as $asset) {
            Gate::authorize('view', $asset);
            abort_unless(Company::isCurrentUserHasAccess($asset), 403);
        }

        return app(DocumentService::class)->generate($version, $user, $assets, $checkout, auth()->user());
    }

    public function history(User $user, Collection $assetIds): Collection
    {
        $query = Document::forUser($user->id)->with('templateVersion')->orderByDesc('id');
        if ($assetIds->isNotEmpty()) {
            $query->whereHas('items', fn ($items) => $items->whereIn('item_id', $assetIds));
        }

        return $query->get()->filter(fn (Document $document) => Gate::allows('view', $document));
    }
}
