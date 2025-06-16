<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
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
class AssetMaintenancesController extends Controller
{

    /**
    *  Returns a view that invokes the ajax tables which actually contains
    * the content for the asset maintenances listing, which is generated in getDatatable.
    *
    * @todo This should be replaced with middleware and/or policies
    * @see AssetMaintenancesController::getDatatable() method that generates the JSON response
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function index() : View
    {
        $this->authorize('view', Asset::class);
        return view('asset_maintenances/index');
    }

    /**
     *  Returns a form view to create a new asset maintenance.
     *
     * @see AssetMaintenancesController::postCreate() method that stores the data
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
        
        return view('asset_maintenances/edit')
                   ->with('assetMaintenanceType', AssetMaintenance::getImprovementOptions())
                   ->with('asset', $asset)
                   ->with('item', new AssetMaintenance);
    }

    /**
    *  Validates and stores the new asset maintenance
    *
    * @see AssetMaintenancesController::getCreate() method for the form
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('update', Asset::class);

        $assets = Asset::whereIn('id', $request->input('selected_assets'))->get();

        // Loop through the selected assets
        foreach ($assets as $asset) {

            $assetMaintenance = new AssetMaintenance();
            $assetMaintenance->supplier_id = $request->input('supplier_id');
            $assetMaintenance->is_warranty = $request->input('is_warranty');
            $assetMaintenance->cost = $request->input('cost');
            $assetMaintenance->notes = $request->input('notes');

            // Save the asset maintenance data
            $assetMaintenance->asset_id = $asset->id;
            $assetMaintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
            $assetMaintenance->title = $request->input('title');
            $assetMaintenance->start_date = $request->input('start_date');
            $assetMaintenance->completion_date = $request->input('completion_date');
            $assetMaintenance->created_by = auth()->id();

            if (($assetMaintenance->completion_date !== null)
                && ($assetMaintenance->start_date !== '')
                && ($assetMaintenance->start_date !== '0000-00-00')
            ) {
                $startDate = Carbon::parse($assetMaintenance->start_date);
                $completionDate = Carbon::parse($assetMaintenance->completion_date);
                $assetMaintenance->asset_maintenance_time = (int) $completionDate->diffInDays($startDate, true);
            }


            // Was the asset maintenance created?
            if (!$assetMaintenance->save()) {
                return redirect()->back()->withInput()->withErrors($assetMaintenance->getErrors());
            }
        }

        return redirect()->route('maintenances.index')
            ->with('success', trans('admin/asset_maintenances/message.create.success'));

    }

    /**
    *  Returns a form view to edit a selected asset maintenance.
    *
    * @see AssetMaintenancesController::postEdit() method that stores the data
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @version v1.0
    * @since [v1.8]
    */
    public function edit(AssetMaintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        return view('asset_maintenances/edit')
            ->with('selected_assets', $maintenance->asset->pluck('id')->toArray())
            ->with('asset_ids', request()->input('asset_ids', []))
            ->with('assetMaintenanceType', AssetMaintenance::getImprovementOptions())
            ->with('item', $maintenance);
    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @see AssetMaintenancesController::postEdit() method that stores the data
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     * @param Request $request
     * @param int $assetMaintenanceId
     * @version v1.0
     * @since [v1.8]
     */
    public function update(Request $request, AssetMaintenance $maintenance) : View | RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        $maintenance->supplier_id = $request->input('supplier_id');
        $maintenance->is_warranty = $request->input('is_warranty', 0);
        $maintenance->cost =  $request->input('cost');
        $maintenance->notes = $request->input('notes');
        $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
        $maintenance->title = $request->input('title');
        $maintenance->start_date = $request->input('start_date');
        $maintenance->completion_date = $request->input('completion_date');


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

        if ($maintenance->save()) {
            return redirect()->route('maintenances.index')
                            ->with('success', trans('admin/asset_maintenances/message.edit.success'));
        }

        return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
    }

    /**
    *  Delete an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $assetMaintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function destroy(AssetMaintenance $maintenance) : RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);
        // Delete the asset maintenance
        $maintenance->delete();
        // Redirect to the asset_maintenance management page
        return redirect()->route('maintenances.index')
                       ->with('success', trans('admin/asset_maintenances/message.delete.success'));
    }

    /**
    *  View an asset maintenance
    *
    * @author  Vincent Sposato <vincent.sposato@gmail.com>
    * @param int $assetMaintenanceId
    * @version v1.0
    * @since [v1.8]
    */
    public function show(AssetMaintenance $maintenance) : View | RedirectResponse
    {
        return view('asset_maintenances/view')->with('assetMaintenance', $maintenance);
    }
}
