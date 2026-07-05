<?php

namespace App\Http\Controllers;

use App\Actions\Depreciations\DestroyDepreciationAction;
use App\Exceptions\ItemStillHasAssetModels;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasLicenses;
use App\Models\Depreciation;
use Illuminate\Http\Request;

class BulkDepreciationsController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Depreciation::class);

        $errors = [];
        $success_count = 0;

        foreach ($request->ids as $id) {
            $depreciation = Depreciation::find($id);
            if (is_null($depreciation)) {
                $errors[] = trans('admin/depreciations/message.does_not_exist');

                continue;
            }
            try {
                DestroyDepreciationAction::run(depreciation: $depreciation);
                $success_count++;
            } catch (ItemStillHasAssets $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_assets_no_count', ['item_name' => $depreciation->name, 'item' => trans('general.depreciation')]);
            } catch (ItemStillHasAssetModels $e) {
                $errors[] = trans('general.bulk_delete_associations.asset_models_no_count', ['item_name' => $depreciation->name, 'item' => trans('general.depreciation')]);
            } catch (ItemStillHasLicenses $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_licenses_no_count', ['item_name' => $depreciation->name, 'item' => trans('general.depreciation')]);
            } catch (\Exception $e) {
                report($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('depreciations.index')
                    ->with('success', trans_choice('admin/depreciations/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('depreciations.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('depreciations.index')
            ->with('success', trans_choice('admin/depreciations/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
