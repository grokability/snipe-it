<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\ReportTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReportTemplatesController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('reports.view');

        // Ignore "options" rules since data does not come in under that key...
        $validated = $request->validate(Arr::except((new ReportTemplate)->getRules(), 'options'));

        $report = $request->user()->reportTemplates()->create([
            'name' => $validated['name'],
            'options' => $request->except(['_token', 'name', 'is_shared']),
            'is_shared' => $request->has('is_shared'),
        ]);

        session()->flash('success', trans('admin/reports/message.create.success'));

        return redirect()->route('report-templates.show', $report->id);
    }

    public function show(ReportTemplate $reportTemplate)
    {
        $this->authorize('reports.view');

        $customfields = CustomField::get();
        $report_templates = ReportTemplate::orderBy('name')->get();

        return view('reports/custom', [
            'customfields' => $customfields,
            'report_templates' => $report_templates,
            'template' => $reportTemplate,
        ]);
    }

    public function edit(ReportTemplate $reportTemplate)
    {
        $this->authorize('reports.view');

        if ($reportTemplate->created_by != auth()->id()) {
            return redirect()
                ->route('report-templates.show', $reportTemplate)
                ->withError(trans('general.report_not_editable'));
        }

        return view('reports/custom', [
            'customfields' => CustomField::get(),
            'template' => $reportTemplate,
        ]);
    }

    public function update(Request $request, ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorize('reports.view');

        if ($reportTemplate->created_by != auth()->id()) {
            return redirect()
                ->route('report-templates.show', $reportTemplate)
                ->withError(trans('general.report_not_editable'));
        }

        $properties = [
            'name' => $request->input('name'),
            'options' => Arr::except($request->all(), ['_token', 'id', 'name', 'is_shared']),
            'is_shared' => $reportTemplate->is_shared,
        ];

        if ($reportTemplate->created_by == $request->user()->id) {
            $properties['is_shared'] = $request->boolean('is_shared');
        }

        $reportTemplate->fill($properties);

        if ($reportTemplate->isInvalid()) {
            return redirect()->back()->withInput()->withErrors($reportTemplate->getErrors());
        }

        $reportTemplate->save();

        session()->flash('success', trans('admin/reports/message.update.success'));

        return redirect()->route('report-templates.show', $reportTemplate->id);
    }

    public function destroy(ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorize('reports.view');

        if ($reportTemplate->creator()->isNot(auth()->user())) {
            return redirect()
                ->route('report-templates.show', $reportTemplate)
                ->withError(trans('general.generic_model_not_found', ['model' => 'report template']));
        }

        $reportTemplate->delete();

        return redirect()->route('reports/custom')
            ->with('success', trans('admin/reports/message.delete.success'));
    }
}
