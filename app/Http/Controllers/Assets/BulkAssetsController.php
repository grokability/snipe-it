<?php

namespace App\Http\Controllers\Assets;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Statuslabel;
use App\Models\Setting;
use App\View\Label;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\CustomField;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BulkAssetsController extends Controller
{
    use CheckInOutRequest;

    /**
     * Display the bulk edit page.
     *
     * This method is super weird because it's kinda of like a controller within a controller.
     * It's main function is to determine what the bulk action in, and then return a view with
     * the information that view needs, be it bulk delete, bulk edit, restore, etc.
     *
     * This is something that made sense at the time, but sort of doesn't make sense now. A JS front-end to determine form
     * action would make a lot more sense here and make things a lot more clear.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @internal param int $assetId
     * @since [v2.0]
     */
    public function edit(Request $request) : View | RedirectResponse
    {
        $this->authorize('view', Asset::class);

        /**
         * No asset IDs were passed
         */
        if (! $request->filled('ids')) {
            return redirect()->back()->with('error', trans('admin/hardware/message.update.no_assets_selected'));
        }

        $asset_ids = $request->input('ids');

        if ($request->input('bulk_actions') === 'checkout') {
            $status_check =$this->hasUndeployableStatus($asset_ids);
            if($status_check && $status_check['status'] === true){

                $asset_tags = implode(', ', array_column($status_check['tags'], 'asset_tag'));
                $asset_ids = $status_check['asset_ids'];

                session()->flash('warning', trans('admin/hardware/message.undeployable', ['asset_tags' => $asset_tags]));
            }

            $request->session()->flashInput(['selected_assets' => $asset_ids]);
            return redirect()->route('hardware.bulkcheckout.show');
        }

        if ($request->input('bulk_actions') === 'maintenance') {
            $request->session()->flashInput(['selected_assets' => $asset_ids]);
            return redirect()->route('maintenances.create');
        }

        // Figure out where we need to send the user after the update is complete, and store that in the session
        $bulk_back_url = request()->headers->get('referer');
        session(['bulk_back_url' => $bulk_back_url]);

        $allowed_columns = [
            'id',
            'name',
            'asset_tag',
            'serial',
            'model_number',
            'last_checkout',
            'notes',
            'expected_checkin',
            'order_number',
            'image',
            'assigned_to',
            'created_at',
            'updated_at',
            'purchase_date',
            'purchase_cost',
            'last_audit_date',
            'next_audit_date',
            'warranty_months',
            'checkout_counter',
            'checkin_counter',
            'requests_counter',
            'byod',
            'asset_eol_date',
        ];


        /**
         * Make sure the column is allowed, and if it's a custom field, make sure we strip the custom_fields. prefix
         */
        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort_override = str_replace('custom_fields.', '', $request->input('sort'));

        // This handles all of the pivot sorting below (versus the assets.* fields in the allowed_columns array)
        $column_sort = in_array($sort_override, $allowed_columns) ? $sort_override : 'assets.id';

        $query = Asset::with('assignedTo', 'location', 'model')
                ->whereIn('assets.id', $asset_ids)
                ->withTrashed();


        switch ($sort_override) {
            case 'model':
                $query->OrderModels($order);
                break;
            case 'model_number':
                $query->OrderModelNumber($order);
                break;
            case 'category':
                $query->OrderCategory($order);
                break;
            case 'manufacturer':
                $query->OrderManufacturer($order);
                break;
            case 'company':
                $query->OrderCompany($order);
                break;
            case 'location':
                $query->OrderLocation($order);
                break;
            case 'rtd_location':
                $query->OrderRtdLocation($order);
                break;
            case 'status_label':
                $query->OrderStatus($order);
                break;
            case 'supplier':
                $query->OrderSupplier($order);
                break;
            case 'assigned_to':
                $query->OrderAssigned($order);
                break;
            default:
                $query->orderBy($column_sort, $order);
                break;
        }
        $assets = $query->get();

        if ($assets->isEmpty()) {
            Log::debug('No assets were found for the provided IDs', ['ids' => $asset_ids]);
            return redirect()->back()->with('error', trans('admin/hardware/message.update.assets_do_not_exist_or_are_invalid'));
        }

        $models = $assets->unique('model_id');
        $modelNames = [];

        foreach($models as $model) {
            $modelNames[] = $model->model?->name;
        }

        if ($request->filled('bulk_actions')) {


            switch ($request->input('bulk_actions')) {
                case 'labels':
                    $this->authorize('view', Asset::class);

                    return (new Label)
                        ->with('assets', $assets)
                        ->with('settings', Setting::getSettings())
                        ->with('bulkedit', true)
                        ->with('count', 0);

                case 'delete':
                    $this->authorize('delete', Asset::class);
                    $assets->each(function ($assets) {
                        $this->authorize('delete', $assets);
                    });

                    return view('hardware/bulk-delete')->with('assets', $assets);

                case 'restore':
                    $this->authorize('update', Asset::class);
                    $assets = Asset::withTrashed()->find($asset_ids);
                    $assets->each(function ($asset) {
                        $this->authorize('delete', $asset);
                    });
                    return view('hardware/bulk-restore')->with('assets', $assets);

                case 'edit':
                    $this->authorize('update', Asset::class);
                    return view('hardware/bulk')
                        ->with('assets', $asset_ids)
                        ->with('statuslabel_list', Helper::statusLabelList())
                        ->with('models', $models->pluck(['model']))
                        ->with('modelNames', $modelNames);
            }
        }



        return redirect()->back()->with('error', 'No action selected');
    }

    /**
     * Save bulk edits
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @internal param array $assets
     * @since [v2.0]
     */
    public function update(Request $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $has_errors = 0;
        $error_array = array();

        // Get the back url from the session and then destroy the session

        $bulk_back_url = $request->session()->pull('bulk_back_url', url()->previous());

       $custom_field_columns = CustomField::all()->pluck('db_column')->toArray();

        // find custom field input attributes that start with 'null_'
        $null_custom_fields_inputs = array_filter($request->all(), function ($key) {
            // filter out all keys that start with 'null_'
            return (strpos($key, 'null_') === 0);
        }, ARRAY_FILTER_USE_KEY);;
        // remove 'null' from the keys
        $custom_fields_to_null = [];
        foreach ($null_custom_fields_inputs as $key => $value) {
            $custom_fields_to_null[str_replace('null', '', $key)] = $value;
        }





     
        if (! $request->filled('ids') || count($request->input('ids')) == 0) {
            return redirect($bulk_back_url)->with('error', trans('admin/hardware/message.update.no_assets_selected'));
        }


        $assets = Asset::whereIn('id', $request->input('ids'))->get();



        /**
         * If ANY of these are filled, prepare to update the values on the assets.
         *
         * Additional checks will be needed for some of them to make sure the values
         * make sense (for example, changing the status ID to something incompatible with
         * its checkout status.
         */

        if (($request->filled('name'))
            || ($request->filled('purchase_date'))
            || ($request->filled('expected_checkin'))
            || ($request->filled('purchase_cost'))
            || ($request->filled('supplier_id'))
            || ($request->filled('order_number'))
            || ($request->filled('warranty_months'))
            || ($request->filled('rtd_location_id'))
            || ($request->filled('requestable'))
            || ($request->filled('company_id'))
            || ($request->filled('status_id'))
            || ($request->filled('model_id'))
            || ($request->filled('next_audit_date'))
            || ($request->filled('asset_eol_date'))
            || ($request->filled('null_name'))
            || ($request->filled('null_purchase_date'))
            || ($request->filled('null_expected_checkin_date'))
            || ($request->filled('null_next_audit_date'))
            || ($request->filled('null_asset_eol_date'))
            || ($request->filled('null_notes'))
            || ($request->anyFilled($custom_field_columns))
            || ($request->anyFilled(array_keys($null_custom_fields_inputs)))

        ) {
            // Let's loop through those assets and build an update array
            foreach ($assets as $asset) {

                $this->update_array = [];

                /**
                 * Leave out model_id and status here because we do math on that later. We have to do some
                 * extra validation and checks on those two.
                 *
                 * It's tempting to make these match the request check above, but some of these values require
                 * extra work to make sure the data makes sense.
                 */
                $this->conditionallyAddItem('name')
                    ->conditionallyAddItem('purchase_date')
                    ->conditionallyAddItem('expected_checkin')
                    ->conditionallyAddItem('order_number')
                    ->conditionallyAddItem('requestable')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('warranty_months')
                    ->conditionallyAddItem('next_audit_date')
                    ->conditionallyAddItem('asset_eol_date')
                    ->conditionallyAddItem('notes');
                    foreach ($custom_field_columns as $key => $custom_field_column) {
                        $this->conditionallyAddItem($custom_field_column); 
                   }
                foreach ($custom_fields_to_null as $key => $custom_field_to_null) {
                    $this->conditionallyAddItem($key);
                }

                if (!($asset->eol_explicit)) {
					if ($request->filled('model_id')) {
						$model = AssetModel::find($request->input('model_id'));
						if ($model->eol > 0) {
							if ($request->filled('purchase_date')) {
								$this->update_array['asset_eol_date'] = Carbon::parse($request->input('purchase_date'))->addMonths($model->eol)->format('Y-m-d');
							} else {
								$this->update_array['asset_eol_date'] = Carbon::parse($asset->purchase_date)->addMonths($model->eol)->format('Y-m-d');
							}
						} else {
							$this->update_array['asset_eol_date'] = null;
						}
					} elseif (($request->filled('purchase_date')) && ($asset->model->eol > 0)) {
						$this->update_array['asset_eol_date'] = Carbon::parse($request->input('purchase_date'))->addMonths($asset->model->eol)->format('Y-m-d');
					}
				}                
                
                /**
                 * Blank out fields that were requested to be blanked out via checkbox
                 */
                if ($request->input('null_name')=='1') {

                    $this->update_array['name'] = null;
                }

                if ($request->input('null_purchase_date')=='1') {
                    $this->update_array['purchase_date'] = null;
                    if (!($asset->eol_explicit)) {
						$this->update_array['asset_eol_date'] = null;
					}
                }

                if ($request->input('null_expected_checkin_date')=='1') {
                    $this->update_array['expected_checkin'] = null;
                }

                if ($request->input('null_next_audit_date')=='1') {
                    $this->update_array['next_audit_date'] = null;
                }

                if ($request->input('null_asset_eol_date')=='1') {
                    $this->update_array['asset_eol_date'] = null;

                    // If they are nulling the EOL date to allow it to calculate, set eol explicit to 0
                    if ($request->input('calc_eol')=='1') {
                        $this->update_array['eol_explicit'] = 0;
                    }
                }

                if ($request->input('null_notes')=='1') {
                    $this->update_array['notes'] = null;
                }



                if ($request->filled('purchase_cost')) {
                    $this->update_array['purchase_cost'] =  $request->input('purchase_cost');
                }

                if ($request->filled('company_id')) {
                    $this->update_array['company_id'] = $request->input('company_id');
                    if ($request->input('company_id') == 'clear') {
                        $this->update_array['company_id'] = null;
                    }
                }

                /**
                 * We're trying to change the model ID - we need to do some extra checks here to make sure
                 * the custom field values work for the custom fieldset rules around this asset. Uniqueness
                 * and requiredness across the fieldset is particularly important, since those are
                 * fieldset-specific attributes.
                 */
                if ($request->filled('model_id')) {
                    $this->update_array['model_id'] = AssetModel::find($request->input('model_id'))->id;
                }

                /**
                 * We're trying to change the status ID - we need to do some extra checks here to
                 * make sure the status label type is one that makes sense for the state of the asset,
                 * for example, we shouldn't be able to make an asset archived if it's currently assigned
                 * to someone/something.
                 */
                if ($request->filled('status_id')) {
                    try {
                        $updated_status = Statuslabel::findOrFail($request->input('status_id'));
                    } catch (ModelNotFoundException $e) {
                        return redirect($bulk_back_url)->with('error', trans('admin/statuslabels/message.does_not_exist'));
                    }

                    // We cannot assign a non-deployable status type if the asset is already assigned.
                    // This could probably be added to a form request.
                    // If the asset isn't assigned, we don't care what the status is.
                    // Otherwise we need to make sure the status type is still a deployable one.

                    $unassigned = $asset->assigned_to == '';
                    $deployable = $updated_status->deployable == '1' && $asset->assetstatus?->deployable == '1';
                    $pending =  $updated_status->pending === 1;

                    if ($unassigned || $deployable || $pending) {
                        $this->update_array['status_id'] = $updated_status->id;
                    }

                }

                /**
                 * We're changing the location ID - figure out which location we should apply
                 * this change to:
                 *
                 * 0 - RTD location only
                 * 1 - location ID and RTD location ID
                 * 2 - location ID only
                 *
                 * Note: this is kinda dumb and we should just use human-readable values IMHO. - snipe
                 */
                if ($request->filled('rtd_location_id')) {

                    if (($request->filled('update_real_loc')) && (($request->input('update_real_loc')) == '0')) {
                        $this->update_array['rtd_location_id'] = $request->input('rtd_location_id');
                    }

                    if (($request->filled('update_real_loc')) && (($request->input('update_real_loc')) == '1')) {
                        $this->update_array['location_id'] = $request->input('rtd_location_id');
                        $this->update_array['rtd_location_id'] = $request->input('rtd_location_id');
                    }

                    if (($request->filled('update_real_loc')) && (($request->input('update_real_loc')) == '2')) {
                        $this->update_array['location_id'] = $request->input('rtd_location_id');
                    }

                }


                /**
                 * ------------------------------------------------------------------------------
                 * ANYTHING that happens past this foreach
                 * WILL NOT BE logged in the edit log_meta data
                 *  ------------------------------------------------------------------------------
                 */
                $changed = [];

                foreach ($this->update_array as $key => $value) {

                    if ($this->update_array[$key] != $asset->{$key}) {
                        $changed[$key]['old'] = $asset->{$key};
                        $changed[$key]['new'] = $this->update_array[$key];
                    }

                }

                /**
                 *
                 * Start all the custom fields shenanigans
                 */

                // Does the model have a fieldset?
                if ($asset->model?->fieldset) {
                    foreach ($asset->model->fieldset->fields as $field) {

                        // null custom fields
                        if ($custom_fields_to_null) {
                            foreach ($custom_fields_to_null as $key => $custom_field_to_null) {
                                if ($field->db_column == $key) {
                                    $this->update_array[$field->db_column] = null;
                                }
                            }
                        }

                        if ((array_key_exists($field->db_column, $this->update_array)) && ($field->field_encrypted == '1')) {
                            if (Gate::allows('admin')) {
                                $decrypted_old = Helper::gracefulDecrypt($field, $asset->{$field->db_column});

                                /*
                                 * Check if the decrypted existing value is different from one we just submitted
                                 * and if not, pull it out of the object since it shouldn't really be updating at all.
                                 * If we don't do this, it will try to re-encrypt it, and the same value encrypted two
                                 * different times will have different values, so it will *look* like it was updated
                                 * but it wasn't.
                                 */
                                if ($decrypted_old != $this->update_array[$field->db_column]) {
                                    $asset->{$field->db_column} = Crypt::encrypt($this->update_array[$field->db_column]);
                                } else {
                                    /*
                                     * Remove the encrypted custom field from the update_array, since nothing changed
                                     */
                                    unset($this->update_array[$field->db_column]);
                                    unset($asset->{$field->db_column});
                                }

                                /*
                                 * These custom fields aren't encrypted, just carry on as usual
                                 */
                            }
                        } else {

                            if ((array_key_exists($field->db_column, $this->update_array)) && ($asset->{$field->db_column} != $this->update_array[$field->db_column])) {

                                // Check if this is an array, and if so, flatten it
                                if (is_array($this->update_array[$field->db_column])) {
                                    $asset->{$field->db_column} = implode(', ', $this->update_array[$field->db_column]);
                                } else {
                                    $asset->{$field->db_column} = $this->update_array[$field->db_column];
                                }
                            }
                        }

                    } // endforeach
                }


                // Check if it passes validation, and then try to save
                if (!$asset->update($this->update_array)) {

                    // Build the error array
                    foreach ($asset->getErrors()->toArray() as $key => $message) {
                        for ($x = 0; $x < count($message); $x++) {
                            $error_array[$key][] = trans('general.asset') . ' ' . $asset->id . ': ' . $message[$x];
                            $has_errors++;
                        }
                    }

                }  // end if saved

            } // end asset foreach

            if ($has_errors > 0) {
                session()->put('bulkedit_ids', $request->input('ids'));
                session()->put('bulk_asset_errors',$error_array);

                return redirect()
                    ->route('hardware.index')
                    ->with('bulk_asset_errors', $error_array)
                    ->withInput();
            }

            return redirect($bulk_back_url)->with('success', trans('admin/hardware/message.update.success'));
        }
        // no values given, nothing to update
        return redirect($bulk_back_url)->with('warning', trans('admin/hardware/message.update.nothing_updated'));
    }

    /**
     * Array to store update data per item
     * @var array
     */
    private $update_array;

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  string $field field name
     */
    protected function conditionallyAddItem($field) : BulkAssetsController
    {
        if (request()->filled($field)) {
            $this->update_array[$field] = request()->input($field);
        }

        return $this;
    }

    /**
     * Save bulk deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @internal param array $assets
     * @since [v2.0]
     */
    public function destroy(Request $request) : RedirectResponse
    {
        $this->authorize('delete', Asset::class);

        $bulk_back_url = route('hardware.index');

        if ($request->session()->has('bulk_back_url')) {
            $bulk_back_url = $request->session()->pull('bulk_back_url');
        }
        $assetIds = $request->get('ids');

        if(empty($assetIds)) {
            return redirect($bulk_back_url)->with('error', trans('admin/hardware/message.delete.nothing_updated'));
        }

        $assignedAssets = Asset::whereIn('id', $assetIds)->whereNotNull('assigned_to')->get();
        if($assignedAssets->isNotEmpty()) {

            //if assets are checked out, return a list of asset tags that would need to be checked in first.
            $assetTags = $assignedAssets->pluck('asset_tag')->implode(', ');
            return redirect($bulk_back_url)->with('error', trans_choice('admin/hardware/message.delete.assigned_to_error', $assignedAssets->count(), ['asset_tag' => $assetTags] ));
        }

        foreach (Asset::wherein('id', $assetIds)->get() as $asset) {
                $asset->delete();
            }

        return redirect($bulk_back_url)->with('success', trans('admin/hardware/message.delete.success'));
            // no values given, nothing to update

    }

    /**
     * Show Bulk Checkout Page
     */
    public function showCheckout() : View
    {
        $this->authorize('checkout', Asset::class);

        $alreadyAssigned = collect();

        if (old('selected_assets') && is_array(old('selected_assets'))) {
            $assets = Asset::findMany(old('selected_assets'));

            [$assignable, $alreadyAssigned] = $assets->partition(function (Asset $asset) {
                return !$asset->assigned_to;
            });

            session()->flashInput(['selected_assets' => $assignable->pluck('id')->values()->toArray()]);
        }

        $do_not_change = ['' => trans('general.do_not_change')];
        $status_label_list = $do_not_change + Helper::deployableStatusLabelList();

        return view('hardware/bulk-checkout', [
            'statusLabel_list' => $status_label_list,
            'removed_assets' => $alreadyAssigned,
        ]);
    }

    /**
     * Process Multiple Checkout Request
     */
    public function storeCheckout(AssetCheckoutRequest $request) : RedirectResponse | ModelNotFoundException
    {
        $this->authorize('checkout', Asset::class);

        try {
            $admin = auth()->user();

            $target = $this->determineCheckoutTarget();
            session()->put(['checkout_to_type' => $target]);

            if (! is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware.bulkcheckout.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            $assets = Asset::findOrFail($asset_ids);

            // Prevent checking out assets that are already checked out
            if ($assets->pluck('assigned_to')->unique()->filter()->isNotEmpty()) {
                // re-add the asset ids so the assets select is re-populated
                $request->session()->flashInput(['selected_assets' => $asset_ids]);

                return redirect(route('hardware.bulkcheckout.show'))
                    ->with('error', trans('general.error_assets_already_checked_out'));
            }

            // Prevent checking out assets across companies if FMCS enabled
            if (Setting::getSettings()->full_multiple_companies_support && $target->company_id) {
                $company_ids = $assets->pluck('company_id')->unique();

                // if there is more than one unique company id or the singular company id does not match
                // then the checkout is invalid
                if ($company_ids->count() > 1 || $company_ids->first() != $target->company_id) {
                    // re-add the asset ids so the assets select is re-populated
                    $request->session()->flashInput(['selected_assets' => $asset_ids]);

                    return redirect(route('hardware.bulkcheckout.show'))
                        ->with('error', trans('general.error_user_company_multiple'));
                }
            }

            if (request('checkout_to_type') == 'asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id) {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }
            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->get('expected_checkin');
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, &$errors, $assets, $request) { //NOTE: $errors is passsed by reference!
                foreach ($assets as $asset) {
                    $this->authorize('checkout', $asset);

                    // See if there is a status label passed
                    if ($request->filled('status_id')) {
                        $asset->status_id = $request->get('status_id');
                    }

                    $checkout_success = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $asset->name, null);

                    //TODO - I think this logic is duplicated in the checkOut method?
                    if ($target->location_id != '') {
                        $asset->location_id = $target->location_id;
                        // TODO - I don't know why this is being saved without events
                        $asset::withoutEvents(function () use ($asset) {
                            $asset->save();
                        });
                    }

                    if (!$checkout_success) {
                        $errors = array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('hardware')->with('success', trans_choice('admin/hardware/message.multi-checkout.success', $asset_ids));
            }
            // Redirect to the asset management page with error
            return redirect()->route('hardware.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $asset_ids))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('hardware.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $request->input('selected_assets')));
        }
        
    }
    public function restore(Request $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $assetIds = $request->get('ids');

        if (empty($assetIds)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.restore.nothing_updated'));
        } else {
            foreach ($assetIds as $key => $assetId) {
                $asset = Asset::withTrashed()->find($assetId);
                $asset->restore();
            } 
            return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.restore.success'));
        }
    }
    public function hasUndeployableStatus (array $asset_ids)
    {
        $undeployable = Asset::whereIn('id', $asset_ids)
            ->undeployable()
            ->get();

        $undeployableTags = $undeployable->map(function ($asset) {
            return [
                'id' => $asset->id,
                'asset_tag' => $asset->asset_tag,
            ];
        })->toArray();

        $undeployableIds = array_column($undeployableTags, 'id');
        $filtered_ids = array_diff($asset_ids, $undeployableIds);

         if($undeployable->isNotEmpty()) {
             return ['status' => true, 'tags' => $undeployableTags, 'asset_ids' => $filtered_ids];
         }
        return false;
    }

    public function bulkEditForm(): View|RedirectResponse
    {
        $this->authorize('update', Asset::class);

        $asset_ids = session()->pull('bulkedit_ids', []);

        if (empty($asset_ids)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.update.no_assets_selected'));
        }

        $assets = Asset::with('model')->withTrashed()->whereIn('id', $asset_ids)->get();

        if ($assets->isEmpty()) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.update.assets_do_not_exist_or_are_invalid'));
        }

        $models = $assets->unique('model_id');
        $modelNames = [];
        foreach ($models as $model) {
            $modelNames[] = $model->model->name;
        }

        return view('hardware/bulk')
            ->with('assets', $asset_ids)
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('models', $models->pluck(['model']))
            ->with('modelNames', $modelNames);
    }
}
