<?php

namespace App\Http\Controllers;

use App\Actions\AssetModels\MergeAssetModelsAction;
use App\Helpers\Helper;
use App\Models\AssetModel;
use App\Models\CustomFieldset;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BulkAssetModelsController extends Controller
{
    /**
     * Returns a view that allows the user to bulk edit model attrbutes
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.7]
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $models_raw_array = $request->input('ids');

        // Make sure some IDs have been selected
        if ((is_array($models_raw_array)) && (count($models_raw_array) > 0)) {
            $models = AssetModel::whereIn('id', $models_raw_array)
                ->with('manufacturer', 'category')
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

                if ($valid_count === 0) {
                    return redirect()->route('models.index')
                        ->with('error', trans('admin/models/message.bulkdelete.nothing_deletable'));
                }

                return view('models/bulk-delete', compact('models'))->with('valid_count', $valid_count);

            }

            // Merge: pick a surviving target, reassign every other selected
            // model's assets onto the target, then delete the sources.
            // Common driver: a sync adapter that auto-created an
            // AssetModel keyed on the vendor's part number sitting next
            // to the same-hardware model keyed on the marketing name.
            // The merge tool lets admins collapse them without hand-
            // walking every asset.
            if ($request->input('bulk_actions') == 'merge') {
                $this->authorize('delete', AssetModel::class);
                if ($models->count() < 2) {
                    return redirect()->route('models.index')
                        ->with('error', trans('admin/models/message.merge.min_two'));
                }

                return view('models/confirm-merge', [
                    'models' => $models,
                    'fieldsets' => CustomFieldset::whereIn('id', $models->pluck('fieldset_id')->filter())->get()->keyBy('id'),
                ]);

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
     *
     * @since [v1.7]
     */
    public function update(Request $request): View|RedirectResponse
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
     *
     * @since [v1.0]
     */
    public function destroy(Request $request): RedirectResponse
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
                    ->with('success', trans('admin/models/message.bulkdelete.success', ['success_count' => $del_count]));
            }

            return redirect()->route('models.index')
                ->with('warning', trans('admin/models/message.bulkdelete.success_partial', ['fail_count' => $del_error_count, 'success_count' => $del_count]));
        }

        return redirect()->route('models.index')
            ->with('error', trans('admin/models/message.bulkdelete.error'));
    }

    /**
     * Merge two or more AssetModels into one target. Every asset on
     * the sources gets its `model_id` pointed at the target, then the
     * source models are soft-deleted. Wrapped in a transaction so a
     * partial failure leaves the models table in its original state.
     *
     * Fixes the "phantom AssetModel" cleanup shape when a sync adapter
     * auto-created models keyed on hardwareModel next to the same-
     * hardware model keyed on the marketing name.
     */
    public function merge(Request $request): RedirectResponse
    {
        $this->authorize('delete', AssetModel::class);

        $target_id = (int) $request->input('merge_into_id');
        $source_ids = array_map('intval', (array) $request->input('ids_to_merge'));
        $source_ids = array_values(array_diff($source_ids, [$target_id]));

        if ($target_id === 0 || $source_ids === []) {
            return redirect()->route('models.index')
                ->with('error', trans('admin/models/message.merge.no_target'));
        }

        $target = AssetModel::find($target_id);
        $sources = AssetModel::whereIn('id', $source_ids)->get();

        if ($target === null || $sources->count() !== count($source_ids)) {
            return redirect()->route('models.index')
                ->with('error', trans('admin/models/message.merge.not_found'));
        }

        $moved_assets = MergeAssetModelsAction::run(
            target: $target,
            sources: $sources,
            admin: User::find(auth()->id()),
        );

        return redirect()->route('models.index')
            ->with('success', trans('admin/models/message.merge.success', [
                'source_count' => $sources->count(),
                'asset_count' => $moved_assets,
                'target' => $target->name,
            ]));
    }
}
