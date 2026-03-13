<?php

namespace App\Http\Controllers\Kits;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\PredefinedKit;
use App\Models\PredefinedLicence;
use App\Models\PredefinedModel;
use Illuminate\Http\Request;

/**
 * This controller handles all access kits management:
 * list, add/remove/change
 *
 * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
 */
class PredefinedKitsController extends Controller
{
    /**
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', PredefinedKit::class);

        return view('kits/index');
    }

    /**
     *  Returns a form view to create a new kit.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return mixed
     */
    public function create()
    {
        $this->authorize('create', PredefinedKit::class);

        return view('kits/create')->with('item', new PredefinedKit);
    }

    /**
     * Validate and process the new Predefined Kit data.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize('create', PredefinedKit::class);
        // Create a new Predefined Kit
        $kit = new PredefinedKit;
        $kit->name = $request->input('name');
        $kit->created_by = auth()->id();

        if (! $kit->save()) {
            return redirect()->back()->withInput()->withErrors($kit->getErrors());
        }
        $success = $kit->save();
        if (! $success) {
            return redirect()->back()->withInput()->withErrors($kit->getErrors());
        }

        return redirect()->route('kits.index')->with('success', trans('admin/kits/general.kit_created'));
    }

    /**
     * Returns a view containing the Predefined Kit edit form.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @since [v1.0]
     * @param int $kit_id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(PredefinedKit $kit)
    {
        $this->authorize('update', PredefinedKit::class);

            return view('kits/edit')
                ->with('item', $kit)
                ->with('models', $kit->models)
                ->with('licenses', $kit->licenses);

    }

    /**
     * Validates and processes form data from the edit
     * Predefined Kit form based on the kit ID passed.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @since [v1.0]
     * @param int $kit_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ImageUploadRequest $request, PredefinedKit $kit)
    {
        $this->authorize('update', PredefinedKit::class);
        $kit->name = $request->input('name');

        if ($kit->save()) {
            return redirect()->route('kits.index')->with('success', trans('admin/kits/general.kit_updated'));
        }

        return redirect()->back()->withInput()->withErrors($kit->getErrors());
    }

    /**
     * Validate and delete the given Predefined Kit.
     * Also delete all contained helping items
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @since [v1.0]
     * @param int $kit_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($kit_id)
    {
        $this->authorize('delete', PredefinedKit::class);
        // Check if the kit exists
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_not_found'));
        }

        // Delete childs
        $kit->models()->detach();
        $kit->licenses()->detach();
        $kit->consumables()->detach();
        $kit->accessories()->detach();
        // Delete the kit
        $kit->delete();

        // Redirect to the kit management page
        return redirect()->route('kits.index')->with('success', trans('admin/kits/general.kit_deleted'));
    }

    /**
     * Get the kit information to present to the kit view page
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @since [v1.0]
     * @param int $modelId
     * @return \Illuminate\Contracts\View\View
     */
    public function show(PredefinedKit $kit)
    {
        return $this->edit($kit);
    }

    /**
     * Returns a view containing the Predefined Kit edit form.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @return \Illuminate\Contracts\View\View
     */
    public function editModel($kit_id, $model_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (($kit = PredefinedKit::find($kit_id))
            && ($model = $kit->models()->find($model_id))) {
            return view('kits/model-edit', [
                'kit' => $kit,
                'model' => $model,
                'item' => $model->pivot,
            ]);
        }

        return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
    }

    /**
     * Get the kit information to present to the kit view page
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $modelId
     * @return \Illuminate\Contracts\View\View
     */
    public function updateModel(Request $request, $kit_id, $model_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        $validator = \Validator::make($request->all(), $kit->makeModelRules($model_id));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $pivot = $kit->models()->wherePivot('id', $request->input('pivot_id'))->first()->pivot;

        $pivot->model_id = $request->input('model_id');
        $pivot->quantity = $request->input('quantity');
        $pivot->save();

        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.kit_model_updated'));
    }

    /**
     * Remove the model from set
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $modelId
     * @return \Illuminate\Contracts\View\View
     */
    public function detachModel($kit_id, $model_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        // Delete childs
        $kit->models()->detach($model_id);

        // Redirect to the kit management page
        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.kit_model_detached'));
    }

