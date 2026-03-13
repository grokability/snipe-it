<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\CustomFieldsetsTransformer;
use App\Http\Transformers\CustomFieldsTransformer;
use App\Models\CustomFieldset;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * This controller handles all actions related to Custom Asset Fieldsets for
 * the Snipe-IT Asset Management application.
 *
 * @todo Improve documentation here.
 * @todo Check for raw DB queries and try to convert them to query builder statements
 * @version    v2.0
 * @author [Brady Wetherington] [<uberbrady@gmail.com>]
 * @author [Josh Gibson]
 */
class CustomFieldsetsController extends Controller
{
    /**
     * Shows the given fieldset and its fields
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @author [Josh Gibson]
     * @param int $id
     * @since [v1.8]
     */
    public function index() : array
    {
        $this->authorize('index', CustomField::class);
        $fieldsets = CustomFieldset::withCount('fields as fields_count', 'models as models_count')->get();

        return (new CustomFieldsetsTransformer)->transformCustomFieldsets($fieldsets, $fieldsets->count());
    }

    /**
     * Shows the given fieldset and its fields
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @author [Josh Gibson]
     * @param int $id
     * @since [v1.8]
     */
    public function show($id) : JsonResponse | array
    {
        $this->authorize('view', CustomField::class);
        if ($fieldset = CustomFieldset::find($id)) {
            return (new CustomFieldsetsTransformer)->transformCustomFieldset($fieldset);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/custom_fields/message.fieldset.does_not_exist')), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id) : JsonResponse
    {
        $this->authorize('update', CustomField::class);
        $fieldset = CustomFieldset::findOrFail($id);
        $fieldset->fill($request->all());

        if ($fieldset->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $fieldset->getErrors()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request) : JsonResponse
    {
        $this->authorize('create', CustomField::class);
        $fieldset = new CustomFieldset;
        $fieldset->fill($request->all());

        if ($fieldset->save()) {
            // Sync fieldset with auto_add_to_fieldsets
            $fields = CustomField::select('id')->where('auto_add_to_fieldsets', '=', '1')->get();

            if ($fields->count() > 0) {

                foreach ($fields as $field) {
                    $field_ids[] = $field->id;
                }

                $fieldset->fields()->sync($field_ids);
            }

            return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $fieldset->getErrors()));
    }

    /**
     * Delete a custom fieldset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     */
    public function destroy($id) : JsonResponse
    {
        $this->authorize('delete', CustomField::class);
        $fieldset = CustomFieldset::findOrFail($id);

        $modelsCount = $fieldset->models->count();
        $fieldsCount = $fieldset->fields->count();

        if (($modelsCount > 0) || ($fieldsCount > 0)) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Fieldset is in use.'));
        }

        if ($fieldset->delete()) {
            return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/custom_fields/message.fieldset.delete.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, 'Unspecified error'));
    }

    /**
     * Return JSON containing a list of fields belonging to a fieldset.
     *
     * @author [V. Cordes] [<volker@fdatek.de>]
     * @since [v4.1.10]
     * @param $fieldsetId
     * @return string JSON
     */
    public function fields($id) : array
    {
        $this->authorize('view', CustomField::class);
        $set = CustomFieldset::findOrFail($id);
        $fields = $set->fields;

        return (new CustomFieldsTransformer)->transformCustomFields($fields, $fields->count());
    }

    /**
     * Return JSON containing a list of fields belonging to a fieldset with the
     * default values for a given model
     *
     * @param $modelId
     * @param $fieldsetId
     * @return string JSON
     */
    public function fieldsWithDefaultValues($fieldsetId, $modelId) : array
    {
        $this->authorize('view', CustomField::class);
        $set = CustomFieldset::findOrFail($fieldsetId);
        $fields = $set->fields;
        return (new CustomFieldsTransformer)->transformCustomFieldsWithDefaultValues($fields, $modelId, $fields->count());
    }
}
