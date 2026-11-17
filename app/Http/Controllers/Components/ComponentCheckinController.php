<?php

namespace App\Http\Controllers\Components;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Component;
use App\Models\ComponentAssignment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ComponentCheckinController extends Controller
{
    /**
     * Returns a view that allows the checkin of a component from an asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentCheckinController::store() method that stores the data.
     * @since [v4.1.4]
     *
     * @return View
     *
     * @throws AuthorizationException
     */
    public function create($component_asset_id)
    {

        if ($component_assets = ComponentAssignment::find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {
                return redirect()->route('components.index')->with('error', trans('admin/components/messages.not_found'));
            }
            if (is_null($asset = Asset::find($component_assets->asset_id))) {
                return redirect()->route('components.index')->with('error',
                    trans('admin/components/message.not_found'));
            }
            $this->authorize('checkin', $component);

            $checkoutBy = $component_assets->created_by
                ? User::withTrashed()->find($component_assets->created_by)
                : null;

            return view('components/checkin', compact('component_assets', 'component', 'asset'))
                ->with('snipe_component', $component)
                ->with('checkoutDate', $component_assets->created_at)
                ->with('checkoutBy', $checkoutBy);
        }

        return redirect()->route('components.index')->with('error', trans('admin/components/messages.not_found'));
    }

    /**
     * Validate and store checkin data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ComponentCheckinController::create() method that returns the form.
     * @since [v4.1.4]
     *
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(Request $request, $component_asset_id, $backto = null)
    {
        if ($component_assets = DB::table('components_assets')->find($component_asset_id)) {
            if (is_null($component = Component::find($component_assets->component_id))) {

                return redirect()->route('components.index')->with('error',
                    trans('admin/components/message.not_found'));
            }

            $this->authorize('checkin', $component);

            $max_to_checkin = $component_assets->assigned_qty;
            $validator = Validator::make($request->all(), [
                'checkin_qty' => "required|numeric|between:1,$max_to_checkin",
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validation passed, so let's figure out what we have to do here.
            $qty_remaining_in_checkout = ($component_assets->assigned_qty - (int) $request->input('checkin_qty'));

            // We have to modify the record to reflect the new qty that's
            // actually checked out.
            $component_assets->assigned_qty = $qty_remaining_in_checkout;
            DB::table('components_assets')->where('id',
                $component_asset_id)->update(['assigned_qty' => $qty_remaining_in_checkout]);

            // If the checked-in qty is exactly the same as the assigned_qty,
            // we can simply delete the associated components_assets record
            if ($qty_remaining_in_checkout == 0) {
                DB::table('components_assets')->where('id', '=', $component_asset_id)->delete();
            }

            $asset = Asset::find($component_assets->asset_id);

            event(new CheckoutableCheckedIn($component, $asset, auth()->user(), $request->input('note'), Carbon::now()));

            session()->put(['redirect_option' => $request->input('redirect_option')]);

            return Helper::getRedirectOption($request, $component->id, 'Components')
                ->with('success', trans('admin/components/message.checkin.success'));
        }

        return redirect()->route('components.index')->with('error', trans('admin/components/message.does_not_exist'));
    }

    /**
     * Render the bulk-checkin interstitial for component pivot rows.
     * The caller ticks rows on the component show page's "Assigned"
     * table and posts their components_assets ids here. The view lists
     * the selections and collects a single shared note applied to
     * every row's checkin.
     */
    public function bulkCheckinShow(Request $request): View|RedirectResponse
    {
        $this->authorize('checkin', Component::class);

        $pivotIds = array_filter((array) $request->input('ids', []));

        if (empty($pivotIds)) {
            return redirect()->back()
                ->with('warning', trans('admin/components/message.bulk.checkin_selected.no_rows_selected'));
        }

        $assignments = $this->scopedAssignmentsForBulkCheckin($pivotIds);

        if ($assignments->isEmpty()) {
            return redirect()->back()
                ->with('warning', trans('admin/components/message.bulk.checkin_selected.no_rows_selected'));
        }

        // ComponentAssignment's `assets()` is a hasMany that returns at
        // most one row (asset_id → Asset.id). Resolve the single Asset
        // up front so the view doesn't need an inline lookup.
        $assetIds = $assignments->pluck('asset_id')->filter()->unique()->all();
        $assetsById = Asset::withTrashed()->whereIn('id', $assetIds)->get()->keyBy('id');
        foreach ($assignments as $assignment) {
            $assignment->setRelation('resolvedAsset', $assetsById->get($assignment->asset_id));
        }

        return view('components.bulk-checkin', [
            'assignments' => $assignments,
            'ids' => $assignments->pluck('id')->all(),
            'return_url' => Helper::sameOriginUrl(URL::previous()) ?? route('components.index'),
        ]);
    }

    /**
     * Apply the bulk checkin after the admin confirms on the
     * interstitial. Each pivot row is fully checked back in (not
     * partial-qty like the single-row form). The shared `note` from
     * the form rides every CheckoutableCheckedIn event.
     */
    public function bulkCheckinStore(Request $request): RedirectResponse
    {
        $this->authorize('checkin', Component::class);

        $redirectTo = Helper::sameOriginUrl($request->input('return_url')) ?? route('components.index');

        $pivotIds = array_filter((array) $request->input('ids', []));

        if (empty($pivotIds)) {
            return redirect($redirectTo)
                ->with('warning', trans('admin/components/message.bulk.checkin_selected.no_rows_selected'));
        }

        $assignments = $this->scopedAssignmentsForBulkCheckin($pivotIds);
        $note = $request->input('note');

        // Count returned ROWS and total UNITS separately. Each pivot
        // carries an assigned_qty, so "3 rows checked in" might really
        // mean "3 rows totaling 25 units back in inventory." The
        // success message surfaces both so the operator can reconcile
        // against what they expected to return.
        $rowCount = 0;
        $unitCount = 0;
        foreach ($assignments as $assignment) {
            $component = $assignment->component;
            if (! $component || ! Gate::allows('checkin', $component)) {
                continue;
            }

            // ComponentAssignment has no belongsTo asset relation
            // (only a quirky `assets()` hasMany), so fetch the
            // Asset directly by its pivot column. withTrashed so a
            // soft-deleted asset doesn't break the checkin event.
            $asset = Asset::withTrashed()->find($assignment->asset_id);
            $qty = (int) $assignment->assigned_qty;

            if ($assignment->delete()) {
                event(new CheckoutableCheckedIn(
                    $component,
                    $asset,
                    auth()->user(),
                    $note,
                    Carbon::now(),
                ));
                $rowCount++;
                $unitCount += $qty;
            }
        }

        return redirect($redirectTo)
            ->with('success', trans_choice('admin/components/message.bulk.checkin_selected.success', $rowCount, [
                'count' => $rowCount,
                'unit_count' => $unitCount,
            ]));
    }

    /**
     * Defense in depth for FMCS / floater. ComponentAssignment doesn't
     * carry CompanyableTrait, so a cross-company pivot id POSTed by
     * hand would reach the loop and rely purely on the per-row Gate
     * check. Narrow the lookup to pivots whose parent Component is
     * reachable via a subquery against Component's auto-applied
     * CompanyableScope.
     *
     * @param  array<int, mixed>  $pivotIds
     * @return \Illuminate\Database\Eloquent\Collection<int, ComponentAssignment>
     */
    protected function scopedAssignmentsForBulkCheckin(array $pivotIds)
    {
        return ComponentAssignment::with('component')
            ->whereIn('id', $pivotIds)
            ->whereIn('component_id', Component::query()->select('id'))
            ->get();
    }
}
