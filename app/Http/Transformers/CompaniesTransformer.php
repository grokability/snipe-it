<?php

namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CompaniesTransformer
{
    public function transformCompanies(Collection $companies, $total)
    {
        $array = [];
        foreach ($companies as $company) {
            $array[] = self::transformCompany($company);
        }

        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformCompany(Company $company = null)
    {
        if ($company) {
            $array = [
                'id' => (int) $company->id,
                'name' => e($company->name),
                'phone' => ($company->phone!='') ? e($company->phone): null,
                'fax' => ($company->fax!='') ? e($company->fax): null,
                'email' => ($company->email!='') ? e($company->email): null,
                'image' =>   ($company->image) ? Storage::disk('public')->url('companies/'.e($company->image)) : null,
                'assets_count' => (int) $company->assets_count,
                'licenses_count' => (int) $company->licenses_count,
                'accessories_count' => (int) $company->accessories_count,
                'consumables_count' => (int) $company->consumables_count,
                'components_count' => (int) $company->components_count,
                'users_count' => (int) $company->users_count,
                'created_by' => ($company->adminuser) ? [
                    'id' => (int) $company->adminuser->id,
                    'name'=> e($company->adminuser->display_name),
                ] : null,
                'notes' => Helper::parseEscapedMarkedownInline($company->notes),
                'created_at' => Helper::getFormattedDateObject($company->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($company->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Company::class),
                'delete' => $company->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }
    }
}
