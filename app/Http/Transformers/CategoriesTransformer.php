<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CategoriesTransformer
{
    public function transformCategories(Collection $categorys, $total)
    {
        $array = [];
        foreach ($categorys as $category) {
            $array[] = self::transformCategory($category);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformCategory(Category $category = null)
    {

        // We only ever use item_count for categories in this transformer, so it makes sense to keep it
        // simple and do this switch here.
        switch ($category->category_type) {
            case 'asset':
                $category->item_count = $category->assets_count;
                break;
            case 'accessory':
                $category->item_count = $category->accessories_count;
                break;
            case 'consumable':
                $category->item_count = $category->consumables_count;
                break;
            case 'component':
                $category->item_count = $category->components_count;
                break;
            case 'license':
                $category->item_count = $category->licenses_count;
                break;
            default:
                $category->item_count = 0;
        }

        if ($category) {
            $array = [
                'id' => (int) $category->id,
                'name' => e($category->name),
                'image' =>   ($category->image) ? Storage::disk('public')->url('categories/'.e($category->image)) : null,
                'category_type' => Helper::categoryTypeList($category->category_type),
                'has_eula' => ($category->getEula() ? true : false),
                'use_default_eula' => ($category->use_default_eula=='1' ? true : false),
                'eula' => ($category->getEula()),
                'checkin_email' => ($category->checkin_email == '1'),
                'require_acceptance' => ($category->require_acceptance == '1'),
                'item_count' => (int) $category->item_count,
                'assets_count' => (int) $category->assets_count,
                'accessories_count' => (int) $category->accessories_count,
                'consumables_count' => (int) $category->consumables_count,
                'components_count' => (int) $category->components_count,
                'licenses_count' => (int) $category->licenses_count,
                'created_by' => ($category->adminuser) ? [
                    'id' => (int) $category->adminuser->id,
                    'name'=> e($category->adminuser->display_name),
                ] : null,
                'notes' => Helper::parseEscapedMarkedownInline($category->notes),
                'created_at' => Helper::getFormattedDateObject($category->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($category->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Category::class),
                'delete' => $category->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }
    }
}
