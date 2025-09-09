<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\AssetModel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

class BulkAssetModelsController extends Controller
{
    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     * @param Request $request
     */
    public function edit(Request $request) : View | RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)
                ->withCount('assets as assets_count')
                ->orderBy('assets_count', 'ASC')
                ->get();

            // If deleting....
            if ($request->input('bulk_actions') == 'delete') {
                $this->authorize('delete', AssetModel::class);
                $valid_count = 0;
                foreach ($models as $model) {
                    if ($model->assets_count == 0) {
                        $valid_count++;
                    }
                }

                return view('models/bulk-delete', compact('models'))->with('valid_count', $valid_count);

                // Otherwise display the bulk edit screen
            }
            $this->authorize('update', AssetModel::class);
            $nochange = ['NC' => 'No Change'];

            return view('models/bulk-edit', compact('models'))
                ->with('fieldset_list', $nochange + Helper::customFieldsetList())
                ->with('depreciation_list', $nochange + Helper::depreciationList());
        }

        return redirect()->route('models.index')
            ->with('error', 'You must select at least one model to edit.');
    }

    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.7]
     * @param Request $request
     */
    public function update(Request $request): View | RedirectResponse
    {
        $this->authorize('update', AssetModel::class);
      
        $models_raw_array = $request->input('ids');
        $update_array = [];

        if (($request->filled('manufacturer_id') && ($request->input('manufacturer_id') != 'NC'))) {
            $update_array['manufacturer_id'] = $request->input('manufacturer_id');
        }
        
        if (($request->filled('category_id') && ($request->input('category_id') != 'NC'))) {
            $update_array['category_id'] = $request->input('category_id');
        }

        if ($request->input('fieldset_id') != 'NC') {
            $update_array['fieldset_id'] = $request->input('fieldset_id');
        }

        if ($request->input('depreciation_id') != 'NC') {
            $update_array['depreciation_id'] = $request->input('depreciation_id');
        }

        if ($request->input('requestable') != '') {
            $update_array['requestable'] = $request->input('requestable');
        }

        if ($request->filled('min_amt')) {
            $update_array['min_amt'] = $request->input('min_amt');
        }

        if ($request->filled('require_serial')) {
            $update_array['require_serial'] = $request->input('require_serial');
        }

        if (count($update_array) > 0) {
            AssetModel::whereIn('id', $models_raw_array)->update($update_array);

            return redirect()->route('models.index')
                ->with('success', trans_choice('admin/models/message.bulkedit.success', count($models_raw_array), ['model_count' => count($models_raw_array)]));
        }

        return redirect()->route('models.index')
            ->with('warning', trans('admin/models/message.bulkedit.error'));
    }

    /**
     * Validate and delete the given Asset Models. An Asset Model
     * cannot be deleted if there are associated assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function destroy(Request $request) : RedirectResponse
    {
        $this->authorize('delete', AssetModel::class);
      
        $models_raw_array = $request->input('ids');

        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)->withCount('assets as assets_count')->get();

            $del_error_count = 0;
            $del_count = 0;

            foreach ($models as $model) {
                if ($model->assets_count > 0) {
                    $del_error_count++;
                } else {
                    $model->delete();
                    $del_count++;
                }
            }

            if ($del_error_count == 0) {
                return redirect()->route('models.index')
                    ->with('success', trans('admin/models/message.bulkdelete.success', ['success_count'=> $del_count]));
            }

            return redirect()->route('models.index')
                ->with('warning', trans('admin/models/message.bulkdelete.success_partial', ['fail_count'=>$del_error_count, 'success_count'=> $del_count]));
        }

        return redirect()->route('models.index')
            ->with('error', trans('admin/models/message.bulkdelete.error'));
    }
}
