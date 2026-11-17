<?php

namespace App\Http\Controllers\Accessories;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BulkAccessoriesController extends Controller
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
     * we fan out to the edit-form view, the delete-confirm view, or
     * the restore handler.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $this->authorize('view', Accessory::class);

        if (! $request->filled('ids')) {
            return redirect()->route('accessories.index')
                ->with('error', trans('admin/accessories/message.bulk.no_accessories_selected'));
        }

        // Restore acts on soft-deleted rows, so widen the lookup with
        // withTrashed for that branch only.
        $query = Accessory::whereIn('id', $request->input('ids'));
        if ($request->input('bulk_actions') === 'restore') {
            $query->withTrashed();
        }
        $accessories = $query->get();

        if ($accessories->isEmpty()) {
            return redirect()->route('accessories.index')
                ->with('error', trans('admin/accessories/message.bulk.no_accessories_selected'));
        }

        return match ($request->input('bulk_actions')) {
            'edit' => $this->showEdit($accessories),
            'delete' => $this->showDelete($accessories),
            'restore' => $this->doRestore($accessories),
            default => redirect()->route('accessories.index')
                ->with('error', trans('admin/accessories/message.bulk.no_action_selected')),
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
        $this->authorize('update', Accessory::class);

        if (! $request->filled('ids')) {
            return redirect()->route('accessories.index')
                ->with('error', trans('admin/accessories/message.bulk.no_accessories_selected'));
        }

        $accessories = Accessory::whereIn('id', $request->input('ids'))->get();

        if ($accessories->isEmpty()) {
            return redirect()->route('accessories.index')
                ->with('error', trans('admin/accessories/message.bulk.no_accessories_selected'));
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
                return redirect()->route('accessories.index')
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
            return redirect()->route('accessories.index')
                ->with('warning', trans('admin/accessories/message.bulk.no_fields_updated'));
        }

        $authorized = $accessories->filter(fn (Accessory $a) => Gate::allows('update', $a));
        $skipped = $accessories->count() - $authorized->count();

        $updated = 0;
        $failed_rows = [];
        foreach ($authorized as $accessory) {
            $accessory->fill($updateData);
            if ($accessory->save()) {
                $updated++;

                continue;
            }
            $failed_rows[] = $accessory->name.': '.$accessory->getErrors()->first();
        }

        $failed = count($failed_rows);
        $redirect = redirect()->route('accessories.index');

        if ($failed > 0) {
            $redirect->with('multi_error_messages', $failed_rows);
        }

        // All target rows rejected by validation. Don't dress a zero-row
        // update up as a success - the model errors tell the real story.
        if ($updated === 0 && $failed > 0) {
            return $redirect->with('error', trans_choice(
                'admin/accessories/message.bulk.update.none_updated',
                $failed,
                ['count' => $failed]
            ));
        }

        $message = trans_choice('admin/accessories/message.bulk.update.success', $updated, ['count' => $updated]);

        if ($skipped > 0) {
            $message .= ' '.trans('admin/accessories/message.bulk.skipped_unauthorized', ['count' => $skipped]);
        }

        return $failed > 0
            ? $redirect->with('warning', $message)
            : $redirect->with('success', $message);
    }

    public function destroy(Request $request)
    {
        $this->authorize('delete', Accessory::class);

        $errors = [];
        $success_count = 0;

        foreach ((array) $request->input('ids', []) as $id) {
            // Accessory uses CompanyableTrait, so Accessory::find() is
            // filtered by CompanyableScope. Cross-company rows return
            // null and fall into the "does_not_exist" bucket without
            // ever reaching authorization.
            $accessory = Accessory::find($id);
            if (is_null($accessory)) {
                $errors[] = trans('admin/accessories/message.does_not_exist', ['id' => $id]);

                continue;
            }

            // Per-row auth as belt-and-braces on top of the query scope.
            // Catches FMCS mismatches that a superuser scope-bypass could
            // let through, plus any future policy tightening (e.g. per-role
            // company-based delete gating).
            if (! Gate::allows('delete', $accessory)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            $accessory->loadCount('checkouts as checkouts_count');
            if (! $accessory->isDeletable()) {
                $errors[] = trans('general.bulk_delete_associations.assoc_checkouts_no_count', [
                    'item_name' => $accessory->name,
                    'item' => trans('general.accessory'),
                ]);

                continue;
            }

            // Note: the image file is deliberately preserved across this
            // soft-delete. Snipe-IT's `snipeit:purge` command permanently
            // removes it later when the row is force-deleted. Keeping
            // the file here means a restored soft-deleted row still has
            // its image.
            $accessory->delete();
            $success_count++;
        }

        if (count($errors) > 0) {
            if ($success_count > 0) {
                return redirect()->route('accessories.index')
                    ->with('success', trans_choice('admin/accessories/message.delete.partial_success', $success_count, ['count' => $success_count]))
                    ->with('multi_error_messages', $errors);
            }

            return redirect()->route('accessories.index')->with('multi_error_messages', $errors);
        }

        return redirect()->route('accessories.index')
            ->with('success', trans_choice('admin/accessories/message.delete.bulk_success', $success_count, ['count' => $success_count]));
    }

    protected function showEdit($accessories): View
    {
        $this->authorize('update', Accessory::class);

        return view('accessories.bulk-edit', [
            'accessories' => $accessories,
            'ids' => $accessories->pluck('id')->all(),
        ]);
    }

    protected function showDelete($accessories): View
    {
        $this->authorize('delete', Accessory::class);

        // Load checkouts_count so the view's isDeletable() check can
        // distinguish "no active checkouts, safe to delete" from
        // "has checkouts, show X instead of a checkbox." Without this,
        // checkouts_count is null and isDeletable()'s === 0 check fails
        // for every row, so every row renders as undeletable.
        $accessories->loadCount('checkouts as checkouts_count');

        return view('accessories.bulk-delete', [
            'accessories' => $accessories,
            'ids' => $accessories->pluck('id')->all(),
        ]);
    }

    /**
     * Bulk-restore handler. Restore is non-destructive, so there's no
     * confirm view: iterate the selection, call ->restore(), redirect
     * back to the Deleted index with a summary. Per-row Gate::allows
     * `delete` keeps restore on the delete-tier auth bar (matches
     * postRestore).
     */
    protected function doRestore($accessories): RedirectResponse
    {
        $this->authorize('delete', Accessory::class);

        $restored = 0;
        $errors = [];

        foreach ($accessories as $accessory) {
            if (! Gate::allows('delete', $accessory)) {
                $errors[] = trans('general.unauthorized');

                continue;
            }

            if ($accessory->deleted_at === null) {
                // Already live. Skip silently.
                continue;
            }

            if ($accessory->restore()) {
                $restored++;
            }
        }

        $redirect = redirect()->route('accessories.index', ['status' => 'Deleted']);

        if (count($errors) > 0 && $restored === 0) {
            return $redirect->with('multi_error_messages', $errors);
        }

        return $redirect
            ->with('success', trans_choice('admin/accessories/message.bulk.restore.success', $restored, ['count' => $restored]))
            ->with('multi_error_messages', $errors ?: null);
    }
}
