<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\CustomFieldsTransformer;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldsController extends Controller
{
    /**
     * Reorder the custom fields within a fieldset
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v3.0]
     */
    public function index(Request $request): array
    {
        $this->authorize('index', CustomField::class);

        $allowed_columns = [
            'id',
            'name',
            'help_text',
            'db_column',
            'format',
            'element',
            'field_encrypted',
            'show_in_listview',
            'display_in_user_view',
            'show_in_email',
            'show_in_requestable_list',
            'is_unique',
            'display_checkin',
            'display_checkout',
            'display_audit',
            'created_at',
            'updated_at',
        ];

        // withCount feeds isDeletable()'s fieldset_count accessor on the
        // transformer's bulk_selectable gate. Without it every row runs
        // a per-field COUNT(*) on custom_field_custom_fieldset in
        // isDeletable()'s fallback branch.
        $fields = CustomField::with('fieldset', 'adminuser')
            ->withCount('fieldset as fieldset_count');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $fields->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('help_text', 'LIKE', '%'.$search.'%')
                    ->orWhere('db_column', 'LIKE', '%'.$search.'%')
                    ->orWhere('format', 'LIKE', '%'.$search.'%')
                    ->orWhere('element', 'LIKE', '%'.$search.'%');
            });
        }

        // Advanced-search filter[] params. filterableFields() widgets
        // put bool filters in the stash. Every searchable: true column
        // auto-gets a text widget. Handle both here.
        $filter = $request->input('filter', []);
        if (is_string($filter)) {
            $decoded = json_decode($filter, true);
            $filter = is_array($decoded) ? $decoded : [];
        }
        $booleanFilterFields = [
            'field_encrypted',
            'show_in_listview',
            'is_unique',
            'display_checkin',
            'display_checkout',
            'display_audit',
            'show_in_email',
            'show_in_requestable_list',
            'display_in_user_view',
            'auto_add_to_fieldsets',
        ];
        foreach ($booleanFilterFields as $boolField) {
            if (array_key_exists($boolField, $filter) && $filter[$boolField] !== '' && $filter[$boolField] !== null) {
                $values = is_array($filter[$boolField]) ? $filter[$boolField] : [$filter[$boolField]];
                $fields->whereIn($boolField, $values);
            }
        }

        // Enum filter on the 'element' column. Accepts filter[element]
        // (current admin UI + new integrations) and filter[type] (older
        // integrations that predated the API-alias cleanup). Whichever
        // is non-empty wins. Values match exactly since the picker emits
        // whole enum values, not substrings.
        // Frontend writes the carrier as a CSV string (see
        // setupSelectCheckboxesWidget in advanced-filters.blade.php),
        // so split on commas before the whereIn.
        $elementFilter = ! empty($filter['element']) ? $filter['element'] : ($filter['type'] ?? null);
        if (! empty($elementFilter)) {
            $values = is_array($elementFilter)
                ? $elementFilter
                : array_values(array_filter(array_map('trim', explode(',', (string) $elementFilter)), 'strlen'));
            if (! empty($values)) {
                $fields->whereIn('element', $values);
            }
        }

        // Text filters generated for every searchable: true column in
        // the presenter. Map the column fields the admin UI emits back
        // to their actual DB column.
        $textFilterMap = [
            'name' => 'name',
            'help_text' => 'help_text',
            'db_column_name' => 'db_column',
            'format' => 'format',
        ];
        foreach ($textFilterMap as $widgetField => $dbColumn) {
            if (! array_key_exists($widgetField, $filter)) {
                continue;
            }
            $value = $filter[$widgetField];
            if ($value === '' || $value === null) {
                continue;
            }
            $values = is_array($value) ? $value : [$value];
            $fields->where(function ($q) use ($values, $dbColumn) {
                foreach ($values as $v) {
                    $q->orWhere($dbColumn, 'LIKE', '%'.$v.'%');
                }
            });
        }

        if (! empty($filter['created_by'])) {
            // Same CSV-carrier shape as the element filter above.
            $userIds = is_array($filter['created_by'])
                ? $filter['created_by']
                : array_values(array_filter(array_map('trim', explode(',', (string) $filter['created_by'])), 'strlen'));
            if (! empty($userIds)) {
                $fields->whereIn('created_by', $userIds);
            }
        }

        $total = $fields->count();
        $offset = ($request->input('offset') > $total) ? $total : app('api_offset_value');
        $limit = app('api_limit_value');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'name';
        $fields->orderBy($sort, $order);

        $fields = $fields->skip($offset)->take($limit)->get();

        return (new CustomFieldsTransformer)->transformCustomFields($fields, $total);
    }

    /**
     * Shows the given field
     *
     * @author [V. Cordes] [<volker@fdatek.de>]
     *
     * @param  int  $id
     *
     * @since [v4.1.10]
     */
    public function show($id): JsonResponse|array
    {
        $this->authorize('view', CustomField::class);
        if ($field = CustomField::find($id)) {
            return (new CustomFieldsTransformer)->transformCustomField($field);
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, trans('admin/custom_fields/message.field.invalid')), 200);
    }

    /**
     * Update the specified field
     *
     * @author [V. Cordes] [<volker@fdatek.de>]
     *
     * @since [v4.1.10]
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::findOrFail($id);

        /**
         * Updated values for the field,
         * without the "field_encrypted" flag, preventing the change of encryption status
         *
         * @var array
         */
        $data = $request->except(['field_encrypted']);

        $validator = Validator::make($data, $field->validationRules());
        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()));
        }

        $field->fill($data);

        if ($field->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $field, trans('admin/custom_fields/message.field.update.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $field->getErrors()));
    }

    /**
     * Store a newly created field.
     *
     * @author [V. Cordes] [<volker@fdatek.de>]
     *
     * @since [v4.1.10]
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', CustomField::class);
        $field = new CustomField;

        $data = $request->all();
        $regex_format = null;

        if ((array_key_exists('format', $data)) && (str_contains($data['format'], 'regex:'))) {
            $regex_format = $data['format'];
        }

        $validator = Validator::make($data, $field->validationRules($regex_format));

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()));
        }
        $field->fill($data);

        if ($field->save()) {
            return response()->json(Helper::formatStandardApiResponse('success', $field, trans('admin/custom_fields/message.field.create.success')));
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $field->getErrors()));
    }

    public function postReorder(Request $request, $id)
    {
        $fieldset = CustomFieldset::find($id);

        $this->authorize('update', $fieldset);

        $fields = [];
        $order_array = [];

        $items = $request->input('item');

        foreach ($items as $order => $field_id) {
            $order_array[$field_id] = $order;
        }

        foreach ($fieldset->fields as $field) {
            $fields[$field->id] = ['required' => $field->pivot->required, 'order' => $order_array[$field->id]];
        }

        return $fieldset->fields()->sync($fields);
    }

    public function associate(Request $request, $field_id): JsonResponse
    {
        $this->authorize('update', CustomFieldset::class);

        $field = CustomField::findOrFail($field_id);

        $fieldset_id = $request->input('fieldset_id');
        foreach ($field->fieldset as $fieldset) {
            if ($fieldset->id == $fieldset_id) {
                return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.update.success')));
            }
        }

        $fieldset = CustomFieldset::findOrFail($fieldset_id);
        $fieldset->fields()->attach($field->id, ['required' => ($request->input('required') == 'on'), 'order' => $request->input('order', $fieldset->fields->count())]);

        return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.update.success')));
    }

    public function disassociate(Request $request, $field_id): JsonResponse
    {
        $this->authorize('update', CustomFieldset::class);
        $field = CustomField::findOrFail($field_id);

        $fieldset_id = $request->input('fieldset_id');
        foreach ($field->fieldset as $fieldset) {
            if ($fieldset->id == $fieldset_id) {
                $fieldset->fields()->detach($field->id);

                return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.update.success')));
            }
        }
        $fieldset = CustomFieldset::findOrFail($fieldset_id);

        return response()->json(Helper::formatStandardApiResponse('success', $fieldset, trans('admin/custom_fields/message.fieldset.update.success')));
    }

    /**
     * Delete a custom field.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    public function destroy($field_id): JsonResponse
    {
        $field = CustomField::findOrFail($field_id);

        $this->authorize('delete', $field);

        if ($field->fieldset->count() > 0) {
            return response()->json(Helper::formatStandardApiResponse('error', null, 'Field is in use.'));
        }

        $field->delete();

        return response()->json(Helper::formatStandardApiResponse('success', null, trans('admin/custom_fields/message.field.delete.success')));
    }
}
