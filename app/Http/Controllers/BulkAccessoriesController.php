<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BulkAccessoriesController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Accessory::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            // Accessory uses CompanyableTrait, so Accessory::find() is
            // filtered by CompanyableScope — cross-company rows return null
            // and fall into the "does_not_exist" bucket without ever
            // reaching authorization.
            $accessory = Accessory::find($id);
            if (is_null($accessory)) {
                $errors[] = trans('admin/accessories/message.does_not_exist', ['id' => $id]);

                continue;
            }

            // Per-row auth as belt-and-braces on top of the query scope.
            // Catches FMCS mismatches that a superuser scope-bypass could
            // let through, plus any future policy tightening (e.g. per-role
            // company-based delete gating).
            if (! Gate::allows('delete', $accessory)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            $accessory->loadCount('checkouts as checkouts_count');
            if (! $accessory->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $accessory->name,
                    'item' => trans('general.accessory'),
                ]);

                continue;
            }

            // Note: the image file is deliberately preserved across this
            // soft-delete. Snipe-IT's `snipeit:purge` command permanently
            // removes it later when the row is force-deleted. Keeping
            // the file here means a restored soft-deleted row still has
            // its image.
            $accessory->delete();
            $success_count++;
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('accessories.index')
                    ->with('success', trans_choice('admin/accessories/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('accessories.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('accessories.index')
            ->with('success', trans_choice('admin/accessories/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