    /**
     * Returns a view containing attached license edit form.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $license_id
     * @return \Illuminate\Contracts\View\View
     */
    public function editLicense($kit_id, $license_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (! ($kit = PredefinedKit::find($kit_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }
        if (! ($license = $kit->licenses()->find($license_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.license_none'));
        }

        return view('kits/license-edit', [
            'kit' => $kit,
            'license' => $license,
            'item' => $license->pivot,
        ]);
    }

    /**
     * Update attached licese
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $license_id
     * @return \Illuminate\Contracts\View\View
     */
    public function updateLicense(Request $request, $kit_id, $license_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        $validator = \Validator::make($request->all(), $kit->makeLicenseRules($license_id));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $pivot = $kit->licenses()->wherePivot('id', $request->input('pivot_id'))->first()->pivot;

        $pivot->license_id = $request->input('license_id');
        $pivot->quantity = $request->input('quantity');
        $pivot->save();

        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.license_updated'));
    }

    /**
     * Remove the license from set
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $license_id
     * @return \Illuminate\Contracts\View\View
     */
    public function detachLicense($kit_id, $license_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        // Delete childs
        $kit->licenses()->detach($license_id);

        // Redirect to the kit management page
        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.license_detached'));
    }

    /**
     * Returns a view containing attached accessory edit form.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $accessoryId
     * @return \Illuminate\Contracts\View\View
     */
    public function editAccessory($kit_id, $accessory_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (! ($kit = PredefinedKit::find($kit_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }
        if (! ($accessory = $kit->accessories()->find($accessory_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.accessory_none'));
        }

        return view('kits/accessory-edit', [
            'kit' => $kit,
            'accessory' => $accessory,
            'item' => $accessory->pivot,
        ]);
    }

    /**
     * Update attached accessory
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $accessory_id
     * @return \Illuminate\Contracts\View\View
     */
    public function updateAccessory(Request $request, $kit_id, $accessory_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        $validator = \Validator::make($request->all(), $kit->makeAccessoryRules($accessory_id));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $pivot = $kit->accessories()->wherePivot('id', $request->input('pivot_id'))->first()->pivot;

        $pivot->accessory_id = $request->input('accessory_id');
        $pivot->quantity = $request->input('quantity');
        $pivot->save();

        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.accessory_updated'));
    }

    /**
     * Remove the accessory from set
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $accessory_id
     * @return \Illuminate\Contracts\View\View
     */
    public function detachAccessory($kit_id, $accessory_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        // Delete childs
        $kit->accessories()->detach($accessory_id);

        // Redirect to the kit management page
        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.accessory_detached'));
    }

    /**
     * Returns a view containing attached consumable edit form.
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $consumable_id
     * @return \Illuminate\Contracts\View\View
     */
    public function editConsumable($kit_id, $consumable_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (! ($kit = PredefinedKit::find($kit_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }
        if (! ($consumable = $kit->consumables()->find($consumable_id))) {
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.consumable_none'));
        }

        return view('kits/consumable-edit', [
            'kit' => $kit,
            'consumable' => $consumable,
            'item' => $consumable->pivot,
        ]);
    }

    /**
     * Update attached consumable
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $kit_id
     * @param int $consumableId
     * @return \Illuminate\Contracts\View\View
     */
    public function updateConsumable(Request $request, $kit_id, $consumable_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        $validator = \Validator::make($request->all(), $kit->makeConsumableRules($consumable_id));

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $pivot = $kit->consumables()->wherePivot('id', $request->input('pivot_id'))->first()->pivot;

        $pivot->consumable_id = $request->input('consumable_id');
        $pivot->quantity = $request->input('quantity');
        $pivot->save();

        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.consumable_updated'));
    }

    /**
     * Remove the consumable from set
     *
     * @author [D. Minaev] [<dmitriy.minaev.v@gmail.com>]
     * @param int $consumable_id
     * @return \Illuminate\Contracts\View\View
     */
    public function detachConsumable($kit_id, $consumable_id)
    {
        $this->authorize('update', PredefinedKit::class);
        if (is_null($kit = PredefinedKit::find($kit_id))) {
            // Redirect to the kits management page
            return redirect()->route('kits.index')->with('error', trans('admin/kits/general.kit_none'));
        }

        // Delete childs
        $kit->consumables()->detach($consumable_id);

        // Redirect to the kit management page
        return redirect()->route('kits.edit', $kit_id)->with('success', trans('admin/kits/general.consumable_detached'));
    }
}
