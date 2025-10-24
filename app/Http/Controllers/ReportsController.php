<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Mail\CheckoutAssetMail;
use App\Models\Accessory;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Maintenance;
use App\Models\CheckoutAcceptance;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Depreciation;
use App\Models\License;
use App\Models\ReportTemplate;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Contracts\View\View;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\StreamedResponse;
use League\Csv\EscapeFormula;
use App\Http\Requests\CustomAssetReportRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

/**
 * This controller handles all actions related to Reports for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ReportsController extends Controller
{
    /**
     * Checks for correct permissions
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Returns a view that displays the accessories report.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @return \Illuminate\Contracts\View\View
    */
    public function getAccessoryReport() : View
    {
        $this->authorize('reports.view');

        return view('reports/accessories');
    }

    /**
    * Exports the accessories to CSV
    *
    * @deprecated Server-side exports have been replaced by datatables export since v2.
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see ManufacturersController::getDatatable() method that generates the JSON response
    * @since [v1.0]
    * @return \Illuminate\Http\Response
    */
    public function exportAccessoryReport() : Response
    {
        $this->authorize('reports.view');
        $accessories = Accessory::orderBy('created_at', 'DESC')->get();

        $rows = [];
        $header = [
            trans('admin/accessories/table.title'),
            trans('admin/accessories/general.accessory_category'),
            trans('admin/accessories/general.total'),
            trans('admin/accessories/general.remaining'),
        ];
        $header = array_map('trim', $header);
        $rows[] = implode(', ', $header);

        // Row per accessory
        foreach ($accessories as $accessory) {
            $row = [];
            $row[] = e($accessory->accessory_name);
            $row[] = e($accessory->accessory_category);
            $row[] = e($accessory->total);
            $row[] = e($accessory->remaining);

            $rows[] = implode(',', $row);
        }

        $csv = implode("\n", $rows);
        $response = response()->make($csv, 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-disposition', 'attachment;filename=report.csv');

        return $response;
    }

    /**
    * Show depreciation report for assets.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    */
    public function getDeprecationReport() : View
    {
        $this->authorize('reports.view');
        $depreciations = Depreciation::get();
        return view('reports/depreciation')->with('depreciations',$depreciations);
    }

    /**
    * Exports the depreciations to CSV
    *
    * @deprecated Server-side exports have been replaced by datatables export since v2.
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    */
    public function exportDeprecationReport() : Response
    {
        $this->authorize('reports.view');
        // Grab all the assets
        $assets = Asset::with('model', 'assignedTo', 'assetstatus', 'defaultLoc', 'assetlog')
                       ->orderBy('created_at', 'DESC')->get();

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
        $csv->setOutputBOM(Reader::BOM_UTF16_BE);

        $rows = [];

        // Create the header row
        $header = [
            trans('admin/hardware/table.asset_tag'),
            trans('admin/hardware/table.title'),
            trans('admin/hardware/table.serial'),
            trans('admin/hardware/table.checkoutto'),
            trans('admin/hardware/table.location'),
            trans('admin/hardware/table.purchase_date'),
            trans('admin/hardware/table.purchase_cost'),
            trans('admin/hardware/table.book_value'),
            trans('admin/hardware/table.diff'),
        ];

        //we insert the CSV header
        $csv->insertOne($header);

        // Create a row per asset
        foreach ($assets as $asset) {
            $row = [];
            $row[] = e($asset->asset_tag);
            $row[] = e($asset->name);
            $row[] = e($asset->serial);

            if ($target = $asset->assignedTo) {
                $row[] = e($target->display_name);
            } else {
                $row[] = ''; // Empty string if unassigned
            }

            if (($asset->assigned_to > 0) && ($location = $asset->location)) {
                if ($location->city) {
                    $row[] = e($location->city).', '.e($location->state);
                } elseif ($location->name) {
                    $row[] = e($location->name);
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = '';  // Empty string if location is not set
            }

            if ($asset->location) {
                $currency = e($asset->location->currency);
            } else {
                $currency = e(Setting::getSettings()->default_currency);
            }

            $row[] = Helper::getFormattedDateObject($asset->purchase_date, 'date', false);
            $row[] = $currency.Helper::formatCurrencyOutput($asset->purchase_cost);
            $row[] = $currency.Helper::formatCurrencyOutput($asset->getDepreciatedValue());
            $row[] = $currency.Helper::formatCurrencyOutput(($asset->purchase_cost - $asset->getDepreciatedValue()));
            $csv->insertOne($row);
        }

        $csv->output('depreciation-report-'.date('Y-m-d').'.csv');
        die;
    }


    /**
     * Displays audit report.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function audit() : View
    {
        $this->authorize('reports.view');
        return view('reports/audit');
    }


    /**
    * Displays activity report.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    */
    public function getActivityReport() : View
    {
        $this->authorize('reports.view');

        return view('reports/activity');
    }

    /**
     * Exports the activity report to CSV
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0.7]
     */
    public function postActivityReport(Request $request) : StreamedResponse
    {
        ini_set('max_execution_time', 12000);
        $this->authorize('reports.view');

        \Debugbar::disable();
        $response = new StreamedResponse(function () {
            Log::debug('Starting streamed response');

            // Open output stream
            $handle = fopen('php://output', 'w');
            stream_set_timeout($handle, 2000);

            $header = [
                trans('general.date'),
                trans('general.created_by'),
                trans('general.action'),
                trans('general.type'),
                trans('general.item'),
                trans('general.license_serial'),
                trans('general.model_name'),
                trans('general.model_no'),
                'To',
                trans('general.notes'),
                trans('admin/settings/general.login_ip'),
                trans('admin/settings/general.login_user_agent'),
                trans('general.action_source'),
                'Changed',

            ];
            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('Starting headers: '.$executionTime);
            fputcsv($handle, $header);
            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('Added headers: '.$executionTime);

            $actionlogs = Actionlog::with('item', 'user', 'target', 'location', 'adminuser')
                ->orderBy('created_at', 'DESC')
                ->chunk(500, function ($actionlogs) use ($handle) {
                    $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                Log::debug('Walking results: '.$executionTime);
                $count = 0;

                foreach ($actionlogs as $actionlog) {
                    $count++;
                    $target_name = '';

                    if ($actionlog->target) {
                        $target_name = $actionlog->target->display_name;
                    }

                    if ($actionlog->item){
                        $item_name = e($actionlog->item->display_name);
                    } else {
                        $item_name = '';
                    }

                    $row = [
                        $actionlog->created_at,
                        ($actionlog->adminuser) ? $actionlog->adminuser->display_name : '',
                        $actionlog->present()->actionType(),
                        e($actionlog->itemType()),
                        ($actionlog->itemType() == 'user') ? $actionlog->filename : $item_name,
                        ($actionlog->item) ? $actionlog->item->serial : null,
                        (($actionlog->item) && ($actionlog->item->model)) ? htmlspecialchars($actionlog->item->model->name, ENT_NOQUOTES) : null,
                        (($actionlog->item) && ($actionlog->item->model))  ? $actionlog->item->model->model_number : null,
                        $target_name,
                        ($actionlog->note) ? e($actionlog->note) : '',
                        $actionlog->remote_ip,
                        $actionlog->user_agent,
                        $actionlog->action_source,
                        $actionlog->log_meta,
                    ];
                    fputcsv($handle, $row);
                }
            });

            // Close the output stream
            fclose($handle);
            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('-- SCRIPT COMPLETED IN '.$executionTime);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-report-'.date('Y-m-d-his').'.csv"',
        ]);


        return $response;
    }


    /**
     * Displays license report
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function getLicenseReport() : View
    {
        $this->authorize('reports.view');
        $licenses = License::with('depreciation')->orderBy('created_at', 'DESC')
                           ->with('company')
                           ->get();

        return view('reports/licenses', compact('licenses'));
    }

    /**
    * Exports the licenses to CSV
    *
    * @deprecated Server-side exports have been replaced by datatables export since v2.
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    */
    public function exportLicenseReport() : Response
    {
        $this->authorize('reports.view');
        $licenses = License::orderBy('created_at', 'DESC')->get();

        $rows = [];
        $header = [
            trans('admin/licenses/table.title'),
            trans('admin/licenses/table.serial'),
            trans('admin/licenses/form.seats'),
            trans('admin/licenses/form.remaining_seats'),
            trans('admin/licenses/form.expiration'),
            trans('general.purchase_date'),
            trans('general.depreciation'),
            trans('general.purchase_cost'),
        ];

        $header = array_map('trim', $header);
        $rows[] = implode(', ', $header);

        // Row per license
        foreach ($licenses as $license) {
            $row = [];
            $row[] = e($license->name);
            $row[] = e($license->serial);
            $row[] = e($license->seats);
            $row[] = $license->remaincount();
            $row[] = $license->expiration_date;
            $row[] = $license->purchase_date;
            $row[] = ($license->depreciation != '') ? '' : e($license->depreciation->name);
            $row[] = '"'.Helper::formatCurrencyOutput($license->purchase_cost).'"';

            $rows[] = implode(',', $row);
        }


        $csv      = implode("\n", $rows);
        $response = response()->make($csv, 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-disposition', 'attachment;filename=report.csv');

        return $response;
    }

    /**
    * Returns a form that allows the user to generate a custom CSV report.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @see ReportsController::postCustomReport() method that generates the CSV
    * @since [v1.0]
    */
    public function getCustomReport(Request $request) : View
    {
        $this->authorize('reports.view');
        $customfields = CustomField::get();
        $report_templates = ReportTemplate::orderBy('name')->get();

        // The view needs a template to render correctly, even if it is empty...
        $template = new ReportTemplate;

        // Set the report's input values in the cases we were redirected back
        // with validation errors so the report is populated as expected.
        if ($request->old()) {
            $template->name = $request->old('name');
            $template->options = $request->old();
        }

        return view('reports/custom', [
            'customfields' => $customfields,
            'report_templates' => $report_templates,
            'template' => $template,
        ]);
    }

    /**
     * Exports the custom report to CSV
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ReportsController::getCustomReport() method that generates form view
     * @since [v1.0]
     */
    public function postCustom(CustomAssetReportRequest $request) : StreamedResponse
    {
        ini_set('max_execution_time', env('REPORT_TIME_LIMIT', 12000)); //12000 seconds = 200 minutes
        $this->authorize('reports.view');


        \Debugbar::disable();
        $customfields = CustomField::get();
        $response = new StreamedResponse(function () use ($customfields, $request) {
            Log::debug('Starting streamed response');
            Log::debug('CSV escaping is set to: '.config('app.escape_formulas'));

            // Open output stream
            $handle = fopen('php://output', 'w');
            stream_set_timeout($handle, 2000);

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            $header = [];

            if ($request->filled('id')) {
                $header[] = trans('general.id');
            }

            if ($request->filled('company')) {
                $header[] = trans('general.company');
            }

            if ($request->filled('asset_name')) {
                $header[] = trans('admin/hardware/form.name');
            }

            if ($request->filled('asset_tag')) {
                $header[] = trans('admin/hardware/table.asset_tag');
            }

            if ($request->filled('model')) {
                $header[] = trans('admin/hardware/form.model');
                $header[] = trans('general.model_no');
            }

            if ($request->filled('category')) {
                $header[] = trans('general.category');
            }

            if ($request->filled('manufacturer')) {
                $header[] = trans('admin/hardware/form.manufacturer');
            }

            if ($request->filled('serial')) {
                $header[] = trans('admin/hardware/table.serial');
            }
            if ($request->filled('purchase_date')) {
                $header[] = trans('admin/hardware/table.purchase_date');
            }

            if ($request->filled('purchase_cost')) {
                $header[] = trans('admin/hardware/table.purchase_cost');
            }

            if ($request->filled('eol')) {
                $header[] = trans('admin/hardware/table.eol');
            }

            if ($request->filled('warranty')) {
                $header[] = trans('admin/hardware/form.warranty');
                $header[] = trans('admin/hardware/form.warranty_expires');
            }

            if ($request->filled('depreciation')) {
                $header[] = trans('admin/hardware/table.book_value');
                $header[] = trans('admin/hardware/table.diff');
                $header[] = trans('admin/hardware/form.fully_depreciated');
            }

            if ($request->filled('order')) {
                $header[] = trans('admin/hardware/form.order');
            }

            if ($request->filled('supplier')) {
                $header[] = trans('general.supplier');
            }

            if ($request->filled('location')) {
                $header[] = trans('admin/hardware/table.location');
            }
            if ($request->filled('location_address')) {
                $header[] = trans('general.address');
                $header[] = trans('general.address');
                $header[] = trans('general.city');
                $header[] = trans('general.state');
                $header[] = trans('general.country');
                $header[] = trans('general.zip');
            }

            if ($request->filled('rtd_location')) {
                $header[] = trans('admin/hardware/form.default_location');
            }
            
            if ($request->filled('rtd_location_address')) {
                $header[] = trans('general.address');
                $header[] = trans('general.address');
                $header[] = trans('general.city');
                $header[] = trans('general.state');
                $header[] = trans('general.country');
                $header[] = trans('general.zip');
            }

            if ($request->filled('assigned_to')) {
                $header[] = trans('admin/hardware/table.checkoutto');
                $header[] = trans('general.type');
            }

            if ($request->filled('username')) {
                $header[] = 'Username';
            }

            if ($request->filled('employee_num')) {
                $header[] = 'Employee No.';
            }

            if ($request->filled('manager')) {
                $header[] = trans('admin/users/table.manager');
            }

            if ($request->filled('department')) {
                $header[] = trans('general.department');
            }

            if ($request->filled('title')) {
                $header[] = trans('admin/users/table.title');
            }

            if ($request->filled('phone')) {
                $header[] = trans('admin/users/table.phone');
            }

            if ($request->filled('user_address')) {
                $header[] = trans('admin/reports/general.custom_export.user_address');
            }

            if ($request->filled('user_city')) {
                $header[] = trans('admin/reports/general.custom_export.user_city');
            }

            if ($request->filled('user_state')) {
                $header[] = trans('admin/reports/general.custom_export.user_state');
            }

            if ($request->filled('user_country')) {
                $header[] = trans('admin/reports/general.custom_export.user_country');
            }

            if ($request->filled('user_zip')) {
                $header[] = trans('admin/reports/general.custom_export.user_zip');
            }

            if ($request->filled('status')) {
                $header[] = trans('general.status');
            }

            if ($request->filled('checkout_date')) {
                $header[] = trans('admin/hardware/table.checkout_date');
            }

            if ($request->filled('checkin_date')) {
                $header[] = trans('admin/hardware/table.last_checkin_date');
            }

            if ($request->filled('expected_checkin')) {
                $header[] = trans('admin/hardware/form.expected_checkin');
            }

            if ($request->filled('created_at')) {
                $header[] = trans('general.created_at');
            }

            if ($request->filled('updated_at')) {
                $header[] = trans('general.updated_at');
            }

            if ($request->filled('deleted_at')) {
                $header[] = trans('general.deleted');
            }

            if ($request->filled('last_audit_date')) {
                $header[] = trans('general.last_audit');
            }

            if ($request->filled('next_audit_date')) {
                $header[] = trans('general.next_audit_date');
            }

            if ($request->filled('notes')) {
                $header[] = trans('general.notes');
            }

            if ($request->filled('url')) {
                $header[] = trans('general.url');
            }


            foreach ($customfields as $customfield) {
                if ($request->input($customfield->db_column_name()) == '1') {
                    $header[] = $customfield->name;
                }
            }

            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('Starting headers: '.$executionTime);
            fputcsv($handle, $header);
            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('Added headers: '.$executionTime);

            $assets = Asset::select('assets.*')->with(
                'location', 'assetstatus', 'company', 'defaultLoc', 'assignedTo',
                'model.category', 'model.manufacturer', 'supplier');
            
            if ($request->filled('by_location_id')) {
                $assets->whereIn('assets.location_id', $request->input('by_location_id'));
            }

            if ($request->filled('by_rtd_location_id')) {
                $assets->whereIn('assets.rtd_location_id', $request->input('by_rtd_location_id'));
            }

            if ($request->filled('by_supplier_id')) {
                $assets->whereIn('assets.supplier_id', $request->input('by_supplier_id'));
            }

            if ($request->filled('by_company_id')) {
                $assets->whereIn('assets.company_id', $request->input('by_company_id'));
            }

            if ($request->filled('by_model_id')) {
                $assets->whereIn('assets.model_id', $request->input('by_model_id'));
            }

            if ($request->filled('by_category_id')) {
                $assets->InCategory($request->input('by_category_id'));
            }

            if ($request->filled('by_dept_id')) {
                $assets->CheckedOutToTargetInDepartment($request->input('by_dept_id'));
            }

            if ($request->filled('by_manufacturer_id')) {
                $assets->ByManufacturer($request->input('by_manufacturer_id'));
            }

            if ($request->filled('by_order_number')) {
                $assets->where('assets.order_number', $request->input('by_order_number'));
            }

            if ($request->filled('by_status_id')) {
                $assets->whereIn('assets.status_id', $request->input('by_status_id'));
            }

            if (($request->filled('purchase_start')) && ($request->filled('purchase_end'))) {
                $assets->whereBetween('assets.purchase_date', [$request->input('purchase_start'), $request->input('purchase_end')]);
            }

            if ($request->filled('purchase_cost_start')) {
                if ($request->filled('purchase_cost_end')) {
                    $assets->whereBetween('assets.purchase_cost', [$request->input('purchase_cost_start'), $request->input('purchase_cost_end')]);
                } else {
                    $assets->where('assets.purchase_cost', ">", $request->input('purchase_cost_start'));
                }
            }

            if (($request->filled('created_start')) && ($request->filled('created_end'))) {
                $created_start = Carbon::parse($request->input('created_start'))->startOfDay();
                $created_end = Carbon::parse($request->input('created_end'))->endOfDay();

                $assets->whereBetween('assets.created_at', [$created_start, $created_end]);
            }

            if (($request->filled('checkout_date_start')) && ($request->filled('checkout_date_end'))) {
                $checkout_start = Carbon::parse($request->input('checkout_date_start'))->startOfDay();
                $checkout_end = Carbon::parse($request->input('checkout_date_end',now()))->endOfDay();

                $actionlogassets = Actionlog::where('action_type','=', 'checkout')
                                              ->where('item_type', 'LIKE', '%Asset%',)
                                              ->whereBetween('action_date',[$checkout_start, $checkout_end])
                                                  ->pluck('item_id');

                $assets->whereIn('assets.id',$actionlogassets);
            }

            if (($request->filled('checkin_date_start'))) {
                $checkin_start = Carbon::parse($request->input('checkin_date_start'))->startOfDay();
                        // use today's date is `checkin_date_end` is not provided
                $checkin_end = Carbon::parse($request->input('checkin_date_end', now()))->endOfDay();

                $assets->whereBetween('assets.last_checkin', [$checkin_start, $checkin_end ]);
            }
            //last checkin is exporting, but currently is a date and not a datetime in the custom report ONLY.

            if (($request->filled('expected_checkin_start')) && ($request->filled('expected_checkin_end'))) {
                    $assets->whereBetween('assets.expected_checkin', [$request->input('expected_checkin_start'), $request->input('expected_checkin_end')]);
            }

            if (($request->filled('asset_eol_date_start')) && ($request->filled('asset_eol_date_end'))) {
                $assets->whereBetween('assets.asset_eol_date', [$request->input('asset_eol_date_start'), $request->input('asset_eol_date_end')]);
            }

            if (($request->filled('last_audit_start')) && ($request->filled('last_audit_end'))) {
                    $last_audit_start = Carbon::parse($request->input('last_audit_start'))->startOfDay();
                    $last_audit_end = Carbon::parse($request->input('last_audit_end'))->endOfDay();

                    $assets->whereBetween('assets.last_audit_date', [$last_audit_start, $last_audit_end]);
            }

            if (($request->filled('next_audit_start')) && ($request->filled('next_audit_end'))) {
                $assets->whereBetween('assets.next_audit_date', [$request->input('next_audit_start'), $request->input('next_audit_end')]);
            }

            if (($request->filled('last_updated_start')) && ($request->filled('last_updated_end'))) {
                $assets->whereBetween('assets.updated_at', [$request->input('last_updated_start'), $request->input('last_updated_end')]);
            }

            if ($request->filled('exclude_archived')) {
                $assets->notArchived();
            }
            if ($request->input('deleted_assets') == 'include_deleted') {
                $assets->withTrashed();
            }
            if ($request->input('deleted_assets') == 'only_deleted') {
                $assets->onlyTrashed();
            }

            Log::debug($assets->toSql());
            $assets->orderBy('assets.id', 'ASC')->chunk(500, function ($assets) use ($handle, $customfields, $request) {
            
                $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                Log::debug('Walking results: '.$executionTime);
                $count = 0;

                $formatter = new EscapeFormula("`");

                foreach ($assets as $asset) {
                    $count++;
                    $row = [];

                    if ($request->filled('id')) {
                        $row[] = ($asset->id) ? $asset->id : '';
                    }

                    if ($request->filled('company')) {
                        $row[] = ($asset->company) ? $asset->company->name : '';
                    }

                    if ($request->filled('asset_name')) {
                        $row[] = ($asset->name) ? $asset->name : '';
                    }

                    if ($request->filled('asset_tag')) {
                        $row[] = ($asset->asset_tag) ? $asset->asset_tag : '';
                    }

                    if ($request->filled('model')) {
                        $row[] = ($asset->model) ? $asset->model->name : '';
                        $row[] = ($asset->model) ? $asset->model->model_number : '';
                    }

                    if ($request->filled('category')) {
                        $row[] = (($asset->model) && ($asset->model->category)) ? $asset->model->category->name : '';
                    }

                    if ($request->filled('manufacturer')) {
                        $row[] = ($asset->model && $asset->model->manufacturer) ? $asset->model->manufacturer->name : '';
                    }

                    if ($request->filled('serial')) {
                        $row[] = ($asset->serial) ? $asset->serial : '';
                    }

                    if ($request->filled('purchase_date')) {
                        $row[] = ($asset->purchase_date) ? $asset->purchase_date : '';
                    }

                    if ($request->filled('purchase_cost')) {
                        $row[] = ($asset->purchase_cost) ? Helper::formatCurrencyOutput($asset->purchase_cost) : '';
                    }

                    if ($request->filled('eol')) {
                        $row[] = ($asset->purchase_date != '') ? $asset->asset_eol_date : '';
                    }

                    if ($request->filled('warranty')) {
                        $row[] = ($asset->warranty_months) ? $asset->warranty_months : '';
                        $row[] = $asset->present()->warranty_expires();
                    }

                    if ($request->filled('depreciation')) {
                        $depreciation = $asset->getDepreciatedValue();
                        $diff = ($asset->purchase_cost - $depreciation);
                        $row[] = Helper::formatCurrencyOutput($depreciation);
                        $row[] = Helper::formatCurrencyOutput($diff);
                        $row[] = (($asset->depreciation) && ($asset->depreciated_date())) ? $asset->depreciated_date()->format('Y-m-d') : '';
                    }

                    if ($request->filled('order')) {
                        $row[] = ($asset->order_number) ? $asset->order_number : '';
                    }

                    if ($request->filled('supplier')) {
                        $row[] = ($asset->supplier) ? $asset->supplier->name : '';
                    }
                    
                    if ($request->filled('location')) {
                        $row[] = ($asset->location) ? $asset->location->display_name : '';
                    }

                    if ($request->filled('location_address')) {
                        $row[] = ($asset->location) ? $asset->location->address : '';
                        $row[] = ($asset->location) ? $asset->location->address2 : '';
                        $row[] = ($asset->location) ? $asset->location->city : '';
                        $row[] = ($asset->location) ? $asset->location->state : '';
                        $row[] = ($asset->location) ? $asset->location->country : '';
                        $row[] = ($asset->location) ? $asset->location->zip : '';
                    }

                    if ($request->filled('rtd_location')) {
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->display_name : '';
                    }

                    if ($request->filled('rtd_location_address')) {
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->address : '';
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->address2 : '';
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->city : '';
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->state : '';
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->country : '';
                        $row[] = ($asset->defaultLoc) ? $asset->defaultLoc->zip : '';
                    }

                    if ($request->filled('assigned_to')) {
                        $row[] = ($asset->assigned) ? $asset->assigned->display_name : '';
                        $row[] = ($asset->assigned) ? $asset->assignedType() : '';
                    }

                    if ($request->filled('username')) {
                        // Only works if we're checked out to a user, not anything else.
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->username : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('employee_num')) {
                        // Only works if we're checked out to a user, not anything else.
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->employee_num : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('manager')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = (($asset->assignedto) && ($asset->assignedto->manager)) ? $asset->assignedto->manager->present()->fullName : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('department')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = (($asset->assignedto) && ($asset->assignedto->department)) ? $asset->assignedto->department->name : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('title')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->jobtitle : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('phone')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->phone : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('user_address')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->address : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('user_city')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->city : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('user_state')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->state : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('user_country')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->country : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('user_zip')) {
                        if ($asset->checkedOutToUser()) {
                            $row[] = ($asset->assignedto) ? $asset->assignedto->zip : '';
                        } else {
                            $row[] = ''; // Empty string if unassigned
                        }
                    }

                    if ($request->filled('status')) {
                        $row[] = ($asset->assetstatus) ? $asset->assetstatus->name.' ('.$asset->present()->statusMeta.')' : '';
                    }

                    if ($request->filled('checkout_date')) {
                        $row[] = ($asset->last_checkout) ? $asset->last_checkout : '';
                    }

                    if ($request->filled('checkin_date')) {
                        $row[] = ($asset->last_checkin)
                            ? Carbon::parse($asset->last_checkin)->format('Y-m-d')
                            : '';
                    }

                    if ($request->filled('expected_checkin')) {
                        $row[] = ($asset->expected_checkin) ? $asset->expected_checkin : '';
                    }

                    if ($request->filled('created_at')) {
                        $row[] = ($asset->created_at) ? $asset->created_at : '';
                    }

                    if ($request->filled('updated_at')) {
                        $row[] = ($asset->updated_at) ? $asset->updated_at : '';
                    }

                    if ($request->filled('deleted_at')) {
                        $row[] = ($asset->deleted_at) ? $asset->deleted_at : '';
                    }

                    if ($request->filled('last_audit_date')) {
                        $row[] = ($asset->last_audit_date) ? $asset->last_audit_date : '';
                    }

                    if ($request->filled('next_audit_date')) {
                        $row[] = ($asset->next_audit_date) ? $asset->next_audit_date : '';
                    }

                    if ($request->filled('notes')) {
                        $row[] = ($asset->notes) ? $asset->notes : '';
                    }

                    if ($request->filled('url')) {
                        $row[] = config('app.url').'/hardware/'.$asset->id ;
                    }

                    foreach ($customfields as $customfield) {
                        $column_name = $customfield->db_column_name();
                        if ($request->filled($customfield->db_column_name())) {
                            $row[] = $asset->$column_name;
                        }
                    }

                    
                    // CSV_ESCAPE_FORMULAS is set to false in the .env
                    if (config('app.escape_formulas') === false) {
                        fputcsv($handle, $row);

                   // CSV_ESCAPE_FORMULAS is set to true or is not set in the .env
                    } else {
                        fputcsv($handle, $formatter->escapeRecord($row));
                    }

                    $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                    Log::debug('-- Record '.$count.' Asset ID:'.$asset->id.' in '.$executionTime);
                }
            });

            // Close the output stream
            fclose($handle);
            $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
            Log::debug('-- SCRIPT COMPLETED IN '.$executionTime);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="custom-assets-report-'.date('Y-m-d-his').'.csv"',
        ]);

        return $response;
    }

    /**
     * getImprovementsReport
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function getMaintenancesReport() : View
    {
        $this->authorize('reports.view');

        return view('reports.maintenances');
    }

    /**
     * exportImprovementsReport
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function exportMaintenancesReport() : Response
    {
        $this->authorize('reports.view');
        // Grab all the improvements
        $Maintenances = Maintenance::with('asset', 'supplier')
                                             ->orderBy('created_at', 'DESC')
                                             ->get();

        $rows = [];

        $header = [
            trans('admin/hardware/table.asset_tag'),
            trans('admin/maintenances/table.asset_name'),
            trans('general.supplier'),
            trans('admin/maintenances/form.asset_maintenance_type'),
            trans('admin/maintenances/form.title'),
            trans('admin/maintenances/form.start_date'),
            trans('admin/maintenances/form.completion_date'),
            trans('admin/maintenances/form.asset_maintenance_time'),
            trans('admin/maintenances/form.cost'),
        ];

        $header = array_map('trim', $header);
        $rows[] = implode(',', $header);

        foreach ($Maintenances as $maintenance) {
            $row = [];
            $row[] = str_replace(',', '', e($maintenance->asset->asset_tag));
            $row[] = str_replace(',', '', e($maintenance->asset->name));
            $row[] = str_replace(',', '', e($maintenance->supplier->name));
            $row[] = e($maintenance->improvement_type);
            $row[] = e($maintenance->name);
            $row[] = e($maintenance->start_date);
            $row[] = e($maintenance->completion_date);
            if (is_null($maintenance->asset_maintenance_time)) {
                $improvementTime = (int) Carbon::now()
                    ->diffInDays(Carbon::parse($maintenance->start_date), true);
            } else {
                $improvementTime = (int) $maintenance->asset_maintenance_time;
            }
            $row[]  = $improvementTime;
            $row[]  = trans('general.currency') . Helper::formatCurrencyOutput($maintenance->cost);
            $rows[] = implode(',', $row);
        }

        // spit out a csv
        $csv      = implode("\n", $rows);
        $response = response()->make($csv, 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-disposition', 'attachment;filename=report.csv');

        return $response;
    }

    /**
     * getAssetAcceptanceReport
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function getAssetAcceptanceReport($deleted = false) : View
    {
        $this->authorize('reports.view');
        $showDeleted = $deleted == 'deleted';

        $query = CheckoutAcceptance::pending()
            ->where('checkoutable_type', 'App\Models\Asset')
            ->with([
                'checkoutable' => function (MorphTo $query) {
                    $query->morphWith([
                        AssetModel::class => ['model'],
                        Company::class => ['company'],
                        Asset::class => ['assignedTo'],
                    ])->with('model.category');
                },
                'assignedTo' => function($query){
                         $query->withTrashed();
                    }
            ]);

        if ($showDeleted) {
            $query->withTrashed();
        }

        $assetsForReport = $query->get()
                ->map(function ($acceptance) {
                    return [
                        'assetItem' => $acceptance->checkoutable,
                        'acceptance' => $acceptance,
                    ];
            });

        return view('reports/unaccepted_assets', compact('assetsForReport','showDeleted' ));
    }

    /**
     * sentAssetAcceptanceReminder
     *
     * @param integer|null $acceptanceId
     * @version v1.0
     */
    public function sentAssetAcceptanceReminder(Request $request) : RedirectResponse
    {
        $this->authorize('reports.view');

        if (!$acceptance = CheckoutAcceptance::pending()->find($request->input('acceptance_id'))) {
            Log::debug('No pending acceptances');
            // Redirect to the unaccepted assets report page with error
            return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.bad_data'));
        }

        $assetItem = $acceptance->checkoutable;

        Log::debug(print_r($assetItem, true));

        if (is_null($acceptance->created_at)){
            Log::debug('No acceptance created_at');
            return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.bad_data'));
        } else {
            $logItem_res = $assetItem->checkouts()->where('created_at', '=', $acceptance->created_at)->get();

            if ($logItem_res->isEmpty()){
                Log::debug('Acceptance date mismatch');
                return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.bad_data'));
            }
            $logItem = $logItem_res[0];
        }
        $email = $assetItem->assignedTo?->email;
        $locale = $assetItem->assignedTo?->locale;

        if (is_null($email) || $email === '') {
            return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.no_email'));
        }

        Mail::to($email)->send((new CheckoutAssetMail($assetItem, $assetItem->assignedTo, $logItem->user, $acceptance, $logItem->note, firstTimeSending: false))->locale($locale));

        return redirect()->route('reports/unaccepted_assets')->with('success', trans('admin/reports/general.reminder_sent'));
    }

    /**
     * sentAssetAcceptanceReminder
     *
     * @param integer|null $acceptanceId
     * @version v1.0
     */
    public function deleteAssetAcceptance($acceptanceId = null) : RedirectResponse
    {
        $this->authorize('reports.view');

        if (!$acceptance = CheckoutAcceptance::pending()->find($acceptanceId)) {
            // Redirect to the unaccepted assets report page with error
            return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.bad_data'));
        }

        if($acceptance->delete()) {
            return redirect()->route('reports/unaccepted_assets')->with('success', trans('admin/reports/general.acceptance_deleted'));
        } else {
            return redirect()->route('reports/unaccepted_assets')->with('error', trans('general.deletion_failed'));
        }
    }

    /**
     * Exports the AssetAcceptance report to CSV
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    public function postAssetAcceptanceReport($deleted = false) : Response
    {
        $this->authorize('reports.view');
        $showDeleted = $deleted == 'deleted';

        /**
         * Get all assets with pending checkout acceptances
         */
        if($showDeleted) {
            $acceptances = CheckoutAcceptance::pending()->where('checkoutable_type', 'App\Models\Asset')->withTrashed()->with(['assignedTo', 'checkoutable.assignedTo', 'checkoutable.model'])->get();
        } else {
            $acceptances = CheckoutAcceptance::pending()->where('checkoutable_type', 'App\Models\Asset')->with(['assignedTo', 'checkoutable.assignedTo', 'checkoutable.model'])->get();
        }

        $assetsForReport = $acceptances
            ->filter(function($acceptance) {
                return $acceptance->checkoutable_type == 'App\Models\Asset';
            })
            ->map(function($acceptance) {
                return ['assetItem' => $acceptance->checkoutable, 'acceptance' => $acceptance];
            });

        $rows = [];

        $header = [
            trans('general.category'),
            trans('admin/hardware/form.model'),
            trans('admin/hardware/form.name'),
            trans('admin/hardware/table.asset_tag'),
            trans('admin/hardware/table.checkoutto'),
        ];

        $header = array_map('trim', $header);
        $rows[] = implode(',', $header);

        foreach ($assetsForReport as $item) {

            if ($item['assetItem'] != null){
            
                $row    = [ ];
                $row[]  = str_replace(',', '', e($item['assetItem']->model->category->name));
                $row[]  = str_replace(',', '', e($item['assetItem']->model->name));
                $row[]  = str_replace(',', '', e($item['assetItem']->name));
                $row[]  = str_replace(',', '', e($item['assetItem']->asset_tag));
                $row[]  = str_replace(',', '', e(($item['acceptance']->assignedTo) ? $item['acceptance']->assignedTo->display_name : trans('admin/reports/general.deleted_user')));
                $rows[] = implode(',', $row);
            }
        }

        // spit out a csv
        $csv      = implode("\n", $rows);
        $response = response()->make($csv, 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-disposition', 'attachment;filename=report.csv');

        return $response;
    }

    /**
     * getCheckedOutAssetsRequiringAcceptance
     *
     * @param $modelsInCategoriesThatRequireAcceptance
     *
     * @return array
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    protected function getCheckedOutAssetsRequiringAcceptance($modelsInCategoriesThatRequireAcceptance) : View
    {
        $this->authorize('reports.view');
        $assets = Asset::deployed()
                        ->inModelList($modelsInCategoriesThatRequireAcceptance)
                        ->select('id')
                        ->get()
                        ->toArray();

        return array_pluck($assets, 'id');
    }

    /**
     * getModelsInCategoriesThatRequireAcceptance
     *
     * @param $assetCategoriesRequiringAcceptance
     * @return array
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    protected function getModelsInCategoriesThatRequireAcceptance($assetCategoriesRequiringAcceptance) : array
    {
        $this->authorize('reports.view');

        return array_pluck(AssetModel::inCategory($assetCategoriesRequiringAcceptance)
                                 ->select('id')
                                 ->get()
                                 ->toArray(), 'id');
    }

    /**
     * getCategoriesThatRequireAcceptance
     *
     * @return array
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    protected function getCategoriesThatRequireAcceptance() : array
    {
        $this->authorize('reports.view');

        return array_pluck(Category::requiresAcceptance()
                                    ->select('id')
                                    ->get()
                                    ->toArray(), 'id');
    }

    /**
     * getAssetsCheckedOutRequiringAcceptance
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     */
    protected function getAssetsCheckedOutRequiringAcceptance() : array
    {
        $this->authorize('reports.view');

        return $this->getCheckedOutAssetsRequiringAcceptance(
            $this->getModelsInCategoriesThatRequireAcceptance($this->getCategoriesThatRequireAcceptance())
        );
    }
}
