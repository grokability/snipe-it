<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomComponentReportRequest;
use App\Models\Actionlog;
use App\Models\Component;
use App\Models\ReportTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\EscapeFormula;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomComponentReportController extends Controller
{
    public function show(Request $request)
    {
        $this->authorize('reports.view');

        $report_templates = ReportTemplate::where('type', 'component')->orderBy('name')->get();

        // The view needs a template to render correctly, even if it is empty...
        $template = new ReportTemplate;

        // Set the report's input values in the cases we were redirected back
        // with validation errors so the report is populated as expected.
        if ($request->old()) {
            $template->name = $request->old('name');
            $template->options = $request->old();
        }

        return view('reports.custom.component', [
            'report_templates' => $report_templates,
            'template' => $template,
        ]);
    }

    public function run(CustomComponentReportRequest $request)
    {
        $this->authorize('reports.view');

        ini_set('max_execution_time', env('REPORT_TIME_LIMIT', 12000)); // 12000 seconds = 200 minutes

        $this->disableDebugbar();

        return new StreamedResponse(function () use ($request) {
            Log::debug('Starting streamed response for custom component report');
            Log::debug('CSV escaping is set to: '.config('app.escape_formulas'));

            // Open output stream
            $handle = fopen('php://output', 'w');
            stream_set_timeout($handle, 2000);

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            $mappings = $this->buildMappings();

            $headerRow = $this->generateHeaders($request, $mappings);

            Log::debug('Adding headers: '.$this->getExecutionTime());
            fputcsv($handle, $headerRow);
            Log::debug('Added headers: '.$this->getExecutionTime());

            $this->buildQuery($request)->orderBy('components.id', 'ASC')->chunk(500, function ($components) use ($handle, $request, $mappings) {
                Log::debug('Walking results: '.$this->getExecutionTime());

                $count = 0;

                $formatter = new EscapeFormula('`');

                /** @var Component $component */
                foreach ($components as $component) {
                    $rowsToWrite = $request->filled('include_assignments') ? $component->qty : 1;

                    for ($i = 0; $i < $rowsToWrite; $i++) {
                        $count++;
                        $row = [];

                        foreach ($mappings as $key => $mapping) {
                            if ($request->filled($key)) {
                                array_push($row, ...($mapping['values'])($component, $i));
                            }
                        }

                        // CSV_ESCAPE_FORMULAS is set to false in the .env
                        if (config('app.escape_formulas') === false) {
                            fputcsv($handle, $row);

                            // CSV_ESCAPE_FORMULAS is set to true or is not set in the .env
                        } else {
                            fputcsv($handle, $formatter->escapeRecord($row));
                        }

                        Log::debug('-- Record '.$count.' Component ID:'.$component->id.' in '.$this->getExecutionTime());
                    }
                }
            });

            // Close the output stream
            fclose($handle);
            $executionTime = $this->getExecutionTime();
            Log::debug('-- SCRIPT COMPLETED IN '.$executionTime);

        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="custom-components-report-'.date('Y-m-d-his').'.csv"',
        ]);
    }

    private function getExecutionTime(): mixed
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Each key corresponds to a form field name submitted by the front end.
     * When that field is present in the request, its headers are added to the CSV
     * and its values closure is called to produce the row data for that column(s).
     */
    private function buildMappings(): array
    {
        return [
            'id' => [
                'headers' => [trans('general.id')],
                'values' => fn ($component, $i) => [$component->id],
            ],
            'company' => [
                'headers' => [trans('general.company')],
                'values' => fn ($component, $i) => [$component->company->name ?? ''],
            ],
            'category' => [
                'headers' => [trans('general.category')],
                'values' => fn ($component, $i) => [$component->category->name ?? ''],
            ],
            'component_name' => [
                'headers' => [trans('admin/components/general.component_name')],
                'values' => fn ($component, $i) => [$component->name],
            ],
            'manufacturer' => [
                'headers' => [trans('general.manufacturer')],
                'values' => fn ($component, $i) => [$component->manufacturer->name ?? ''],
            ],
            'model' => [
                'headers' => [trans('general.model_no')],
                'values' => fn ($component, $i) => [$component->model_number],
            ],
            'serial' => [
                'headers' => [trans('general.serial_number')],
                'values' => fn ($component, $i) => [$component->serial],
            ],
            'include_assignments' => [
                'headers' => [
                    trans('admin/hardware/form.name'),
                    trans('admin/hardware/form.tag'),
                    trans('admin/reports/general.custom_export.asset_company'),
                    trans('admin/reports/general.custom_export.asset_serial'),
                    trans('admin/hardware/form.checkout_date'),
                    trans('general.assigned_quantity'),
                ],
                'values' => fn ($component, $i) => isset($component->assets[$i]) ? [
                    $component->assets[$i]->name ?? '',
                    $component->assets[$i]->asset_tag ?? '',
                    $component->assets[$i]->company->name ?? '',
                    $component->assets[$i]->serial,
                    $component->assets[$i]->pivot->created_at ?? '',
                    $component->assets[$i]->pivot->assigned_qty,
                ] : array_fill(0, 6, ''),
            ],
            'purchase_date' => [
                'headers' => [trans('general.purchase_date')],
                'values' => fn ($component, $i) => [$component->purchase_date ? Carbon::make($component->purchase_date)->format('Y-m-d') : ''],
            ],
            'quantity' => [
                'headers' => [trans('general.quantity')],
                'values' => fn ($component, $i) => [$component->qty],
            ],
            'min_amount' => [
                'headers' => [trans('general.min_amt')],
                'values' => fn ($component, $i) => [$component->min_amt],
            ],
            'unit_cost' => [
                'headers' => [trans('general.unit_cost')],
                'values' => fn ($component, $i) => [$component->purchase_cost],
            ],
            'order' => [
                'headers' => [trans('admin/hardware/form.order')],
                'values' => fn ($component, $i) => [$component->order_number],
            ],
            'supplier' => [
                'headers' => [trans('general.supplier')],
                'values' => fn ($component, $i) => [$component->supplier?->name],
            ],
            'location' => [
                'headers' => [trans('general.location')],
                'values' => fn ($component, $i) => [$component->location->name ?? ''],
            ],
            'location_address' => [
                'headers' => [
                    trans('general.address'),
                    trans('general.address'),
                    trans('general.city'),
                    trans('general.state'),
                    trans('general.country'),
                    trans('general.zip'),
                ],
                'values' => fn ($component, $i) => [
                    $component->location->address ?? '',
                    $component->location->address2 ?? '',
                    $component->location->city ?? '',
                    $component->location->state ?? '',
                    $component->location->country ?? '',
                    $component->location->zip ?? '',
                ],
            ],
            'created_at' => [
                'headers' => [trans('general.created_at')],
                'values' => fn ($component, $i) => [$component->created_at],
            ],
            'updated_at' => [
                'headers' => [trans('general.updated_at')],
                'values' => fn ($component, $i) => [$component->updated_at],
            ],
            'deleted_at' => [
                'headers' => [trans('general.deleted')],
                'values' => fn ($component, $i) => [$component->deleted_at ?? ''],
            ],
            'notes' => [
                'headers' => [trans('general.notes')],
                'values' => fn ($component, $i) => [$component->notes],
            ],
        ];
    }

    private function generateHeaders(Request $request, array $mappings): array
    {
        $headers = [];

        foreach ($mappings as $key => $mapping) {
            if ($request->filled($key)) {
                array_push($headers, ...$mapping['headers']);
            }
        }

        return $headers;
    }

    private function buildQuery(Request $request): Builder
    {
        $query = Component::select('components.*')
            ->with([
                'category',
                'company',
                'location',
                'manufacturer',
                'supplier',
            ]);

        $request->whenFilled('include_assignments', fn () => $query->with('assets.company'));

        $query = $this->appendLocalConstraints($query, $request, [
            'by_model_number' => 'components.model_number',
            'by_name' => 'components.name',
            'by_order_number' => 'components.order_number',
        ]);

        $query = $this->appendForeignConstraints($query, $request, [
            'by_category_id' => 'components.category_id',
            'by_company_id' => 'components.company_id',
            'by_location_id' => 'components.location_id',
            'by_manufacturer_id' => 'components.manufacturer_id',
            'by_supplier_id' => 'components.supplier_id',
        ]);

        $query = $this->appendNumericalBoundaries($query, $request, [
            // formKey => column
            // _start and _end will be appended to the key
            // ie: quantity_start|quantity_end => qty
            'quantity' => 'qty',
            'min_quantity' => 'min_amt',
            'unit_cost' => 'purchase_cost',
        ]);

        $query = $this->appendDateWindowBoundaries($query, $request, [
            // formKey => column
            // _start and _end will be appended to the key
            // ie: purchase_start|purchase_end => purchase_date
            'purchase' => 'purchase_date',
            'created' => 'created_at',
            'last_updated' => 'updated_at',
        ]);

        $query = $this->appendBeforeDateBoundaries($query, $request, [
            // formKey => column
            'last_updated_before' => 'updated_at',
        ]);

        if ($request->filled('checkout_date_start') && $request->filled('checkout_date_end')) {
            $checkout_start = Carbon::parse($request->input('checkout_date_start'))->startOfDay();
            $checkout_end = Carbon::parse($request->input('checkout_date_end', now()))->endOfDay();

            $componentIdsWithinCheckoutRange = Actionlog::where('action_type', '=', 'checkout')
                ->where('item_type', Component::class)
                ->whereBetween('action_date', [$checkout_start, $checkout_end])
                ->pluck('item_id');

            $query->whereIn('components.id', $componentIdsWithinCheckoutRange);
        }

        if ($request->input('deleted_components') === 'include_deleted') {
            $query->withTrashed();
        }

        if ($request->input('deleted_components') === 'only_deleted') {
            $query->onlyTrashed();
        }

        return $query;
    }

    private function appendLocalConstraints(Builder $query, Request $request, array $constraints): Builder
    {
        foreach ($constraints as $formKey => $column) {
            if ($request->filled($formKey)) {
                $query->where($column, $request->input($formKey));
            }
        }

        return $query;
    }

    private function appendForeignConstraints(Builder $query, Request $request, array $constraints): Builder
    {
        foreach ($constraints as $formKey => $column) {
            if ($request->filled($formKey)) {
                $query->whereIn($column, $request->input($formKey));
            }
        }

        return $query;
    }

    private function appendNumericalBoundaries(Builder $query, Request $request, array $mapping): Builder
    {
        foreach ($mapping as $formKey => $column) {
            if ($request->filled(["{$formKey}_start", "{$formKey}_end"])) {
                $query->whereBetween("components.{$column}", [
                    $request->input("{$formKey}_start"),
                    $request->input("{$formKey}_end"),
                ]);
            }
        }

        return $query;
    }

    private function appendDateWindowBoundaries(Builder $query, Request $request, array $mapping): Builder
    {
        foreach ($mapping as $formKey => $column) {
            if (($request->filled("{$formKey}_start")) && ($request->filled("{$formKey}_end"))) {
                $start = Carbon::parse($request->input("{$formKey}_start"))->startOfDay();
                $end = Carbon::parse($request->input("{$formKey}_end"))->endOfDay();

                $query->whereBetween("components.{$column}", [$start, $end]);
            }
        }

        return $query;
    }

    private function appendBeforeDateBoundaries(Builder $query, Request $request, array $mapping): Builder
    {
        foreach ($mapping as $formKey => $column) {
            if ($request->filled($formKey)) {
                $date = Carbon::parse(today()->subDays($request->input($formKey)));
                $query->where('components.updated_at', '<', $date);
            }
        }

        return $query;
    }
}
