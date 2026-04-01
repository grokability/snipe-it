<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePrintableRequest;
use App\Models\Category;
use App\Models\CustomField;
use App\Models\Printable;
use App\Services\PrintableService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * This class controls all actions related to Printable HTML templates.
 *
 * Printables are HTML templates that can be assigned to Categories.
 * Assets that belong to a category with a Printable assigned to it can
 * have that Printable generated/rendered with asset-specific variable data.
 *
 * Template management requires admin or superuser access (enforced via
 * the `PrintablePolicy` which maps to the 'categories' permission column).
 */
class PrintablesController extends Controller
{
    public function __construct(private readonly PrintableService $service) {}

    /**
     * Display a listing of all printable templates.
     */
    public function index(): View
    {
        $this->authorize('view', Printable::class);

        $printables = Printable::with('categories', 'creator')
            ->withCount('categories')
            ->latest()
            ->paginate(25);

        return view('printables.index', compact('printables'));
    }

    /**
     * Show the form for creating a new printable template.
     */
    public function create(): View
    {
        $this->authorize('create', Printable::class);

        $categories   = Category::where('category_type', 'asset')->orderBy('name')->get();
        $customFields = CustomField::where('field_encrypted', 0)->orderBy('name')->get();
        $variables    = PrintableService::availableVariables($customFields);

        return view('printables.edit', [
            'item'       => new Printable,
            'categories' => $categories,
            'variables'  => $variables,
        ]);
    }

    /**
     * Store a newly created printable template.
     */
    public function store(SavePrintableRequest $request): RedirectResponse
    {
        $this->authorize('create', Printable::class);

        $printable             = new Printable;
        $printable->name       = $request->input('name');
        $printable->content    = $request->input('content');
        $printable->created_by = auth()->id();

        if (! $printable->save()) {
            return redirect()->back()->withInput()->withErrors($printable->getErrors());
        }

        $printable->categories()->sync($request->input('category_ids', []));

        return redirect()->route('printables.index')
            ->with('success', trans('admin/printables/message.create.success'));
    }

    /**
     * Show the form for editing an existing printable template.
     */
    public function edit(Printable $printable): View
    {
        $this->authorize('update', Printable::class);

        $categories   = Category::where('category_type', 'asset')->orderBy('name')->get();
        $customFields = CustomField::where('field_encrypted', 0)->orderBy('name')->get();
        $variables    = PrintableService::availableVariables($customFields);

        return view('printables.edit', [
            'item'       => $printable,
            'categories' => $categories,
            'variables'  => $variables,
        ]);
    }

    /**
     * Update the specified printable template.
     */
    public function update(SavePrintableRequest $request, Printable $printable): RedirectResponse
    {
        $this->authorize('update', Printable::class);

        $printable->name    = $request->input('name');
        $printable->content = $request->input('content');

        if (! $printable->save()) {
            return redirect()->back()->withInput()->withErrors($printable->getErrors());
        }

        $printable->categories()->sync($request->input('category_ids', []));

        return redirect()->route('printables.index')
            ->with('success', trans('admin/printables/message.update.success'));
    }

    /**
     * Remove the specified printable template.
     */
    public function destroy(Printable $printable): RedirectResponse
    {
        $this->authorize('delete', Printable::class);

        $printable->delete();

        return redirect()->route('printables.index')
            ->with('success', trans('admin/printables/message.delete.success'));
    }
}
