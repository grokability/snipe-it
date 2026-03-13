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
            'options' => $request->except(['_token', 'name']),
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

        return view('reports/custom', [
            'customfields' => CustomField::get(),
            'template' => $reportTemplate,
        ]);
    }

    public function update(Request $request, ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorize('reports.view');

        // Ignore "options" rules since data does not come in under that key...
        $validated = $request->validate(Arr::except((new ReportTemplate)->getRules(), 'options'));

        $reportTemplate->update([
            'name' => $validated['name'],
            'options' => $request->except(['_token', 'name']),
        ]);

        session()->flash('success', trans('admin/reports/message.update.success'));

        return redirect()->route('report-templates.show', $reportTemplate->id);
    }

    public function destroy(ReportTemplate $reportTemplate): RedirectResponse
    {
        $this->authorize('reports.view');

        $reportTemplate->delete();

        return redirect()->route('reports/custom')
            ->with('success', trans('admin/reports/message.delete.success'));
    }
}
