<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * This controller handles all actions related to Companies for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
final class CompaniesController extends Controller
{
    /**
     * Returns view to display listing of companies.
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     */
    public function index(): View
    {
        $this->authorize('view', Company::class);

        return view('companies/index');
    }

    /**
     * Returns view to create a new company.
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     */
    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies/edit')->with('item', new Company);
    }

    /**
     * Save data from new company form.
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     *
     * @param  Request  $request
     */
    public function store(ImageUploadRequest $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $company = new Company;
        $company->name = $request->input('name');
        $company->parent_id = $request->input('parent_id');
        $company->phone = $request->input('phone');
        $company->fax = $request->input('fax');
        $company->email = $request->input('email');
        $company->tag_color = $request->input('tag_color');
        $company->notes = $request->input('notes');
        $company->created_by = auth()->id();

        $company = $request->handleImages($company);

        if ($company->save()) {
            return redirect()->route('companies.index')
                ->with('success', trans('admin/companies/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($company->getErrors());
    }

    /**
     * Return form to edit existing company.
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     *
     * @param  int  $companyId
     */
    public function edit(Company $company): View|RedirectResponse
    {
        $this->authorize('update', $company);

        return view('companies/edit')->with('item', $company);
    }

    /**
     * Save data from edit company form.
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     *
     * @param  int  $companyId
     */
    public function update(ImageUploadRequest $request, Company $company): RedirectResponse
    {

        $this->authorize('update', $company);
        $company->name = $request->input('name');
        $company->parent_id = $request->input('parent_id');
        $company->phone = $request->input('phone');
        $company->fax = $request->input('fax');
        $company->email = $request->input('email');
        $company->tag_color = $request->input('tag_color');
        $company->notes = $request->input('notes');

        $company = $request->handleImages($company);

        if ($company->save()) {
            return redirect()->route('companies.index')
                ->with('success', trans('admin/companies/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($company->getErrors());
    }

    /**
     * Delete company
     *
     * @author [Abdullah Alansari] [<ahimta@gmail.com>]
     *
     * @since [v1.8]
     *
     * @param  int  $companyId
     */
    public function destroy($companyId): RedirectResponse
    {

        if (is_null($company = Company::find($companyId))) {
            return redirect()->route('companies.index')
                ->with('error', trans('admin/companies/message.not_found'));
        }

        $this->authorize('delete', $company);
        if (! $company->isDeletable()) {
            return redirect()->route('companies.index')
                ->with('error', trans('admin/companies/message.assoc_users'));
        }

        if ($company->image) {
            try {
                Storage::disk('public')->delete('companies'.'/'.$company->image);
            } catch (\Exception $e) {
                Log::debug($e);
            }
        }

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', trans('admin/companies/message.delete.success'));
    }

    public function show(Company $company): View|RedirectResponse
    {
        $this->authorize('view', Company::class);

        // Eager-load the hierarchy so the sidebar's parent + children list
        // doesn't trigger lazy lookups during render.
        $company->loadMissing('parent', 'children');

        // Counts on the tab badges include hierarchy (the row + parent + children)
        // to match the tab contents the API serves under expand_company_hierarchy=1.
        // Computed here so the Blade can stay free of @php blocks.
        $reachableIds = Company::reachableCompanyIds($company->id);

        return view('companies/view')
            ->with('company', $company)
            ->with('tabCounts', [
                'users' => User::whereHas('companies', fn ($q) => $q->whereIn('companies.id', $reachableIds))->count(),
                'assets' => Asset::whereIn('company_id', $reachableIds)->AssetsForShow()->count(),
                'licenses' => License::whereIn('company_id', $reachableIds)->count(),
                'accessories' => Accessory::whereIn('company_id', $reachableIds)->count(),
                'consumables' => Consumable::whereIn('company_id', $reachableIds)->count(),
                'components' => Component::whereIn('company_id', $reachableIds)->count(),
            ]);
    }
}
