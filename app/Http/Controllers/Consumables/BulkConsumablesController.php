<?php

namespace App\Http\Controllers\Consumables;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Consumable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BulkConsumablesController extends Controller
{
    /**
     * Fields admins may bulk-write across the selected rows. Mirrors
     * BulkComponentsController::EDITABLE_FIELDS. qty / supplier_id /
     * purchase_date / purchase_cost are quantity-adjustment territory
     * and stay out of bulk edit.
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
     * we route to the edit-form view, the delete-confirm view, or
     * the restore handler.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $this->authorize('view', Consumable::class);

        if (! $request->filled('ids')) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.bulk.no_consumables_selected'));
        }

        // Restore acts on soft-deleted rows, so widen the lookup with
        // withTrashed for that branch only.
        $query = Consumable::whereIn('id', $request->input('ids'));
        if ($request->input('bulk_actions') === 'restore') {
            $query->withTrashed();
        }
        $consumables = $query->get();

        if ($consumables->isEmpty()) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.bulk.no_consumables_selected'));
        }

        return match ($request->input('bulk_actions')) {
            'edit' => $this->showEdit($consumables),
            'delete' => $this->showDelete($consumables),
            'restore' => $this->doRestore($consumables),
            default => redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.bulk.no_action_selected')),
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
        $this->authorize('update', Consumable::class);

        if (! $request->filled('ids')) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.bulk.no_consumables_selected'));
        }

        $consumables = Consumable::whereIn('id', $request->input('ids'))->get();

        if ($consumables->isEmpty()) {
            return redirect()->route('consumables.index')
                ->with('error', trans('admin/consumables/message.bulk.no_consumables_selected'));
        }

        $updateData = [];
        foreach (self::EDITABLE_FIELDS as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $value = $request->input($field);

            // Treat blank selects / cleared text inputs as "don't touch".
            if ($value === '' || $value === null) {
                continue;
            }

            $updateData[$field] = $value;
        }

        // Route company_id through getIdForCurrentUser so a non-superuser
        // can't hand-craft a POST that writes a cross-tenant company_id
        // across the selection. Matches BulkComponentsController's shape.
        if (array_key_exists('company_id', $updateData)) {
            try {
                $updateData['company_id'] = Company::getIdForCurrentUser($updateData['company_id']);
            } catch (ValidationException $e) {
                return redirect()->route('consumables.index')
                    ->withErrors($e->errors())
                    ->with('error', trans('general.unauthorized'));
            }
        }

        // Opt-in null-outs via null_{field}=1 checkboxes.
        foreach (['category_id', 'company_id', 'manufacturer_id', 'location_id', 'default_supplier_id', 'min_amt'] as $field) {
            if ($request->boolean('null_'.$field)) {
                if ($field === 'category_id') {
                    continue;
                }
                $updateData[$field] = null;
            }
        }

        if (empty($updateData)) {
            return redirect()->route('consumables.index')
                ->with('warning', trans('admin/consumables/message.bulk.no_fields_updated'));
        }

        $authorized = $consumables->filter(fn (Consumable $c) => Gate::allows('update', $c));
        $skipped = $consumables->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $consumable) {
            $consumable->fill($updateData);
            if ($consumable->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $consumable->name.': '.$consumable->getErrors()->first();
        }

        $failed = count($failed_rows);
        $redirect = redirect()->route('consumables.index');

        if ($failed > 0) {
            $redirect->with('multi_error_messages', $failed_rows);
        }

        // All target rows rejected by validation. Don't dress a zero-row
        // update up as a success - the model errors tell the real story.
        if ($updated === 0 && $failed > 0) {
            return $redirect->with('error', trans_choice(
                'admin/consumables/message.bulk.update.none_updated',
                $failed,
                ['count' => $failed]
            ));
        }

        $message = trans_choice('admin/consumables/message.bulk.update.success', $updated, ['count' => $updated]);

        if ($skipped > 0) {
            $message .= ' '.trans('admin/consumables/message.bulk.skipped_unauthorized', ['count' => $skipped]);
        }

        return $failed > 0
            ? $redirect->with('warning', $message)
            : $redirect->with('success', $message);
    }

    public function destroy(Request $request)
    {
        $this->authorize('delete', Consumable::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            // Consumable uses CompanyableTrait, so Consumable::find() is
            // filtered by CompanyableScope. Cross-company rows return
            // null and fall into the "does_not_exist" bucket without
            // ever reaching authorization.
            $consumable = Consumable::find($id);
            if (is_null($consumable)) {
                $errors[] = trans('admin/consumables/message.does_not_exist', ['id' => $id]);

                continue;
            }

            // Per-row auth as belt-and-braces on top of the query scope.
            // Catches FMCS mismatches that a superuser scope-bypass could
            // let through, plus any future policy tightening (e.g. per-role
            // company-based delete gating).
            if (! Gate::allows('delete', $consumable)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            $consumable->loadCount('outstandingAssignments as outstanding_assignments_count');
            if (! $consumable->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $consumable->name,
                    'item' => trans('general.consumable'),
                ]);

                continue;
            }

            // Note: the image file is deliberately preserved across this
            // soft-delete. Snipe-IT's `snipeit:purge` command permanently
            // removes it later when the row is force-deleted. Keeping
            // the file here means a restored soft-deleted row still has
            // its image.
            $consumable->delete();
            $success_count++;
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('consumables.index')
                    ->with('success', trans_choice('admin/consumables/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('consumables.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('consumables.index')
            ->with('success', trans_choice('admin/consumables/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }

    protected function showEdit($consumables): View
    {
        $this->authorize('update', Consumable::class);

        return view('consumables.bulk-edit', [
            'consumables' => $consumables,
            'ids' => $consumables->pluck('id')->all(),
        ]);
    }

    protected function showDelete($consumables): View
    {
        $this->authorize('delete', Consumable::class);

        // Load outstanding_assignments_count so the view's isDeletable()
        // check can distinguish "no outstanding units, safe to delete"
        // from "has unconsumed distributions, show X instead of a
        // checkbox." Without this, isDeletable() falls back to a
        // per-row count query.
        $consumables->loadCount('outstandingAssignments as outstanding_assignments_count');

        return view('consumables.bulk-delete', [
            'consumables' => $consumables,
            'ids' => $consumables->pluck('id')->all(),
        ]);
    }

    /**
     * Bulk-restore handler. Restore is non-destructive, so there's no
     * confirm view: iterate the selection, call ->restore(), redirect
     * back to the Deleted index with a summary. Per-row Gate::allows
     * `delete` keeps restore on the delete-tier auth bar (matches
     * postRestore).
     */
    protected function doRestore($consumables): RedirectResponse
    {
        $this->authorize('delete', Consumable::class);

        $restored = 0;
        $errors = [];

        foreach ($consumables as $consumable) {
            if (! Gate::allows('delete', $consumable)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($consumable->deleted_at === null) {
                // Already live. Skip silently.
                continue;
            }

            if ($consumable->restore()) {
                $restored++;
            }
        }

        $redirect = redirect()->route('consumables.index', ['status' => 'Deleted']);

        if (count($errors) > 0 && $restored === 0) {
            return $redirect->with('multi_error_messages', $errors);
        }

        return $redirect
            ->with('success', trans_choice('admin/consumables/message.bulk.restore.success', $restored, ['count' => $restored]))
            ->with('multi_error_messages', $errors ?: null);
    }
}
