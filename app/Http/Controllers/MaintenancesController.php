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
            $maintenance->expected_completion_date = $request->input('expected_completion_date', $request->input('completion_date'));
            $maintenance->responsible_party_id = $request->input('responsible_party_id') ?: auth()->id();
            $maintenance->created_by = auth()->id();

            // Backfilled completion: user is recording a maintenance that
            // was already finished. Same transition logic as update() —
            // null-blank submissions stay null, a supplied date marks the
            // row complete and stamps who did it plus how long it took.
            $shouldLogComplete = $this->applyCompletionState(
                $maintenance,
                $request->filled('completed_at') ? $request->input('completed_at') : null,
                wasCompletedBefore: false,
            );

            $request->handleImages($maintenance);

            // Was the asset maintenance created?
            if (! $maintenance->save()) {
                return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
            }

            if ($shouldLogComplete) {
                $this->logMaintenanceCompleteAction($maintenance);
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
        $maintenance->expected_completion_date = $request->input('expected_completion_date', $request->input('completion_date'));
        $maintenance->responsible_party_id = $request->input('responsible_party_id');
        $maintenance->url = $request->input('url');
        $request->handleImages($maintenance);

        $shouldLogComplete = $this->applyCompletionState(
            $maintenance,
            $request->filled('completed_at') ? $request->input('completed_at') : null,
            wasCompletedBefore: $maintenance->completed_at !== null,
        );

        if ($maintenance->save()) {
            $this->storeUploadedFiles($request, $maintenance);

            if ($shouldLogComplete) {
                $this->logMaintenanceCompleteAction($maintenance);
            }

            return redirect()->route('maintenances.index')
                ->with('success', trans('admin/maintenances/message.edit.success'));
        }

        return redirect()->back()->withInput()->withErrors($maintenance->getErrors());
    }

    /**
     * Apply the null / date / cleared transitions of `completed_at` on
     * either create or edit. Returns true when the transition is
     * "not-completed → completed" so the caller knows to fire the
     * MaintenanceComplete action log AFTER the row is saved (and thus
     * has an id).
     *
     * asset_maintenance_time uses start_date → completed_at (not
     * created_at → completed_at). For a real-time completion the two are
     * effectively the same; for a backfill (start_date well in the past,
     * created_at just now) using start_date is the truthful duration of
     * the actual maintenance work.
     */
    private function applyCompletionState(Maintenance $maintenance, ?string $submittedCompletedAt, bool $wasCompletedBefore): bool
    {
        if ($submittedCompletedAt !== null) {
            $maintenance->completed_at = $submittedCompletedAt;
            if (! $wasCompletedBefore) {
                $maintenance->completed_by = auth()->id();
                $maintenance->asset_maintenance_time = $this->computeMaintenanceDurationDays($maintenance);

                return true;
            }

            return false;
        }

        $maintenance->completed_at = null;
        $maintenance->completed_by = null;
        $maintenance->asset_maintenance_time = null;

        return false;
    }

    /**
     * Days between start_date and completed_at, absolute so ordering of
     * inputs doesn't matter. Falls back to now() for start_date when the
     * caller somehow submitted a completion without a start (shouldn't
     * happen — start_date is required by the model — but guards against
     * a null-deref if a future validation change lets one through).
     */
    private function computeMaintenanceDurationDays(Maintenance $maintenance): int
    {
        $start = $maintenance->start_date ?? now();

        return (int) $start->diffInDays($maintenance->completed_at, true);
    }

    private function logMaintenanceCompleteAction(Maintenance $maintenance): void
    {
        $logAction = new Actionlog;
        $logAction->item_type = Maintenance::class;
        $logAction->item_id = $maintenance->id;
        $logAction->target_type = Asset::class;
        $logAction->target_id = $maintenance->asset_id;
        $logAction->created_by = auth()->id();
        $logAction->logaction(ActionType::MaintenanceComplete);
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
