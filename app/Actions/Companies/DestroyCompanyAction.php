<?php

namespace App\Actions\Companies;

use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasChildCompanies;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Exceptions\ItemStillHasUsers;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DestroyCompanyAction
{
    /**
     * @throws ItemStillHasAssets
     * @throws ItemStillHasAccessories
     * @throws ItemStillHasLicenses
     * @throws ItemStillHasComponents
     * @throws ItemStillHasConsumables
     * @throws ItemStillHasUsers
     * @throws ItemStillHasChildCompanies
     */
    public static function run(Company $company): bool
    {
        $company->loadCount([
            'assets as assets_count',
            'accessories as accessories_count',
            'licenses as licenses_count',
            'components as components_count',
            'consumables as consumables_count',
            'users as users_count',
            // Self-relation: children() drops CompanyableScope (which hardcodes
            // companies.id and collides with Eloquent's laravel_reserved_0
            // alias). Match that here or the count aliases fight the scope.
            'children as children_count' => fn ($q) => $q->withoutGlobalScopes(),
        ]);

        if ($company->assets_count > 0) {
            throw new ItemStillHasAssets($company);
        }

        if ($company->accessories_count > 0) {
            throw new ItemStillHasAccessories($company);
        }

        if ($company->licenses_count > 0) {
            throw new ItemStillHasLicenses($company);
        }

        if ($company->components_count > 0) {
            throw new ItemStillHasComponents($company);
        }

        if ($company->consumables_count > 0) {
            throw new ItemStillHasConsumables($company);
        }

        if ($company->users_count > 0) {
            throw new ItemStillHasUsers($company);
        }

        if ($company->children_count > 0) {
            throw new ItemStillHasChildCompanies($company);
        }

        if ($company->image) {
            try {
                Storage::disk('public')->delete('companies/'.$company->image);
            } catch (\Exception $e) {
                Log::info($e->getMessage());
            }
        }

        $company->delete();

        return true;
    }
}
