<?php

namespace App\Actions\Categories;

use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssetModels;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class DestroyCategoryAction
{
    /**
     * @throws ItemStillHasAssets
     * @throws ItemStillHasAssetModels
     * @throws ItemStillHasComponents
     * @throws ItemStillHasAccessories
     * @throws ItemStillHasLicenses
     * @throws ItemStillHasConsumables
     */
    static function run(Category $category): bool
    {
        $category->loadCount([
            'assets as assets_count',
            'accessories as accessories_count',
            'consumables as consumables_count',
            'components as components_count',
            'licenses as licenses_count',
            'models as models_count'
        ]);

        if ($category->assets_count > 0) {
            throw new ItemStillHasAssets($category);
        }
        if ($category->accessories_count > 0) {
            throw new ItemStillHasAccessories($category);
        }
        if ($category->consumables_count > 0) {
            throw new ItemStillHasConsumables($category);
        }
        if ($category->components_count > 0) {
            throw new ItemStillHasComponents($category);
        }
        if ($category->licenses_count > 0) {
            throw new ItemStillHasLicenses($category);
        }
        if ($category->models_count > 0) {
            throw new ItemStillHasAssetModels($category);
        }

        Storage::disk('public')->delete('categories'.'/'.$category->image);
        $category->delete();

        return true;
    }
}