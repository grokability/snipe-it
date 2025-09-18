<?php

namespace App\Http\Controllers\Licenses;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * This controller handles all actions related to Licenses for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class LicensesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the licenses listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('view', License::class);

        return view('licenses/index');
    }

    /**
     * Returns a form view that allows an admin to create a new licence.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AccessoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', License::class);
        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];

        return view('licenses/edit')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('maintained_list', $maintained_list)
            ->with('item', new License);
    }

    /**
     * Validates and stores the license form data submitted from the new
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getCreate() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', License::class);
        // create a new model instance
        $license = new License();
        // Save the license data
        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained', 0);
        $license->manufacturer_id   = $request->input('manufacturer_id');
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->seats             = $request->input('seats');
        $license->serial            = $request->input('serial');
        $license->supplier_id       = $request->input('supplier_id');
        $license->category_id       = $request->input('category_id');
        $license->termination_date  = $request->input('termination_date');
        $license->created_by           = auth()->id();
        $license->min_amt           = $request->input('min_amt');

        if($request->get('redirect_option') === 'back'){
            session()->put(['redirect_option' => 'index']);
        } else {
            session()->put(['redirect_option' => $request->get('redirect_option')]);
        }

        if ($license->save()) {
            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
     * Returns a form with existing license data to allow an admin to
     * update license information.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(License $license)
    {

        $this->authorize('update', $license);
        session()->put('back_url', url()->previous());
        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];

        return view('licenses/edit')
            ->with('item', $license)
            ->with('depreciation_list', Helper::depreciationList())
            ->with('maintained_list', $maintained_list);
    }


    /**
     * Validates and stores the license form data submitted from the edit
     * license form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LicensesController::getEdit() method that provides the form view
     * @since [v1.0]
     * @param Request $request
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, License $license)
    {


        $this->authorize('update', $license);

        $license->company_id        = Company::getIdForCurrentUser($request->input('company_id'));
        $license->depreciation_id   = $request->input('depreciation_id');
        $license->expiration_date   = $request->input('expiration_date');
        $license->license_email     = $request->input('license_email');
        $license->license_name      = $request->input('license_name');
        $license->maintained        = $request->input('maintained',0);
        $license->name              = $request->input('name');
        $license->notes             = $request->input('notes');
        $license->order_number      = $request->input('order_number');
        $license->purchase_cost     = $request->input('purchase_cost');
        $license->purchase_date     = $request->input('purchase_date');
        $license->purchase_order    = $request->input('purchase_order');
        $license->reassignable      = $request->input('reassignable', 0);
        $license->serial            = $request->input('serial');
        $license->termination_date  = $request->input('termination_date');
        $license->seats             = e($request->input('seats'));
        $license->manufacturer_id   =  $request->input('manufacturer_id');
        $license->supplier_id       = $request->input('supplier_id');
        $license->category_id       = $request->input('category_id');
        $license->min_amt           = $request->input('min_amt');

        session()->put(['redirect_option' => $request->get('redirect_option')]);

        if ($license->save()) {
            return Helper::getRedirectOption($request, $license->id, 'Licenses')
                ->with('success', trans('admin/licenses/message.update.success'));
        }
        // If we can't adjust the number of seats, the error is flashed to the session by the event handler in License.php
        return redirect()->back()->withInput()->withErrors($license->getErrors());
    }

    /**
     * Checks to see whether the selected license can be deleted, and
     * if it can, marks it as deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(License $license)
    {
        // Check if the license exists
        if (is_null($license = License::find($license->id))) {
            // Redirect to the license management page
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.not_found'));
        }

        $this->authorize('delete', $license);

        if ($license->assigned_seats_count == 0) {
            // Delete the license and the associated license seats
            DB::table('license_seats')
                ->where('license_id', $license->id)
                ->update(['assigned_to' => null, 'asset_id' => null]);

            $licenseSeats = $license->licenseseats();
            $licenseSeats->delete();
            $license->delete();

            // Redirect to the licenses management page
            return redirect()->route('licenses.index')->with('success', trans('admin/licenses/message.delete.success'));
            // Redirect to the license management page
        }
        // There are still licenses in use.
        return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.assoc_users'));
    }

    /**
     * Makes the license detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $licenseId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(License $license)
    {
        $license = License::with('assignedusers')->find($license->id);

        $users_count = User::where('autoassign_licenses', '1')->count();

        $total_seats_count = (int) $license->totalSeatsByLicenseID();
        $available_seats_count = $license->availCount()->count();
        $unreassignable_seats_count = License::unReassignableCount($license);

        if(!$license->reassignable){
            $checkedout_seats_count = ($total_seats_count - $available_seats_count - $unreassignable_seats_count );
        }
        else {
            $checkedout_seats_count = ($total_seats_count - $available_seats_count);
        }
        if($license->isInactive()){
            session()->flash('warning', (trans('admin/licenses/message.checkout.license_is_inactive')));
        }

        $this->authorize('view', $license);
        return view('licenses.view', compact('license'))
            ->with('users_count', $users_count)
            ->with('total_seats_count', $total_seats_count)
            ->with('available_seats_count', $available_seats_count)
            ->with('checkedout_seats_count', $checkedout_seats_count)
            ->with('unreassignable_seats_count', $unreassignable_seats_count);

    }


    /**
     * Returns a view with prepopulated data for clone
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $licenseId
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getClone($licenseId = null) : \Illuminate\Contracts\View\View | \Illuminate\Http\RedirectResponse
    {
        if (is_null($license_to_clone = License::find($licenseId))) {
            return redirect()->route('licenses.index')->with('error', trans('admin/licenses/message.does_not_exist'));
        }

        $this->authorize('create', License::class);

        $maintained_list = [
            '' => 'Maintained',
            '1' => 'Yes',
            '0' => 'No',
        ];
        //clone the orig
        $license = clone $license_to_clone;
        $license->id = null;
        $license->serial = null;

        // Show the page
        return view('licenses/edit')
        ->with('depreciation_list', Helper::depreciationList())
        ->with('item', $license)
        ->with('maintained_list', $maintained_list);
    }

    /**
     * Exports Licenses to CSV
     *
     * @author [G. Martinez]
     * @since [v6.3]
     * @return StreamedResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getExportLicensesCsv()
    {
        $this->authorize('view', License::class);
        \Debugbar::disable();

        $response = new StreamedResponse(function () {
            // Open output stream
            $handle = fopen('php://output', 'w');
            $licenses = License::with('company',
                          'manufacturer',
                          'category',
                          'supplier',
                          'adminuser',
                          'assignedusers');
            if (request()->filled('category_id')) {
                $licenses = $licenses->where('category_id', request()->input('category_id'));
            }
            $licenses = $licenses->orderBy('created_at', 'DESC');
            Company::scopeCompanyables($licenses)
                ->chunk(500, function ($licenses) use ($handle) {
                    $headers = [
                        // strtolower to prevent Excel from trying to open it as a SYLK file
                        strtolower(trans('general.id')),
                        trans('general.company'),
                        trans('general.name'),
                        trans('general.serial_number'),
                        trans('general.purchase_date'),
                        trans('general.purchase_cost'),
                        trans('general.order_number'),
                        trans('general.licenses_available'),
                        trans('admin/licenses/table.seats'),
                        trans('general.created_by'),
                        trans('general.depreciation'),
                        trans('general.updated_at'),
                        trans('admin/licenses/table.deleted_at'),
                        trans('general.email'),
                        trans('admin/hardware/form.fully_depreciated'),
                        trans('general.supplier'),
                        trans('admin/licenses/form.expiration'),
                        trans('admin/licenses/form.purchase_order'),
                        trans('admin/licenses/form.termination_date'),
                        trans('admin/licenses/form.maintained'),
                        trans('general.manufacturer'),
                        trans('general.category'),
                        trans('general.min_amt'),
                        trans('admin/licenses/form.reassignable'),
                        trans('general.notes'),
                        trans('general.created_at'),
                    ];

                    fputcsv($handle, $headers);

                    foreach ($licenses as $license) {
                        // Add a new row with data
                        $values = [
                            $license->id,
                            $license->company ? $license->company->name: '',
                            $license->name,
                            $license->serial,
                            $license->purchase_date,
                            $license->purchase_cost,
                            $license->order_number,
                            $license->free_seat_count,
                            $license->seats,
                            ($license->adminuser ? $license->adminuser->display_name : trans('admin/reports/general.deleted_user')),
                            $license->depreciation ? $license->depreciation->name: '',
                            $license->updated_at,
                            $license->deleted_at,
                            $license->email,
                            ( $license->depreciate == '1') ? trans('general.yes') : trans('general.no'),
                            ($license->supplier) ? $license->supplier->name: '',
                            $license->expiration_date,
                            $license->purchase_order,
                            $license->termination_date,
                            ( $license->maintained == '1') ? trans('general.yes') : trans('general.no'),
                            $license->manufacturer ? $license->manufacturer->name: '',
                            $license->category ? $license->category->name: '',
                            $license->min_amt,
                            ( $license->reassignable == '1') ? trans('general.yes') : trans('general.no'),
                            $license->notes,
                            $license->created_at,
                        ];

                        fputcsv($handle, $values);
                    }
                });

            // Close the output stream
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="licenses-'.date('Y-m-d-his').'.csv"',
        ]);

        return $response;
    }
}
