<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomConsumableReportRequest;
use App\Models\Actionlog;
use App\Models\Consumable;
use App\Models\ReportTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Csv\EscapeFormula;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Mirror of CustomComponentReportController for consumables. Same
 * form / query / streaming shape. Two intentional differences:
 *
 *   - No `serial` column: consumables are bulk items without a
 *     per-unit serial. Component's serial column doesn't apply here.
 *   - `include_assignments` walks `$consumable->users` rather than
 *     `$component->assets`. Each pivot row on consumables_users is
 *     one assigned unit (consumables lack the pivot->assigned_qty
 *     Components use). Same "write $qty rows, fill blanks past
 *     users->count()" pattern otherwise.
 */
class CustomConsumableReportController extends Controller
{
    public function show(Request $request)
    {
        $this->authorize('reports.view');

        $report_templates = ReportTemplate::where('type', 'consumable')->orderBy('name')->get();

        // The view needs a template to render correctly, even if it is empty...
        $template = new ReportTemplate;

        // Set the report's input values in the cases we were redirected back
        // with validation errors so the report is populated as expected.
        if ($request->old()) {
            $template->name = $request->old('name');
            $template->options = $request->old();
        }

        return view('reports.custom.consumable', [
            'report_templates' => $report_templates,
            'template' => $template,
        ]);
    }

