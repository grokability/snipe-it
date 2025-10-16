<?php

namespace App\Http\Controllers;

use App\Actions\Categories\DestroyCategoryAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssetModels;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Models\Category;
use Illuminate\Http\Request;

class BulkCategoriesController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Category::class);

        $errors = [];
        $success_count = 0;

        foreach ($request->ids as $id) {
            $category = Category::find($id);
            if (is_null($category)) {
                $errors[] = trans('admin/categories/message.does_not_exist');
                continue;
            }
            try {
                DestroyCategoryAction::run(category: $category);
                $success_count++;
            } catch (ItemStillHasAccessories $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_assets_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);
            } catch (ItemStillHasAssetModels) {
                $errors[] = trans('general.bulk_delete_associations.assoc_asset_models_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);
            } catch (ItemStillHasAssets) {
                $errors[] = trans('general.bulk_delete_associations.assoc_assets_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);
            } catch (ItemStillHasComponents) {
                $errors[] = trans('general.bulk_delete_associations.assoc_components_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);
            } catch (ItemStillHasConsumables) {
                $errors[] = trans('general.bulk_delete_associations.assoc_consumables_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);
            } catch (ItemStillHasLicenses) {
                $errors[] = trans('general.bulk_delete_associations.assoc_licenses_no_count', ['item_name' => $category->name, 'item' => trans('general.category')]);;
            } catch (\Exception $e) {
                report($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }
        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('categories.index')->with('success', trans_choice('admin/categories/message.delete.partial_success', $success_count, ['count' => $success_count]))->with('multi_error_messages', $errors);
            }
            return redirect()->route('categories.index')->with('multi_error_messages', $errors);
        } else {
            return redirect()->route('categories.index')->with('success', trans('admin/categories/message.delete.bulk_success'));
        }
    }
}
