<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;

/**
 * This controller handles all actions related to Asset Maintenance for
 * the Snipe-IT Asset Management application.
 *
 * @version    v2.0
 */
class MaintenancesController extends Controller
{

    /**
    *  Returns a view that invokes the ajax tables which actually contains
    * the content for the asset maintenances listing.
    */
    public function index() : View
    {
        $this->authorize('view', Asset::class);
        return view('maintenances.index');
    }

    /**
     *  Returns a form view to create a new asset maintenance.
     *
     * @see MaintenancesController::postCreate() method that stores the data
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @version v1.0
     * @since [v1.8]
     * @return mixed
     */
    public function create() : View
    {
        $this->authorize('update', Asset::class);
        $asset = null;

        if ($asset = Asset::find(request('asset_id'))) {
            // We have to set this so that the correct property is set in the select2 ajax dropdown
            $asset->asset_id = $asset->id;
        }
        
        return view('maintenances/edit')
                   ->with('maintenanceType', Maintenance::getImprovementOptions())
                   ->with('asset', $asset)
                   ->with('item', new Maintenance);
    }

    /**
    *  Validates and stores the new asset maintenance
    *
    * @see MaintenancesController::getCreate() method for the form
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function store(ImageUploadRequest $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);

        $assets = Asset::whereIn('id', $request->input('selected_assets'))->get();

        // Loop through the selected assets
        foreach ($assets as $asset) {

            $maintenance = new Maintenance();
            $maintenance->supplier_id = $request->input('supplier_id');
            $maintenance->is_warranty = $request->input('is_warranty');
            $maintenance->cost = $request->input('cost');
            $maintenance->notes = $request->input('notes');
            $maintenance->url = $request->input('url');

            // Save the asset maintenance data
            $maintenance->asset_id = $asset->id;
            $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
            $maintenance->name = $request->input('name');
            $maintenance->start_date = $request->input('start_date');
            $maintenance->completion_date = $request->input('completion_date');
            $maintenance->created_by = auth()->id();

            if (($maintenance->completion_date !== null)
                && ($maintenance->start_date !== '')
                && ($maintenance->start_date !== '0000-00-00')
            ) {
                $startDate = Carbon::parse($maintenance->start_date);
                $completionDate = Carbon::parse($maintenance->completion_date);
                $maintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
            }

            $maintenance = $request->handleImages($maintenance);

            // Was the asset maintenance created?
            if (!$maintenance->save()) {
                return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
            }
        }

        return redirect()->route('maintenances.index')
            ->with('success', trans('admin/maintenances/message.create.success'));

    }

    /**
    *  Returns a form view to edit a selected asset maintenance.
    *
    * @see MaintenancesController::postEdit() method that stores the data
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function edit(Maintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        return view('maintenances/edit')
            ->with('selected_assets', $maintenance->asset->pluck('id')->toArray())
            ->with('asset_ids', request()->input('asset_ids', []))
            ->with('maintenanceType', Maintenance::getImprovementOptions())
            ->with('item', $maintenance);
    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @see MaintenancesController::postEdit() method that stores the data
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @param Request $request
     * @param int $maintenanceId
     * @version v1.0
     * @since [v1.8]
     */
    public function update(ImageUploadRequest $request, Maintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        $maintenance->supplier_id = $request->input('supplier_id');
        $maintenance->is_warranty = $request->input('is_warranty', 0);
        $maintenance->cost =  $request->input('cost');
        $maintenance->notes = $request->input('notes');
        $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
        $maintenance->name = $request->input('name');
        $maintenance->start_date = $request->input('start_date');
        $maintenance->completion_date = $request->input('completion_date');
        $maintenance->url = $request->input('url');


        // Todo - put this in a getter/setter?
        if (($maintenance->completion_date == null))
        {
            if (($maintenance->asset_maintenance_time !== 0)
              || (! is_null($maintenance->asset_maintenance_time))
            ) {
                $maintenance->asset_maintenance_time = null;
            }
        }

        if (($maintenance->completion_date !== null)
          && ($maintenance->start_date !== '')
          && ($maintenance->start_date !== '0000-00-00')
        ) {
            $startDate = Carbon::parse($maintenance->start_date);
            $completionDate = Carbon::parse($maintenance->completion_date);
            $maintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
        }
        $maintenance = $request->handleImages($maintenance);

        if ($maintenance->save()) {
            return redirect()->route('maintenances.index')
                            ->with('success', trans('admin/maintenances/message.edit.success'));
        }

        return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
    }

    /**
    *  Delete an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $maintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function destroy(Maintenance $maintenance) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);
        // Delete the asset maintenance
        $maintenance->delete();
        // Redirect to the asset_maintenance management page
        return redirect()->route('maintenances.index')
                       ->with('success', trans('admin/maintenances/message.delete.success'));
    }

    /**
    *  View an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $maintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function show(Maintenance $maintenance) : View | RedirectResponse
    {
        return view('maintenances.view')->with('maintenance', $maintenance);
    }
}