    public function run(CustomConsumableReportRequest $request)
    {
        $this->authorize('reports.view');

        ini_set('max_execution_time', config('app.report_time_limit', 12000)); // seconds; default 12000 (200 min)

        $this->disableDebugbar();

        return new StreamedResponse(function () use ($request) {
            Log::debug('Starting streamed response for custom consumable report');
            Log::debug('CSV escaping is set to: '.config('app.escape_formulas'));

            // Open output stream
            $handle = fopen('php://output', 'w');
            stream_set_timeout($handle, 2000);

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            $mappings = $this->buildMappings();

            $headerRow = $this->generateHeaders($request, $mappings);

            Log::debug('Adding headers: '.$this->getExecutionTime());
            fputcsv($handle, $headerRow, escape: '\\', eol: "\r\n");
            Log::debug('Added headers: '.$this->getExecutionTime());

            $this->buildQuery($request)->orderBy('consumables.id', 'ASC')->chunk(500, function ($consumables) use ($handle, $request, $mappings) {
                Log::debug('Walking results: '.$this->getExecutionTime());

                $count = 0;

                $formatter = new EscapeFormula('`');

                /** @var Consumable $consumable */
                foreach ($consumables as $consumable) {
                    $rowsToWrite = $request->filled('include_assignments') ? $consumable->qty : 1;

                    for ($i = 0; $i < $rowsToWrite; $i++) {
                        $count++;
                        $row = [];

                        foreach ($mappings as $key => $mapping) {
                            if ($request->filled($key)) {
                                array_push($row, ...($mapping['values'])($consumable, $i));
                            }
                        }

                        // CSV_ESCAPE_FORMULAS is set to false in the .env
                        if (config('app.escape_formulas') === false) {
                            fputcsv($handle, $row, escape: '\\', eol: "\r\n");

                            // CSV_ESCAPE_FORMULAS is set to true or is not set in the .env
                        } else {
                            fputcsv($handle, $formatter->escapeRecord($row), escape: '\\', eol: "\r\n");
                        }

                        Log::debug('-- Record '.$count.' Consumable ID:'.$consumable->id.' in '.$this->getExecutionTime());
                    }
                }
            });

            // Close the output stream
            fclose($handle);
            $executionTime = $this->getExecutionTime();
            Log::debug('-- SCRIPT COMPLETED IN '.$executionTime);

        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="custom-consumables-report-'.date('Y-m-d-his').'.csv"',
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
                'values' => fn ($consumable, $i) => [$consumable->id],
            ],
            'company' => [
                'headers' => [trans('general.company')],
                'values' => fn ($consumable, $i) => [$consumable->company->name ?? ''],
            ],
            'category' => [
                'headers' => [trans('general.category')],
                'values' => fn ($consumable, $i) => [$consumable->category->name ?? ''],
            ],
            'consumable_name' => [
                'headers' => [trans('admin/consumables/general.consumable_name')],
                'values' => fn ($consumable, $i) => [$consumable->name],
            ],
            'manufacturer' => [
                'headers' => [trans('general.manufacturer')],
                'values' => fn ($consumable, $i) => [$consumable->manufacturer->name ?? ''],
            ],
            'model' => [
                'headers' => [trans('general.model_no')],
                'values' => fn ($consumable, $i) => [$consumable->model_number],
            ],
            'include_assignments' => [
                'headers' => [
                    trans('general.user'),
                    trans('admin/reports/general.custom_export.asset_company'),
                    trans('admin/hardware/form.checkout_date'),
                    trans('general.created_by'),
                ],
                // Hoist into a full closure so the checked-out-by lookup
                // can go through withTrashed()->find (soft-deleted admins
                // still resolve to a name) with an explicit truthy-check
                // rather than a nested nullsafe chain phpstan warns on.
                'values' => function ($consumable, $i) {
                    if (! isset($consumable->users[$i])) {
                        return array_fill(0, 4, '');
                    }
                    $user = $consumable->users[$i];
                    $creator = $user->pivot->created_by
                        ? \App\Models\User::withTrashed()->find($user->pivot->created_by)
                        : null;

                    return [
                        $user->display_name ?? '',
                        $user->companies
                            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                            ->pluck('name')
                            ->join(' | '),
                        $user->pivot->created_at ?? '',
                        $creator ? ($creator->display_name ?? '') : '',
                    ];
                },
            ],
            'purchase_date' => [
                'headers' => [trans('general.purchase_date')],
                'values' => fn ($consumable, $i) => [
                    ($d = $consumable->lastOrderDefaults()['purchase_date'] ?? null) ? Carbon::make($d)->format('Y-m-d') : '',
                ],
            ],
            'quantity' => [
                'headers' => [trans('general.quantity')],
                'values' => fn ($consumable, $i) => [$consumable->qty],
            ],
            'min_amount' => [
                'headers' => [trans('general.min_amt')],
                'values' => fn ($consumable, $i) => [$consumable->min_amt],
            ],
            'unit_cost' => [
                'headers' => [trans('general.unit_cost')],
                'values' => fn ($consumable, $i) => [$consumable->lastOrderDefaults()['unit_cost'] ?? ''],
            ],
            'order' => [
                'headers' => [trans('admin/hardware/form.order')],
                // See CustomComponentReportController's order mapping for
                // the "multi-order comma list" rationale.
                'values' => fn ($consumable, $i) => [
                    $consumable->orders
                        ->pluck('order_number')
                        ->filter()
                        ->unique()
                        ->implode(', '),
                ],
            ],
            'supplier' => [
                'headers' => [trans('general.supplier')],
                'values' => fn ($consumable, $i) => [$consumable->lastAcquisitionSupplier()?->name],
            ],
            'location' => [
                'headers' => [trans('general.location')],
                'values' => fn ($consumable, $i) => [$consumable->location->name ?? ''],
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
                'values' => fn ($consumable, $i) => [
                    $consumable->location->address ?? '',
                    $consumable->location->address2 ?? '',
                    $consumable->location->city ?? '',
                    $consumable->location->state ?? '',
                    $consumable->location->country ?? '',
                    $consumable->location->zip ?? '',
                ],
            ],
            'created_at' => [
                'headers' => [trans('general.created_at')],
                'values' => fn ($consumable, $i) => [$consumable->created_at],
            ],
            'updated_at' => [
                'headers' => [trans('general.updated_at')],
                'values' => fn ($consumable, $i) => [$consumable->updated_at],
            ],
            'deleted_at' => [
                'headers' => [trans('general.deleted')],
                'values' => fn ($consumable, $i) => [$consumable->deleted_at ?? ''],
            ],
            'notes' => [
                'headers' => [trans('general.notes')],
                'values' => fn ($consumable, $i) => [$consumable->notes],
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
        $query = Consumable::select('consumables.*')
            ->with([
                'category',
                'company',
                'location',
                'manufacturer',
                'defaultSupplier',
                // See CustomComponentReportController::buildQuery for the
                // rationale on eager-loading orders + orderItems.order.supplier.
                'orders',
                'orderItems.order.supplier',
            ]);

        $request->whenFilled('include_assignments', fn () => $query->with('users.companies'));

        $query = $this->appendLocalConstraints($query, $request, [
            'by_model_number' => 'consumables.model_number',
            'by_name' => 'consumables.name',
        ]);

        // See CustomComponentReportController for the by_order_number
        // whereHas('orders') rationale.
        $request->whenFilled('by_order_number', function ($value) use ($query) {
            $query->whereHas('orders', function ($q) use ($value) {
                $q->where('orders.order_number', $value);
            });
        });

        $query = $this->appendForeignConstraints($query, $request, [
            'by_category_id' => 'consumables.category_id',
            'by_company_id' => 'consumables.company_id',
            'by_location_id' => 'consumables.location_id',
            'by_manufacturer_id' => 'consumables.manufacturer_id',
            'by_supplier_id' => 'consumables.default_supplier_id',
        ]);

        $query = $this->appendNumericalBoundaries($query, $request, [
            // See CustomComponentReportController for the unit_cost
            // parent-vs-per-order rationale.
            'quantity' => 'qty',
            'min_quantity' => 'min_amt',
            'unit_cost' => 'default_purchase_cost',
        ]);

        $query = $this->appendDateWindowBoundaries($query, $request, [
            'created' => 'created_at',
            'last_updated' => 'updated_at',
        ]);

        // See CustomComponentReportController for the purchase_start /
        // purchase_end Orders-EXISTS subquery rationale.
        if ($request->filled('purchase_start') || $request->filled('purchase_end')) {
            $start = $request->filled('purchase_start')
                ? Carbon::parse($request->input('purchase_start'))->startOfDay()
                : null;
            $end = $request->filled('purchase_end')
                ? Carbon::parse($request->input('purchase_end'))->endOfDay()
                : null;

            $query->whereExists(function ($sub) use ($start, $end) {
                $sub->from('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.item_id', 'consumables.id')
                    ->where('order_items.item_type', Consumable::class);
                if ($start) {
                    $sub->where('orders.purchase_date', '>=', $start);
                }
                if ($end) {
                    $sub->where('orders.purchase_date', '<=', $end);
                }
            });
        }

        $query = $this->appendBeforeDateBoundaries($query, $request, [
            'last_updated_before' => 'updated_at',
        ]);

        if ($request->filled('checkout_date_start') && $request->filled('checkout_date_end')) {
            $checkout_start = Carbon::parse($request->input('checkout_date_start'))->startOfDay();
            $checkout_end = Carbon::parse($request->input('checkout_date_end', now()))->endOfDay();

            $consumableIdsWithinCheckoutRange = Actionlog::where('action_type', '=', 'checkout')
                ->where('item_type', Consumable::class)
                ->whereBetween('action_date', [$checkout_start, $checkout_end])
                ->pluck('item_id');

            $query->whereIn('consumables.id', $consumableIdsWithinCheckoutRange);
        }

        if ($request->input('deleted_consumables') === 'include_deleted') {
            $query->withTrashed();
        }

        if ($request->input('deleted_consumables') === 'only_deleted') {
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
                $query->whereBetween("consumables.{$column}", [
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

                $query->whereBetween("consumables.{$column}", [$start, $end]);
            }
        }

        return $query;
    }

    private function appendBeforeDateBoundaries(Builder $query, Request $request, array $mapping): Builder
    {
        // Column is intentionally unread here — the only current caller
        // asks about updated_at and the WHERE is hardcoded to match.
        // Kept as a mapping (rather than a bare list of form keys) so
        // future callers can widen it to other columns without changing
        // the caller shape.
        foreach (array_keys($mapping) as $formKey) {
            if ($request->filled($formKey)) {
                $date = Carbon::parse(today()->subDays($request->input($formKey)));
                $query->where('consumables.updated_at', '<', $date);
            }
        }

        return $query;
    }
}
