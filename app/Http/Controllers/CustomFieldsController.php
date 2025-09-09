<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\CustomFieldRequest;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Custom Asset Fields for
 * the Snipe-IT Asset Management application.
 *
 * @todo Improve documentation here.
 * @todo Check for raw DB queries and try to convert them to query builder statements
 * @version    v2.0
 * @author [Brady Wetherington] [<uberbrady@gmail.com>]
 */
class CustomFieldsController extends Controller
{
    /**
     * Returns a view with a listing of custom fields.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     */
    public function index() : View
    {
        $this->authorize('view', CustomField::class);

        $fieldsets = CustomFieldset::with('fields', 'models')->get();
        $fields = CustomField::with('fieldset')->get();

        return view('custom_fields.index')->with('custom_fieldsets', $fieldsets)->with('custom_fields', $fields);
    }

    /**
     * Just redirect the user back if they try to view the details of a field.
     * We already show those details on the listing page.
     *
     * @see CustomFieldsController::storeField()
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.1.5]
     */
    public function show() : RedirectResponse
    {
        return redirect()->route('fields.index');
    }


    /**
     * Returns a view with a form to create a new custom field.
     *
     * @see CustomFieldsController::storeField()
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     */
    public function create(Request $request) : View
    {
        $this->authorize('create', CustomField::class);
        $fieldsets = CustomFieldset::get();

        return view('custom_fields.fields.edit', [
            'predefinedFormats' => Helper::predefined_formats(),
            'customFormat' => '',
            'fieldsets' => $fieldsets,
            'field' => new CustomField(),
        ]);
    }

    /**
     * Validates and stores a new custom field.
     *
     * @see CustomFieldsController::createField()
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     */
    public function store(CustomFieldRequest $request) : RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        $show_in_email = $request->input("show_in_email", 0);
        $display_in_user_view = $request->input("display_in_user_view", 0);

        // Override the display settings if the field is encrypted
        if ($request->input("field_encrypted") == '1') {
            $show_in_email = '0';
            $display_in_user_view = '0';
        }
        
        $field = new CustomField([
            "name" => trim($request->input("name")),
            "element" => $request->input("element"),
            "help_text" => $request->input("help_text"),
            "field_values" => $request->input("field_values"),
            "field_encrypted" => $request->input("field_encrypted", 0),
            "show_in_email" => $show_in_email,
            "is_unique" => $request->input("is_unique", 0),
            "display_in_user_view" => $display_in_user_view,
            "auto_add_to_fieldsets" => $request->input("auto_add_to_fieldsets", 0),
            "show_in_listview" => $request->input("show_in_listview", 0),
            "show_in_requestable_list" => $request->input("show_in_requestable_list", 0),
            "display_checkin" => $request->input("display_checkin", 0),
            "display_checkout" => $request->input("display_checkout", 0),
            "display_audit" => $request->input("display_audit", 0),
            "created_by" => auth()->id()
        ]);


        if ($request->filled('custom_format')) {
            $field->format = $request->get('custom_format');
        } else {
            $field->format = $request->get('format');
        }

        if ($field->save()) {

            // Sync fields with fieldsets
            $fieldset_array = $request->input('associate_fieldsets');
            if ($request->has('associate_fieldsets') && (is_array($fieldset_array))) {
                $field->fieldset()->sync(array_keys($fieldset_array));
            } else {
                $field->fieldset()->sync([]);
            }


            return redirect()->route('fields.index')->with('success', trans('admin/custom_fields/message.field.create.success'));
        }

        return redirect()->back()->with('selected_fieldsets', $request->input('associate_fieldsets'))->withInput()
            ->with('error', trans('admin/custom_fields/message.field.create.error'));
    }


    /**
     * Detach a custom field from a fieldset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     */
    public function deleteFieldFromFieldset($field_id, $fieldset_id) : RedirectResponse
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
     * @since [v1.8]
     */
    public function destroy(CustomField $field) : RedirectResponse
    {
        $this->authorize('delete', CustomField::class);

        if (($field->fieldset) && ($field->fieldset->count() > 0)) {
            return redirect()->back()->with('error', trans('admin/custom_fields/message.field.delete.in_use'));
        }
        $field->delete();
        return redirect()->route("fields.index")
            ->with("success", trans('admin/custom_fields/message.field.delete.success'));
    }


    /**
     * Return a view to edit a custom field
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $id
     * @since [v4.0]
     */
    public function edit(Request $request, CustomField $field) : View | RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $fieldsets = CustomFieldset::get();
        $customFormat = '';
        if ((stripos($field->format, 'regex') === 0) && ($field->format !== CustomField::PREDEFINED_FORMATS['MAC'])) {
            $customFormat = $field->format;
        }

        return view('custom_fields.fields.edit', [
            'field'             => $field,
            'customFormat'      => $customFormat,
            'fieldsets'         => $fieldsets,
            'predefinedFormats' => Helper::predefined_formats(),
        ]);

    }


    /**
     * Store the updated field
     *
     * @todo Allow encrypting/decrypting if encryption status changes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $id
     * @since [v4.0]
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(CustomFieldRequest $request, CustomField $field) : RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $show_in_email = $request->get("show_in_email", 0);
        $display_in_user_view = $request->get("display_in_user_view", 0);

        // Override the display settings if the field is encrypted
        if ($request->get("field_encrypted") == '1') {
            $show_in_email = '0';
            $display_in_user_view = '0';
        }
        
        $field->name          = trim($request->get("name"));
        $field->element       = $request->get("element");
        $field->field_values  = $request->get("field_values");
        $field->created_by       = auth()->id();
        $field->help_text     = $request->get("help_text");
        $field->show_in_email = $show_in_email;
        $field->is_unique     = $request->get("is_unique", 0);
        $field->display_in_user_view = $display_in_user_view;
        $field->auto_add_to_fieldsets = $request->get("auto_add_to_fieldsets", 0);
        $field->show_in_listview = $request->get("show_in_listview", 0);
        $field->show_in_requestable_list = $request->get("show_in_requestable_list", 0);
        $field->display_checkin = $request->get("display_checkin", 0);
        $field->display_checkout = $request->get("display_checkout", 0);
        $field->display_audit = $request->get("display_audit", 0);

        if ($request->get('format') == 'CUSTOM REGEX') {
            $field->format = $request->get('custom_format');
        } else {
            $field->format = $request->get('format');
        }

        if ($field->element == 'checkbox' || $field->element == 'radio'){
            $field->format = 'ANY';
        }

        if ($field->save()) {

            // Sync fields with fieldsets
            $fieldset_array = $request->input('associate_fieldsets');
            if ($request->has('associate_fieldsets') && (is_array($fieldset_array))) {
                $field->fieldset()->sync(array_keys($fieldset_array));
            } else {
                $field->fieldset()->sync([]);
            }

            return redirect()->route('fields.index')->with('success', trans('admin/custom_fields/message.field.update.success'));
        }

        return redirect()->back()->withInput()->with('error', trans('admin/custom_fields/message.field.update.error'));
    }
}
