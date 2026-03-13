<?php

namespace App\Actions\Suppliers;

use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Models\Supplier;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasMaintenances;
use App\Exceptions\ItemStillHasLicenses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DestroySupplierAction
{
    /**
     *
     * @throws ItemStillHasLicenses
     * @throws ItemStillHasAssets
     * @throws ItemStillHasMaintenances
     * @throws ItemStillHasAccessories
     * @throws ItemStillHasConsumables
     * @throws ItemStillHasComponents
     */
    static function run(Supplier $supplier): bool
    {
        $supplier->loadCount([
            'maintenances as maintenances_count',
            'assets as assets_count',
            'licenses as licenses_count',
            'accessories as accessories_count',
            'consumables as consumables_count',
            'components as components_count',
        ]);
        if ($supplier->assets_count > 0) {
            throw new ItemStillHasAssets($supplier);
        }

        if ($supplier->maintenances_count > 0) {
            throw new ItemStillHasMaintenances($supplier);
        }

        if ($supplier->licenses_count > 0) {
            throw new ItemStillHasLicenses($supplier);
        }

        if ($supplier->accessories_count > 0) {
            throw new ItemStillHasAccessories($supplier);
        }

        if ($supplier->consumables_count > 0) {
            throw new ItemStillHasConsumables($supplier);
        }

        if ($supplier->components_count > 0) {
            throw new ItemStillHasComponents($supplier);
        }

        if ($supplier->image) {
            try {
                Storage::disk('public')->delete('suppliers/'.$supplier->image);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }

        $supplier->delete();

        return true;
    }
}
