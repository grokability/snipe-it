<?php

namespace App\Http\Controllers;

use App\Enums\ActionType;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
    public function index(): View
    {
        $this->authorize('view', Asset::class);

        return view('maintenances.index');
    }

    /**
     *  Returns a form view to create a new asset maintenance.
     *
     * @see MaintenancesController::postCreate() method that stores the data
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     *
     * @since [v1.8]
     *
     * @return mixed
     */
    public function create(): View
    {
        $this->authorize('update', Asset::class);
        $asset = null;

        if ($asset = Asset::find(request('asset_id'))) {
            // We have to set this so that the correct property is set in the select2 ajax dropdown
            $asset->asset_id = $asset->id;
        }

        return view('maintenances/edit')
            ->with('maintenanceType', Maintenance::getImprovementOptions())
            ->with('maintenanceTypes', MaintenanceType::orderBy('name')->get())
            ->with('asset', $asset)
            ->with('item', new Maintenance);
    }

    /**
     *  Validates and stores the new asset maintenance
     *
     * @see MaintenancesController::getCreate() method for the form
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function store(ImageUploadRequest $request): RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->validateUploadedFiles($request);

        $assets = Asset::whereIn('id', $request->input('selected_assets'))->get();

        // Loop through the selected assets
        foreach ($assets as $asset) {

            if (! Company::isCurrentUserHasAccess($asset)) {
                continue;
            }

            $maintenance = new Maintenance;
            $maintenance->supplier_id = $request->input('supplier_id');
            $maintenance->is_warranty = $request->input('is_warranty');
            $maintenance->cost = $request->input('cost');
            $maintenance->notes = $request->input('notes');
            $maintenance->url = $request->input('url');

            // Save the asset maintenance data
            $maintenance->asset_id = $asset->id;
            $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
            $maintenance->maintenance_type_id = $request->input('maintenance_type_id');
            $maintenance->name = $request->input('name');
            $maintenance->start_date = $request->input('start_date');
            $maintenance->completion_date = $request->input('completion_date');
            $maintenance->responsible_party_id = $request->input('responsible_party_id') ?: auth()->id();
            $maintenance->created_by = auth()->id();

            $request->handleImages($maintenance);

            // Was the asset maintenance created?
            if (! $maintenance->save()) {
                return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
            }

            $this->storeUploadedFiles($request, $maintenance);
        }

        return redirect()->route('maintenances.index')
            ->with('success', trans('admin/maintenances/message.create.success'));

    }

    /**
     *  Returns a form view to edit a selected asset maintenance.
     *
     * @see MaintenancesController::postEdit() method that stores the data
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function edit(Maintenance $maintenance): View|RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);

        return view('maintenances/edit')
            ->with('selected_assets', $maintenance->asset->pluck('id')->toArray())
            ->with('asset_ids', request()->input('asset_ids', []))
            ->with('maintenanceType', Maintenance::getImprovementOptions())
            ->with('maintenanceTypes', MaintenanceType::orderBy('name')->get())
            ->with('item', $maintenance);
    }

    /**
     *  Validates and stores an update to an asset maintenance
     *
     * @see MaintenancesController::postEdit() method that stores the data
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @param  Request  $request
     * @param  int  $maintenanceId
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function update(ImageUploadRequest $request, Maintenance $maintenance): View|RedirectResponse
    {
        $this->authorize('update', Asset::class);
        $this->authorize('update', $maintenance->asset);
        $this->validateUploadedFiles($request);

        $maintenance->supplier_id = $request->input('supplier_id');
        $maintenance->is_warranty = $request->input('is_warranty', 0);
        $maintenance->cost = $request->input('cost');
        $maintenance->notes = $request->input('notes');
        $maintenance->asset_maintenance_type = $request->input('asset_maintenance_type');
        $maintenance->maintenance_type_id = $request->input('maintenance_type_id');
        $maintenance->name = $request->input('name');
        $maintenance->start_date = $request->input('start_date');
        $maintenance->completion_date = $request->input('completion_date');
        $maintenance->responsible_party_id = $request->input('responsible_party_id');
        $maintenance->url = $request->input('url');
        $request->handleImages($maintenance);

        if ($maintenance->save()) {
            $this->storeUploadedFiles($request, $maintenance);

            return redirect()->route('maintenances.index')
                ->with('success', trans('admin/maintenances/message.edit.success'));
        }

        return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
    }

    /**
     * Stores any generic file uploads submitted from the maintenance form.
     */
    private function storeUploadedFiles(ImageUploadRequest $request, Maintenance $maintenance): void
    {
        if (! $request->hasFile('file')) {
            return;
        }

        $objectType = 'maintenances';
        $storagePath = self::$map_storage_path[$objectType];

        if (! Storage::exists($storagePath)) {
            Storage::makeDirectory($storagePath, 775);
        }

        $uploadFileRequest = app(UploadFileRequest::class);

        foreach ((array) $request->file('file') as $file) {
            if (! $file) {
                continue;
            }

            $fileName = $uploadFileRequest->handleFile(
                $storagePath,
                self::$map_file_prefix[$objectType].'-'.$maintenance->id,
                $file
            );

            $maintenance->logUpload($fileName, $request->input('file_notes'));
        }
    }

    /**
     * Validate generic file uploads with the shared UploadFileRequest rules.
     */
    private function validateUploadedFiles(ImageUploadRequest $request): void
    {
        if (! $request->hasFile('file')) {
            return;
        }

        $uploadFileRequest = app(UploadFileRequest::class);

        Validator::make(
            array_merge($request->all(), ['file' => $request->file('file')]),
            $uploadFileRequest->rules()
        )->validate();
    }

    /**
     * Mark a maintenance record as complete, logging who completed it and when.
     */
    public function complete(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $this->authorize('update', $maintenance->asset);

        if ($maintenance->completed_at) {
            return redirect()->back()
                ->with('warning', trans('admin/maintenances/form.already_complete'));
        }

        $maintenance->completed_at = now();
        $maintenance->completed_by = auth()->id();
        $maintenance->asset_maintenance_time = (int) $maintenance->created_at->diffInDays(now(), true);
        $maintenance->saveQuietly();

        $logAction = new Actionlog;
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_by = auth()->id();
        $logAction->note = $request->input('note');
        $logAction->logaction(ActionType::MaintenanceComplete);

        return redirect()->back()
            ->with('success', trans('admin/maintenances/message.complete.success'));
    }

    /**
     *  Delete an asset maintenance
     *
     * @author  Vincent Sposato <vincent.sposato@gmail.com>
     *
     * @param  int  $maintenanceId
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function destroy(Maintenance $maintenance): RedirectResponse
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
     *
     * @param  int  $maintenanceId
     *
     * @version v1.0
     *
     * @since [v1.8]
     */
    public function show(Maintenance $maintenance): View|RedirectResponse
    {
        $this->authorize('view', $maintenance->asset);

        return view('maintenances.view')->with('maintenance', $maintenance);
    }
}
