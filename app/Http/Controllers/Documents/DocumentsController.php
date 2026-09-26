<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\Documents\DocumentService;
use App\Services\Documents\SignatureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentsController extends Controller
{
    public function __construct(
        private DocumentService $documents,
        private SignatureService $signatures,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $this->authorize('view', Document::class);

        $query = Document::with(['assignedTo', 'templateVersion.template'])->withCount('items');
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($query) use ($search) {
                $query->where('number', 'like', '%'.$search.'%')
                    ->orWhereHas('assignedTo', fn ($users) => $users->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%'));
            });
        }
        if (in_array($request->input('status'), Document::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        return view('documents.index')->with('documents', $query->orderByDesc('id')->paginate(25)->withQueryString());
    }

    public function create(): View
    {
        $this->authorize('create', Document::class);

        return view('documents.create')
            ->with('templates', DocumentTemplate::active()
                ->whereHas('currentVersion')
                ->with('currentVersion')
                ->orderBy('name')
                ->get());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'document_template_id' => ['required', 'integer', 'exists:document_templates,id'],
            'assigned_to_id' => ['required', 'integer', 'exists:users,id'],
            'asset_ids' => ['required', 'array', 'min:1'],
            'asset_ids.*' => ['integer', 'exists:assets,id'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        $template = DocumentTemplate::with('currentVersion')->findOrFail($validated['document_template_id']);
        if (! $template->active) {
            return redirect()->back()->withInput()->with('error', trans('documents.general.template_unavailable'));
        }
        $version = $template->currentVersion;

        if (! $version) {
            return redirect()->back()->withInput()->with('error', trans('documents.general.no_published_version'));
        }

        $assignee = \App\Models\User::findOrFail($validated['assigned_to_id']);
        app(\App\Services\Documents\DocumentPrintService::class)->authorizeUser($assignee);
        $assets = Asset::whereIn('id', $validated['asset_ids'])->get();
        foreach ($assets as $asset) {
            $this->authorize('view', $asset);
        }

        try {
            $document = $this->documents->generate($version, $assignee, $assets, ['notes' => $validated['notes'] ?? null], $request->user());
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('documents.show', $document)
            ->with('success', trans('documents.general.created'));
    }

    public function show(Document $document): View
    {
        $this->authorize('view', $document);

        $document->load(['assignedTo', 'templateVersion.template', 'items', 'signatures']);

        $logs = \App\Models\Actionlog::where('item_type', Document::class)
            ->where('item_id', $document->id)
            ->with('adminuser')
            ->orderByDesc('created_at')
            ->get();

        return view('documents.show')
            ->with('document', $document)
            ->with('progress', $document->signatureProgress())
            ->with('logs', $logs);
    }

    public function printView(Document $document): View
    {
        $this->authorize('download', $document);
        $document->load(['templateVersion', 'items', 'signatures']);

        return view('documents.print', ['document' => $document, 'isPreview' => false]);
    }

    /**
     * Stream the stored (immutable) PDF (spec §10).
     */
    public function pdf(Request $request, Document $document): BinaryFileResponse
    {
        $this->authorize('download', $document);

        if (! $document->pdf_path || ! Storage::exists($document->pdf_path)) {
            abort(404, trans('documents.general.pdf_missing'));
        }

        $this->documents->logDownloaded($document, $request->user());

        if ($request->boolean('inline')) {
            return response()->file(Storage::path($document->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$document->number.'.pdf"',
            ]);
        }

        return response()->download(Storage::path($document->pdf_path), $document->number.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Record a signature (digital capture or printed wet-ink).
     */
    public function sign(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('sign', $document);

        $validated = $request->validate([
            'role' => ['required', 'string', 'max:30'],
            'method' => ['required', 'in:digital,printed'],
            'signature' => ['required_if:method,digital', 'nullable', 'string'],
            'agree_terms' => $document->templateVersion?->eula_enabled && $request->input('method') === 'digital'
                ? ['accepted'] : ['nullable', 'boolean'],
        ]);

        $role = $validated['role'];
        $progress = $document->signatureProgress();

        if (! in_array($role, $progress['required'])) {
            return redirect()->back()->with('error', trans('documents.general.unknown_role'));
        }

        if (in_array($role, $progress['obtained'])) {
            return redirect()->back()->with('error', trans('documents.general.already_signed'));
        }

        try {
            if ($validated['method'] === 'digital') {
                $this->signatures->storeDigital($document, $role, $request->user(), (string) $validated['signature'], $request);
            } else {
                $this->signatures->recordPrinted($document, $role, $request->user(), $request);
            }
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('documents.show', $document)
            ->with('success', trans('documents.general.signature_captured'));
    }

    public function markPending(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('update', $document);

        try {
            $this->documents->markPendingSignature($document, $request->user());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('documents.show', $document)
            ->with('success', trans('documents.general.mark_pending_success'));
    }

    public function cancel(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('cancel', $document);

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:191'],
        ]);

        try {
            $this->documents->cancel($document, $request->user(), $validated['cancel_reason']);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('documents.show', $document)
            ->with('success', trans('documents.general.cancel_success'));
    }
}
