<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\CustomField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkCustomFieldsController extends Controller
{
    /**
     * Bulk-destroy custom fields. UI filters out non-deletable rows via
     * checkboxEnabledFormatter, but the POST is re-checked here with
     * isDeletable() so a snapshot-replay or a hand-crafted request
     * can't bypass the "still attached to a fieldset" guard.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', CustomField::class);

        $ids = $request->input('ids', []);
        if (! is_array($ids) || empty($ids)) {
            return redirect()->route('fields.index')
                ->with('error', trans('admin/custom_fields/message.field.none_selected'));
        }

        $errors = [];
        $success_count = 0;
        foreach ($ids as $id) {
            $field = CustomField::find($id);
            if (is_null($field)) {
                $errors[] = trans('admin/custom_fields/message.field.invalid');

                continue;
            }
            if (! $field->isDeletable()) {
                $errors[] = trans('admin/custom_fields/message.field.delete.in_use_named', ['name' => $field->name]);

                continue;
            }
            try {
                $field->delete();
                $success_count++;
            } catch (\Throwable $e) {
                Handler::reportOrRethrow($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('fields.index')
                    ->with('success', trans_choice('admin/custom_fields/message.field.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('fields.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('fields.index')
            ->with('success', trans_choice('admin/custom_fields/message.field.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
