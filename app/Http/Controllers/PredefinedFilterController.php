<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\PredefinedFilter;
use Illuminate\Support\Arr;


class PredefinedFilterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PredefinedFilter::class);  // TODO: needed? or should everyone be able to create

        $rules = (new PredefinedFilter)->getRules();
        $validated = $request->validate($rules);

        $predefined_filter = PredefinedFilter::create(array_merge(
            $validated,
            ['created_by' => $request->user()->id]
        ));

        session()->flash('success', __('admin/reports/message.create.success'));

        // Weiterleitung zur Detailseite (oder Übersicht)
        return redirect()->route('/hardware', $predefined_filter->id);  // TODO: Return hardware view
    }
}
