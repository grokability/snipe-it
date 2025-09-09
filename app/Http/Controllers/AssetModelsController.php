<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\StoreAssetModelRequest;
use App\Models\Actionlog;
use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\SnipeModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;


/**
 * This class controls all actions related to asset models for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetModelsController extends Controller
{
    protected MessageBag $validatorErrors;
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function index() : View
    {
        $this->authorize('index', AssetModel::class);

        return view('models/index');
    }

    /**
     * Returns a view containing the asset model creation form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function create() : View
    {
        $this->authorize('create', AssetModel::class);

        return view('models/edit')->with('category_type', 'asset')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('item', new AssetModel);
    }

    /**
     * Validate and process the new Asset Model data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param ImageUploadRequest $request
     */
    public function store(StoreAssetModelRequest $request) : RedirectResponse
    {
        $this->authorize('create', AssetModel::class);
        $model = new AssetModel;

        $model->eol = $request->input('eol');
        $model->depreciation_id = $request->input('depreciation_id');
        $model->name = $request->input('name');
        $model->model_number = $request->input('model_number');
        $model->min_amt = $request->input('min_amt');
        $model->manufacturer_id = $request->input('manufacturer_id');
        $model->category_id = $request->input('category_id');
        $model->notes = $request->input('notes');
        $model->created_by = auth()->id();
        $model->requestable = $request->has('requestable');
        $model->require_serial = $request->input('require_serial', 0);

        if ($request->input('fieldset_id') != '') {
            $model->fieldset_id = $request->input('fieldset_id');
        }

        if ($request->has('use_cloned_image')) {
            $cloned_model_img = AssetModel::select('image')->find($request->input('clone_image_from_id'));
            if ($cloned_model_img) {
                $new_image_name = 'clone-'.date('U').'-'.$cloned_model_img->image;
                $new_image = 'models/'.$new_image_name;
                Storage::disk('public')->copy('models/'.$cloned_model_img->image, $new_image);
                $model->image = $new_image_name;
            }

        } else {
            $model = $request->handleImages($model);
        }



        if ($model->save()) {
            if ($this->shouldAddDefaultValues($request->input())) {
                if (!$this->assignCustomFieldsDefaultValues($model, $request->input('default_values'))){
                    return redirect()->back()->withInput()->with('error', trans('admin/custom_fields/message.fieldset_default_value.error'));
                }
            }

            return redirect()->route('models.index')->with('success', trans('admin/models/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Returns a view containing the asset model edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     */
    public function edit(AssetModel $model) : View | RedirectResponse
    {
        $this->authorize('update', AssetModel::class);
        $category_type = 'asset';
        return view('models/edit', compact('category_type'))->with('item', $model)->with('depreciation_list', Helper::depreciationList());
    }


    /**
     * Validates and processes form data from the edit
     * Asset Model form based on the model ID passed.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param ImageUploadRequest $request
     * @param int $modelId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StoreAssetModelRequest $request, AssetModel $model) : RedirectResponse
    {
        $this->authorize('update', AssetModel::class);

        $model = $request->handleImages($model);
        $model->depreciation_id = $request->input('depreciation_id');
        $model->eol = $request->input('eol');
        $model->name = $request->input('name');
        $model->model_number = $request->input('model_number');
        $model->min_amt = $request->input('min_amt');
        $model->manufacturer_id = $request->input('manufacturer_id');
        $model->category_id = $request->input('category_id');
        $model->notes = $request->input('notes');
        $model->requestable = $request->input('requestable', '0');
        $model->require_serial = $request->input('require_serial', 0);
        $model->fieldset_id = $request->input('fieldset_id');

        if ($model->save()) {
            $this->removeCustomFieldsDefaultValues($model);

            if ($this->shouldAddDefaultValues($request->input())) {
                if (!$this->assignCustomFieldsDefaultValues($model, $request->input('default_values'))) {
                    return redirect()->back()->withInput()->withErrors($this->validatorErrors);
                }
            }

            if ($model->wasChanged('eol')) {
                    if ($model->eol > 0) {
                        $newEol = $model->eol; 
                        $model->assets()->whereNotNull('purchase_date')->where('eol_explicit', false)
                            ->update(['asset_eol_date' => DB::raw('DATE_ADD(purchase_date, INTERVAL ' . $newEol . ' MONTH)')]);
                        } elseif ($model->eol == 0) {
    						$model->assets()->whereNotNull('purchase_date')->where('eol_explicit', false)
    							->update(['asset_eol_date' => DB::raw('null')]);
					}
                }
            return redirect()->route('models.index')->with('success', trans('admin/models/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($model->getErrors());
    }

    /**
     * Validate and delete the given Asset Model. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     */
    public function destroy(AssetModel $model) : RedirectResponse
    {
        $this->authorize('delete', AssetModel::class);


        if ($model->assets()->count() > 0) {
            // Throw an error that this model is associated with assets
            return redirect()->route('models.index')->with('error', trans('admin/models/message.assoc_users'));
        }

        // Delete the model
        $model->delete();

        // Redirect to the models management page
        return redirect()->route('models.index')->with('success', trans('admin/models/message.delete.success'));
    }

    /**
     * Restore a given Asset Model (mark as un-deleted)
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $id
     */
    public function getRestore($id) : RedirectResponse
    {
        $this->authorize('create', AssetModel::class);

        if ($model = AssetModel::withTrashed()->find($id)) {

            if ($model->deleted_at == '') {
                return redirect()->back()->with('error', trans('general.not_deleted', ['item_type' => trans('general.asset_model')]));
            }

            if ($model->restore()) {
                $logaction = new Actionlog();
                $logaction->item_type = AssetModel::class;
                $logaction->item_id = $model->id;
                $logaction->created_at = date('Y-m-d H:i:s');
                $logaction->created_by = auth()->id();
                $logaction->logaction('restore');


                // Redirect them to the deleted page if there are more, otherwise the section index
                $deleted_models = AssetModel::onlyTrashed()->count();
                if ($deleted_models > 0) {
                    return redirect()->back()->with('success', trans('admin/models/message.restore.success'));
                }
                return redirect()->route('models.index')->with('success', trans('admin/models/message.restore.success'));
            }

            // Check validation
            return redirect()->back()->with('error', trans('general.could_not_restore', ['item_type' => trans('general.asset_model'), 'error' => $model->getErrors()->first()]));
        }

        return redirect()->back()->with('error', trans('admin/models/message.does_not_exist'));

    }


    /**
     * Get the model information to present to the model view page
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     */
    public function show(AssetModel $model) : View | RedirectResponse
    {
        $this->authorize('view', AssetModel::class);
        return view('models/view', compact('model'));
    }

    /**
     * Get the clone page to clone a model
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     */
    public function getClone(AssetModel $model) : View | RedirectResponse
    {
        $this->authorize('create', AssetModel::class);

        $cloned_model = clone $model;
        $model->id = null;
        $model->deleted_at = null;

        // Show the page
        return view('models/edit')
            ->with('depreciation_list', Helper::depreciationList())
            ->with('item', $model)
            ->with('model_id', $model->id)
            ->with('cloned_model', $cloned_model);
    }


    /**
     * Get the custom fields form
     *
     * @author [B. Wetherington] [<uberbrady@gmail.com>]
     * @since [v2.0]
     * @param int $modelId
     */
    public function getCustomFields($modelId) : View
    {
        return view('models.custom_fields_form')->with('model', AssetModel::find($modelId));
    }



    /**
     * Returns a view that allows the user to bulk edit model attributes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     */
    public function postBulkEdit(Request $request) : View | RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->orderBy('assets_count', 'ASC')->get();

            // If deleting....
            if ($request->input('bulk_actions') == 'delete') {
                $valid_count = 0;
                foreach ($models as $model) {
                    if ($model->assets_count == 0) {
                        $valid_count++;
                    }
                }

                return view('models/bulk-delete', compact('models'))->with('valid_count', $valid_count);

            // Otherwise display the bulk edit screen
            } else {
                $nochange = ['NC' => 'No Change'];
                $fieldset_list = $nochange + Helper::customFieldsetList();
                $depreciation_list = $nochange + Helper::depreciationList();

                return view('models/bulk-edit', compact('models'))
                    ->with('fieldset_list', $fieldset_list)
                    ->with('depreciation_list', $depreciation_list);
            }
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }



    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     */
    public function postBulkEditSave(Request $request) : RedirectResponse
    {
        $models_raw_array = $request->input('ids');
        $update_array = [];


        if (($request->filled('manufacturer_id') && ($request->input('manufacturer_id') != 'NC'))) {
            $update_array['manufacturer_id'] = $request->input('manufacturer_id');
        }
        if (($request->filled('category_id') && ($request->input('category_id') != 'NC'))) {
            $update_array['category_id'] = $request->input('category_id');
        }
        if ($request->input('fieldset_id') != 'NC') {
            $update_array['fieldset_id'] = $request->input('fieldset_id');
        }
        if ($request->input('depreciation_id') != 'NC') {
            $update_array['depreciation_id'] = $request->input('depreciation_id');
        }

        
        if (count($update_array) > 0) {
            AssetModel::whereIn('id', $models_raw_array)->update($update_array);

            return redirect()->route('models.index')
                ->with('success', trans('admin/models/message.bulkedit.success'));
        }

        return redirect()->route('models.index')
            ->with('warning', trans('admin/models/message.bulkedit.error'));
    }

    /**
     * Validate and delete the given Asset Models. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param int $modelId
     */
    public function postBulkDelete(Request $request) : RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->get();

            $del_error_count = 0;
            $del_count = 0;

            foreach ($models as $model) {

                if ($model->assets_count > 0) {
                    $del_error_count++;
                } else {
                    $model->delete();
                    $del_count++;
                }
            }


            if ($del_error_count == 0) {
                return redirect()->route('models.index')
                    ->with('success', trans('admin/models/message.bulkdelete.success', ['success_count'=> $del_count]));
            }

            return redirect()->route('models.index')
                ->with('warning', trans('admin/models/message.bulkdelete.success_partial', ['fail_count'=>$del_error_count, 'success_count'=> $del_count]));
        }

        return redirect()->route('models.index')
            ->with('error', trans('admin/models/message.bulkdelete.error'));
    }

    /**
     * Returns true if a fieldset is set, 'add default values' is ticked and if
     * any default values were entered into the form.
     *
     * @param  array  $input
     */
    private function shouldAddDefaultValues(array $input) : bool
    {
        return ! empty($input['add_default_values'])
            && ! empty($input['default_values'])
            && ! empty($input['fieldset_id']);
    }

    /**
     * Adds default values to a model (as long as they are truthy)
     *
     * @param  AssetModel $model
     * @param  array      $defaultValues
     */
    private function assignCustomFieldsDefaultValues(AssetModel|SnipeModel $model, array $defaultValues): bool
    {
        $data = array();
        foreach ($defaultValues as $customFieldId => $defaultValue) {
            $customField = CustomField::find($customFieldId);

            $data[$customField->db_column] = $defaultValue;
        }

        $allRules = $model->fieldset->validation_rules();
        $rules = array();

        foreach ($allRules as $field => $validation) {
            // If the field is marked as required, eliminate the rule so it doesn't interfere with the default values
            // (we are at model level, the rule still applies when creating a new asset using this model)
            $index = array_search('required', $validation);
            if ($index !== false){
                $validation[$index] = 'nullable';
            }
            $rules[$field] = $validation;
        }

        $attributes = [];
        foreach ($model->fieldset->fields as $field) {
            $attributes[$field->db_column] = trim(preg_replace('/_+|snipeit|\d+/', ' ', $field->db_column));
        }

        $validator = Validator::make($data, $rules)->setAttributeNames($attributes);

        if($validator->fails()){
            $this->validatorErrors = $validator->errors();
            return false;
        }

        foreach ($defaultValues as $customFieldId => $defaultValue) {
            if(is_array($defaultValue)){
                $model->defaultValues()->attach($customFieldId, ['default_value' => implode(', ', $defaultValue)]);
            }elseif ($defaultValue) {
                $model->defaultValues()->attach($customFieldId, ['default_value' => $defaultValue]);
            }
        }
        return true;
    }

    /**
     * Removes all default values
     *
     */
    private function removeCustomFieldsDefaultValues(AssetModel|SnipeModel $model): void
    {
        $model->defaultValues()->detach();
    }
}
