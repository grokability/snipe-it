<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use App\Models\CustomFieldset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkCustomFieldsetsController extends Controller
{
    /**
     * Bulk-destroy custom fieldsets. UI filters out non-deletable rows via
     * checkboxEnabledFormatter, but the POST is re-checked here with
     * isDeletable() so a snapshot-replay or a hand-crafted request
     * can't bypass the "still attached to an asset model" guard.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', CustomFieldset::class);

        $ids = $request->input('ids', []);
        if (! is_array($ids) || empty($ids)) {
            return redirect()->route('fields.index')->withFragment('fieldsets')
                ->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
        }

        $errors = [];
        $success_count = 0;
        foreach ($ids as $id) {
            $fieldset = CustomFieldset::find($id);
            if (is_null($fieldset)) {
                $errors[] = trans('admin/custom_fields/message.fieldset.does_not_exist');

                continue;
            }
            if (! $fieldset->isDeletable()) {
                $errors[] = trans('admin/custom_fields/message.fieldset.delete.in_use_named', ['name' => $fieldset->name]);

                continue;
            }
            try {
                $fieldset->delete();
                $success_count++;
            } catch (\Throwable $e) {
                Handler::reportOrRethrow($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('fields.index')->withFragment('fieldsets')
                    ->with('success', trans_choice('admin/custom_fields/message.fieldset.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('fields.index')->withFragment('fieldsets')
                ->with('multi_error_messages', $errors);
        }

        return redirect()->route('fields.index')->withFragment('fieldsets')
            ->with('success', trans_choice('admin/custom_fields/message.fieldset.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
