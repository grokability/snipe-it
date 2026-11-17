<?php

namespace App\Http\Controllers\Components;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BulkComponentsController extends Controller
{
    /**
     * Fields admins may bulk-write across the selected rows. Everything
     * else on the Component model is excluded. qty / supplier_id /
     * purchase_date / purchase_cost are quantity-adjustment territory
     * and stay out of bulk edit (see Component::$fillable).
     *
     * @var array<int, string>
     */
    public const EDITABLE_FIELDS = [
        'category_id',
        'company_id',
        'manufacturer_id',
        'location_id',
        'default_supplier_id',
        'min_amt',
        'requestable',
        'archived',
    ];

    /**
     * Dispatcher. The index POSTs here with ids[] + bulk_actions, and
     * we fan out to the edit-form view or the delete-confirm view.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $this->authorize('view', Component::class);

        if (! $request->filled('ids')) {
            return redirect()->route('components.index')
                ->with('error', trans('admin/components/message.bulk.no_components_selected'));
        }

        // Restore acts on soft-deleted rows, so widen the lookup with
        // withTrashed for that branch only. The CompanyableScope still
        // applies via the auto-scope on Component, so cross-tenant ids
        // continue to drop.
        $query = Component::whereIn('id', $request->input('ids'));
        if ($request->input('bulk_actions') === 'restore') {
            $query->withTrashed();
        }
        $components = $query->get();

        if ($components->isEmpty()) {
            return redirect()->route('components.index')
                ->with('error', trans('admin/components/message.bulk.no_components_selected'));
        }

        return match ($request->input('bulk_actions')) {
            'edit' => $this->showEdit($components),
            'delete' => $this->showDelete($components),
            'restore' => $this->doRestore($components),
            default => redirect()->route('components.index')
                ->with('error', trans('admin/components/message.bulk.no_action_selected')),
        };
    }

    /**
     * Apply the bulk-edit form. Each editable field is written only
     * when the admin provided a value (empty fields are left alone),
     * so admins can tweak just the fields they care about without
     * flattening everything else to blank.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', Component::class);

        if (! $request->filled('ids')) {
            return redirect()->route('components.index')
                ->with('error', trans('admin/components/message.bulk.no_components_selected'));
        }

        $components = Component::whereIn('id', $request->input('ids'))->get();

        if ($components->isEmpty()) {
            return redirect()->route('components.index')
                ->with('error', trans('admin/components/message.bulk.no_components_selected'));
        }

        $updateData = [];
        foreach (self::EDITABLE_FIELDS as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $value = $request->input($field);

            // Treat blank selects / cleared text inputs as "don't touch".
            // The dedicated null_* checkboxes (below) are the opt-in to
            // actually clear a column.
            if ($value === '' || $value === null) {
                continue;
            }

            $updateData[$field] = $value;
        }

        // Route company_id through getIdForCurrentUser so a non-superuser
        // can't hand-craft a POST that writes a cross-tenant company_id
        // across the selection. Mirrors the single-component edit path at
        // ComponentsController::update. Throws ValidationException on a
        // submitted-but-foreign company, which lets the request fail with
        // a proper field error rather than a silent nullout.
        if (array_key_exists('company_id', $updateData)) {
            try {
                $updateData['company_id'] = Company::getIdForCurrentUser($updateData['company_id']);
            } catch (ValidationException $e) {
                return redirect()->route('components.index')
                    ->withErrors($e->errors())
                    ->with('error', trans('general.unauthorized'));
            }
        }

        // Opt-in null-outs. Checkbox named null_{field}=1 clears that
        // column across the selection, giving admins a way to blank out
        // nullable columns like location/supplier/manufacturer.
        foreach (['category_id', 'company_id', 'manufacturer_id', 'location_id', 'default_supplier_id', 'min_amt'] as $field) {
            if ($request->boolean('null_'.$field)) {
                // category_id is NOT NULL on the schema (component rows
                // without a category are unusable), so skip clearing it.
                if ($field === 'category_id') {
                    continue;
                }
                $updateData[$field] = null;
            }
        }

        if (empty($updateData)) {
            return redirect()->route('components.index')
                ->with('warning', trans('admin/components/message.bulk.no_fields_updated'));
        }

        $authorized = $components->filter(fn (Component $c) => Gate::allows('update', $c));
        $skipped = $components->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $component) {
            $component->fill($updateData);
            if ($component->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $component->name.': '.$component->getErrors()->first();
        }

        $failed = count($failed_rows);
        $redirect = redirect()->route('components.index');

        if ($failed > 0) {
            $redirect->with('multi_error_messages', $failed_rows);
        }

        // All target rows rejected by validation. Don't dress a zero-row
        // update up as a success - the model errors tell the real story.
        if ($updated === 0 && $failed > 0) {
            return $redirect->with('error', trans_choice(
                'admin/components/message.bulk.update.none_updated',
                $failed,
                ['count' => $failed]
            ));
        }

        $message = trans_choice('admin/components/message.bulk.update.success', $updated, ['count' => $updated]);

        if ($skipped > 0) {
            $message .= ' '.trans('admin/components/message.bulk.skipped_unauthorized', ['count' => $skipped]);
        }

        return $failed > 0
            ? $redirect->with('warning', $message)
            : $redirect->with('success', $message);
    }

    /**
     * Soft-delete each selected row. Per-row gating via isDeletable
     * mirrors BulkAccessoriesController: a selected-but-undeletable
     * row (e.g. a component with checkouts) is reported in the error
     * bucket instead of silently skipped.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorize('delete', Component::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            $component = Component::find($id);
            if (is_null($component)) {
                $errors[] = trans('admin/components/message.does_not_exist', ['id' => $id]);

                continue;
            }

            if (! Gate::allows('delete', $component)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if (! $component->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $component->name,
                    'item' => trans('general.component'),
                ]);

                continue;
            }

            $component->delete();
            $success_count++;
        }

        $redirect = redirect()->route('components.index');

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return $redirect
                    ->with('success', trans_choice('admin/components/message.bulk.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return $redirect->with('multi_error_messages', $errors);
        }

        return $redirect->with('success', trans_choice('admin/components/message.bulk.delete.success', $success_count, ['count' => $success_count]));
    }

    protected function showEdit($components): View
    {
        $this->authorize('update', Component::class);

        return view('components.bulk-edit', [
            'components' => $components,
            'ids' => $components->pluck('id')->all(),
        ]);
    }

    protected function showDelete($components): View
    {
        $this->authorize('delete', Component::class);

        return view('components.bulk-delete', [
            'components' => $components,
            'ids' => $components->pluck('id')->all(),
        ]);
    }

    /**
     * Bulk-restore handler. Restore is non-destructive, so there's no
     * confirm view: iterate the selection, call ->restore(), redirect
     * back to the Deleted index with a summary. Per-row Gate::allows
     * `delete` keeps restore on the delete-tier auth bar (matches
     * postRestore + AssetModelsController).
     */
    protected function doRestore($components): RedirectResponse
    {
        $this->authorize('delete', Component::class);

        $restored = 0;
        $errors = [];

        foreach ($components as $component) {
            if (! Gate::allows('delete', $component)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($component->deleted_at === null) {
                // Already live. Skip without erroring so a stale
                // selection doesn't blow up the whole batch.
                continue;
            }

            if ($component->restore()) {
                $restored++;
            }
        }

        $redirect = redirect()->route('components.index', ['status' => 'Deleted']);

        if (count($errors) > 0 && $restored === 0) {
            return $redirect->with('multi_error_messages', $errors);
        }

        return $redirect
            ->with('success', trans_choice('admin/components/message.bulk.restore.success', $restored, ['count' => $restored]))
            ->with('multi_error_messages', $errors ?: null);
    }
}
