<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
class CustomFieldsController extends Controller
{
    /**
     * Returns a view with a listing of custom fields.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    public function index(): View
    {
        $this->authorize('view', CustomField::class);

        // Pre-fetch every fieldset so the fieldsets tab can pre-render
        // one teleported clone-fieldset livewire modal per row. The
        // AJAX actions column matches the row id back to the matching
        // #cloneFieldsetModal{id} via data-target.
        $cloneable_fieldsets = CustomFieldset::select('id', 'name')->get();

        return view('custom_fields.index', compact('cloneable_fieldsets'));
    }

    /**
     * Just redirect the user back if they try to view the details of a field.
     * We already show those details on the listing page.
     *
     * @see CustomFieldsController::storeField()
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v5.1.5]
     */
    public function show(): RedirectResponse
    {
        return redirect()->route('fields.index');
    }

    /**
     * Detach a custom field from a fieldset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v3.0]
     */
    public function deleteFieldFromFieldset($field_id, $fieldset_id): RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::find($field_id);

        // Check that the field exists - this is mostly related to the demo, where we
        // rewrite the data every x minutes, so it's possible someone might be disassociating
        // a field from a fieldset just as we're wiping the database
        if (($field) && ($fieldset_id)) {

            if ($field->fieldset()->detach($fieldset_id)) {
                return redirect()->route('fieldsets.show', ['fieldset' => $fieldset_id])
                    ->with('success', trans('admin/custom_fields/message.field.delete.success'));
            } else {
                return redirect()->back()->with('error', trans('admin/custom_fields/message.field.delete.error'))
                    ->withInput();
            }
        }

        return redirect()->back()->with('error', trans('admin/custom_fields/message.field.delete.error'));

    }

    /**
     * Delete a custom field.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     *
     * @since [v1.8]
     */
    public function destroy(CustomField $field): RedirectResponse
    {
        $this->authorize('delete', CustomField::class);

        if (($field->fieldset) && ($field->fieldset->count() > 0)) {
            return redirect()->back()->with('error', trans('admin/custom_fields/message.field.delete.in_use'));
        }
        $field->delete();

        return redirect()->route('fields.index')
            ->with('success', trans('admin/custom_fields/message.field.delete.success'));
    }
}
