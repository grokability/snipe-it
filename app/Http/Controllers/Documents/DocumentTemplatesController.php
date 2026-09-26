<?php

namespace App\Http\Controllers\Documents;

use App\Enums\ActionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentMarkdownPreviewRequest;
use App\Http\Requests\DocumentTemplateRequest;
use App\Models\Actionlog;
use App\Models\DocumentTemplate;
use App\Services\Documents\TemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentTemplatesController extends Controller
{
    public function __construct(private TemplateService $templates)
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->authorize('view', DocumentTemplate::class);

        return view('documents.templates.index')
            ->with('templates', DocumentTemplate::with('currentVersion')->orderBy('name')->get());
    }

    public function create(): View
    {
        $this->authorize('create', DocumentTemplate::class);

        return view('documents.templates.edit')->with('item', new DocumentTemplate);
    }

    public function store(DocumentTemplateRequest $request): RedirectResponse
    {
        $this->authorize('create', DocumentTemplate::class);

        $validated = $request->validated();
        $validated['signature_config'] = $this->rolesFromRequest($request);

        $template = $this->templates->create($validated, $request->user());

        return redirect()->route('documents.templates.show', $template)
            ->with('success', trans('documents.general.template_created'));
    }

    public function show(DocumentTemplate $template): View
    {
        $this->authorize('view', DocumentTemplate::class);

        return view('documents.templates.show')
            ->with('item', $template)
            ->with('versions', $template->versions()->orderByDesc('version')->get());
    }

    public function preview(Request $request, DocumentTemplate $template): \Illuminate\Http\Response|View
    {
        $this->authorize('view', DocumentTemplate::class);
        $terms = $this->templates->terms($template);
        $version = new \App\Models\DocumentTemplateVersion([
            'snapshot' => $this->templates->snapshot($template),
            'version' => ($template->currentVersion?->version ?? 0) + 1,
            'eula_enabled' => $terms['eula_enabled'],
            'eula_title' => $terms['eula_title'],
            'eula_body' => $terms['eula_body'],
            'eula_version' => $terms['eula_version'],
        ]);
        $document = new \App\Models\Document([
            'number' => 'PREVIEW', 'type' => $template->type,
            'data' => array_merge(\App\Services\Documents\PlaceholderRegistry::sampleContext(), [
                'document.number' => 'PREVIEW', 'document.type' => $template->type,
                'document.date' => now()->format('Y-m-d'),
                'document.date_jalali' => \App\Support\Jalali\Jalali::format(now(), 'Y/m/d'),
                'user.name' => trans('documents.general.sample_employee'),
                'user.employee_id' => 'EMP-001', 'user.email' => 'employee@example.com',
            ]),
        ]);
        $document->created_at = now();
        $document->setRelation('templateVersion', $version);
        $document->setRelation('signatures', new \Illuminate\Database\Eloquent\Collection);
        $document->setRelation('items', new \Illuminate\Database\Eloquent\Collection([
            new \App\Models\DocumentItem(['asset_tag' => 'ASSET-001', 'name' => trans('documents.general.sample_asset'), 'model_name' => 'Model 1', 'serial' => 'SN-001']),
        ]));

        if ($request->boolean('web')) {
            return view('documents.print', ['document' => $document, 'isPreview' => true]);
        }

        return response(app(\App\Services\Documents\PdfService::class)->generate($document), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="template-preview.pdf"',
        ]);
    }

    public function markdownPreview(DocumentMarkdownPreviewRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();

        return response()->json(['html' => $this->templates->renderTerms($validated['text'] ?? '', $validated['format'], \App\Services\Documents\PlaceholderRegistry::sampleContext($validated['header_config'] ?? []), \App\Services\Documents\PlaceholderRegistry::sampleAssets())]);
    }

    public function edit(DocumentTemplate $template): View
    {
        $this->authorize('update', DocumentTemplate::class);

        return view('documents.templates.edit')->with('item', $template);
    }

    public function update(DocumentTemplateRequest $request, DocumentTemplate $template): RedirectResponse
    {
        $this->authorize('update', DocumentTemplate::class);

        $validated = $request->validated();
        $validated['signature_config'] = $this->rolesFromRequest($request);

        $this->templates->update($template, $validated, $request->user());

        return redirect()->route('documents.templates.show', $template)
            ->with('success', trans('documents.general.template_updated'));
    }

    /**
     * Map the checkbox row (signature_roles[]) into the signature_config shape.
     */
    private function rolesFromRequest(DocumentTemplateRequest $request): array
    {
        return array_merge($request->validated('signature_config', []), ['roles' => $request->input('signature_roles', ['employee', 'it_representative'])]);
    }

    /**
     * Freeze the current working copy as a new immutable version (spec §3).
     */
    public function publish(Request $request, DocumentTemplate $template): RedirectResponse
    {
        $this->authorize('update', DocumentTemplate::class);

        $request->merge(array_merge($this->templates->terms($template), $request->only(['eula_enabled', 'eula_title', 'eula_body', 'eula_version', 'eula_format'])));
        $validated = $request->validate([
            'eula_enabled' => ['nullable', 'boolean'],
            'eula_title' => ['nullable', 'string', 'max:191'],
            'eula_body' => ['required_if:eula_enabled,1', 'nullable', 'string', 'max:60000'],
            'eula_version' => ['required_if:eula_enabled,1', 'nullable', 'string', 'max:40'],
            'eula_format' => ['required', 'in:plain,markdown'],
        ]);

        $version = $this->templates->publishVersion($template, $validated, $request->user());

        $log = new Actionlog;
        $log->item_type = DocumentTemplate::class;
        $log->item_id = $template->id;
        $log->created_by = $request->user()->id;
        $log->note = "Template '{$template->name}' published as v{$version->version}.";
        $log->logaction(ActionType::Update);

        return redirect()->route('documents.templates.show', $template)
            ->with('success', trans('documents.general.version_published')." (v{$version->version})");
    }

    public function destroy(Request $request, DocumentTemplate $template): RedirectResponse
    {
        $this->authorize('delete', DocumentTemplate::class);

        if ($template->documents()->exists()) {
            return redirect()->route('documents.templates.show', $template)
                ->with('error', trans('documents.general.has_documents'));
        }

        $template->delete();

        return redirect()->route('documents.templates.index')
            ->with('success', trans('documents.general.template_deleted'));
    }
}
