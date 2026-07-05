<?php

namespace App\Http\Controllers;

use App\Actions\StatusLabels\DestroyStatuslabelAction;
use App\Exceptions\ItemStillHasAssets;
use App\Models\Statuslabel;
use Illuminate\Http\Request;

class BulkStatuslabelsController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Statuslabel::class);

        $errors = [];
        $success_count = 0;

        foreach ($request->ids as $id) {
            $statuslabel = Statuslabel::find($id);
            if (is_null($statuslabel)) {
                $errors[] = trans('admin/statuslabels/message.does_not_exist');

                continue;
            }
            try {
                DestroyStatuslabelAction::run(statuslabel: $statuslabel);
                $success_count++;
            } catch (ItemStillHasAssets $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_assets_no_count', ['item_name' => $statuslabel->name, 'item' => trans('admin/statuslabels/table.status_label')]);
            } catch (\Exception $e) {
                report($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('statuslabels.index')
                    ->with('success', trans_choice('admin/statuslabels/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('statuslabels.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('statuslabels.index')
            ->with('success', trans_choice('admin/statuslabels/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
