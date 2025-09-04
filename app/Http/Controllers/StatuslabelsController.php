<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Statuslabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Status Labels for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class StatuslabelsController extends Controller
{
    /**
     * Show a list of all the statuslabels.
     */
    public function index() : View
    {
        $this->authorize('view', Statuslabel::class);
        return view('statuslabels.index');
    }

    public function show(Statuslabel $statuslabel) : View | RedirectResponse
    {
        $this->authorize('view', Statuslabel::class);
        return view('statuslabels.view')->with('statuslabel', $statuslabel);
    }

    /**
     * Statuslabel create.
     *
     */
    public function create() : View
    {
        // Show the page
        $this->authorize('create', Statuslabel::class);

        return view('statuslabels/edit')
            ->with('item', new Statuslabel)
            ->with('statuslabel_types', Helper::statusTypeList());
    }

    /**
     * Statuslabel create form processing.
     *
     * @param Request $request
     */
    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('create', Statuslabel::class);
        // create a new model instance
        $statusLabel = new Statuslabel();

        if ($request->missing('statuslabel_types')) {
            return redirect()->back()->withInput()->withErrors(['statuslabel_types' => trans('validation.statuslabel_type')]);
        }

        $statusType = Statuslabel::getStatuslabelTypesForDB($request->input('statuslabel_types'));

        // Save the Statuslabel data
        $statusLabel->name = $request->input('name');
        $statusLabel->created_by = auth()->id();
        $statusLabel->notes = $request->input('notes');
        $statusLabel->deployable = $statusType['deployable'];
        $statusLabel->pending = $statusType['pending'];
        $statusLabel->archived = $statusType['archived'];
        $statusLabel->color = $request->input('color');
        $statusLabel->show_in_nav = $request->input('show_in_nav', 0);
        $statusLabel->default_label = $request->input('default_label', 0);

        if ($statusLabel->save()) {
            // Redirect to the new Statuslabel  page
            return redirect()->route('statuslabels.index')->with('success', trans('admin/statuslabels/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($statusLabel->getErrors());
    }

    /**
     * Statuslabel update.
     *
     * @param  int $statuslabelId
     */
    public function edit(Statuslabel $statuslabel) : View | RedirectResponse
    {
        $this->authorize('update', Statuslabel::class);

        $statuslabel_types = ['' => trans('admin/hardware/form.select_statustype')] + ['undeployable' => trans('admin/hardware/general.undeployable')] + ['pending' => trans('admin/hardware/general.pending')] + ['archived' => trans('admin/hardware/general.archived')] + ['deployable' => trans('admin/hardware/general.deployable')];

        return view('statuslabels/edit', compact('statuslabel_types'))
            ->with('item', $statuslabel)
            ->with('use_statuslabel_type', $statuslabel);
    }

    /**
     * Statuslabel update form processing page.
     *
     * @param  int $statuslabelId
     */
    public function update(Request $request, Statuslabel $statuslabel) : RedirectResponse
    {
        $this->authorize('update', Statuslabel::class);

        if (! $request->filled('statuslabel_types')) {
            return redirect()->back()->withInput()->withErrors(['statuslabel_types' => trans('validation.statuslabel_type')]);
        }

        // Update the Statuslabel data
        $statustype = Statuslabel::getStatuslabelTypesForDB($request->input('statuslabel_types'));
        $statuslabel->name = $request->input('name');
        $statuslabel->notes = $request->input('notes');
        $statuslabel->deployable = $statustype['deployable'];
        $statuslabel->pending = $statustype['pending'];
        $statuslabel->archived = $statustype['archived'];
        $statuslabel->color = $request->input('color');
        $statuslabel->show_in_nav = $request->input('show_in_nav', 0);
        $statuslabel->default_label = $request->input('default_label', 0);

        // Was the asset created?
        if ($statuslabel->save()) {
            // Redirect to the saved Statuslabel page
            return redirect()->route('statuslabels.index')->with('success', trans('admin/statuslabels/message.update.success'));
        }

        return redirect()->back()->withInput()->withErrors($statuslabel->getErrors());
    }

    /**
     * Delete the given Statuslabel.
     *
     * @param  int $statuslabelId
     */
    public function destroy($statuslabelId) : RedirectResponse
    {
        $this->authorize('delete', Statuslabel::class);
        // Check if the Statuslabel exists
        if (is_null($statuslabel = Statuslabel::find($statuslabelId))) {
            return redirect()->route('statuslabels.index')->with('error', trans('admin/statuslabels/message.not_found'));
        }

        // Check that there are no assets associated
        if ($statuslabel->assets()->count() == 0) {
            $statuslabel->delete();

            return redirect()->route('statuslabels.index')->with('success', trans('admin/statuslabels/message.delete.success'));
        }

        return redirect()->route('statuslabels.index')->with('error', trans('admin/statuslabels/message.assoc_assets'));
    }
}
