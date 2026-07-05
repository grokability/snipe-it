<?php

namespace App\Http\Controllers;

use App\Actions\Companies\DestroyCompanyAction;
use App\Exceptions\ItemStillHasAccessories;
use App\Exceptions\ItemStillHasAssets;
use App\Exceptions\ItemStillHasChildCompanies;
use App\Exceptions\ItemStillHasComponents;
use App\Exceptions\ItemStillHasConsumables;
use App\Exceptions\ItemStillHasLicenses;
use App\Exceptions\ItemStillHasUsers;
use App\Models\Company;
use Illuminate\Http\Request;

class BulkCompaniesController extends Controller
{
    public function destroy(Request $request)
    {
        $this->authorize('delete', Company::class);

        $errors = [];
        $success_count = 0;

        foreach ($request->ids as $id) {
            $company = Company::find($id);
            if (is_null($company)) {
                $errors[] = trans('admin/companies/message.does_not_exist');

                continue;
            }
            try {
                DestroyCompanyAction::run(company: $company);
                $success_count++;
            } catch (ItemStillHasAssets $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_assets_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasAccessories $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_accessories_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasLicenses $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_licenses_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasComponents $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_components_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasConsumables $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_consumables_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasUsers $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_users_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (ItemStillHasChildCompanies $e) {
                $errors[] = trans('general.bulk_delete_associations.assoc_child_companies_no_count', ['item_name' => $company->name, 'item' => trans('general.company')]);
            } catch (\Exception $e) {
                report($e);
                $errors[] = trans('general.something_went_wrong');
            }
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('companies.index')
                    ->with('success', trans_choice('admin/companies/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('companies.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('companies.index')
            ->with('success', trans_choice('admin/companies/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }
}
