<?php

namespace App\Http\Controllers;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * This controller handles all actions related to Custom Asset Fields for
 * the Snipe-IT Asset Management application.
 *
 * @todo Improve documentation here.
 * @todo Check for raw DB queries and try to convert them to query builder statements
 *
 * @version    v2.0
 *
 * @author [Brady Wetherington] [<uberbrady@gmail.com>]
 */
class CustomFieldsetsController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('fields.index')
            ->withFragment('fieldsets')
            ->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
    }

    /**
     * Validates and stores a new custom field.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    public function show(CustomFieldset $fieldset): View|RedirectResponse
    {
        $cfset = CustomFieldset::with('fields')
            ->where('id', '=', $fieldset->id)
            ->orderBy('id', 'ASC')
            ->first();

        $this->authorize('view', $cfset);

        if ($cfset) {
            $custom_fields_list = ['' => 'Add New Field to Fieldset'] + CustomField::pluck('name', 'id')->toArray();

            $maxid = 0;
            foreach ($cfset->fields as $field) {
                if ($field->pivot->order > $maxid) {
                    $maxid = $field->pivot->order;
                }
                if (isset($custom_fields_list[$field->id])) {
                    unset($custom_fields_list[$field->id]);
                }
            }

            return view('custom_fields.fieldsets.view')->with('custom_fieldset', $cfset)->with('maxid', $maxid + 1)->with('custom_fields_list', $custom_fields_list);
        }

        return redirect()->route('fields.index')
            ->withFragment('fieldsets')
            ->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
    }

    /**
     * Returns a view with a form for creating a new custom fieldset.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    /**
     * "New Fieldset" entry point. Shows a name-only prompt instead of
     * the full picker, since the picker needs a persisted fieldset
     * (with an id) to operate on. After the admin submits, store()
     * saves, runs auto-add, and redirects to the picker.
     */
    public function create(): View
    {
        $this->authorize('create', CustomField::class);

        return view('custom_fields.fieldsets.view')->with('custom_fieldset', new CustomFieldset);
    }

    /**
     * Programmatic create path. Still used by external POSTers against
     * the resource route. The UI "New Fieldset" button no longer
     * routes through here, it goes through create() above.
     *
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        $fieldset = new CustomFieldset([
            'name' => $request->input('name'),
            'created_by' => auth()->id(),
        ]);

        $validator = Validator::make($request->all(), $fieldset->rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $fieldset->save();
        $this->syncAutoAddFields($fieldset);

        return redirect()->route('fieldsets.show', [$fieldset->id])
            ->with('success', trans('admin/custom_fields/message.fieldset.create.success'));
    }

    /**
     * Attach every CustomField flagged auto_add_to_fieldsets=1 to the
     * given fieldset, assigning sequential order values + an explicit
     * required=false pivot. Shared by create() and store() so both
     * entry points produce consistent pivot rows. The prior
     * sync($field_ids) call passed bare ids, which left order + required
     * at their column defaults. Every auto-added field landed tied at
     * order=0 and the first drag reorder was funky.
     */
    private function syncAutoAddFields(CustomFieldset $fieldset): void
    {
        $autoAddFieldIds = CustomField::where('auto_add_to_fieldsets', '=', '1')
            ->orderBy('name')
            ->pluck('id')
            ->all();

        if (empty($autoAddFieldIds)) {
            return;
        }

        $pivot = [];
        $order = 1;
        foreach ($autoAddFieldIds as $fieldId) {
            $pivot[(int) $fieldId] = ['order' => $order, 'required' => false];
            $order++;
        }

        $fieldset->fields()->sync($pivot);
    }

    /**
     * Presents edit form for fieldset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v6.0.14]
     */
    public function edit(CustomFieldset $fieldset): View|RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        return view('custom_fields.fieldsets.view')->with('custom_fieldset', $fieldset);
    }

    /**
     * Saves updated fieldset data
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v6.0.14]
     */
    public function update(Request $request, CustomFieldset $fieldset): RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        $fieldset->name = $request->input('name');

        if ($fieldset->save()) {
            return redirect()->route('fields.index')->withFragment('fieldsets')->with('success', trans('admin/custom_fields/general.fieldset_updated'));
        }

        return redirect()->back()->withInput()->withErrors($fieldset->getErrors());

    }

    /**
     * Validates a custom fieldset and then deletes if it has no models associated.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @param  int  $id
     *
     * @since [v1.8]
     */
    public function destroy($id): RedirectResponse
    {
        $fieldset = CustomFieldset::find($id);

        $this->authorize('delete', $fieldset);

        if ($fieldset) {
            $models = AssetModel::where('fieldset_id', '=', $id);
            if ($models->count() == 0) {
                $fieldset->delete();

                return redirect()->route('fields.index')->withFragment('fieldsets')->with('success', trans('admin/custom_fields/message.fieldset.delete.success'));
            }

            return redirect()->route('fields.index')->withFragment('fieldsets')->with('error', trans('admin/custom_fields/message.fieldset.delete.in_use'));
        }

        return redirect()->route('fields.index')->withFragment('fieldsets')->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
    }

    /**
     * Associate the custom field with a custom fieldset.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    public function associate(Request $request, $id): RedirectResponse
    {
        $set = CustomFieldset::find($id);

        $this->authorize('update', $set);

        if ($request->filled('field_id')) {
            foreach ($set->fields as $field) {
                if ($field->id == $request->input('field_id')) {
                    return redirect()->route('fieldsets.show', [$id])->withInput()->withErrors(['field_id' => trans('admin/custom_fields/message.field.already_added')]);
                }
            }

            $set->fields()->attach($request->input('field_id'), ['required' => ($request->input('required') == 'on'), 'order' => (int) $request->input('order', 1)]);

            return redirect()->route('fieldsets.show', [$id])->with('success', trans('admin/custom_fields/message.field.create.assoc_success'));
        }

        return redirect()->route('fieldsets.show', [$id])->with('error', trans('admin/custom_fields/message.field.none_selected'));
    }

    /**
     * Set the field in a fieldset to required
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.0]
     */
    public function makeFieldRequired($fieldset_id, $field_id): RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::findOrFail($field_id);
        $fieldset = CustomFieldset::findOrFail($fieldset_id);
        $fields[$field->id] = ['required' => 1];
        $fieldset->fields()->syncWithoutDetaching($fields);

        return redirect()->route('fieldsets.show', ['fieldset' => $fieldset_id])
            ->with('success', trans('Field successfully set to required'));
    }

    /**
     * Set the field in a fieldset to optional
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.0]
     */
    public function makeFieldOptional($fieldset_id, $field_id): RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::findOrFail($field_id);
        $fieldset = CustomFieldset::findOrFail($fieldset_id);
        $fields[$field->id] = ['required' => 0];
        $fieldset->fields()->syncWithoutDetaching($fields);

        return redirect()->route('fieldsets.show', ['fieldset' => $fieldset_id])
            ->with('success', trans('Field successfully set to optional'));
    }
}
