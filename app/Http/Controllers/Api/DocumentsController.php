<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\DocumentsTransformer;
use App\Models\Asset;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\Documents\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function __construct(private DocumentService $documents)
    {
        $this->middleware('api');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', Document::class);

        $allowed_columns = [
            'id', 'number', 'type', 'status', 'created_at',
        ];

        $documents = Document::with(['assignedTo', 'templateVersion.template']);

        if ($request->filled('type')) {
            $documents->where('type', '=', $request->input('type'));
        }

        if ($request->filled('status')) {
            $documents->where('status', '=', $request->input('status'));
        }

        if ($request->filled('assigned_to_id')) {
            $documents->where('assigned_to_id', '=', $request->input('assigned_to_id'));
        }

        if ($request->filled('search')) {
            $documents->where('number', 'LIKE', '%'.$request->input('search').'%');
        }

        $total = $documents->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $documents->orderBy($sort, $order);

        $documents = $documents->skip($offset)->take($limit)->get();

        return response()->json((new DocumentsTransformer)->transformDocuments($documents, $total));
    }

    public function show(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json(
            Helper::formatStandardApiResponse('success', (new DocumentsTransformer)->transformDocument($document->load(['assignedTo', 'templateVersion.template', 'signatures'])), trans('documents.general.details'))
        );
    }

    /**
     * Generate a document from a published template version (spec §9).
     */
    public function store(Request $request): JsonResponse
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
            return response()->json(Helper::formatStandardApiResponse('error', null, trans('documents.general.template_unavailable')));
        }
        $version = $template->currentVersion;

        if (! $version) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('documents.general.no_published_version'))
            );
        }

        $assignee = \App\Models\User::findOrFail($validated['assigned_to_id']);
        app(\App\Services\Documents\DocumentPrintService::class)->authorizeUser($assignee);
        $assets = Asset::whereIn('id', $validated['asset_ids'])->get();
        foreach ($assets as $asset) {
            $this->authorize('view', $asset);
        }
        try {
            $document = $this->documents->generate(
                $version,
                $assignee,
                $assets,
                ['notes' => $validated['notes'] ?? null],
                $request->user()
            );
        } catch (\RuntimeException $e) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, $e->getMessage())
            );
        }

        return response()->json(
            Helper::formatStandardApiResponse('success', (new DocumentsTransformer)->transformDocument($document), trans('documents.general.created'))
        );
    }

    /**
     * Documents covering a given asset (spec §12 traceability).
     */
    public function assetDocuments(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('view', Document::class);

        $documents = Document::forAsset($asset->id)
            ->with(['assignedTo', 'templateVersion.template'])
            ->orderByDesc('id')
            ->get();

        return response()->json((new DocumentsTransformer)->transformDocuments($documents, $documents->count()));
    }
}
